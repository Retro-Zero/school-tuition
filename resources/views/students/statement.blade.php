@extends('layouts.blank')

@section('content')
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            background: #eef1f5;
        }

        .statement-toolbar {
            max-width: 210mm;
            margin: 16px auto 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .statement-sheet {
            max-width: 210mm;
            min-height: 280mm;
            margin: 16px auto 32px;
            padding: 14mm 12mm;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 14px rgba(0, 0, 0, .08);
            color: #1f2937;
        }

        .statement-sheet h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .statement-sheet h2 {
            font-size: 15px;
            margin: 4px 0 0;
            color: #555;
            font-weight: 400;
        }

        .statement-head {
            text-align: center;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .statement-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 28px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .statement-meta span.k {
            color: #6b7280;
            margin-left: 4px;
        }

        .statement-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-bottom: 14px;
        }

        .statement-table th,
        .statement-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 9px;
            text-align: right;
        }

        .statement-table thead th {
            background: #f1f5f9;
            font-weight: 700;
        }

        .statement-table tfoot td {
            font-weight: 700;
            background: #f8fafc;
        }

        .statement-table .total-balance td {
            background: #fef2f2;
            color: #b91c1c;
        }

        .statement-table .total-balance.is-settled td {
            background: #f0fdf4;
            color: #15803d;
        }

        .statement-section-title {
            font-size: 13px;
            font-weight: 700;
            margin: 18px 0 8px;
        }

        .statement-undertaking {
            font-size: 12.5px;
            line-height: 2;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            background: #fbfdff;
        }

        .statement-signs {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            margin-top: 34px;
            font-size: 12.5px;
        }

        .statement-signs div {
            flex: 1;
            text-align: center;
            border-top: 1px dashed #94a3b8;
            padding-top: 8px;
            color: #475569;
        }

        .statement-note {
            margin-top: 18px;
            font-size: 11.5px;
            color: #6b7280;
        }

        @media print {
            body {
                background: #fff;
            }

            .statement-toolbar {
                display: none !important;
            }

            .statement-sheet {
                box-shadow: none;
                border-radius: 0;
                margin: 0;
                padding: 0;
                max-width: none;
                min-height: auto;
            }
        }
    </style>

    <div class="statement-toolbar">
        <div class="text-muted small">صورت‌حساب شهریه — {{ $student->first_name }} {{ $student->last_name }}</div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">چاپ</button>
            <a href="{{ route('students.show', [$year, $student]) }}" class="btn btn-light btn-sm">بازگشت</a>
        </div>
    </div>

    <div class="statement-sheet">

        <div class="statement-head">
            <h1>{{ config('school.name') }}</h1>
            <h2>صورت‌حساب شهریه — سال مالی <x-year-name :year="$year" /></h2>
        </div>

        <div class="statement-meta">
            <div><span class="k">نام و نام خانوادگی:</span><strong>{{ $student->first_name }} {{ $student->last_name }}</strong></div>
            <div><span class="k">کد ملی:</span><strong>{{ $student->national_id }}</strong></div>
            <div><span class="k">پایه:</span><strong>{{ $student->gradeLevel->name ?? '—' }}</strong></div>
            <div><span class="k">رشته:</span><strong>{{ $student->studyField->name ?? '—' }}</strong></div>
            <div><span class="k">وضعیت:</span><strong>{{ $student->is_active ? 'فعال' : 'غیرفعال' }}</strong></div>
            <div><span class="k">تاریخ صدور:</span><strong>{{ \App\Support\Jalali::format(now()) }}</strong></div>
        </div>

        <div class="statement-section-title">ریز هزینه‌ها</div>
        <table class="statement-table">
            <thead>
                <tr>
                    <th style="width: 65%">شرح</th>
                    <th>مبلغ (ریال)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>شهریه پایه سالانه</td>
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
                                (کد {{ $student->coupon->code }})
                            @endif
                        </td>
                        <td>{{ $line->discount > 0 ? '−' : '' }}@fa($line->discount)</td>
                    </tr>
                @endif
                <tr>
                    <td>
                        کلاس‌های تابستان (@fa($line->summerSessions) جلسه) و
                        فوق برنامه (@fa($line->extraSessions) جلسه)
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
                        <td>@fa($line->carryOverDebt)</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td>جمع کل هزینه‌ها</td>
                    <td>@fa($line->charges)</td>
                </tr>
                <tr>
                    <td>پرداخت‌شده</td>
                    <td>@fa($line->paid)</td>
                </tr>
                <tr class="total-balance {{ $line->isSettled() ? 'is-settled' : '' }}">
                    <td>باقی‌مانده</td>
                    <td>@fa($line->balance())</td>
                </tr>
            </tfoot>
        </table>

        <div class="statement-section-title">سابقه پرداخت‌ها</div>
        @if ($payments->isEmpty())
            <div style="font-size:12.5px;color:#6b7280">تا تاریخ صدور این صورت‌حساب پرداختی ثبت نشده است.</div>
        @else
            <table class="statement-table">
                <thead>
                    <tr>
                        <th style="width: 18%">تاریخ</th>
                        <th style="width: 22%">مبلغ (ریال)</th>
                        <th style="width: 20%">روش پرداخت</th>
                        <th style="width: 18%">پیگیری</th>
                        <th>توضیح</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <td>{{ \App\Support\Jalali::format($payment->paid_on) }}</td>
                            <td>@fa($payment->amount)</td>
                            <td>{{ $payment->method === 'pos' ? 'کارتخوان (POS)' : 'پرداخت بانکی' }}</td>
                            <td>{{ $payment->reference ?: '—' }}</td>
                            <td>{{ $payment->note ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>جمع پرداخت‌ها</td>
                        <td colspan="4">@fa($line->paid)</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="statement-undertaking">
            اینجانب ولی دانش‌آموز <strong>{{ $student->first_name }} {{ $student->last_name }}</strong> متعهد می‌گردم
            نسبت به پرداخت مبلغ <strong><x-money :value="$line->charges" /></strong> بابت شهریه سال تحصیلی
            <strong><x-year-name :year="$year" /></strong> اقدام کنم.
            @if ($line->balance() > 0)
                تا تاریخ صدور این صورت‌حساب، <strong><x-money :value="$line->paid" /></strong> پرداخت شده و
                <strong><x-money :value="$line->balance()" /></strong> باقی مانده است.
            @elseif ($line->balance() < 0)
                تا تاریخ صدور این صورت‌حساب، <strong><x-money :value="$line->paid" /></strong> پرداخت شده است و
                حساب دانش‌آموز <strong><x-money :value="abs($line->balance())" /> بستانکار</strong> است.
            @else
                تا تاریخ صدور این صورت‌حساب، کل مبلغ شهریه پرداخت شده و حساب دانش‌آموز
                <strong>تسویه</strong> است.
            @endif
        </div>

        <div class="statement-signs">
            <div>امضای ولی دانش‌آموز</div>
            <div>مهر و امضای مدرسه</div>
        </div>

        <div class="statement-note">
            نکته: مبالغ درج‌شده در این صورت‌حساب تا تاریخ صدور معتبر است و ممکن است پس از این تاریخ تغییر کند.
        </div>

    </div>
@endsection
