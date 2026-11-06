@props(['year'])

<span {{ $attributes->class(['badge', $year->is_open ? 'badge-soft-success' : 'badge-soft-muted']) }}>
    <bdi>{{ \App\Support\PersianNumber::digits($year->name) }}</bdi>
</span>
