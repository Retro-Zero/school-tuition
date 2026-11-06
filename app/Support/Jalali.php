<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Minimal Jalali <-> Gregorian helpers. V1 stored dates as free text
 * ("18 مرداد 1402"), so the importer has to parse them and V2 stores real dates.
 */
final class Jalali
{
    public const MONTHS = [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
        5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
        9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    ];

    /** @return array{0:int,1:int,2:int} [gy, gm, gd] */
    public static function toGregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;
        $days = -355668 + (365 * $jy) + intdiv($jy, 33) * 8 + intdiv(($jy % 33) + 3, 4)
            + $jd + ($jm < 7 ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);

        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;
            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;
        $isLeap = ($gy % 4 === 0 && $gy % 100 !== 0) || $gy % 400 === 0;
        $monthLengths = [0, 31, $isLeap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        $gm = 1;
        while ($gm <= 12 && $gd > $monthLengths[$gm]) {
            $gd -= $monthLengths[$gm];
            $gm++;
        }

        return [$gy, $gm, $gd];
    }

    public static function date(int $jy, int $jm, int $jd = 1): Carbon
    {
        [$gy, $gm, $gd] = self::toGregorian($jy, $jm, $jd);

        return Carbon::create($gy, $gm, $gd)->startOfDay();
    }

    /** @return array{0:int,1:int,2:int} [jy, jm, jd] */
    public static function fromGregorian(int $gy, int $gm, int $gd): array
    {
        $gDaysInMonth = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400) + $gd;

        for ($i = 1; $i < $gm; $i++) {
            $days += $gDaysInMonth[$i];
        }
        if ($gm > 2 && (($gy % 4 === 0 && $gy % 100 !== 0) || $gy % 400 === 0)) {
            $days++;
        }

        $jy = -1595 + 33 * intdiv($days, 12053);
        $days %= 12053;

        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    /** Show a stored (Gregorian) date the way the school writes it: 1402/08/18. */
    public static function format(?\DateTimeInterface $date): string
    {
        if (! $date) {
            return '';
        }

        [$jy, $jm, $jd] = self::fromGregorian(
            (int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j')
        );

        return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    }

    /** Parse a manually typed Jalali date (1402/08/18). */
    public static function parseInput(?string $text): ?Carbon
    {
        $text = self::normalizeDigits((string) $text);

        if (! preg_match('#^(\d{4})[/-](\d{1,2})[/-](\d{1,2})$#', $text, $m)) {
            return null;
        }

        return self::date((int) $m[1], (int) $m[2], (int) $m[3]);
    }

    public static function today(): string
    {
        return self::format(Carbon::now());
    }

    public static function normalizeDigits(string $text): string
    {
        return str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $text
        );
    }

    public static function monthNumber(string $name): ?int
    {
        $i = array_search($name, self::MONTHS, true);

        return $i === false ? null : (int) $i;
    }

    /** Parse "18 مرداد 1402" (also tolerates "۱۸ مرداد ۱۴۰۲" and Persian digits). */
    public static function parsePersian(string $text): ?Carbon
    {
        $text = self::normalizeDigits(trim($text));

        if (! preg_match('/^(\d{1,2})\s+(\S+)\s+(\d{4})$/u', $text, $m)) {
            return null;
        }

        $month = self::monthNumber($m[2]);

        if ($month === null) {
            return null;
        }

        return self::date((int) $m[3], $month, (int) $m[1]);
    }
}
