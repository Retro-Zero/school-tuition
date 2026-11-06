<?php

namespace App\Queries;

use App\Models\Coupon;
use App\Models\FinancialYear;
use App\Models\GradeLevel;
use App\Models\StudyField;
use App\Models\Subject;

/** Options for the create/edit student form. */
class StudentFormData
{
    public function for(FinancialYear $year): array
    {
        return [
            'year' => $year,
            'grades' => GradeLevel::orderBy('sort_order')->get(),
            'fields' => StudyField::orderBy('sort_order')->get(),
            'subjects' => Subject::orderBy('sort_order')->get(),
            'coupons' => Coupon::where('financial_year_id', $year->id)->orderBy('code')->get(),
        ];
    }
}
