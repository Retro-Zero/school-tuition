@extends('layouts.app')

@section('title', 'سال‌های مالی')


@section('heading', 'سال‌های مالی')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">لیست سال‌های مالی</h1>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- ROW-1 : new year -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">ایجاد سال مالی جدید</h4>
                            </div>
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('years.store') }}">
                                    @csrf
                                    <div class="row mb-4">
                                        <label for="name" class="col-md-3 form-label">نام سال مالی</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="1403-1404" value="{{ old('name') }}"
                                                pattern="^\d{4}-\d{4}$" title="مثال: 1403-1404" maxlength="9"
                                                required>
                                            <small class="text-muted fs-12">به شکل ۱۴۰۳-۱۴۰۴ (با ارقام انگلیسی)</small>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <label for="starts_on" class="col-md-3 form-label">تاریخ شروع (اختیاری)</label>
                                        <div class="col-md-9">
                                            <input type="date" class="form-control" id="starts_on" name="starts_on"
                                                value="{{ old('starts_on') }}">
                                        </div>
                                    </div>
                                    <div class="mb-0 mt-4 row justify-content-end">
                                        <div class="col-md-9">
                                            <button type="submit" class="btn btn-primary">ثبت سال مالی</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ROW-1 END -->

                <!-- ROW-2 : years table -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">سال‌های مالی ثبت‌شده</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="basic-datatable">
                                        <thead>
                                            <tr>
                                                <th class="wd-15p border-bottom-0">نام سال</th>
                                                <th class="wd-15p border-bottom-0">تعداد دانش‌آموزان</th>
                                                <th class="wd-15p border-bottom-0">وضعیت</th>
                                                <th class="wd-15p border-bottom-0">عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($years as $item)
                                                <tr>
                                                    <td class="fw-bold">{{ $item->name }}</td>
                                                    <td>@fa($item->students_count)</td>
                                                    <td>
                                                        @if ($item->is_open)
                                                            <span class="badge bg-success-transparent">باز</span>
                                                        @else
                                                            <span class="badge bg-danger-transparent">بسته</span>
                                                        @endif
                                                    </td>
                                                    <td style="display:flex;align-items:center;justify-content:space-around">
                                                        <a href="{{ route('report', $item) }}" class="btn btn-sm btn-primary">
                                                            گزارش
                                                        </a>
                                                        <a href="{{ route('prices.edit', $item) }}" class="btn btn-sm btn-info">
                                                            تعرفه‌ها
                                                        </a>
                                                        <a href="{{ route('years.edit', $item) }}" class="btn btn-sm btn-warning">
                                                            ویرایش
                                                        </a>
                                                        <form method="POST" action="{{ route('years.destroy', $item) }}"
                                                            class="m-0"
                                                            onsubmit="return confirm('سال مالی {{ $item->name }} حذف شود؟')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">
                                                        هنوز سال مالی ثبت نشده است.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ROW-2 END -->

            </div>
            <!-- CONTAINER END -->
        </div>
    </div>
@endsection
