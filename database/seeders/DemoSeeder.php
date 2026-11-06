<?php

namespace Database\Seeders;

use App\Actions\Pricing\EnsurePriceBook;
use App\Models\BaseTuition;
use App\Models\Commitment;
use App\Models\Coupon;
use App\Models\Enrolment;
use App\Models\FinancialYear;
use App\Models\GradeLevel;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudyField;
use App\Models\Subject;
use App\Models\UnitPrice;
use App\Models\User;
use App\Support\Jalali;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Fictional school data for screenshots, demos and manual testing.
 *
 * Nothing here is real: names, national IDs and phone numbers are generated. The
 * figures are shaped like a real year so the screens show realistic behaviour
 * (partial payments, one inactive student, one credit carry-over, discounts).
 */
class DemoSeeder extends Seeder
{
    private const FIRST_NAMES = [
        'علی', 'محمد', 'رضا', 'امیر', 'حسین', 'مهدی', 'سینا', 'کیان', 'آرش', 'پارسا',
        'زهرا', 'فاطمه', 'مریم', 'نرگس', 'سارا', 'نگار', 'الهام', 'مهسا', 'ریحانه', 'یاسمن',
        'بهنام', 'دانیال', 'سامان', 'طاها', 'عرشیا', 'آیدا', 'شیوا', 'کیمیا', 'هستی', 'رها',
    ];

    private const LAST_NAMES = [
        'احمدی', 'رضایی', 'محمدی', 'حسینی', 'کریمی', 'موسوی', 'صادقی', 'جعفری', 'قاسمی', 'نوری',
        'شریفی', 'کاظمی', 'مرادی', 'یوسفی', 'سلیمانی', 'بهرامی', 'زمانی', 'اکبری', 'فراهانی', 'میرزایی',
    ];

    public function run(): void
    {
        $this->wipe();

        $this->call(TaxonomySeeder::class);

        $grades = GradeLevel::orderBy('sort_order')->get();
        $fields = StudyField::orderBy('sort_order')->get();
        $subjects = Subject::orderBy('sort_order')->get();

        $current = FinancialYear::create([
            'name' => '1402-1403',
            'starts_on' => Jalali::date(1402, 7, 1),
            'ends_on' => Jalali::date(1403, 3, 31),
            'is_open' => true,
        ]);

        FinancialYear::create([
            'name' => '1401-1402',
            'starts_on' => Jalali::date(1401, 7, 1),
            'ends_on' => Jalali::date(1402, 3, 31),
            'is_open' => false,
        ]);

        $this->seedPrices($current, $grades);

        $coupons = collect([
            ['code' => 'فرهنگی دهم و یازدهم', 'amount' => 19_500_000],
            ['code' => 'فرهنگی دوازدهم', 'amount' => 24_000_000],
            ['code' => 'تخفیف المپیاد', 'amount' => 15_000_000],
            ['code' => 'خواهر و برادر', 'amount' => 8_000_000],
        ])->map(fn (array $c) => Coupon::create($c + ['financial_year_id' => $current->id]));

        $this->seedStudents($current, $grades, $fields, $subjects, $coupons);

        User::updateOrCreate(
            ['username' => 'demo'],
            ['name' => 'مدیر نمونه', 'password' => Hash::make('demo'), 'role' => User::ROLE_ADMIN],
        );

        $this->command?->info('Demo data ready: 80 students, login demo / demo');
    }

    private function seedPrices(FinancialYear $year, $grades): void
    {
        $book = (new EnsurePriceBook)->handle($year);
        $book->update(['insurance_amount' => 300_000]);

        $base = [65_350_000, 65_000_000, 80_000_000];
        $summer = [140_240, 140_240, 220_190];
        $extra = [180_000, 180_000, 280_000];

        foreach ($grades->values() as $i => $grade) {
            BaseTuition::create([
                'price_book_id' => $book->id,
                'grade_level_id' => $grade->id,
                'amount' => $base[$i] ?? $base[0],
            ]);

            foreach (['summer' => $summer, 'extra' => $extra] as $term => $prices) {
                UnitPrice::create([
                    'price_book_id' => $book->id,
                    'grade_level_id' => $grade->id,
                    'term' => $term,
                    'amount' => $prices[$i] ?? $prices[0],
                ]);
            }
        }
    }

