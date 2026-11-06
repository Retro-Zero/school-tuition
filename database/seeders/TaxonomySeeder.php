<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\StudyField;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * The school's reference data. Grades and subjects are rows now, so adding a
 * پایه or a درس is an insert, not a migration plus six code edits.
 */
class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['دهم', 'یازدهم', 'دوازدهم'] as $i => $name) {
            GradeLevel::updateOrCreate(['name' => $name], ['sort_order' => $i + 1]);
        }

        foreach (['تجربی', 'ریاضی', 'انسانی'] as $i => $name) {
            StudyField::updateOrCreate(['name' => $name], ['sort_order' => $i + 1]);
        }

        $subjects = [
            'physics' => 'فیزیک',
            'chemistry' => 'شیمی',
            'biology' => 'زیست',
            'math' => 'ریاضی',
            'arabic' => 'عربی',
            'literature' => 'ادبیات',
            'philosophy' => 'فلسفه',
        ];

        $i = 1;
        foreach ($subjects as $key => $name) {
            Subject::updateOrCreate(['key' => $key], ['name' => $name, 'sort_order' => $i++]);
        }
    }
}
