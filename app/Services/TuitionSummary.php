<?php

namespace App\Services;

/** Aggregated figures for a year (used by the report screen and the parity test). */
final class TuitionSummary
{
    public int $students = 0;

    public int $activeStudents = 0;

    public int $charges = 0;

    public int $paid = 0;

    public int $extras = 0;

    public int $discounts = 0;

    public int $council = 0;

    public int $olympiad = 0;

    public int $carryOverDebt = 0;

    public function add(TuitionLine $line, bool $isActive): void
    {
        $this->students++;
        $this->activeStudents += $isActive ? 1 : 0;
        $this->charges += $line->charges;
        $this->paid += $line->paid;
        $this->extras += $line->extras;
        $this->discounts += $line->discount;
        $this->council += $line->council;
        $this->olympiad += $line->olympiad;
        $this->carryOverDebt += $line->carryOverDebt;
    }

    public function balance(): int
    {
        return $this->charges - $this->paid;
    }
}
