<?php

namespace App\Services;

use App\Models\PriceBook;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * The one place where tuition is calculated.
 *
 * V1 had this formula copy-pasted in six methods and it had already drifted
 * (inactive students lost the `council` term in the transaction path). Every
 * screen, the PDF and the reports now call this class.
 *
 * Charge model (mirrors V1 so historical figures stay comparable):
 *   active   : base(grade) - discount + insurance + extras + council + olympiad + carry_over
 *   inactive : extras + council + olympiad + carry_over
 *   balance  = charges - payments
 */
class Tuition
{
    public function __construct(private readonly PriceBook $book) {}

    /**
     * Load the price book for a year. Read-only on purpose: this is called from GET
     * screens, and V2's first version used firstOrCreate() here, so opening a report
     * could INSERT a price book. Creating one is EnsurePriceBook's job.
     *
     * A year without a price book simply prices everything at zero.
     */
    public static function forYearId(int $financialYearId): self
    {
        $book = PriceBook::with(['baseTuitions', 'unitPrices'])
            ->where('financial_year_id', $financialYearId)
            ->first();

        if (! $book) {
            $book = new PriceBook(['financial_year_id' => $financialYearId, 'insurance_amount' => 0]);
            $book->setRelation('baseTuitions', collect());
            $book->setRelation('unitPrices', collect());
        }

        return new self($book);
    }

    public function book(): PriceBook
    {
        return $this->book;
    }

    /**
     * Full financial line for one student.
     *
     * Expects `enrolments` (with subject) and the payments sum to be available;
     * when they are not loaded it falls back to querying.
     */
    public function line(Student $student): TuitionLine
    {
        $gradeId = (int) $student->grade_level_id;

        $summerSessions = 0;
        $extraSessions = 0;
        $extras = 0;

        foreach ($student->enrolments as $enrolment) {
            $qty = (int) $enrolment->quantity;
            $unit = $this->book->unitFor($gradeId, $enrolment->term);

            if ($enrolment->term === 'summer') {
                $summerSessions += $qty;
            } else {
                $extraSessions += $qty;
            }

            $extras += $qty * $unit;
        }

        // withSum() sets the attribute to NULL when a student has no payments, and
        // `?? $student->payments()->sum(...)` treats NULL as "not loaded" — that ran one
        // extra SUM per empty student (47 of them on the 207-row list). Only fall back
        // to a query when the aggregate was never loaded at all.
        $paid = array_key_exists('payments_sum_amount', $student->getAttributes())
            ? (int) $student->payments_sum_amount
            : (int) $student->payments()->sum('amount');

        $base = $student->is_active ? $this->book->baseFor($gradeId) : 0;
        $insurance = $student->is_active ? (int) $this->book->insurance_amount : 0;
        $discount = $student->is_active ? (int) ($student->coupon?->amount ?? 0) : 0;

        $charges = $base
            + $insurance
            + $extras
            + (int) $student->council_amount
            + (int) $student->olympiad_amount
            + (int) $student->carry_over_debt
            - $discount;

        return new TuitionLine(
            base: $base,
            insurance: $insurance,
            discount: $discount,
            extras: $extras,
            summerSessions: $summerSessions,
            extraSessions: $extraSessions,
            council: (int) $student->council_amount,
            olympiad: (int) $student->olympiad_amount,
            carryOverDebt: (int) $student->carry_over_debt,
            charges: $charges,
            paid: $paid,
        );
    }

    /** Attach a `tuition` TuitionLine to every student and return the totals. */
    public function decorate(Collection $students): TuitionSummary
    {
        $summary = new TuitionSummary;

        foreach ($students as $student) {
            $line = $this->line($student);
            $student->setAttribute('tuition', $line);
            $summary->add($line, (bool) $student->is_active);
        }

        return $summary;
    }
}
