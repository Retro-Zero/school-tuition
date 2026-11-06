@extends('layouts.app')

@section('title', $student->first_name.' '.$student->last_name.' — '.$year->name)


@section('heading', $student->first_name . ' ' . $student->last_name)
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                {{-- سرصفحه --}}
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                                    <div>
                                        <h3 class="mb-1">
                                            {{ $student->first_name }} {{ $student->last_name }}
                                            @if ($student->is_active)
                                                <x-status-pill :active="true" />
                                            @else
                                                <x-status-pill :active="false" />
                                            @endif
                                        </h3>
                                        <div class="text-muted small">
                                            کد ملی: {{ $student->national_id }}
                                            <span class="mx-2">|</span>
                                            {{ $student->gradeLevel->name ?? '—' }}
                                            {{ $student->studyField->name ?? '' }}
                                            <span class="mx-2">|</span>
                                            سال مالی <x-year-name :year="$year" />
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('payments.create', [$year, $student]) }}" class="btn btn-primary">
                                            افزودن پرداخت
                                        </a>
                                        <a href="{{ route('students.edit', [$year, $student]) }}" class="btn btn-ghost">
                                            ویرایش
                                        </a>
                                        <a href="{{ route('students.statement', [$year, $student]) }}" target="_blank"
                                           class="btn btn-ghost">
                                            چاپ صورت‌حساب
                                        </a>
                                        <a href="{{ route('students.index', $year) }}" class="btn btn-light">بازگشت</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- خلاصه مالی --}}
                <div class="stat-grid is-thirds">
                    <x-stat-card label="جمع هزینه‌ها" :value="$line->charges" money
                        :hint="$student->is_active ? 'دانش‌آموز فعال' : 'دانش‌آموز غیرفعال'"
                        icon="fe fe-file-text" />
                    <x-stat-card label="پرداخت‌شده" :value="$line->paid" money
                        :hint="number_format($payments->count()) . ' فقره پرداخت'"
                        icon="fe fe-check-circle" tone="success" />
                    <x-stat-card label="باقی‌مانده" :value="$line->balance()" money
                        :hint="$line->isSettled() ? 'تسویه شده' : 'بدهکار'"
                        icon="fe fe-alert-circle" :tone="$line->isSettled() ? 'success' : 'danger'" />
                </div>

                <div class="row row-sm">
                    {{-- ریز هزینه‌ها --}}
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">ریز هزینه‌ها</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>شرح</th>
                                                <th class="wd-25p">مبلغ (ریال)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>شهریه پایه</td>
                                                <td>@fa($line->base)</td>
                                            </tr>
                                            <tr>
                                                <td>بیمه دانش‌آموزی</td>
                                                <td>@fa($line->insurance)</td>
                                            </tr>
                                            @if ($line->discount != 0)
                                            <tr>
                                                <td>
                                                    تخفیف
                                                    @if ($student->coupon)
                                                        <span class="text-muted small">({{ $student->coupon->code }})</span>
                                                    @endif
                                                </td>
                                                <td class="text-danger">
                                                    {{ $line->discount > 0 ? '−' : '' }}@fa($line->discount)
                                                </td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td>
                                                    کلاس‌های تابستان و فوق برنامه
                                                    <div class="text-muted small">
                                                        تابستان: @fa($line->summerSessions) جلسه
                                                        — فوق برنامه: @fa($line->extraSessions) جلسه
                                                    </div>
                                                </td>
                                                <td>@fa($line->extras)</td>
                                            </tr>
                                            @if ($line->council != 0)
                                                <tr>
                                                    <td>هزینه مشاوره (شورا)</td>
                                                    <td>@fa($line->council)</td>
                                                </tr>
                                            @endif
                                            @if ($line->olympiad != 0)
                                                <tr>
                                                    <td>کلاس‌های المپیاد</td>
                                                    <td>@fa($line->olympiad)</td>
                                                </tr>
                                            @endif
                                            @if ($line->carryOverDebt != 0)
                                                <tr>
                                                    <td>بدهی سال قبل</td>
                                                    <td class="{{ $line->carryOverDebt < 0 ? 'text-success' : '' }}">
                                                        @fa($line->carryOverDebt)
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold">
                                                <td>جمع هزینه‌ها</td>
                                                <td>@fa($line->charges)</td>
                                            </tr>
                                            <tr class="fw-bold">
                                                <td>پرداخت‌شده</td>
                                                <td class="text-success">@fa($line->paid)</td>
                                            </tr>
                                            <tr class="fw-bold">
                                                <td>باقی‌مانده</td>
                                                <td class="{{ $line->balance() > 0 ? 'text-danger' : 'text-success' }}">
                                                    @fa($line->balance())
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                @if ($student->enrolments->isNotEmpty())
                                    <div class="mt-4">
                                        <div class="text-muted small mb-2">کلاس‌های ثبت‌شده</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($student->enrolments as $enrolment)
                                                @if ($enrolment->subject && $enrolment->quantity > 0)
                                                    <span class="badge bg-light text-dark border">
                                                        {{ $enrolment->subject->name }}
                                                        ({{ $enrolment->term === 'summer' ? 'تابستان' : 'فوق برنامه' }})
                                                        — @fa($enrolment->quantity) جلسه
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- مشخصات --}}
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">مشخصات</h4>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="wd-40p">کد ملی</th>
                                            <td>{{ $student->national_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>نام و نام خانوادگی</th>
                                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>شماره تماس</th>
                                            <td>{{ $student->phone ?: '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th>پایه</th>
                                            <td>{{ $student->gradeLevel->name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th>رشته</th>
                                            <td>{{ $student->studyField->name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th>کد تخفیف</th>
                                            <td>{{ $student->coupon->code ?? 'ندارد' }}</td>
                                        </tr>
                                        <tr>
                                            <th>وضعیت</th>
                                            <td>{{ $student->is_active ? 'فعال' : 'غیرفعال' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- پرداخت‌ها --}}
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title mb-0">پرداخت‌ها</h4>
                                <a href="{{ route('payments.create', [$year, $student]) }}" class="btn btn-sm btn-primary">
                                    افزودن پرداخت
                                </a>
                            </div>
                            <div class="card-body">
                                @if ($payments->isEmpty())
                                    <div class="text-muted text-center py-4">تا کنون پرداختی ثبت نشده است.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-nowrap mb-0">
                                            <thead>
                                                <tr>
                                                    <th>تاریخ پرداخت</th>
                                                    <th>مبلغ (ریال)</th>
                                                    <th>روش پرداخت</th>
                                                    <th>شماره پیگیری</th>
                                                    <th>توضیح</th>
                                                    <th class="wd-15p">عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($payments as $payment)
                                                    <tr>
                                                        <td>{{ \App\Support\Jalali::format($payment->paid_on) }}</td>
                                                        <td class="fw-bold">@fa($payment->amount)</td>
                                                        <td>{{ $payment->method === 'pos' ? 'کارتخوان (POS)' : 'پرداخت بانکی' }}</td>
                                                        <td>{{ $payment->reference ?: '—' }}</td>
                                                        <td>{{ $payment->note ?: '—' }}</td>
                                                        <td>
                                                            <div class="d-flex gap-1">
                                                                <a href="{{ route('payments.edit', [$year, $payment]) }}"
                                                                   class="btn btn-sm btn-warning">ویرایش</a>
                                                                <form method="POST"
                                                                      action="{{ route('payments.destroy', [$year, $payment]) }}"
                                                                      onsubmit="return confirm('این پرداخت حذف شود؟');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="fw-bold">
                                                    <td colspan="1">جمع پرداخت‌ها</td>
                                                    <td colspan="5">@fa($line->paid)</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- تعهدات --}}
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">پرداخت‌های مقرر (تعهدات)</h4>
                            </div>
                            <div class="card-body">
                                @if ($commitments->isEmpty())
                                    <div class="text-muted text-center py-4">تعهد پرداختی برای این دانش‌آموز ثبت نشده است.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-nowrap mb-0">
                                            <thead>
                                                <tr>
                                                    <th>سررسید</th>
                                                    <th>عنوان</th>
                                                    <th>مبلغ (ریال)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($commitments as $commitment)
                                                    <tr>
                                                        <td>{{ \App\Support\Jalali::format($commitment->due_on) }}</td>
                                                        <td>{{ $commitment->label ?: '—' }}</td>
                                                        <td>@fa($commitment->amount)</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="fw-bold">
                                                    <td colspan="2">جمع تعهدات</td>
                                                    <td>@fa($commitments->sum('amount'))</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- حذف دانش‌آموز --}}
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card border-danger">
                            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <div class="fw-bold">حذف دانش‌آموز</div>
                                    <div class="text-muted small">
                                        دانش‌آموز از لیست این سال حذف می‌شود، اما سوابق پرداخت او حفظ خواهد شد.
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('students.destroy', [$year, $student]) }}"
                                      onsubmit="return confirm('دانش‌آموز {{ $student->first_name }} {{ $student->last_name }} حذف شود؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">حذف دانش‌آموز</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
