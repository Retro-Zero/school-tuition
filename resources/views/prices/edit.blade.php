@extends('layouts.app')

@section('title', 'تعرفه‌ها — '.$year->name)


@section('heading', 'تعرفه‌ها')
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">تعرفه‌های تحصیلی <x-year-name :year="$year" /></h1>
                </div>
                <!-- PAGE-HEADER END -->

                <form method="POST" action="{{ route('prices.update', $year) }}" class="row">
                    @csrf
                    @method('PUT')

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title mb-0">تعرفه‌های تحصیلی</h4>
                                <small class="text-muted">مبالغ به ریال</small>
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
                                    <label for="insurance" class="col-md-3 form-label">هزینه بیمه</label>
                                    <div class="col-md-9">
                                        <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control"
                                            placeholder="هزینه بیمه" name="insurance" id="insurance" data-money
                                            value="{{ old('insurance', $book->insurance_amount) }}" required>
                                        <span class="text-muted fs-12" data-preview-for="insurance">—</span>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom mb-0">
                                        <thead>
                                            <tr>
                                                <th class="wd-15p border-bottom-0">پایه</th>
                                                <th class="border-bottom-0">شهریه پایه آن پایه</th>
                                                <th class="border-bottom-0">نرخ هر جلسه تابستان</th>
                                                <th class="border-bottom-0">نرخ هر جلسه فوق‌العاده</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($grades as $grade)
                                                <tr>
                                                    <td class="fw-bold">{{ $grade->name }}</td>
                                                    <td>
                                                        <input type="text" inputmode="numeric" pattern="[0-9]*"
                                                            class="form-control" name="base[{{ $grade->id }}]"
                                                            id="base-{{ $grade->id }}" data-money
                                                            value="{{ old('base.'.$grade->id, $base[$grade->id] ?? 0) }}"
                                                            required>
                                                        <span class="text-muted fs-12"
                                                            data-preview-for="base-{{ $grade->id }}">—</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" inputmode="numeric" pattern="[0-9]*"
                                                            class="form-control" name="units[{{ $grade->id }}|summer]"
                                                            id="unit-{{ $grade->id }}-summer" data-money
                                                            value="{{ old('units.'.$grade->id.'|summer', $units[$grade->id.'|summer']->amount ?? 0) }}"
                                                            required>
                                                        <span class="text-muted fs-12"
                                                            data-preview-for="unit-{{ $grade->id }}-summer">—</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" inputmode="numeric" pattern="[0-9]*"
                                                            class="form-control" name="units[{{ $grade->id }}|extra]"
                                                            id="unit-{{ $grade->id }}-extra" data-money
                                                            value="{{ old('units.'.$grade->id.'|extra', $units[$grade->id.'|extra']->amount ?? 0) }}"
                                                            required>
                                                        <span class="text-muted fs-12"
                                                            data-preview-for="unit-{{ $grade->id }}-extra">—</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">
                                                        پایه تحصیلی ثبت نشده است.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mb-0 mt-4 row justify-content-end">
                                    <div class="col-md-9 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">ثبت</button>
                                        <a href="{{ route('report', $year) }}" class="btn btn-light">بازگشت به گزارش</a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>

            </div>
            <!-- CONTAINER END -->
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            function refresh(input) {
                var digits = (input.value || '').replace(/[^0-9]/g, '');

                if (input.value !== digits) {
                    input.value = digits;
                }

                var span = document.querySelector('[data-preview-for="' + input.id + '"]');

                if (!span) {
                    return;
                }

                span.textContent = digits === ''
                    ? '—'
                    : Number(digits).toLocaleString('en-US') + ' ریال';
            }

            document.querySelectorAll('input[data-money]').forEach(function (input) {
                input.addEventListener('input', function () {
                    refresh(input);
                });

                refresh(input);
            });
        })();
    </script>
@endpush
