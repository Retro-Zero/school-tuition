@extends('layouts.app')

@section('title', 'لیست تخفیفی‌ها — '.$year->name)


@section('heading', 'لیست تخفیفی‌ها')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <h1 class="page-title">لیست تخفیفی‌ها</h1>
                </div>

                <div class="row row-sm mb-3">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-muted small">تعداد دانش‌آموزان تخفیفی</div>
                                <div class="h4 mb-0">@fa($students->count())</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-muted small">مجموع تخفیف اعمال‌شده (ریال)</div>
                                <div class="h4 mb-0">@fa($summary->discounts)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">دانش‌آموزان دارای کد تخفیف</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="basic-datatable">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">کدملی</th>
                                            <th class="wd-15p border-bottom-0">نام</th>
                                            <th class="wd-20p border-bottom-0">نام خانوادگی</th>
                                            <th class="wd-15p border-bottom-0">پایه</th>
                                            <th class="wd-15p border-bottom-0">رشته</th>
                                            <th class="wd-15p border-bottom-0">کد تخفیف</th>
                                            <th class="wd-15p border-bottom-0">مبلغ تخفیف (ریال)</th>
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
                                                <td>
                                                    <span class="badge bg-info">{{ $student->coupon?->code ?? '—' }}</span>
                                                </td>
                                                <td>@fa($student->tuition->discount)</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">دانش‌آموزی با کد تخفیف ثبت نشده است.</td>
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