    private function seedStudents(FinancialYear $year, $grades, $fields, $subjects, $coupons): void
    {
        $faker = fake('fa_IR');
        $usedIds = [];
        $usedNames = [];

        for ($i = 0; $i < 80; $i++) {
            $grade = $grades[$i % $grades->count()];
            $field = $fields[$i % $fields->count()];

            do {
                $nationalId = (string) $faker->numerify('##########');
            } while (isset($usedIds[$nationalId]));
            $usedIds[$nationalId] = true;

            do {
                $first = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
                $last = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
            } while (isset($usedNames["$first $last"]));
            $usedNames["$first $last"] = true;

            $isActive = $i % 13 !== 0;              // a few deactivated students
            $hasCoupon = $i % 7 === 0;

            $carryOver = 0;
            if ($i === 42) {
                $carryOver = -7_000_000;            // brought-forward credit (real V1 case)
            } elseif ($i % 9 === 0) {
                $carryOver = $faker->numberBetween(1, 25) * 1_000_000;
            }

            $student = Student::create([
                'financial_year_id' => $year->id,
                'national_id' => $nationalId,
                'first_name' => $first,
                'last_name' => $last,
                'phone' => '0912'.$faker->numerify('#######'),
                'grade_level_id' => $grade->id,
                'study_field_id' => $field->id,
                'is_active' => $isActive,
                'coupon_id' => $hasCoupon ? $coupons[$i % $coupons->count()]->id : null,
                'council_amount' => $i % 11 === 0 ? 500_000 : 0,
                'olympiad_amount' => $i % 17 === 0 ? 5_000_000 : 0,
                'carry_over_debt' => $carryOver,
            ]);

            // enrolments: summer sessions for everyone, extra classes for some
            $summerSessions = [0, 6, 10, 20][$i % 4];
            $extraSessions = $i % 3 === 0 ? [3, 6][$i % 2] : 0;

            foreach ($subjects as $s => $subject) {
                if ($summerSessions > 0 && $s % 2 === 0) {
                    Enrolment::create([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'term' => 'summer',
                        'quantity' => $summerSessions,
                    ]);
                }

                if ($extraSessions > 0 && $s % 3 === 0) {
                    Enrolment::create([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'term' => 'extra',
                        'quantity' => $extraSessions,
                    ]);
                }
            }

            $this->seedPayments($student, $year, $i);
        }
    }

    private function seedPayments(Student $student, FinancialYear $year, int $index): void
    {
        if ($index % 5 === 4) {
            return;                                  // some students have paid nothing yet
        }

        $installments = 1 + ($index % 4);
        $amounts = [10_000_000, 15_000_000, 20_000_000];

        for ($n = 0; $n < $installments; $n++) {
            $month = 7 + (($index + $n) % 6);

            Payment::create([
                'student_id' => $student->id,
                'financial_year_id' => $year->id,
                'amount' => $amounts[($index + $n) % 3],
                'method' => $n % 2 === 0 ? 'pos' : 'bank',
                'paid_on' => Jalali::date(1402, $month, 10 + ($n % 15))->toDateString(),
                'reference' => $n % 2 === 0 ? null : (string) (100000 + $index * 10 + $n),
            ]);
        }
    }

    private function wipe(): void
    {
        Commitment::query()->delete();
        Payment::withTrashed()->forceDelete();
        Enrolment::query()->delete();
        Student::withTrashed()->forceDelete();
        Coupon::query()->delete();

        DB::table('unit_prices')->delete();
        DB::table('base_tuitions')->delete();
        DB::table('price_books')->delete();
        FinancialYear::query()->delete();

        foreach ([
            'commitments', 'payments', 'enrolments', 'students', 'coupons',
            'unit_prices', 'base_tuitions', 'price_books', 'financial_years',
        ] as $table) {
            DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
        }
    }
}
