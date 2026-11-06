<?php

namespace App\Import;

use App\Models\BaseTuition;
use App\Models\Commitment;
use App\Models\Coupon;
use App\Models\Enrolment;
use App\Models\FinancialYear;
use App\Models\GradeLevel;
use App\Models\Payment;
use App\Models\PriceBook;
use App\Models\Student;
use App\Models\StudyField;
use App\Models\Subject;
use App\Models\UnitPrice;
use App\Support\Jalali;
use Carbon\Carbon;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Support\Facades\DB;

/**
 * One-way import from the V1 database (connection "v1").
 *
 *   financial_years                  -> financial_years
 *   prices (Persian columns)         -> price_books + base_tuitions + unit_prices
 *   users                            -> students (+ enrolments from the 14 subject columns)
 *   coupons                          -> coupons
 *   transactions                     -> payments
 *   payment_agreement (9 columns)    -> commitments (rows)
 *
 * It wipes the V2 tables first, so it is idempotent.
 */
class V1Importer
{
    private const GRADE_SLUGS = ['دهم' => 'ten', 'یازدهم' => 'eleven', 'دوازدهم' => 'twelve'];

    private const SUBJECT_KEYS = ['physics', 'chemistry', 'biology', 'math', 'arabic', 'literature', 'philosophy'];

    /** Jalali months in V1 payment_agreement, in calendar order, with their year offset. */
    private const MONTH_COLUMNS = [
        'mehr' => [7, 0], 'aban' => [8, 0], 'azar' => [9, 0], 'dey' => [10, 0],
        'bahman' => [11, 0], 'esfand' => [12, 0], 'farvardin' => [1, 1],
        'ordibehesht' => [2, 1], 'khordad' => [3, 1],
    ];

    public function __construct(private readonly ?\Closure $progress = null) {}

    public function run(): void
    {
        $v1 = DB::connection('v1');

        DB::connection()->getPdo();
        (new TaxonomySeeder)->run();

        $gradeIds = GradeLevel::pluck('id', 'name');
        $fieldIds = StudyField::pluck('id', 'name');
        $subjectIds = Subject::pluck('id', 'key');

        // wipe + sequence reset first, outside the transaction: ALTER TABLE commits implicitly
        $this->wipe();
        $this->resetSequences();

        DB::transaction(function () use ($v1, $gradeIds, $fieldIds, $subjectIds) {
            $yearMap = $this->importYears($v1);
            $this->importPriceBooks($v1, $yearMap, $gradeIds);
            $couponMap = $this->importCoupons($v1, $yearMap);
            $studentMap = $this->importStudents($v1, $yearMap, $gradeIds, $fieldIds, $subjectIds, $couponMap);
            $this->importPayments($v1, $studentMap, $yearMap);
            $this->importCommitments($v1, $studentMap, $yearMap);
        });
    }

    private function importYears($v1): array
    {
        $yearMap = [];

        foreach ($v1->table('financial_years')->orderBy('id')->get() as $year) {
            $startYear = (int) explode('-', $year->name)[0];
            $model = FinancialYear::create([
                'name' => $year->name,
                'starts_on' => Jalali::date($startYear, 1, 1),
                'ends_on' => Jalali::date($startYear + 1, 12, 29),
                'is_open' => false,          // V1 years are history; new years start open
            ]);
            $yearMap[$year->id] = $model->id;
        }

        return $yearMap;
    }

    private function importPriceBooks($v1, array $yearMap, $gradeIds): void
    {
        foreach ($v1->table('prices')->get() as $row) {
            if (! isset($yearMap[$row->financial_year_id])) {
                continue;
            }

            $book = PriceBook::create([
                'financial_year_id' => $yearMap[$row->financial_year_id],
                'insurance_amount' => (int) $row->insurance,
            ]);

            foreach (self::GRADE_SLUGS as $gradeName => $slug) {
                BaseTuition::create([
                    'price_book_id' => $book->id,
                    'grade_level_id' => $gradeIds[$gradeName],
                    'amount' => (int) $row->{$gradeName},
                ]);

                foreach (['summer', 'extra'] as $term) {
                    UnitPrice::create([
                        'price_book_id' => $book->id,
                        'grade_level_id' => $gradeIds[$gradeName],
                        'term' => $term,
                        'amount' => (int) $row->{"{$slug}_{$term}"},
                    ]);
                }
            }
        }
    }

    private function importCoupons($v1, array $yearMap): array
    {
        $couponMap = [];

        foreach ($v1->table('coupons')->orderBy('id')->get() as $row) {
            if (! isset($yearMap[$row->financial_year_id])) {
                continue;
            }

            $couponMap[$row->id] = Coupon::create([
                'financial_year_id' => $yearMap[$row->financial_year_id],
                'code' => $row->code,
                'amount' => (int) $row->price,
            ])->id;
        }

        return $couponMap;
    }

