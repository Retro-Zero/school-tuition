<?php

namespace App\Queries;

use App\Models\FinancialYear;
use App\Models\Payment;
use App\Models\Student;
use App\Services\TuitionSummary;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The annual report screen, cached.
 *
 * The key changes whenever a student or payment is added, so the cache invalidates
 * itself without anyone remembering to clear it.
 */
class ReportSummary
{
    /** Bump when the shape of data() changes, so old cache entries are ignored. */
    private const CACHE_VERSION = 2;

    public function __construct(private readonly FinancialYear $year) {}

    public function data(): array
    {
        return Cache::remember($this->cacheKey(), now()->addMinutes(10), fn () => $this->compute());
    }

    private function cacheKey(): string
    {
        $students = Student::forYear($this->year->id)->count();
        $lastPayment = (int) Payment::where('financial_year_id', $this->year->id)->max('id');
        $touched = (string) $this->year->updated_at?->timestamp;

        return 'report:v'.self::CACHE_VERSION.":{$this->year->id}:{$students}:{$lastPayment}:{$touched}";
    }

    /** @return array{students: Collection, summary: TuitionSummary, debtors: Collection, discounts: Collection, olympiadStudents: Collection, carryOverStudents: Collection} */
    private function compute(): array
    {
        $students = (new StudentQuery($this->year))->all();

        $summary = new TuitionSummary;
        foreach ($students as $student) {
            $summary->add($student->tuition, (bool) $student->is_active);
        }

        $debtors = $students
            ->filter(fn ($s) => $s->tuition->balance() > 0)
            ->sortByDesc(fn ($s) => $s->tuition->balance())
            ->values();

        return [
            'students' => $students,
            'summary' => $summary,
            'debtors' => $debtors,
            'discounts' => $students->filter(fn ($s) => $s->coupon_id !== null)->values(),
            'olympiadStudents' => $students->filter(fn ($s) => $s->olympiad_amount > 0)->values(),
            'carryOverStudents' => $students->filter(fn ($s) => $s->carry_over_debt != 0)->values(),
            'byGrade' => $this->group($students, fn ($s) => $s->gradeLevel?->name ?? '—'),
            'byField' => $this->group($students, fn ($s) => $s->studyField?->name ?? '—'),
            'byClass' => $this->group(
                $students,
                fn ($s) => trim(($s->gradeLevel?->name ?? '—').' '.($s->studyField?->name ?? '')),
            ),
        ];
    }

    /**
     * Totals per group: پایه، رشته، and پایه + رشته (a "class" in this school).
     *
     * @return Collection<int, array{name: string, students: int, active: int, charges: int, paid: int, balance: int}>
     */
    private function group($students, callable $key): Collection
    {
        return $students
            ->groupBy($key)
            ->map(function ($group, $name) {
                return [
                    'name' => (string) $name,
                    'students' => $group->count(),
                    'active' => $group->where('is_active', true)->count(),
                    'charges' => (int) $group->sum(fn ($s) => $s->tuition->charges),
                    'paid' => (int) $group->sum(fn ($s) => $s->tuition->paid),
                    'balance' => (int) $group->sum(fn ($s) => $s->tuition->balance()),
                ];
            })
            ->sortBy('name')
            ->values();
    }
}
