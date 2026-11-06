<?php

namespace App\Support;

/** School-year calendar helpers (a سال مالی runs مهر → خرداد). */
final class SchoolCalendar
{
    /** Months a payment agreement can cover, in school order. */
    public const COMMITMENT_MONTHS = [
        7 => 'مهر', 8 => 'آبان', 9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
    ];

    /** Gregorian range [start, end) of a Jalali month inside a school year. */
    public static function monthRange(int $startYear, int $month): array
    {
        $year = $month >= 7 ? $startYear : $startYear + 1;
        $start = Jalali::date($year, $month, 1);

        $nextMonth = $month === 12 ? 1 : $month + 1;
        $nextYear = $month === 12 ? $year + 1 : $year;

        return [$start, Jalali::date($nextYear, $nextMonth, 1)];
    }
}
