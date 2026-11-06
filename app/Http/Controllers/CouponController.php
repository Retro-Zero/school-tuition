<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponRequest;
use App\Models\Coupon;
use App\Models\FinancialYear;
use App\Models\Student;
use App\Services\Tuition;

class CouponController extends Controller
{
    public function index(FinancialYear $year)
    {
        $coupons = Coupon::where('financial_year_id', $year->id)
            ->withCount('students')
            ->orderBy('code')
            ->get();

        return view('coupons.index', compact('year', 'coupons'));
    }

    /** لیست تخفیفی‌ها — students holding a coupon. */
    public function students(FinancialYear $year)
    {
        $students = Student::forYear($year->id)
            ->whereNotNull('coupon_id')
            ->with(['gradeLevel', 'studyField', 'coupon', 'enrolments'])
            ->withSum('payments', 'amount')
            ->orderBy('last_name')
            ->get();

        $summary = Tuition::forYearId($year->id)->decorate($students);

        return view('coupons.students', compact('year', 'students', 'summary'));
    }

    public function create(FinancialYear $year)
    {
        return view('coupons.create', compact('year'));
    }

    public function store(CouponRequest $request, FinancialYear $year)
    {
        Coupon::create($request->validated() + ['financial_year_id' => $year->id]);

        return redirect()->route('coupons.index', $year)->with('status', 'کد تخفیف ثبت شد.');
    }

    public function edit(FinancialYear $year, Coupon $coupon)
    {
        return view('coupons.edit', compact('year', 'coupon'));
    }

    public function update(CouponRequest $request, FinancialYear $year, Coupon $coupon)
    {
        $coupon->update($request->validated());

        return redirect()->route('coupons.index', $year)->with('status', 'کد تخفیف ویرایش شد.');
    }

    public function destroy(FinancialYear $year, Coupon $coupon)
    {
        $used = $coupon->students()->count();

        if ($used > 0) {
            return redirect()->route('coupons.index', $year)
                ->with('error', 'این کد تخفیف برای '.$used.' دانش‌آموز ثبت شده و قابل حذف نیست.');
        }

        $coupon->delete();

        return redirect()->route('coupons.index', $year)->with('status', 'کد تخفیف حذف شد.');
    }
}
