<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Coupon> */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'financial_year_id' => FinancialYear::factory(),
            'code' => 'تخفیف '.fake()->unique()->numberBetween(1, 9999),
            'amount' => fake()->numberBetween(1_000_000, 20_000_000),
        ];
    }
}
