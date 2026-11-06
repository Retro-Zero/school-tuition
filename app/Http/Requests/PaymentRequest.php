<?php

namespace App\Http\Requests;

use App\Rules\JalaliDate;
use App\Support\Jalali;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManage();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', Rule::in(['pos', 'bank'])],
            'paid_on' => ['required', 'string', new JalaliDate],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'مبلغ پرداخت را وارد کنید.',
            'amount.integer' => 'مبلغ پرداخت باید عدد باشد (بدون جداکننده).',
            'amount.min' => 'مبلغ پرداخت باید بیشتر از صفر باشد.',
            'method.required' => 'روش پرداخت را انتخاب کنید.',
            'paid_on.required' => 'تاریخ پرداخت را وارد کنید.',
        ];
    }

    /** Payment attributes with the Jalali date converted to a real date. */
    public function paymentData(): array
    {
        $data = $this->validated();
        $data['paid_on'] = Jalali::parseInput($data['paid_on'])->toDateString();

        return $data;
    }
}
