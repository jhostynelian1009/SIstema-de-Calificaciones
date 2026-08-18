@extends('layouts.app')

@section('title', 'Gestión de Actividades - ' . ($assignment->subject?->name ?? 'Asignatura') . ' (P' . $partial->number . ')')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 fs-7">
                <li class="breadcrumb-item"><a href="{{ route('teacher.assignments.index') }}">Mis Asignaciones</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.assignments.show', $assignment) }}">{{ $assignment->subject?->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Parcial {{ $partial->number }}</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Actividades Evaluativas — Parcial {{ $partial->number }} (P{{ $partial->number }})</h1>
        <p class="text-muted mb-0 fs-7">
            {{ $assignment->course?->name }} | {{ $assignment->subject?->name }} | {{ $assignment->academicPeriod?->name }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('teacher.assignments.show', $assignment) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a la Asignación
        </a>
        @if(!$summary['is_published'])
            <a href="{{ route('teacher.assignments.partials.activities.create', [$assignment, $partial]) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Nueva Actividad
            </a>
        @endif
    </div>
</div>

<!-- Published Lock Banner -->
@if($summary['is_published'])
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
        <i class="bi bi-lock-fill fs-3 text-warning-emphasis"></i>
        <div>
            <h6 class="fw-bold mb-1 text-warning-emphasis">Parcial Publicado — Edición Bloqueada</h6>
            <p class="mb-0 fs-7 text-dark">
                El estado persistido de este parcial es <strong>Publicado</strong>. Las actividades no se pueden crear, editar ni cambiar de estado mientras el parcial permanezca en este estado.
            </p>
        </div>
    </div>
@endif

<!-- Partial & Weight Summary Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-4">
            <div class="col-md-3 border-end">
                <span class="text-muted fs-8 d-block mb-1">Estado de Publicación</span>
                <span class="badge {{ $summary['publication_status']->badgeClass() }} px-3 py-2 fs-7">
                    {{ $summary['publication_status_label'] }}
                </span>
                <span class="text-muted fs-8 d-block mt-2">Peso del Parcial: <strong>50.00%</strong></span>
            </div>

            <div class="col-md-6 border-end">
                <div class="d-flex justify-content-between align-items-center mb-2 fs-7">
                    <span class="fw-bold text-dark">Avance de Ponderación Acumulada</span>
                    <span class="badge {{ $summary['badge_class'] }} px-3 py-1 fs-8">
                        {{ $summary['weighted_status_label'] }}
                    </span>
                </div>

                <div class="progress rounded-pill mb-2" style="height: 12px;">
                    <div class="progress-bar {{ $summary['total_units'] === 10000 ? 'bg-success' : ($summary['total_units'] > 0 ? 'bg-warning' : 'bg-secondary') }}" 
                         role="progressbar" 
                         style="width: {{ min(100, $summary['total_units'] / 100) }}%;" 
                         aria-valuenow="{{ $summary['total_units'] / 100 }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>

                <div class="d-flex justify-content-between fs-8 text-muted">
                    <span>Asignado: <strong>{{ $summary['total_percentage'] }}%</strong></span>
                    <span>Disponible: <strong>{{ $summary['remaining_percentage'] }}%</strong></span>
                </div>
            </div>

            <div class="col-md-3 text-md-center">
                <span class="text-muted fs-8 d-block mb-1">Actividades Activas</span>
                <h3 class="fw-bold text-primary mb-0">{{ $summary['active_count'] }}</h3>
                <small class="text-muted fs-8">en este parcial</small>
            </div>
        </div>
    </div>
</div>

<!-- Activities List Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0 text-primary">
            <i class="bi bi-list-task me-2"></i> Nómina de Actividades Evaluativas
        </h5>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light fs-7 text-uppercase text-muted">
                <tr>
                    <th style="width: 50px;" class="ps-4">#</th>
                    <th>Nombre de Actividad</th>
                    <th>Fecha de Entrega</th>
                    <th class="text-center">Porcentaje (%)</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end pe-4">Acciones</th>
                </tr>
            </thead>
            <tbody class="fs-7">
                @forelse($activities as $index => $act)
                    <tr class="{{ !$act->active ? 'bg-light text-muted' : '' }}">
                        <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                        <td>
                            <strong class="{{ $act->active ? 'text-dark' : 'text-muted text-decoration-line-through' }}">
                                {{ $act->name }}
                            </strong>
                            @if($act->description)
                                <small class="d-block text-muted text-truncate" style="max-width: 320px;">
                                    {{ $act->description }}
                                </small>
                            @endif
                        </td>
                        <td>
                            @if($act->due_date)
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-calendar3 me-1"></i> {{ $act->due_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted fs-8">Sin fecha</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="fw-bold {{ $act->active ? 'text-primary' : 'text-muted' }}">
                                {{ number_format($act->percentage, 2) }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @if($act->active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-8">
                                    Activa
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-8">
                                    Inactiva
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @if(!$summary['is_published'])
                                <a href="{{ route('teacher.activities.edit', $act) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>

                                <form action="{{ route('teacher.activities.toggle-status', $act) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($act->active)
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('¿Desea desactivar esta actividad? Su porcentaje dejará de participar en el total.')">
                                            <i class="bi bi-slash-circle me-1"></i> Desactivar
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('¿Desea reactivar esta actividad?')">
                                            <i class="bi bi-check-circle me-1"></i> Reactivar
                                        </button>
                                    @endif
                                </form>
                            @else
                                <span class="badge bg-light text-muted border fs-8">
                                    <i class="bi bi-lock-fill"></i> Bloqueado
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-1 d-block mb-2 text-muted"></i>
                            <h6 class="fw-semibold">No existen actividades registradas para este parcial</h6>
                            <p class="mb-0 fs-7">Presione el botón "Nueva Actividad" para comenzar a estructurar la ponderación evaluativa del parcial.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
