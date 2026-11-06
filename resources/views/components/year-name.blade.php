@props(['year'])

{{-- <bdi> isolates the digit run, otherwise RTL text renders 1402-1403 as 1403-1402 --}}
<bdi {{ $attributes }}>{{ \App\Support\PersianNumber::digits($year->name) }}</bdi>
