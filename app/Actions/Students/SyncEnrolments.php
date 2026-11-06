<?php

namespace App\Actions\Students;

use App\Models\Enrolment;
use App\Models\Student;
use App\Models\Subject;

/** Replaces a student's enrolments from the form payload (enrolments[term][subject key]). */
class SyncEnrolments
{
    public function handle(Student $student, array $enrolments, bool $replace = true): void
    {
        if ($replace) {
            $student->enrolments()->delete();
        }

        $subjects = Subject::pluck('id', 'key');

        foreach ($enrolments as $term => $perSubject) {
            if (! in_array($term, ['summer', 'extra'], true) || ! is_array($perSubject)) {
                continue;
            }

            foreach ($perSubject as $key => $quantity) {
                $quantity = (int) $quantity;

                if ($quantity <= 0 || ! isset($subjects[$key])) {
                    continue;
                }

                Enrolment::create([
                    'student_id' => $student->id,
                    'subject_id' => $subjects[$key],
                    'term' => $term,
                    'quantity' => $quantity,
                ]);
            }
        }
    }
}
