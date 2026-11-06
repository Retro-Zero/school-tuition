<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinancialYearRequest;
use App\Models\FinancialYear;
use App\Models\Student;

class FinancialYearController extends Controller
{
    public function index()
    {
        $years = FinancialYear::withCount('students')->orderByDesc('id')->get();

        return view('years.index', compact('years'));
    }

    public function store(FinancialYearRequest $request)
    {
        $data = $request->validated();

        $year = FinancialYear::create([
            'name' => $data['name'],
            'starts_on' => $data['starts_on'] ?? null,
            'is_open' => true,
        ]);

        return redirect()->route('prices.edit', $year)
            ->with('status', 'سال مالی '.$year->name.' ساخته شد. اکنون تعرفه‌ها را وارد کنید.');
    }

    public function edit(FinancialYear $year)
    {
        return view('years.edit', compact('year'));
    }

    public function update(FinancialYearRequest $request, FinancialYear $year)
    {
        $data = $request->validated();

        $year->update([
            'name' => $data['name'],
            'is_open' => $request->boolean('is_open'),
        ]);

        return redirect()->route('years.index')->with('status', 'سال مالی ویرایش شد.');
    }

    public function destroy(FinancialYear $year)
    {
        $students = Student::withTrashed()->where('financial_year_id', $year->id)->count();

        if ($students > 0) {
            return redirect()->route('years.index')
                ->with('error', 'این سال مالی '.$students.' دانش‌آموز دارد و قابل حذف نیست.');
        }

        $year->delete();

        return redirect()->route('years.index')->with('status', 'سال مالی حذف شد.');
    }
}
