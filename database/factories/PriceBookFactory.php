<?php

namespace Database\Factories;

use App\Models\FinancialYear;
use App\Models\PriceBook;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PriceBook> */
class PriceBookFactory extends Factory
{
    protected $model = PriceBook::class;

    public function definition(): array
    {
        return [
            'financial_year_id' => FinancialYear::factory(),
            'insurance_amount' => 300_000,
        ];
    }
}
