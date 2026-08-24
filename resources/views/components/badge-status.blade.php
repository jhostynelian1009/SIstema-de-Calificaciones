@props([
    'active' => true,
    'activeText' => 'Activo',
    'inactiveText' => 'Inactivo'
])

@if($active)
    <span class="badge bg-success-subtle"><i class="bi bi-check-circle-fill me-1"></i> {{ $activeText }}</span>
@else
    <span class="badge bg-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i> {{ $inactiveText }}</span>
@endif
