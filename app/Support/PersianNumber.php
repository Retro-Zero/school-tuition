<?php

namespace App\Support;

/**
 * Display-side digit conversion.
 *
 * Money, counts and dates are shown in Persian digits (۱۲۳), but identifiers —
 * national IDs, phone numbers, references — stay Latin so they can be copied into
 * other systems. Inputs always post Latin digits; Jalali::normalizeDigits() accepts both.
 */
final class PersianNumber
{
    private const PERSIAN = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    public static function digits(string|int|float|null $value): string
    {
        return str_replace(range(0, 9), self::PERSIAN, (string) $value);
    }

    /** number_format with Persian digits — the usual case for money. */
    public static function format(int|float|null $value, int $decimals = 0): string
    {
        return self::digits(number_format((float) $value, $decimals));
    }

    /**
     * For the @fa() directive: numbers get grouped, anything else (a year like
     * 1402-1403, a pre-formatted string) is only transliterated.
     */
    public static function forDisplay(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            return self::format($value);
        }

        if (is_string($value) && preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            return self::format((float) $value);
        }

        return self::digits(is_scalar($value) ? (string) $value : '');
    }
}
