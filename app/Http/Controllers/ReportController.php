<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use App\Queries\ReportSummary;

class ReportController extends Controller
{
    public function index(FinancialYear $year)
    {
        // constructed explicitly: the container would hand ReportSummary an empty
        // FinancialYear instead of the route-bound one
        return view('report', (new ReportSummary($year))->data() + ['year' => $year]);
    }
}
