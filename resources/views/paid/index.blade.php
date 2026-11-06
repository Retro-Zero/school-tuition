@extends('layouts.app')

@section('title', 'لیست پرداختی‌ها — '.$year->name)


@section('heading', 'پرداختی‌ها')
@section('content')
    @php
        $gradeOptions = $students->pluck('gradeLevel.name')->filter()->unique()->sort()->values();
        $fieldOptions = $students->pluck('studyField.name')->filter()->unique()->sort()->values();
        $totalPaid = $students->sum(fn ($s) => $s->tuition->paid);
        $totalBalance = $students->sum(fn ($s) => $s->tuition->balance());
    @endphp
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <h1 class="page-title">لیست پرداختی‌ها</h1>
                </div>

                <div class="row row-sm mb-3">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-muted small">تعداد دانش‌آموزان پرداخت‌کننده</div>
                                <div class="h4 mb-0">@fa($students->count())</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-muted small">جمع پرداختی (ریال)</div>
                                <div class="h4 mb-0 text-success">@fa($totalPaid)</div>
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
                                <h3 class="card-title">دانش‌آموزان دارای پرداخت</h3>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('paid.index', $year) }}"
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
                                        <a href="{{ route('paid.index', $year) }}" class="btn btn-light">حذف فیلتر</a>
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
                                            @php($balance = $student->tuition->balance())
                                            <tr @class(['is-settled' => $balance <= 0])>
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
                                                <td>
                                                    <span class="badge badge-soft-success">@fa($student->tuition->paid)</span>
                                                </td>
                                                <td>
                                                    @if ($balance > 0)
                                                        <span class="badge badge-soft-danger">@fa($balance)</span>
                                                    @else
                                                        <span class="badge badge-soft-success">تسویه‌شده</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">پرداختی ثبت نشده است.</td>
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
