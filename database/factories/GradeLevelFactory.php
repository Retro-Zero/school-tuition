<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GradeLevel> */
class GradeLevelFactory extends Factory
{
    protected $model = GradeLevel::class;

    public function definition(): array
    {
        return [
            'name' => 'پایه '.fake()->unique()->numberBetween(1, 99),
            'sort_order' => fake()->numberBetween(1, 9),
        ];
    }
}
