@extends('layouts.app')

@section('title', 'گزارش سالانه — '.$year->name)


@section('heading', 'گزارش سالانه')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">گزارش سالانه <x-year-name :year="$year" /></h1>
                </div>
                <!-- PAGE-HEADER END -->

                <div class="stat-grid is-thirds">
                    <x-stat-card label="تعداد دانش‌آموزان" :value="number_format($summary->activeStudents)"
                        :hint="number_format($summary->activeStudents) . ' فعال از ' . number_format($summary->students)"
                        icon="fe fe-users" />
                    <x-stat-card label="جمع شهریه امسال" :value="$summary->charges" money
                        icon="fe fe-file-text" />
                    <x-stat-card label="جمع پرداختی‌ها" :value="$summary->paid" money
                        icon="fe fe-check-circle" tone="success" />
                    <x-stat-card label="باقی‌مانده" :value="$summary->balance()" money
                        icon="fe fe-alert-circle"
                        :tone="$summary->balance() > 0 ? 'danger' : 'success'" />
                    <x-stat-card label="مجموع بدهی پیشین" :value="$summary->carryOverDebt" money
                        icon="fe fe-rotate-ccw" tone="warning" />
                    <x-stat-card label="جمع شورا" :value="$summary->council" money
                        icon="fe fe-users" />
                    <x-stat-card label="المپیاد" :value="$summary->olympiad" money
                        icon="fe fe-award" />
                    <x-stat-card label="کلاس‌های فوق‌العاده" :value="$summary->extras" money
                        icon="fe fe-book-open" />
                    <x-stat-card label="جمع تخفیف‌ها" :value="$summary->discounts" money
                        icon="fe fe-percent" tone="success" />
                </div>
                <!-- ROW-1 END -->

                <!-- ROW-1.5 : breakdown by grade / field / class -->
                @php
                    $breakdowns = [
                        ['title' => 'بر اساس پایه', 'label' => 'پایه', 'rows' => $byGrade],
                        ['title' => 'بر اساس رشته', 'label' => 'رشته', 'rows' => $byField],
                        ['title' => 'بر اساس کلاس', 'label' => 'پایه و رشته', 'rows' => $byClass],
                    ];
                @endphp

                <div class="row row-sm">
                    @foreach ($breakdowns as $block)
                        <div class="col-xl-4 col-lg-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h3 class="card-title mb-0">{{ $block['title'] }}</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0 breakdown-table">
                                            <thead>
                                                <tr>
                                                    <th>{{ $block['label'] }}</th>
                                                    <th class="text-end">تعداد</th>
                                                    <th class="text-end">شهریه</th>
                                                    <th class="text-end">باقی‌مانده</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($block['rows'] as $row)
                                                    <tr>
                                                        <td class="text-nowrap">{{ $row['name'] }}</td>
                                                        <td class="text-end">@fa($row['students'])</td>
                                                        <td class="text-end text-nowrap">@fa(number_format($row['charges']))</td>
                                                        <td class="text-end text-nowrap {{ $row['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                                            @fa(number_format($row['balance']))
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-3">داده‌ای نیست</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- ROW-1.5 END -->

                <!-- ROW-2 : debtors -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">لیست بدهکاران</h3>
                                <span class="badge bg-danger-transparent">@fa($debtors->count()) نفر</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="basic-datatable">
                                        <thead>
                                            <tr>
                                                <th class="wd-15p border-bottom-0">کدملی</th>
                                                <th class="wd-15p border-bottom-0">نام</th>
                                                <th class="wd-15p border-bottom-0">نام خانوادگی</th>
                                                <th class="wd-15p border-bottom-0">پایه و رشته</th>
                                                <th class="wd-15p border-bottom-0">شماره تماس</th>
                                                <th class="wd-15p border-bottom-0">میزان بدهی</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($debtors as $student)
                                                <tr>
                                                    <td>{{ $student->national_id }}</td>
                                                    <td>{{ $student->first_name }}</td>
                                                    <td>{{ $student->last_name }}</td>
                                                    <td>{{ $student->gradeLevel->name }} {{ $student->studyField->name }}</td>
                                                    <td>{{ $student->phone }}</td>
                                                    <td class="fw-bold text-danger">@fa($student->tuition->balance())</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ROW-2 END -->

                <!-- ROW-3 : discounts -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">لیست افرادی که شامل تخفیف شدند</h3>
                                <span class="badge bg-success-transparent">@fa($discounts->count()) نفر</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="basic-datatable1">
                                        <thead>
                                            <tr>
                                                <th class="wd-15p border-bottom-0">کدملی</th>
                                                <th class="wd-15p border-bottom-0">نام</th>
                                                <th class="wd-15p border-bottom-0">نام خانوادگی</th>
                                                <th class="wd-15p border-bottom-0">پایه و رشته</th>
                                                <th class="wd-15p border-bottom-0">کد تخفیف</th>
                                                <th class="wd-15p border-bottom-0">میزان تخفیف</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($discounts as $student)
                                                <tr>
                                                    <td>{{ $student->national_id }}</td>
                                                    <td>{{ $student->first_name }}</td>
                                                    <td>{{ $student->last_name }}</td>
                                                    <td>{{ $student->gradeLevel->name }} {{ $student->studyField->name }}</td>
                                                    <td>{{ $student->coupon?->code }}</td>
                                                    <td class="fw-bold text-success">@fa($student->tuition->discount)</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ROW-3 END -->

                <!-- ROW-4 : olympiad -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">لیست افرادی که در کلاس‌های المپیاد شرکت کردند</h3>
                                <span class="badge bg-info-transparent">@fa($olympiadStudents->count()) نفر</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="basic-datatable2">
                                        <thead>
                                            <tr>
                                                <th class="wd-15p border-bottom-0">کدملی</th>
                                                <th class="wd-15p border-bottom-0">نام</th>
                                                <th class="wd-15p border-bottom-0">نام خانوادگی</th>
                                                <th class="wd-15p border-bottom-0">پایه و رشته</th>
                                                <th class="wd-15p border-bottom-0">شماره تماس</th>
                                                <th class="wd-15p border-bottom-0">هزینه کلاس</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($olympiadStudents as $student)
                                                <tr>
                                                    <td>{{ $student->national_id }}</td>
                                                    <td>{{ $student->first_name }}</td>
                                                    <td>{{ $student->last_name }}</td>
                                                    <td>{{ $student->gradeLevel->name }} {{ $student->studyField->name }}</td>
                                                    <td>{{ $student->phone }}</td>
                                                    <td>@fa($student->tuition->olympiad)</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ROW-4 END -->

                <!-- ROW-5 : carry over debt -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">لیست افرادی که بدهی پیشین دارند</h3>
                                <span class="badge bg-warning-transparent">@fa($carryOverStudents->count()) نفر</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="basic-datatable3">
                                        <thead>
                                            <tr>
                                                <th class="wd-15p border-bottom-0">کدملی</th>
                                                <th class="wd-15p border-bottom-0">نام</th>
                                                <th class="wd-15p border-bottom-0">نام خانوادگی</th>
                                                <th class="wd-15p border-bottom-0">پایه و رشته</th>
                                                <th class="wd-15p border-bottom-0">شماره تماس</th>
                                                <th class="wd-15p border-bottom-0">میزان بدهی پیشین</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($carryOverStudents as $student)
                                                <tr>
                                                    <td>{{ $student->national_id }}</td>
                                                    <td>{{ $student->first_name }}</td>
                                                    <td>{{ $student->last_name }}</td>
                                                    <td>{{ $student->gradeLevel->name }} {{ $student->studyField->name }}</td>
                                                    <td>{{ $student->phone }}</td>
                                                    <td>@fa($student->tuition->carryOverDebt)</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ROW-5 END -->

            </div>
            <!-- CONTAINER END -->
        </div>
    </div>
@endsection
