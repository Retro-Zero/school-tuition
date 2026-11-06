<?php

namespace App\Actions\Payments;

use App\Models\FinancialYear;
use App\Models\Payment;
use App\Services\Tuition;
use Illuminate\Validation\ValidationException;

class UpdatePayment
{
    public function handle(FinancialYear $year, Payment $payment, array $data): Payment
    {
        $student = $payment->student;

        $charges = Tuition::forYearId($year->id)->line($student)->charges;
        $others = (int) $student->payments()->whereKeyNot($payment->id)->sum('amount');
        $available = $charges - $others;

        if ($data['amount'] > max($available, 0)) {
            throw ValidationException::withMessages([
                'amount' => 'مبلغ پرداختی از باقی‌مانده شهریه بیشتر است. حداکثر: '.number_format(max($available, 0)).' ریال',
            ]);
        }

        $payment->update($data);

        return $payment;
    }
}
