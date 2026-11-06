<?php

namespace Database\Factories;

use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FinancialYear> */
class FinancialYearFactory extends Factory
{
    protected $model = FinancialYear::class;

    public function definition(): array
    {
        $start = fake()->numberBetween(1400, 1405);

        return [
            'name' => $start.'-'.($start + 1),
            'starts_on' => now()->startOfYear()->toDateString(),
            'ends_on' => now()->endOfYear()->toDateString(),
            'is_open' => true,
        ];
    }
}
