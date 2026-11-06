<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinancialYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManage();
    }

    public function rules(): array
    {
        $year = $this->route('year');

        return [
            'name' => ['required', 'string', 'max:20', 'regex:/^\d{4}-\d{4}$/',
                Rule::unique('financial_years', 'name')->ignore($year?->id)],
            'starts_on' => ['nullable', 'date'],
            'is_open' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام سال مالی الزامی است.',
            'name.regex' => 'نام سال مالی باید به شکل ۱۴۰۳-۱۴۰۴ باشد.',
            'name.unique' => 'این سال مالی قبلاً ثبت شده است.',
        ];
    }
}
