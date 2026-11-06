<?php

namespace Database\Factories;

use App\Models\FinancialYear;
use App\Models\GradeLevel;
use App\Models\Student;
use App\Models\StudyField;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Student> */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'financial_year_id' => FinancialYear::factory(),
            'national_id' => fake()->unique()->numerify('##########'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => '0912'.fake()->numerify('#######'),
            'grade_level_id' => GradeLevel::factory(),
            'study_field_id' => StudyField::factory(),
            'is_active' => true,
            'council_amount' => 0,
            'olympiad_amount' => 0,
            'carry_over_debt' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
