<?php

namespace App\Import;

use App\Models\FinancialYear;
use App\Models\Payment;
use App\Models\Student;
use App\Services\Tuition;
use Illuminate\Support\Facades\DB;

/**
 * Compares the V1 database against the imported V2 data: student counts, charges,
 * payments and balance must match to the rial, otherwise the migration moved money.
 */
class ParityReport
{
    private const GRADE_SLUGS = ['دهم' => 'ten', 'یازدهم' => 'eleven', 'دوازدهم' => 'twelve'];

    private const SUBJECT_KEYS = ['physics', 'chemistry', 'biology', 'math', 'arabic', 'literature', 'philosophy'];

    /** @return list<array{label: string, v1: int, v2: int, ok: bool}> */
    public function rows(): array
    {
        [$v1Students, $v1Active, $v1Paid, $v1Charges] = $this->v1Figures();
        [$v2Students, $v2Active, $v2Paid, $v2Charges] = $this->v2Figures();

        $rows = [
            ['label' => 'students', 'v1' => $v1Students, 'v2' => $v2Students],
            ['label' => 'active students', 'v1' => $v1Active, 'v2' => $v2Active],
            ['label' => 'charges (ریال)', 'v1' => $v1Charges, 'v2' => $v2Charges],
            ['label' => 'payments (ریال)', 'v1' => $v1Paid, 'v2' => $v2Paid],
            ['label' => 'balance (ریال)', 'v1' => $v1Charges - $v1Paid, 'v2' => $v2Charges - $v2Paid],
        ];

        foreach ($rows as &$row) {
            $row['ok'] = $row['v1'] === $row['v2'];
        }

        return $rows;
    }

    public function matches(): bool
    {
        foreach ($this->rows() as $row) {
            if (! $row['ok']) {
                return false;
            }
        }

        return true;
    }

    /** V1's own numbers, computed the way V1's report page did. */
    private function v1Figures(): array
    {
        $v1 = DB::connection('v1');

        $students = $v1->table('users')->count();
        $active = $v1->table('users')->where('status', 'active')->count();
        $paid = (int) $v1->table('transactions')->sum('price');

        $prices = $v1->table('prices')->get()->keyBy('financial_year_id');
        $coupons = $v1->table('coupons')->get()->keyBy('id');
        $charges = 0;

        foreach ($v1->table('users')->get() as $u) {
            $p = $prices[$u->financial_year_id] ?? null;
            if (! $p) {
                continue;
            }

            $extras = 0;
            foreach (self::GRADE_SLUGS as $gradeName => $slug) {
                if ($u->grade === $gradeName) {
                    $summer = 0;
                    $extra = 0;
                    foreach (self::SUBJECT_KEYS as $key) {
                        $summer += (int) $u->{"summer_{$key}"};
                        $extra += (int) $u->{"extra_{$key}"};
                    }
                    $extras = $summer * (int) $p->{"{$slug}_summer"} + $extra * (int) $p->{"{$slug}_extra"};
                }
            }

            $discount = $u->coupon_id ? (int) ($coupons[$u->coupon_id]->price ?? 0) : 0;

            $charges += ($u->status === 'active')
                ? (int) $p->{$u->grade} - $discount + (int) $p->insurance + $extras
                    + (int) $u->olympiad + (int) $u->debt + (int) $u->council
                : $extras + (int) $u->olympiad + (int) $u->debt + (int) $u->council;
        }

        return [$students, $active, $paid, $charges];
    }

    private function v2Figures(): array
    {
        $students = Student::count();
        $active = Student::where('is_active', true)->count();
        $paid = (int) Payment::sum('amount');
        $charges = 0;

        foreach (FinancialYear::all() as $year) {
            $rows = Student::forYear($year->id)
                ->with(['enrolments', 'coupon'])
                ->withSum('payments', 'amount')
                ->get();

            $charges += Tuition::forYearId($year->id)->decorate($rows)->charges;
        }

        return [$students, $active, $paid, $charges];
    }
}
