<?php

namespace App\Http\Controllers;

use App\Actions\Payments\RecordPayment;
use App\Actions\Payments\UpdatePayment;
use App\Http\Requests\PaymentRequest;
use App\Models\FinancialYear;
use App\Models\Payment;
use App\Models\Student;
use App\Services\Tuition;

class PaymentController extends Controller
{
    public function index(FinancialYear $year)
    {
        $payments = Payment::where('financial_year_id', $year->id)
            // include students that were soft-deleted: their payments are history
            ->with(['student' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('paid_on')
            ->orderByDesc('id')
            ->paginate(100);

        $total = (int) Payment::where('financial_year_id', $year->id)->sum('amount');

        return view('payments.index', compact('year', 'payments', 'total'));
    }

    public function create(FinancialYear $year, Student $student)
    {
        $student->loadSum('payments', 'amount');
        $line = Tuition::forYearId($year->id)->line($student);

        return view('payments.create', compact('year', 'student', 'line'));
    }

    public function store(PaymentRequest $request, FinancialYear $year, Student $student, RecordPayment $action)
    {
        $action->handle($year, $student, $request->paymentData(), $request->user());

        return redirect()->route('students.show', [$year, $student])->with('status', 'پرداخت ثبت شد.');
    }

    public function edit(FinancialYear $year, Payment $payment)
    {
        $student = $payment->student;
        abort_if($student === null, 404, 'دانش‌آموز این پرداخت حذف شده است.');

        $student->loadSum('payments', 'amount');
        $line = Tuition::forYearId($year->id)->line($student);

        return view('payments.edit', compact('year', 'payment', 'student', 'line'));
    }

    public function update(PaymentRequest $request, FinancialYear $year, Payment $payment, UpdatePayment $action)
    {
        $action->handle($year, $payment, $request->paymentData());

        return redirect()->route('students.show', [$year, $payment->student_id])->with('status', 'پرداخت ویرایش شد.');
    }

    public function destroy(FinancialYear $year, Payment $payment)
    {
        $studentId = $payment->student_id;
        $payment->delete();   // soft delete: the row stays for history

        return redirect()->route('students.show', [$year, $studentId])
            ->with('status', 'پرداخت حذف شد. ردیف در سوابق باقی می‌ماند.');
    }
}
