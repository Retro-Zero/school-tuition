<?php

namespace App\Http\Requests;

use App\Support\Jalali;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManage();
    }

    protected function prepareForValidation(): void
    {
        $clean = [];
        foreach (['national_id', 'phone'] as $field) {
            if ($this->filled($field)) {
                $clean[$field] = Jalali::normalizeDigits((string) $this->input($field));
            }
        }

        if ($clean) {
            $this->merge($clean);
        }
    }

    public function rules(): array
    {
        $year = $this->route('year');
        $student = $this->route('student');

        return [
            'national_id' => ['required', 'string', 'max:20',
                Rule::unique('students', 'national_id')
                    ->where('financial_year_id', $year->id)
                    ->whereNull('deleted_at')
                    ->ignore($student?->id)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'grade_level_id' => ['required', Rule::exists('grade_levels', 'id')],
            'study_field_id' => ['required', Rule::exists('study_fields', 'id')],
            'coupon_id' => ['nullable', Rule::exists('coupons', 'id')->where('financial_year_id', $year->id)],
            'council_amount' => ['nullable', 'integer', 'min:0'],
            'olympiad_amount' => ['nullable', 'integer', 'min:0'],
            'carry_over_debt' => ['nullable', 'integer'],
            'enrolments' => ['nullable', 'array'],
            'enrolments.*.*' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function messages(): array
    {
        return [
            'national_id.required' => 'کد ملی الزامی است.',
            'national_id.unique' => 'این کد ملی در این سال مالی قبلاً ثبت شده است.',
            'first_name.required' => 'نام الزامی است.',
            'last_name.required' => 'نام خانوادگی الزامی است.',
            'grade_level_id.required' => 'پایه تحصیلی را انتخاب کنید.',
            'study_field_id.required' => 'رشته تحصیلی را انتخاب کنید.',
            'enrolments.*.*.integer' => 'تعداد جلسات باید عدد باشد.',
        ];
    }

    /** Validated data with the one-off amounts normalised to integers. */
    public function studentData(): array
    {
        $data = $this->validated();

        $data['council_amount'] = (int) ($data['council_amount'] ?? 0);
        $data['olympiad_amount'] = (int) ($data['olympiad_amount'] ?? 0);
        $data['carry_over_debt'] = (int) ($data['carry_over_debt'] ?? 0);
        $data['coupon_id'] = $data['coupon_id'] ?? null;

        return $data;
    }
}
