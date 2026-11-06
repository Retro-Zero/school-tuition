@props([
    'value' => 0,
    'suffix' => 'ریال',
    'strong' => false,
])

{{-- Number grouping, Persian digits, and the currency on its own line when the
     card is narrow — the figure is never truncated. --}}
<span @class(['money', 'fw-bold' => $strong])><bdi class="num">{{ \App\Support\PersianNumber::format((int) $value) }}</bdi>@if ($suffix !== '') <span class="cur">{{ $suffix }}</span>@endif</span>
