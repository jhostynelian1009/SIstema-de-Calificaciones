@extends('layouts.app')

@section('title', 'Gestión de Períodos Académicos - Sistema de Calificaciones')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-calendar3 me-2"></i> Períodos Académicos
        </h1>
        <p class="text-muted mb-0">Configuración de lectivos y estructura de parciales (P1 50% / P2 50%)</p>
    </div>
    <div>
        <a href="{{ route('admin.academic-periods.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Período
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.academic-periods.index') }}" class="row g-3 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nombre..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="status" class="form-select">
                    <option value="">-- Todos los estados --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Período activo únicamente</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Períodos inactivos</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-grow-1">Filtrar</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.academic-periods.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-0">
        @if($periods->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Nombre del Período</th>
                            <th scope="col">Fechas (Inicio - Fin)</th>
                            <th scope="col">Estructura Parciales</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($periods as $period)
                            <tr class="{{ $period->active ? 'table-success-subtle' : '' }}">
                                <td class="ps-4 fw-bold text-dark">
                                    {{ $period->name }}
                                    @if($period->active)
                                        <span class="badge bg-success ms-2 fs-8">ACTIVO ACTUAL</span>
                                    @endif
                                </td>
                                <td class="fs-7">
                                    <i class="bi bi-calendar-event me-1 text-muted"></i>
                                    {{ $period->starts_at?->format('d/m/Y') }} &mdash; {{ $period->ends_at?->format('d/m/Y') }}
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @forelse($period->partials as $partial)
                                            <span class="badge bg-light text-dark border font-monospace fs-8">
                                                P{{ $partial->number }}: {{ number_format($partial->weight, 0) }}%
                                            </span>
                                        @empty
                                            <span class="badge bg-danger">Sin parciales</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    @if($period->active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                            <i class="bi bi-slash-circle me-1"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.academic-periods.edit', $period) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil me-1"></i> Editar
                                        </a>
                                        @if(!$period->active)
                                            <form method="POST" action="{{ route('admin.academic-periods.activate', $period) }}" class="d-inline" onsubmit="return confirm('¿Está seguro de activar este período? Se desactivará cualquier otro período activo.');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" title="Activar este período">
                                                    <i class="bi bi-star-fill me-1"></i> Activar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($periods->hasPages())
                <div class="card-footer bg-white border-top p-3">
                    {{ $periods->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-semibold text-secondary mb-1">No se encontraron períodos académicos</h5>
                <p class="text-muted fs-7 mb-3">No hay períodos registrados o ninguno coincide con los criterios de búsqueda.</p>
                <a href="{{ route('admin.academic-periods.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Registrar Primer Período
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
