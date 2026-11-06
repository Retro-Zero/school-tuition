<?php

namespace App\Actions\Students;

use App\Models\Student;

class ToggleStudentStatus
{
    public function handle(Student $student): Student
    {
        $student->update(['is_active' => ! $student->is_active]);

        return $student;
    }
}
