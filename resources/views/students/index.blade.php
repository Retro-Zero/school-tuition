@extends('layouts.app')

@section('title', 'دانش‌آموزان — '.$year->name)


@section('heading', 'دانش‌آموزان')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">
                                    دانش‌آموزان <x-year-name :year="$year" />
                                    @if (($grade && $grade !== 'همه') || ($field && $field !== 'همه'))
                                        <span class="text-muted fs-14">
                                            — {{ $grade !== 'همه' ? $grade : '' }} {{ $field !== 'همه' ? $field : '' }}
                                        </span>
                                    @endif
                                </h3>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('students.index', $year) }}" class="btn btn-sm btn-light">همه</a>
                                    <a href="{{ route('students.create', $year) }}" class="btn btn-primary">
                                        افزودن دانش‌آموز جدید
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="stat-grid">
                                    <x-stat-card label="تعداد دانش‌آموز" :value="number_format($summary->students)"
                                        :hint="number_format($summary->activeStudents) . ' فعال'" icon="fe fe-users" />
                                    <x-stat-card label="جمع شهریه" :value="$summary->charges" money
                                        icon="fe fe-file-text" />
                                    <x-stat-card label="جمع پرداختی" :value="$summary->paid" money
                                        icon="fe fe-check-circle" tone="success" />
                                    <x-stat-card label="باقی‌مانده کل" :value="$summary->balance()" money
                                        icon="fe fe-alert-circle" :tone="$summary->balance() > 0 ? 'danger' : 'success'" />
                                </div>

                                <div class="table-responsive">
                                    {{-- no DataTables here: pagination is server-side --}}
                                    <table class="table table-bordered text-nowrap border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="wd-15p border-bottom-0 d-none d-lg-table-cell">کدملی</th>
                                                <th class="border-bottom-0">نام و نام خانوادگی</th>
                                                <th class="border-bottom-0">پایه و رشته</th>
                                                <th class="border-bottom-0 d-none d-md-table-cell">شهریه</th>
                                                <th class="border-bottom-0 d-none d-md-table-cell">پرداختی</th>
                                                <th class="border-bottom-0">باقی‌مانده</th>
                                                <th class="wd-15p border-bottom-0">عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($students as $student)
                                                <tr @class(['is-settled' => $student->tuition->balance() <= 0])>
                                                    <td class="d-none d-lg-table-cell">{{ $student->national_id }}</td>
                                                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                                    <td>{{ $student->gradeLevel->name }} {{ $student->studyField->name }}</td>
                                                    <td class="d-none d-md-table-cell">@fa(number_format($student->tuition->charges))</td>
                                                    <td class="d-none d-md-table-cell">@fa(number_format($student->tuition->paid))</td>
                                                    <td class="fw-bold {{ $student->tuition->balance() > 0 ? 'text-danger' : 'text-success' }}">
                                                        @fa($student->tuition->balance())
                                                    </td>
                                                    <td>
                                                        <div class="row-actions">
                                                        <form method="POST" action="{{ route('students.toggle', [$year, $student]) }}" class="m-0">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit"
                                                                class="btn btn-xs {{ $student->is_active ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                                                {{ $student->is_active ? 'فعال' : 'غیرفعال' }}
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('students.show', [$year, $student]) }}"
                                                            class="btn-icon" title="مشاهده" aria-label="مشاهده">
                                                            <i class="fe fe-eye" aria-hidden="true"></i>
                                                        </a>
                                                        <a href="{{ route('students.edit', [$year, $student]) }}"
                                                            class="btn-icon" title="ویرایش" aria-label="ویرایش">
                                                            <i class="fe fe-edit-2" aria-hidden="true"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('students.destroy', [$year, $student]) }}" class="m-0"
                                                            onsubmit="return confirm('این دانش‌آموز حذف شود؟ سوابق پرداخت حفظ می‌شود.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-icon is-danger" title="حذف" aria-label="حذف">
                                                                <i class="fe fe-trash-2" aria-hidden="true"></i>
                                                            </button>
                                                        </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3">
                                    <div class="text-muted" style="font-size:12.5px">
                                        @fa($students->total()) دانش‌آموز —
                                        نمایش @fa($students->firstItem() ?? 0) تا
                                        @fa($students->lastItem() ?? 0)
                                    </div>
                                    {{ $students->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