    private function importStudents($v1, array $yearMap, $gradeIds, $fieldIds, $subjectIds, array $couponMap): array
    {
        $studentMap = [];
        $rows = $v1->table('users')->orderBy('id')->get();

        foreach ($rows as $index => $row) {
            if (! isset($yearMap[$row->financial_year_id])) {
                continue;
            }

            $student = Student::create([
                'financial_year_id' => $yearMap[$row->financial_year_id],
                'national_id' => (string) $row->national_id,
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'phone' => $row->phone,
                'grade_level_id' => $gradeIds[$row->grade],
                'study_field_id' => $fieldIds[$row->field],
                'is_active' => ($row->status ?? 'active') === 'active',
                'coupon_id' => $row->coupon_id ? ($couponMap[$row->coupon_id] ?? null) : null,
                'council_amount' => (int) $row->council,
                'olympiad_amount' => (int) $row->olympiad,
                'carry_over_debt' => (int) $row->debt,
            ]);

            $studentMap[$row->id] = $student->id;

            foreach (self::SUBJECT_KEYS as $key) {
                foreach (['summer', 'extra'] as $term) {
                    $qty = (int) ($row->{"{$term}_{$key}"} ?? 0);

                    if ($qty > 0) {
                        Enrolment::create([
                            'student_id' => $student->id,
                            'subject_id' => $subjectIds[$key],
                            'term' => $term,
                            'quantity' => $qty,
                        ]);
                    }
                }
            }

            $this->tick($index + 1, count($rows), 'دانش‌آموزان');
        }

        return $studentMap;
    }

    private function importPayments($v1, array $studentMap, array $yearMap): void
    {
        $rows = $v1->table('transactions')->orderBy('id')->get();

        foreach ($rows as $index => $row) {
            if (! isset($studentMap[$row->user_id], $yearMap[$row->financial_year_id])) {
                continue;
            }

            $paidOn = Jalali::parsePersian((string) $row->register_date)
                ?? Carbon::parse($row->created_at ?? now())->startOfDay();

            Payment::create([
                'student_id' => $studentMap[$row->user_id],
                'financial_year_id' => $yearMap[$row->financial_year_id],
                'amount' => (int) $row->price,
                'method' => in_array($row->method, ['pos', 'bank'], true) ? $row->method : 'pos',
                'paid_on' => $paidOn,
                'note' => 'imported from V1 transaction #'.$row->id,
            ]);

            $this->tick($index + 1, count($rows), 'پرداخت‌ها');
        }
    }

    private function importCommitments($v1, array $studentMap, array $yearMap): void
    {
        foreach ($v1->table('payment_agreement')->orderBy('id')->get() as $row) {
            if (! isset($studentMap[$row->user_id], $yearMap[$row->financial_year_id])) {
                continue;
            }

            $yearName = $v1->table('financial_years')->where('id', $row->financial_year_id)->value('name');
            $startYear = (int) explode('-', (string) $yearName)[0];

            foreach (self::MONTH_COLUMNS as $column => [$month, $yearOffset]) {
                $amount = (int) ($row->{$column} ?? 0);

                if ($amount <= 0) {
                    continue;
                }

                Commitment::updateOrCreate(
                    [
                        'student_id' => $studentMap[$row->user_id],
                        'due_on' => Jalali::date($startYear + $yearOffset, $month, 1),
                    ],
                    [
                        'financial_year_id' => $yearMap[$row->financial_year_id],
                        'amount' => $amount,
                        'label' => Jalali::MONTHS[$month],
                    ],
                );
            }
        }
    }

    private function wipe(): void
    {
        Commitment::query()->delete();
        Payment::withTrashed()->forceDelete();
        Enrolment::query()->delete();
        Student::withTrashed()->forceDelete();
        Coupon::query()->delete();
        UnitPrice::query()->delete();
        BaseTuition::query()->delete();
        PriceBook::query()->delete();
        FinancialYear::query()->delete();
    }

    /**
     * Deterministic ids, so a fresh import always produces year 1/2 and students 1..N.
     * Must run OUTSIDE a transaction: ALTER TABLE causes an implicit commit in MariaDB.
     */
    private function resetSequences(): void
    {
        foreach ([
            'commitments', 'payments', 'enrolments', 'students', 'coupons',
            'unit_prices', 'base_tuitions', 'price_books', 'financial_years',
        ] as $table) {
            DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
        }
    }

    private function tick(int $done, int $total, string $label): void
    {
        if ($this->progress) {
            ($this->progress)($done, $total, $label);
        }
    }
}
