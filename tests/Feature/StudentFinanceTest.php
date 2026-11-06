<?php

namespace Tests\Feature;

use App\Actions\Pricing\EnsurePriceBook;
use App\Models\BaseTuition;
use App\Models\Enrolment;
use App\Models\FinancialYear;
use App\Models\GradeLevel;
use App\Models\StudyField;
use App\Models\Subject;
use App\Models\UnitPrice;
use App\Models\User;
use App\Services\Tuition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * End-to-end through the real screens: create a year with a price list, a student
 * with one enrolment, then pay — and check the numbers the school would see.
 */
class StudentFinanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private FinancialYear $year;

    private GradeLevel $grade;

    private StudyField $field;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->year = FinancialYear::factory()->create(['name' => '1402-1403']);
        $this->grade = GradeLevel::factory()->create(['name' => 'دهم']);
        $this->field = StudyField::factory()->create(['name' => 'تجربی']);
        $this->subject = Subject::factory()->create(['key' => 'physics', 'name' => 'فیزیک']);

        $book = (new EnsurePriceBook)->handle($this->year);
        $book->update(['insurance_amount' => 300_000]);

        BaseTuition::create([
            'price_book_id' => $book->id,
            'grade_level_id' => $this->grade->id,
            'amount' => 60_000_000,
        ]);

        UnitPrice::create([
            'price_book_id' => $book->id,
            'grade_level_id' => $this->grade->id,
            'term' => 'summer',
            'amount' => 100_000,
        ]);
    }

    private function createStudent(array $overrides = []): TestResponse
    {
        return $this->actingAs($this->admin)->post(route('students.store', $this->year), array_merge([
            'national_id' => '0012345678',
            'first_name' => 'علی',
            'last_name' => 'رضایی',
            'grade_level_id' => $this->grade->id,
            'study_field_id' => $this->field->id,
            'council_amount' => 1_000_000,
            'enrolments' => ['summer' => ['physics' => 10]],
        ], $overrides));
    }

    public function test_creating_a_student_persists_enrolments_and_computes_the_balance(): void
    {
        $this->createStudent()->assertRedirect();

        $student = $this->year->students()->firstOrFail();

        // 60,000,000 base + 300,000 insurance + 10 × 100,000 sessions + 1,000,000 council
        $this->assertSame(1, $student->enrolments()->count());

        $this->actingAs($this->admin)
            ->get(route('students.show', [$this->year, $student]))
            ->assertOk()
            ->assertSee('۶۲,۳۰۰,۰۰۰');   // Persian digits, grouped
    }

    public function test_duplicate_national_id_in_the_same_year_is_rejected(): void
    {
        $this->createStudent();
        $this->createStudent(['first_name' => 'دوم'])
            ->assertSessionHasErrors('national_id');

        $this->assertSame(1, $this->year->students()->count());
    }

    public function test_a_payment_above_the_balance_is_refused(): void
    {
        $this->createStudent();
        $student = $this->year->students()->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('payments.store', [$this->year, $student]), [
                'amount' => 99_000_000,
                'method' => 'pos',
                'paid_on' => '1402/08/18',
            ])
            ->assertSessionHasErrors('amount');

        $this->assertSame(0, $student->payments()->count());
    }

    public function test_a_payment_within_the_balance_is_recorded(): void
    {
        $this->createStudent();
        $student = $this->year->students()->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('payments.store', [$this->year, $student]), [
                'amount' => 10_000_000,
                'method' => 'bank',
                'paid_on' => '1402/08/18',
            ])
            ->assertRedirect(route('students.show', [$this->year, $student]));

        $payment = $student->payments()->firstOrFail();

        $this->assertSame(10_000_000, $payment->amount);
        $this->assertSame('2023-11-09', $payment->paid_on->toDateString());   // 18 مرداد 1402
    }

    public function test_a_settled_statement_does_not_ask_for_zero_rial(): void
    {
        $this->createStudent();
        $student = $this->year->students()->with('enrolments.subject')->firstOrFail();

        $charges = Tuition::forYearId($this->year->id)->line($student)->charges;

        $this->actingAs($this->admin)->post(route('payments.store', [$this->year, $student]), [
            'amount' => $charges,
            'method' => 'pos',
            'paid_on' => '1402/08/18',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('students.statement', [$this->year, $student]))
            ->assertOk();

        $response->assertSee('تسویه');
        $response->assertDontSee('باقی مانده است');
        $response->assertDontSee('۰ باقی');
    }

    public function test_deleting_a_student_keeps_their_payments(): void
    {
        $this->createStudent();
        $student = $this->year->students()->firstOrFail();

        $this->actingAs($this->admin)->post(route('payments.store', [$this->year, $student]), [
            'amount' => 5_000_000,
            'method' => 'pos',
            'paid_on' => '1402/08/18',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('students.destroy', [$this->year, $student]))
            ->assertRedirect(route('students.index', $this->year));

        $this->assertSoftDeleted('students', ['id' => $student->id]);
        $this->assertSame(1, $student->payments()->count());
    }

    public function test_a_viewer_cannot_change_data(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->post(route('students.store', $this->year), [
                'national_id' => '0099999999',
                'first_name' => 'نمی‌تواند',
                'last_name' => 'ثبت کند',
                'grade_level_id' => $this->grade->id,
                'study_field_id' => $this->field->id,
            ])
            ->assertForbidden();
    }
}
