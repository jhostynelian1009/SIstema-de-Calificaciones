@props([
    'title' => '',
    'subtitle' => null,
    'icon' => null
])

<div class="page-header">
    <div>
        <h1 class="page-header-title">
            @if($icon)
                <i class="bi {{ $icon }} me-2 text-brand-primary"></i>
            @endif
            {{ $title }}
        </h1>
        @if($subtitle)
            <p class="page-header-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="d-flex align-items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
