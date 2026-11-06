<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use App\Queries\StudentQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class DebtorController extends Controller
{
    public function index(FinancialYear $year)
    {
        $grade = request()->query('grade');
        $field = request()->query('field');

        $owing = (new StudentQuery($year, $grade, $field))->owing();

        return view('debtors.index', [
            'year' => $year,
            'students' => $this->paginate($owing),
            'summary' => (new StudentQuery($year, $grade, $field))->totals(),
            'grade' => $grade,
            'field' => $field,
        ]);
    }

    private function paginate($collection): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $collection->forPage($page, 50)->values(),
            $collection->count(),
            50,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }
}
