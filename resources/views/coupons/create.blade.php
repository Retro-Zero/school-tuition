@extends('layouts.app')

@section('title', 'ایجاد کد تخفیف — '.$year->name)


@section('heading', 'کد تخفیف جدید')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <h1 class="page-title">ایجاد کد تخفیف جدید</h1>
                </div>

                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">اطلاعات کد تخفیف</h4>
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

                                <form method="POST" action="{{ route('coupons.store', $year) }}" class="form-horizontal">
                                    @csrf

                                    <div class="row mb-4">
                                        <label for="code" class="col-md-3 form-label">عنوان کد تخفیف</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                                   id="code" name="code" placeholder="کد تخفیف"
                                                   value="{{ old('code') }}" required autocomplete="off">
                                            @error('code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="amount" class="col-md-3 form-label">مبلغ تخفیف (ریال)</label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                                   id="amount" name="amount" placeholder="مبلغ به ریال" min="0" step="1"
                                                   value="{{ old('amount') }}" required autocomplete="off">
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-0 mt-4 row justify-content-end">
                                        <div class="col-md-9">
                                            <button type="submit" class="btn btn-primary">ثبت</button>
                                            <a href="{{ route('coupons.index', $year) }}" class="btn btn-light">انصراف</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
