@props([
    'value' => 0,
    'suffix' => 'ریال',
    'strong' => false,
])

{{-- One place for money formatting: number grouping, currency label, tabular digits. --}}
<span @class(['money', 'fw-bold' => $strong])>@fa((int) $value)@if ($suffix !== '') {{ $suffix }}@endif</span>
