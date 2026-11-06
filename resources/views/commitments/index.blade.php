@extends('layouts.app')

@section('title', 'پرداختی‌های مقرر ماهانه')


@section('heading', 'پرداختی‌های مقرر ماهانه')
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

                <!-- Row: month tabs -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($months as $m => $monthLabel)
                                        <a href="{{ route('commitments.index', [$year, 'month' => $m]) }}"
                                            class="btn btn-sm {{ (int) $m === (int) $month ? 'btn-primary' : 'btn-outline-primary' }}">
                                            {{ $monthLabel }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Row -->

                <!-- Row: table -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                                <h3 class="card-title">اقساط ماه {{ $label }}</h3>
                                <div class="text-muted" style="font-size:13px">
                                    جمع کل: <span class="fw-bold text-body">@fa($total)</span> ریال
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="basic-datatable">
                                        <thead>
                                        <tr>
                                            <th class="wd-25p border-bottom-0">دانش‌آموز</th>
                                            <th class="wd-15p border-bottom-0">پایه</th>
                                            <th class="wd-15p border-bottom-0">مبلغ قسط</th>
                                            <th class="wd-30p border-bottom-0">ویرایش مبلغ</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($commitments as $commitment)
                                            <tr>
                                                <td>
                                                    {{ trim(($commitment->student->first_name ?? '') . ' ' . ($commitment->student->last_name ?? '')) ?: '—' }}
                                                </td>
                                                <td>{{ $commitment->student?->gradeLevel?->name ?? '—' }}</td>
                                                <td><x-money :value="$commitment->amount" /></td>
                                                <td>
                                                    <form method="POST"
                                                        action="{{ route('commitments.update', [$year, $commitment]) }}"
                                                        class="d-flex align-items-center gap-2 m-0">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="number" name="amount" step="1" min="0"
                                                            class="form-control form-control-sm" style="min-width: 140px"
                                                            value="{{ $commitment->amount }}" required>
                                                        <button type="submit" class="btn btn-sm btn-primary">ذخیره</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    برای ماه {{ $label }} قسطی ثبت نشده است.
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
                <!-- End Row -->

            </div>
            <!-- CONTAINER CLOSED -->
        </div>
    </div>
@endsection
