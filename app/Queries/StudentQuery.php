<?php

namespace App\Queries;

use App\Models\FinancialYear;
use App\Models\Student;
use App\Services\Tuition;
use App\Services\TuitionSummary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Every read of the student roster goes through here, so the filters, eager loads
 * and the tuition decoration cannot drift apart between screens.
 */
class StudentQuery
{
    public function __construct(
        private readonly FinancialYear $year,
        private readonly ?string $grade = null,
        private readonly ?string $field = null,
    ) {}

    private function builder(): Builder
    {
        return Student::forYear($this->year->id)->filtered($this->grade, $this->field);
    }

    /** Totals for the whole filtered set, with the minimum columns the formula needs. */
    public function totals(): TuitionSummary
    {
        $students = $this->builder()
            ->select(['id', 'grade_level_id', 'is_active', 'coupon_id',
                'council_amount', 'olympiad_amount', 'carry_over_debt'])
            ->with(['enrolments:id,student_id,term,quantity', 'coupon:id,amount'])
            ->withSum('payments', 'amount')
            ->get();

        return Tuition::forYearId($this->year->id)->decorate($students);
    }

    /** One page of fully loaded, decorated students. */
    public function page(int $perPage = 50): LengthAwarePaginator
    {
        $students = $this->builder()
            ->with(['gradeLevel', 'studyField', 'coupon', 'enrolments'])
            ->withSum('payments', 'amount')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($perPage)
            ->withQueryString();

        Tuition::forYearId($this->year->id)->decorate($students->getCollection());

        return $students;
    }

    /** Fully loaded and decorated, for screens that filter on the computed balance. */
    public function all(): Collection
    {
        $students = $this->builder()
            ->with(['gradeLevel', 'studyField', 'coupon', 'enrolments'])
            ->withSum('payments', 'amount')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        Tuition::forYearId($this->year->id)->decorate($students);

        return $students;
    }

    public function owing(): Collection
    {
        return $this->all()->filter(fn (Student $s) => $s->tuition->balance() > 0)->values();
    }

    public function paying(): Collection
    {
        return $this->all()->filter(fn (Student $s) => $s->tuition->paid > 0)->values();
    }

    public function discounted(): Collection
    {
        return $this->all()->filter(fn (Student $s) => $s->coupon_id !== null)->values();
    }

    public function olympiad(): Collection
    {
        return $this->all()->filter(fn (Student $s) => $s->olympiad_amount > 0)->values();
    }

    public function withCarryOver(): Collection
    {
        return $this->all()->filter(fn (Student $s) => $s->carry_over_debt != 0)->values();
    }
}
