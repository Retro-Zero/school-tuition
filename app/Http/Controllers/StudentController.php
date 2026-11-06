<?php

namespace App\Http\Controllers;

use App\Actions\Students\DeleteStudent;
use App\Actions\Students\StoreStudent;
use App\Actions\Students\ToggleStudentStatus;
use App\Actions\Students\UpdateStudent;
use App\Http\Requests\StudentRequest;
use App\Models\FinancialYear;
use App\Models\Student;
use App\Queries\StudentFormData;
use App\Queries\StudentQuery;
use App\Services\Tuition;

class StudentController extends Controller
{
    public function index(FinancialYear $year)
    {
        $grade = request()->query('grade');
        $field = request()->query('field');

        $query = new StudentQuery($year, $grade, $field);

        $summary = $query->totals();
        $students = $query->page(50);

        return view('students.index', compact('year', 'students', 'summary', 'grade', 'field'));
    }

    public function create(FinancialYear $year, StudentFormData $form)
    {
        return view('students.create', $form->for($year));
    }

    public function store(StudentRequest $request, FinancialYear $year, StoreStudent $action)
    {
        $student = $action->handle($year, $request->studentData(), $request->user());

        return redirect()->route('students.show', [$year, $student])
            ->with('status', 'دانش‌آموز ثبت شد.');
    }

    public function show(FinancialYear $year, Student $student)
    {
        $student->load(['gradeLevel', 'studyField', 'coupon', 'enrolments.subject'])
            ->loadSum('payments', 'amount');

        $line = Tuition::forYearId($year->id)->line($student);
        $payments = $student->payments()->orderByDesc('paid_on')->orderByDesc('id')->get();
        $commitments = $student->commitments()->orderBy('due_on')->get();

        return view('students.show', compact('year', 'student', 'line', 'payments', 'commitments'));
    }

    public function edit(FinancialYear $year, Student $student, StudentFormData $form)
    {
        $student->load('enrolments');

        return view('students.edit', $form->for($year) + compact('student'));
    }

    public function update(StudentRequest $request, FinancialYear $year, Student $student, UpdateStudent $action)
    {
        $action->handle($student, $request->studentData());

        return redirect()->route('students.show', [$year, $student])->with('status', 'اطلاعات دانش‌آموز ویرایش شد.');
    }

    public function destroy(FinancialYear $year, Student $student, DeleteStudent $action)
    {
        $action->handle($student);

        return redirect()->route('students.index', $year)
            ->with('status', 'دانش‌آموز حذف شد. سوابق پرداخت حفظ شد.');
    }

    public function toggle(FinancialYear $year, Student $student, ToggleStudentStatus $action)
    {
        $action->handle($student);

        return back()->with('status', $student->is_active ? 'دانش‌آموز فعال شد.' : 'دانش‌آموز غیرفعال شد.');
    }

    /** Printable statement (V1: users/print). */
    public function statement(FinancialYear $year, Student $student)
    {
        $student->load(['gradeLevel', 'studyField', 'coupon', 'enrolments.subject'])
            ->loadSum('payments', 'amount');

        $line = Tuition::forYearId($year->id)->line($student);
        $payments = $student->payments()->orderBy('paid_on')->get();

        return view('students.statement', compact('year', 'student', 'line', 'payments'));
    }
}
