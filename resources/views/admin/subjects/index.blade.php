@extends('layouts.app')

@section('title', 'Gestión de Asignaturas - Sistema de Calificaciones')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-book me-2"></i> Asignaturas Académicas
        </h1>
        <p class="text-muted mb-0">Catálogo de materias y áreas de enseñanza</p>
    </div>
    <div>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nueva Asignatura
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.subjects.index') }}" class="row g-3 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o código..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="status" class="form-select">
                    <option value="">-- Todos los estados --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activas únicamente</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivas únicamente</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-grow-1">Filtrar</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-0">
        @if($subjects->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Código</th>
                            <th scope="col">Nombre de Asignatura</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $subject)
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1 fs-7">
                                        {{ $subject->code }}
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark">{{ $subject->name }}</td>
                                <td class="text-muted fs-7">{{ Str::limit($subject->description ?? 'Sin descripción', 60) }}</td>
                                <td>
                                    @if($subject->active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i> Activa
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                            <i class="bi bi-slash-circle me-1"></i> Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil me-1"></i> Editar
                                        </a>
                                        <form method="POST" action="{{ route('admin.subjects.toggle-status', $subject) }}" class="d-inline" onsubmit="return confirm('¿Está seguro de cambiar el estado de esta asignatura?');">
                                            @csrf
                                            @method('PATCH')
                                            @if($subject->active)
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Desactivar">
                                                    <i class="bi bi-pause-circle me-1"></i> Desactivar
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Activar">
                                                    <i class="bi bi-play-circle me-1"></i> Activar
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($subjects->hasPages())
                <div class="card-footer bg-white border-top p-3">
                    {{ $subjects->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-book-half fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-semibold text-secondary mb-1">No se encontraron asignaturas</h5>
                <p class="text-muted fs-7 mb-3">No hay asignaturas registradas o ninguna coincide con los criterios de búsqueda.</p>
                <a href="{{ route('admin.subjects.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Registrar Primera Asignatura
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
