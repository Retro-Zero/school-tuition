@extends('layouts.app')

@section('title', 'لیست تراکنش‌ها')


@section('heading', 'تراکنش‌ها')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <div class="stat-grid">
                    <x-stat-card label="جمع پرداختی‌های این سال" :value="$total" money
                        icon="fe fe-check-circle" tone="success" />
                    <x-stat-card label="تراکنش‌های این صفحه" :value="number_format($payments->count())"
                        :hint="'از ' . number_format($payments->total()) . ' تراکنش'"
                        icon="fe fe-credit-card" />
                </div>

                <!-- Row: table -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">لیست تراکنش‌ها</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">تاریخ پرداخت</th>
                                            <th class="wd-20p border-bottom-0">دانش‌آموز</th>
                                            <th class="wd-15p border-bottom-0">مبلغ</th>
                                            <th class="wd-10p border-bottom-0">روش پرداخت</th>
                                            <th class="wd-15p border-bottom-0">شماره پیگیری</th>
                                            <th class="wd-20p border-bottom-0">عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($payments as $payment)
                                            <tr>
                                                <td>{{ \App\Support\Jalali::format($payment->paid_on) }}</td>
                                                <td>
                                                    @if ($payment->student && ! $payment->student->trashed())
                                                        <a href="{{ route('students.show', [$year, $payment->student_id]) }}">
                                                            {{ trim(($payment->student->first_name ?? '') . ' ' . ($payment->student->last_name ?? '')) ?: '—' }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">
                                                            {{ trim(($payment->student->first_name ?? '') . ' ' . ($payment->student->last_name ?? '')) ?: '—' }}
                                                        </span>
                                                        <span class="badge bg-secondary-transparent">حذف‌شده</span>
                                                    @endif
                                                </td>
                                                <td><x-money :value="$payment->amount" /></td>
                                                <td>{{ $payment->method === 'pos' ? 'کارتخوان' : 'بانک' }}</td>
                                                <td>{{ $payment->reference ?: '—' }}</td>
                                                <td>
                                                    <div class="row-actions">
                                                        @if ($payment->student && ! $payment->student->trashed())
                                                            <a href="{{ route('payments.edit', [$year, $payment]) }}"
                                                                class="btn-icon" title="ویرایش" aria-label="ویرایش">
                                                                <i class="fe fe-edit-2" aria-hidden="true"></i>
                                                            </a>
                                                        @endif

                                                        <form method="POST"
                                                            action="{{ route('payments.destroy', [$year, $payment]) }}"
                                                            class="m-0"
                                                            onsubmit="return confirm('این پرداخت حذف شود؟ ردیف در سوابق باقی می‌ماند.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-icon is-danger"
                                                                title="حذف" aria-label="حذف">
                                                                <i class="fe fe-trash-2" aria-hidden="true"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    برای این سال مالی هنوز پرداختی ثبت نشده است.
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    {{-- Bootstrap 5 markup: the app never calls Paginator::useBootstrapFive(), and the
                                         Tailwind default would render unstyled inside this admin template. --}}
                                    {{ $payments->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Row -->

            </div>
            <!-- CONTAINER CLOSED -->
        </div>
    </div>
@endsection
