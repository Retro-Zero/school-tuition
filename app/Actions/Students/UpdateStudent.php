<?php

namespace App\Actions\Students;

use App\Models\Student;
use Illuminate\Support\Facades\DB;

class UpdateStudent
{
    public function __construct(private SyncEnrolments $syncEnrolments) {}

    public function handle(Student $student, array $data): Student
    {
        $enrolments = $data['enrolments'] ?? [];
        unset($data['enrolments']);

        DB::transaction(function () use ($student, $data, $enrolments) {
            $student->update($data);
            $this->syncEnrolments->handle($student, $enrolments);
        });

        return $student;
    }
}
