<?php

namespace App\Rules;

use App\Support\Jalali;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Accepts dates typed the way the school writes them: 1402/08/18. */
class JalaliDate implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || Jalali::parseInput($value) === null) {
            $fail('تاریخ را به شکل ۱۴۰۲/۰۸/۱۸ وارد کنید.');
        }
    }
}
