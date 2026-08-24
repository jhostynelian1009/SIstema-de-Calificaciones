@props([
    'icon' => 'bi-inbox',
    'title' => 'No se encontraron registros',
    'text' => 'No existen elementos cargados o la búsqueda actual no generó ningún resultado.'
])

<div class="empty-state">
    <div class="empty-icon">
        <i class="bi {{ $icon }}"></i>
    </div>
    <h5 class="empty-title">{{ $title }}</h5>
    <p class="empty-text">{{ $text }}</p>
    @if(isset($action))
        <div>
            {{ $action }}
        </div>
    @endif
</div>
