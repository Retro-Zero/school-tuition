<?php

namespace Tests\Unit;

use App\Models\BaseTuition;
use App\Models\Coupon;
use App\Models\Enrolment;
use App\Models\PriceBook;
use App\Models\Student;
use App\Models\UnitPrice;
use App\Services\Tuition;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Pins the tuition formula so the six-screen duplication of V1 can never come back.
 * No database: models are assembled in memory.
 */
class TuitionTest extends TestCase
{
    private function book(): PriceBook
    {
        $book = new PriceBook(['insurance_amount' => 300_000]);
        $book->setRelation('baseTuitions', new Collection([
            new BaseTuition(['grade_level_id' => 1, 'amount' => 65_350_000]),
        ]));
        $book->setRelation('unitPrices', new Collection([
            new UnitPrice(['grade_level_id' => 1, 'term' => 'summer', 'amount' => 140_240]),
            new UnitPrice(['grade_level_id' => 1, 'term' => 'extra', 'amount' => 180_000]),
        ]));

        return $book;
    }

    private function student(bool $active = true): Student
    {
        $student = new Student([
            'grade_level_id' => 1,
            'is_active' => $active,
            'council_amount' => 1_000_000,
            'olympiad_amount' => 5_000_000,
            'carry_over_debt' => 0,
        ]);

        $student->setRelation('coupon', new Coupon(['amount' => 19_500_000]));
        $student->setRelation('enrolments', new Collection([
            new Enrolment(['term' => 'summer', 'quantity' => 20]),
            new Enrolment(['term' => 'extra', 'quantity' => 3]),
        ]));
        $student->payments_sum_amount = 10_000_000;

        return $student;
    }

    public function test_active_student_charges_match_the_v1_formula(): void
    {
        $line = (new Tuition($this->book()))->line($this->student());

        // 65,350,000 base + 300,000 insurance + (20×140,240 + 3×180,000)
        // + 1,000,000 council + 5,000,000 olympiad − 19,500,000 discount
        $this->assertSame(65_350_000, $line->base);
        $this->assertSame(300_000, $line->insurance);
        $this->assertSame(19_500_000, $line->discount);
        $this->assertSame(20 * 140_240 + 3 * 180_000, $line->extras);
        $this->assertSame(55_494_800, $line->charges);
        $this->assertSame(10_000_000, $line->paid);
        $this->assertSame(45_494_800, $line->balance());
    }

    public function test_inactive_student_pays_only_extras_and_one_off_amounts(): void
    {
        $line = (new Tuition($this->book()))->line($this->student(active: false));

        $this->assertSame(0, $line->base, 'inactive students are not charged base tuition');
        $this->assertSame(0, $line->insurance);
        $this->assertSame(0, $line->discount, 'discounts only apply to active students (V1 behaviour)');
        $this->assertSame(9_344_800, $line->charges, 'extras 3,344,800 + council 1,000,000 + olympiad 5,000,000');

        $this->assertSame(-655_200, $line->balance(), '9,344,800 charged − 10,000,000 paid');
    }

    public function test_negative_carry_over_is_a_credit_and_reduces_the_balance(): void
    {
        $student = $this->student();
        $student->carry_over_debt = -7_000_000;   // the real case in the V1 data (student پیری)

        $line = (new Tuition($this->book()))->line($student);

        $this->assertSame(55_494_800 - 7_000_000, $line->charges);
    }

    public function test_summary_aggregates_every_line(): void
    {
        $tuition = new Tuition($this->book());
        $summary = $tuition->decorate(new Collection([$this->student(), $this->student(active: false)]));

        $this->assertSame(2, $summary->students);
        $this->assertSame(1, $summary->activeStudents);
        $this->assertSame(55_494_800 + 9_344_800, $summary->charges);
        $this->assertSame(20_000_000, $summary->paid);
        $this->assertSame(44_839_600, $summary->balance());
    }
}
