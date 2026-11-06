@extends('layouts.app')

@section('title', 'لیست بدهکاران — '.$year->name)


@section('heading', 'بدهکاران')
@section('content')
    @php
        $gradeOptions = $students->pluck('gradeLevel.name')->filter()->unique()->sort()->values();
        $fieldOptions = $students->pluck('studyField.name')->filter()->unique()->sort()->values();
        $totalCharges = $students->sum(fn ($s) => $s->tuition->charges);
        $totalBalance = $students->sum(fn ($s) => $s->tuition->balance());
    @endphp
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <h1 class="page-title">لیست بدهکاران</h1>
                </div>

                <div class="row row-sm mb-3">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-muted small">تعداد بدهکاران</div>
                                <div class="h4 mb-0">@fa($students->count())</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-muted small">جمع شهریه (ریال)</div>
                                <div class="h4 mb-0">@fa($totalCharges)</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-muted small">جمع باقی‌مانده (ریال)</div>
                                <div class="h4 mb-0 text-danger">@fa($totalBalance)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">دانش‌آموزان بدهکار</h3>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('debtors.index', $year) }}"
                                      class="row g-2 align-items-end mb-4">
                                    <div class="col-md-4">
                                        <label for="grade" class="form-label">پایه</label>
                                        <select name="grade" id="grade" class="form-select">
                                            <option value="">همه</option>
                                            @foreach ($gradeOptions as $gradeOption)
                                                <option value="{{ $gradeOption }}"
                                                    @selected($grade === $gradeOption)>{{ $gradeOption }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="field" class="form-label">رشته</label>
                                        <select name="field" id="field" class="form-select">
                                            <option value="">همه</option>
                                            @foreach ($fieldOptions as $fieldOption)
                                                <option value="{{ $fieldOption }}"
                                                    @selected($field === $fieldOption)>{{ $fieldOption }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary">فیلتر</button>
                                        <a href="{{ route('debtors.index', $year) }}" class="btn btn-light">حذف فیلتر</a>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="basic-datatable">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">کدملی</th>
                                            <th class="wd-15p border-bottom-0">نام</th>
                                            <th class="wd-20p border-bottom-0">نام خانوادگی</th>
                                            <th class="wd-15p border-bottom-0">پایه</th>
                                            <th class="wd-15p border-bottom-0">رشته</th>
                                            <th class="wd-15p border-bottom-0">جمع شهریه (ریال)</th>
                                            <th class="wd-15p border-bottom-0">پرداختی (ریال)</th>
                                            <th class="wd-15p border-bottom-0">باقی‌مانده (ریال)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($students as $student)
                                            <tr>
                                                <td>{{ $student->national_id }}</td>
                                                <td>
                                                    <a href="{{ route('students.show', [$year, $student]) }}">
                                                        {{ $student->first_name }}
                                                    </a>
                                                </td>
                                                <td>{{ $student->last_name }}</td>
                                                <td>{{ $student->gradeLevel?->name ?? '—' }}</td>
                                                <td>{{ $student->studyField?->name ?? '—' }}</td>
                                                <td>@fa($student->tuition->charges)</td>
                                                <td>@fa($student->tuition->paid)</td>
                                                <td>
                                                    <span class="badge bg-danger">@fa($student->tuition->balance())</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">بدهکاری یافت نشد.</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection