@extends('layouts.app')

@section('title', 'دانش‌آموز جدید — '.$year->name)


@section('heading', 'افزودن دانش‌آموز')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <div>
                                <h3 class="mb-0">ایجاد دانش‌آموز جدید</h3>
                                <span class="text-muted small">سال مالی <x-year-name :year="$year" /></span>
                            </div>
                            <a href="{{ route('students.index', $year) }}" class="btn btn-light">بازگشت</a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <div class="fw-bold mb-2">ثبت اطلاعات انجام نشد:</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('students.store', $year) }}">
                            @csrf

                            {{-- مشخصات دانش‌آموز --}}
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">مشخصات دانش‌آموز</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row row-sm">
                                        <div class="col-md-6 mb-4">
                                            <label for="national_id" class="form-label">کد ملی دانش‌آموز</label>
                                            <input type="text" id="national_id" name="national_id"
                                                   class="form-control @error('national_id') is-invalid @enderror"
                                                   placeholder="کد ملی" inputmode="numeric" required
                                                   value="{{ old('national_id') }}">
                                            @error('national_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label for="phone" class="form-label">شماره تماس</label>
                                            <input type="text" id="phone" name="phone"
                                                   class="form-control @error('phone') is-invalid @enderror"
                                                   placeholder="شماره تماس" inputmode="tel"
                                                   value="{{ old('phone') }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label for="first_name" class="form-label">نام</label>
                                            <input type="text" id="first_name" name="first_name"
                                                   class="form-control @error('first_name') is-invalid @enderror"
                                                   placeholder="نام" required value="{{ old('first_name') }}">
                                            @error('first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label for="last_name" class="form-label">نام خانوادگی</label>
                                            <input type="text" id="last_name" name="last_name"
                                                   class="form-control @error('last_name') is-invalid @enderror"
                                                   placeholder="نام خانوادگی" required value="{{ old('last_name') }}">
                                            @error('last_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label for="grade_level_id" class="form-label">پایه</label>
                                            <select id="grade_level_id" name="grade_level_id"
                                                    class="form-control form-select @error('grade_level_id') is-invalid @enderror"
                                                    required>
                                                <option value="">پایه را انتخاب کنید</option>
                                                @foreach ($grades as $grade)
                                                    <option value="{{ $grade->id }}"
                                                        @selected(old('grade_level_id') == $grade->id)>{{ $grade->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('grade_level_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label for="study_field_id" class="form-label">رشته</label>
                                            <select id="study_field_id" name="study_field_id"
                                                    class="form-control form-select @error('study_field_id') is-invalid @enderror"
                                                    required>
                                                <option value="">رشته را انتخاب کنید</option>
                                                @foreach ($fields as $field)
                                                    <option value="{{ $field->id }}"
                                                        @selected(old('study_field_id') == $field->id)>{{ $field->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('study_field_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-12 mb-0">
                                            <label for="coupon_id" class="form-label">کد تخفیف</label>
                                            <select id="coupon_id" name="coupon_id"
                                                    class="form-control form-select @error('coupon_id') is-invalid @enderror">
                                                <option value="">بدون تخفیف</option>
                                                @foreach ($coupons as $coupon)
                                                    <option value="{{ $coupon->id }}"
                                                        @selected(old('coupon_id') == $coupon->id)>
                                                        {{ $coupon->code }} — <x-money :value="$coupon->amount" />
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('coupon_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- هزینه‌های جانبی --}}
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">هزینه‌های جانبی</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row row-sm">
                                        <div class="col-md-4 mb-4">
                                            <label for="council_amount" class="form-label">هزینه مشاوره</label>
                                            <input type="number" id="council_amount" name="council_amount" min="0"
                                                   step="1" class="form-control @error('council_amount') is-invalid @enderror"
                                                   placeholder="ریال" value="{{ old('council_amount', 0) }}">
                                            @error('council_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-4">
                                            <label for="olympiad_amount" class="form-label">هزینه کلاس‌های المپیاد</label>
                                            <input type="number" id="olympiad_amount" name="olympiad_amount" min="0"
                                                   step="1" class="form-control @error('olympiad_amount') is-invalid @enderror"
                                                   placeholder="ریال" value="{{ old('olympiad_amount', 0) }}">
                                            @error('olympiad_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-4">
                                            <label for="carry_over_debt" class="form-label">بدهی سال قبل</label>
                                            <input type="number" id="carry_over_debt" name="carry_over_debt" step="1"
                                                   class="form-control @error('carry_over_debt') is-invalid @enderror"
                                                   placeholder="ریال" value="{{ old('carry_over_debt', 0) }}">
                                            @error('carry_over_debt')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- کلاس‌های تابستان --}}
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">کلاس‌های تابستان</h4>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">تعداد جلسات هر درس را وارد کنید؛ خالی یا صفر یعنی ثبت‌نام نشده است.</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="wd-50p">درس</th>
                                                    <th>تعداد جلسات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($subjects as $subject)
                                                    <tr>
                                                        <td>{{ $subject->name }}</td>
                                                        <td>
                                                            <input type="number" min="0" max="999" step="1"
                                                                   name="enrolments[summer][{{ $subject->key }}]"
                                                                   class="form-control @error('enrolments.summer.'.$subject->key) is-invalid @enderror"
                                                                   placeholder="۰"
                                                                   value="{{ old('enrolments.summer.'.$subject->key, 0) }}">
                                                            @error('enrolments.summer.'.$subject->key)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- کلاس‌های فوق برنامه --}}
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">کلاس‌های فوق برنامه</h4>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">تعداد جلسات هر درس را وارد کنید؛ خالی یا صفر یعنی ثبت‌نام نشده است.</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="wd-50p">درس</th>
                                                    <th>تعداد جلسات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($subjects as $subject)
                                                    <tr>
                                                        <td>{{ $subject->name }}</td>
                                                        <td>
                                                            <input type="number" min="0" max="999" step="1"
                                                                   name="enrolments[extra][{{ $subject->key }}]"
                                                                   class="form-control @error('enrolments.extra.'.$subject->key) is-invalid @enderror"
                                                                   placeholder="۰"
                                                                   value="{{ old('enrolments.extra.'.$subject->key, 0) }}">
                                                            @error('enrolments.extra.'.$subject->key)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mb-4">
                                <button type="submit" class="btn btn-primary">ثبت دانش‌آموز</button>
                                <a href="{{ route('students.index', $year) }}" class="btn btn-light">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
