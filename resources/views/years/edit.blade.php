@extends('layouts.app')

@section('title', 'ویرایش سال مالی — '.$year->name)


@section('heading', 'ویرایش سال مالی')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">ویرایش سال مالی <x-year-name :year="$year" /></h1>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Row -->
                <form method="POST" action="{{ route('years.update', $year) }}" class="row">
                    @csrf
                    @method('PUT')

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">ویرایش سال مالی</h4>
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

                                <div class="row mb-4">
                                    <label for="name" class="col-md-3 form-label">نام</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="1403-1404" value="{{ old('name', $year->name) }}"
                                            pattern="^\d{4}-\d{4}$" title="مثال: 1403-1404" maxlength="9" required>
                                        <small class="text-muted fs-12">به شکل ۱۴۰۳-۱۴۰۴ (با ارقام انگلیسی)</small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label class="col-md-3 form-label">وضعیت</label>
                                    <div class="col-md-9">
                                        <div class="form-check">
                                            <input type="hidden" name="is_open" value="0">
                                            <input type="checkbox" class="form-check-input" id="is_open" name="is_open"
                                                value="1" {{ old('is_open', $year->is_open) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_open">
                                                باز بودن سال مالی برای ثبت جدید
                                            </label>
                                        </div>
                                        <small class="text-muted fs-12">
                                            سال مالی بسته فقط برای مشاهده و گزارش است.
                                        </small>
                                    </div>
                                </div>

                                <div class="mb-0 mt-4 row justify-content-end">
                                    <div class="col-md-9 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">ثبت</button>
                                        <a href="{{ route('years.index') }}" class="btn btn-light">انصراف</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- End Row -->

            </div>
            <!-- CONTAINER END -->
        </div>
    </div>
@endsection
