<?php

namespace Database\Factories;

use App\Models\StudyField;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StudyField> */
class StudyFieldFactory extends Factory
{
    protected $model = StudyField::class;

    public function definition(): array
    {
        return [
            'name' => 'رشته '.fake()->unique()->numberBetween(1, 99),
            'sort_order' => fake()->numberBetween(1, 9),
        ];
    }
}
