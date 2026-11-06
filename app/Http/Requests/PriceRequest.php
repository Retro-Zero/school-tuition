<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManage();
    }

    public function rules(): array
    {
        return [
            'insurance' => ['required', 'integer', 'min:0'],
            'base' => ['required', 'array'],
            'base.*' => ['required', 'integer', 'min:0'],
            'units' => ['required', 'array'],
            'units.*' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'insurance.required' => 'مبلغ بیمه را وارد کنید.',
            'insurance.integer' => 'مبالغ باید عدد باشند.',
            'base.*.integer' => 'شهریه پایه باید عدد باشد.',
            'units.*.integer' => 'نرخ هر جلسه باید عدد باشد.',
        ];
    }
}
