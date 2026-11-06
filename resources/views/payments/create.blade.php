@extends('layouts.app')

@section('title', 'ثبت پرداخت')


@section('heading', 'ثبت پرداخت')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Row: student summary -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">ثبت پرداخت جدید</h3>
                            </div>
                            <div class="card-body">
                                <div class="row row-sm">
                                    <div class="col-md-6 col-xl-4 mb-3">
                                        <span class="text-muted d-block">دانش‌آموز</span>
                                        <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                    </div>
                                    <div class="col-md-6 col-xl-4 mb-3">
                                        <span class="text-muted d-block">کد ملی</span>
                                        <strong>{{ $student->national_id }}</strong>
                                    </div>
                                    <div class="col-md-6 col-xl-4 mb-3">
                                        <span class="text-muted d-block">پایه</span>
                                        <strong>{{ $student->gradeLevel?->name ?? '—' }}</strong>
                                    </div>
                                    <div class="col-md-6 col-xl-4 mb-3">
                                        <span class="text-muted d-block">شهریه مقرر</span>
                                        <strong><x-money :value="$line->charges" /></strong>
                                    </div>
                                    <div class="col-md-6 col-xl-4 mb-3">
                                        <span class="text-muted d-block">پرداخت‌شده تاکنون</span>
                                        <strong><x-money :value="$line->paid" /></strong>
                                    </div>
                                    <div class="col-md-6 col-xl-4 mb-3">
                                        <span class="text-muted d-block">باقی‌مانده</span>
                                        <strong class="text-danger"><x-money :value="$line->balance()" /></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Row -->

                <!-- Row: form -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">اطلاعات پرداخت</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('payments.store', [$year, $student]) }}"
                                    class="form-horizontal">
                                    @csrf

                                    <div class="row mb-4">
                                        <label for="amount" class="col-md-3 form-label">مبلغ پرداخت (ریال)</label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control" id="amount" name="amount"
                                                placeholder="مبلغ به ریال، بدون جداکننده" min="1" step="1"
                                                value="{{ old('amount') }}" required>
                                            <small class="text-muted">حداکثر قابل ثبت: <x-money :value="$line->balance()" /></small>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="method" class="col-md-3 form-label">روش پرداخت</label>
                                        <div class="col-md-9">
                                            <select name="method" id="method" class="form-control form-select" required>
                                                <option value="pos" @selected(old('method') === 'pos')>کارتخوان</option>
                                                <option value="bank" @selected(old('method') === 'bank')>بانک</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="paid_on" class="col-md-3 form-label">تاریخ پرداخت</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="paid_on" name="paid_on"
                                                placeholder="1402/08/18" autocomplete="off" dir="ltr"
                                                value="{{ old('paid_on', \App\Support\Jalali::today()) }}" required>
                                            <small class="text-muted">تاریخ شمسی به شکل ۱۴۰۲/۰۸/۱۸</small>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="reference" class="col-md-3 form-label">شماره پیگیری</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="reference" name="reference"
                                                placeholder="اختیاری" maxlength="100" dir="ltr"
                                                value="{{ old('reference') }}">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="note" class="col-md-3 form-label">توضیحات</label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" id="note" name="note" rows="3"
                                                placeholder="اختیاری" maxlength="500">{{ old('note') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="mb-0 mt-4 row justify-content-end">
                                        <div class="col-md-9 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">ثبت پرداخت</button>
                                            <a href="{{ route('students.show', [$year, $student]) }}"
                                                class="btn btn-light">بازگشت</a>
                                        </div>
                                    </div>
                                </form>
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
