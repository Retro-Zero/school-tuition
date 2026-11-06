<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManage();
    }

    public function rules(): array
    {
        $year = $this->route('year');
        $coupon = $this->route('coupon');

        return [
            'code' => ['required', 'string', 'max:100',
                Rule::unique('coupons', 'code')->where('financial_year_id', $year->id)->ignore($coupon?->id)],
            'amount' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'عنوان کد تخفیف الزامی است.',
            'code.unique' => 'این کد تخفیف در این سال مالی وجود دارد.',
            'amount.required' => 'مبلغ تخفیف را وارد کنید.',
            'amount.integer' => 'مبلغ تخفیف باید عدد باشد.',
        ];
    }
}
