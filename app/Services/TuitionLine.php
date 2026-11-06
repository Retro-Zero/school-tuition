<?php

namespace App\Services;

/** Immutable financial snapshot of one student for one year. */
final class TuitionLine
{
    public function __construct(
        public readonly int $base,
        public readonly int $insurance,
        public readonly int $discount,
        public readonly int $extras,
        public readonly int $summerSessions,
        public readonly int $extraSessions,
        public readonly int $council,
        public readonly int $olympiad,
        public readonly int $carryOverDebt,
        public readonly int $charges,
        public readonly int $paid,
    ) {}

    public function balance(): int
    {
        return $this->charges - $this->paid;
    }

    public function isSettled(): bool
    {
        return $this->balance() <= 0;
    }

    public function toArray(): array
    {
        return [
            'base' => $this->base,
            'insurance' => $this->insurance,
            'discount' => $this->discount,
            'extras' => $this->extras,
            'summer_sessions' => $this->summerSessions,
            'extra_sessions' => $this->extraSessions,
            'council' => $this->council,
            'olympiad' => $this->olympiad,
            'carry_over_debt' => $this->carryOverDebt,
            'charges' => $this->charges,
            'paid' => $this->paid,
            'balance' => $this->balance(),
        ];
    }
}
