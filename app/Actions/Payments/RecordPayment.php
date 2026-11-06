<?php

namespace App\Actions\Payments;

use App\Models\FinancialYear;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\Tuition;
use Illuminate\Validation\ValidationException;

class RecordPayment
{
    public function handle(FinancialYear $year, Student $student, array $data, ?User $author = null): Payment
    {
        $student->loadSum('payments', 'amount');
        $balance = Tuition::forYearId($year->id)->line($student)->balance();

        if ($data['amount'] > max($balance, 0)) {
            throw ValidationException::withMessages([
                'amount' => 'مبلغ پرداختی از باقی‌مانده شهریه بیشتر است. باقی‌مانده: '.number_format($balance).' ریال',
            ]);
        }

        return Payment::create($data + [
            'student_id' => $student->id,
            'financial_year_id' => $year->id,
            'created_by' => $author?->id,
        ]);
    }
}
