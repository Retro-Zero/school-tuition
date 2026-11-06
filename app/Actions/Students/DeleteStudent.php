<?php

namespace App\Actions\Students;

use App\Models\Student;

/**
 * Soft delete only: payments and history stay in the database.
 * (V1 hard-deleted and the ON DELETE CASCADE wiped the student's payment record.)
 */
class DeleteStudent
{
    public function handle(Student $student): void
    {
        $student->delete();
    }
}
