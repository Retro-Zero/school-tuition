@extends('layouts.app')

@section('title', 'کدهای تخفیف — '.$year->name)


@section('heading', 'کدهای تخفیف')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <h1 class="page-title">لیست کدهای تخفیف</h1>
                </div>

                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <a href="{{ route('coupons.create', $year) }}" class="btn btn-primary mb-4">
                                    افزودن کد تخفیف
                                </a>
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="basic-datatable">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">عنوان کد تخفیف</th>
                                            <th class="wd-15p border-bottom-0">مبلغ تخفیف (ریال)</th>
                                            <th class="wd-15p border-bottom-0">تعداد دانش‌آموز</th>
                                            <th class="wd-15p border-bottom-0" style="text-align: center">عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($coupons as $coupon)
                                            <tr>
                                                <td>{{ $coupon->code }}</td>
                                                <td>@fa($coupon->amount)</td>
                                                <td>
                                                    <span class="badge bg-info">@fa($coupon->students_count)</span>
                                                </td>
                                                <td>
                                                    <div class="row-actions">
                                                        <a href="{{ route('coupons.edit', [$year, $coupon]) }}"
                                                           class="btn-icon" title="ویرایش" aria-label="ویرایش">
                                                            <i class="fe fe-edit-2" aria-hidden="true"></i>
                                                        </a>
                                                        <form method="POST"
                                                              action="{{ route('coupons.destroy', [$year, $coupon]) }}"
                                                              class="m-0"
                                                              onsubmit="return confirm('آیا از حذف این کد تخفیف مطمئن هستید؟');">
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
                                                <td colspan="4" class="text-center text-muted">کد تخفیفی ثبت نشده است.</td>
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
