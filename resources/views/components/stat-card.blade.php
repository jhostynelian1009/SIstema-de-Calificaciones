@props([
    'icon' => 'bi-bar-chart-fill',
    'color' => 'primary',
    'label' => '',
    'value' => '0',
    'desc' => null
])

<div class="stat-card">
    <div class="stat-icon {{ $color }}">
        <i class="bi {{ $icon }}"></i>
    </div>
    <div>
        <div class="stat-label">{{ $label }}</div>
        <div class="stat-value">{{ $value }}</div>
        @if($desc)
            <div class="stat-desc">{{ $desc }}</div>
        @endif
    </div>
</div>
