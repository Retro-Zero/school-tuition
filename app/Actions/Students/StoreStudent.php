<?php

namespace App\Actions\Students;

use App\Models\FinancialYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreStudent
{
    public function __construct(private SyncEnrolments $syncEnrolments) {}

    public function handle(FinancialYear $year, array $data, ?User $author = null): Student
    {
        $enrolments = $data['enrolments'] ?? [];
        unset($data['enrolments']);

        return DB::transaction(function () use ($year, $data, $enrolments, $author) {
            $student = Student::create($data + [
                'financial_year_id' => $year->id,
                'is_active' => true,
                'created_by' => $author?->id,
            ]);

            $this->syncEnrolments->handle($student, $enrolments);

            return $student;
        });
    }
}
