<?php

namespace App\Http\Controllers;

use App\Models\Commitment;
use App\Models\FinancialYear;
use App\Support\Jalali;
use App\Support\SchoolCalendar;
use Illuminate\Http\Request;

class CommitmentController extends Controller
{
    public function index(FinancialYear $year, ?int $month = null)
    {
        $month = $month ?: 7;
        $months = SchoolCalendar::COMMITMENT_MONTHS;

        abort_unless(isset($months[$month]), 404);

        [$start, $end] = SchoolCalendar::monthRange($year->startYear(), $month);

        $commitments = Commitment::where('financial_year_id', $year->id)
            ->where('due_on', '>=', $start)
            ->where('due_on', '<', $end)
            ->with('student.gradeLevel')
            ->get()
            ->sortBy(fn ($c) => $c->student?->last_name)
            ->values();

        $total = $commitments->sum('amount');
        $label = $months[$month];

        return view('commitments.index', compact('year', 'month', 'months', 'label', 'commitments', 'total'));
    }

    public function update(Request $request, FinancialYear $year, Commitment $commitment)
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:0'],
        ], [
            'amount.required' => 'مبلغ را وارد کنید.',
            'amount.integer' => 'مبلغ باید عدد باشد.',
        ]);

        $commitment->update($data);

        [, $jalaliMonth] = Jalali::fromGregorian(
            (int) $commitment->due_on->format('Y'),
            (int) $commitment->due_on->format('n'),
            (int) $commitment->due_on->format('j'),
        );

        return back()->with('status', 'مبلغ قسط '.Jalali::MONTHS[$jalaliMonth].' به‌روزرسانی شد.');
    }
}
