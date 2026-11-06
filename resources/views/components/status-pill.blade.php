@props(['active' => true])

<span {{ $attributes->class(['badge', $active ? 'badge-soft-success' : 'badge-soft-danger']) }}>
    {{ $active ? 'فعال' : 'غیرفعال' }}
</span>
