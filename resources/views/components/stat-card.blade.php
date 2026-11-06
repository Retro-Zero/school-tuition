@props([
    'label' => '',
    'value' => '',
    'hint' => null,
    'icon' => 'fe fe-activity',
    'tone' => 'default',
    'money' => false,
])

<div {{ $attributes->class(['stat-card', 'is-'.$tone => $tone !== 'default']) }}>
    <div class="stat-icon"><i class="{{ $icon }}"></i></div>
    <div>
        <div class="stat-label">{{ $label }}</div>
        <div class="stat-value">
            @if ($money)
                <x-money :value="$value" />
            @else
                {{ \App\Support\PersianNumber::digits($value) }}
            @endif
        </div>
        @if ($hint)
            <div class="stat-hint">{{ \App\Support\PersianNumber::digits($hint) }}</div>
        @endif
    </div>
</div>
