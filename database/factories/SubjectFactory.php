<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subject> */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $key = fake()->unique()->lexify('subject-????');

        return [
            'name' => 'درس '.$key,
            'key' => $key,
            'sort_order' => fake()->numberBetween(1, 9),
        ];
    }
}
