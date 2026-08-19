@extends('layouts.app')

@section('title', 'Vista Previa y Publicación de Parcial')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teacher.assignments.index') }}">Mis Asignaciones</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.assignments.show', $assignment) }}">{{ $assignment->subject?->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Vista Previa {{ $partial->name }}</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Vista Previa para Publicación Oficial - {{ $partial->name }}</h1>
        <p class="text-muted mb-0">Verifique la consistencia de la ponderación y calificaciones antes de publicar los resultados finales.</p>
    </div>
    <a href="{{ route('teacher.assignments.show', $assignment) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a la Asignación
    </a>
</div>

<!-- Header Context Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3 mb-2 mb-md-0">
                <small class="text-muted d-block fs-7">Curso</small>
                <strong class="fs-6 text-dark">{{ $assignment->course?->name }}</strong>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <small class="text-muted d-block fs-7">Asignatura</small>
                <strong class="fs-6 text-primary">{{ $assignment->subject?->name }}</strong>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <small class="text-muted d-block fs-7">Período Lectivo</small>
                <strong class="fs-6 text-dark">{{ $assignment->academicPeriod?->name }}</strong>
            </div>
            <div class="col-md-3 text-md-end">
                <small class="text-muted d-block fs-7">Estado Persistido / Calculado</small>
                <span class="badge bg-secondary fs-7 me-1">{{ $publication->status->label() }}</span>
                <span class="badge bg-info text-dark fs-7">{{ strtoupper($readiness['calculated_status']) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Readiness Summary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <small class="text-muted d-block">Ponderación Total</small>
            <div class="d-flex align-items-center justify-content-between mt-1">
                <span class="fs-4 fw-bold {{ $readiness['total_percentage'] == 100.00 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($readiness['total_percentage'], 2) }}%
                </span>
                <span class="badge bg-light text-dark border">Meta: 100.00%</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <small class="text-muted d-block">Actividades Activas</small>
            <div class="fs-4 fw-bold text-dark mt-1">
                {{ $readiness['active_activities_count'] }}
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <small class="text-muted d-block">Estudiantes Matriculados</small>
            <div class="fs-4 fw-bold text-dark mt-1">
                {{ $readiness['active_students_count'] }}
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <small class="text-muted d-block">Avance de Captura</small>
            <div class="fs-4 fw-bold {{ $readiness['pending_grades_count'] === 0 ? 'text-success' : 'text-warning' }} mt-1">
                {{ $readiness['completed_grades_count'] }} / {{ $readiness['expected_grades_count'] }}
            </div>
        </div>
    </div>
</div>

<!-- Pending Issues Card if not ready -->
@if(!$readiness['is_ready'])
    <div class="card border-danger shadow-sm mb-4">
        <div class="card-header bg-danger text-white py-3">
            <h6 class="m-0 fw-bold">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Requisitos Pendientes para la Publicación
            </h6>
        </div>
        <div class="card-body">
            <p class="text-dark mb-2">Para habilitar la publicación oficial, deben resolverse los siguientes puntos:</p>
            <ul class="mb-0 text-danger fs-7">
                @foreach($readiness['pending_issues'] as $issue)
                    <li>{{ $issue }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<!-- Provisional Averages Preview Table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="card-title fw-bold mb-0 text-primary">
            <i class="bi bi-calculator me-2"></i> Vista Previa de Promedios del Parcial por Estudiante
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Estudiante</th>
                    <th>Correo Electrónico</th>
                    <th class="text-center">Promedio Parcial (Provisional)</th>
                    <th>Estado de Cálculo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentProvisionalResults as $index => $item)
                    @php
                        $calc = $item['calculation'];
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $index + 1 }}</td>
                        <td class="fw-bold text-dark">{{ $item['student']->name }}</td>
                        <td class="text-muted small">{{ $item['student']->email }}</td>
                        <td class="text-center">
                            @if($calc['calculable'])
                                <span class="badge bg-primary fs-6 px-3 py-2">
                                    {{ $calc['score_formatted'] }} / 10.00
                                </span>
                            @else
                                <span class="badge bg-secondary fs-7">No calculable</span>
                            @endif
                        </td>
                        <td>
                            @if($calc['calculable'])
                                <span class="text-success fs-7 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Completo
                                </span>
                            @else
                                <span class="text-danger fs-7">
                                    <i class="bi bi-x-circle me-1"></i> {{ $calc['error'] }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No existen estudiantes matriculados activos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Publication Action Bar -->
<div class="card border-0 shadow-sm p-4 text-end">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            @if($readiness['is_ready'])
                <span class="text-success fw-bold fs-6">
                    <i class="bi bi-check-circle-fill me-1"></i> La estructura de calificaciones está 100% completa y lista para su publicación.
                </span>
            @else
                <span class="text-danger fw-bold fs-6">
                    <i class="bi bi-lock-fill me-1"></i> No se puede publicar hasta resolver todos los requisitos pendientes.
                </span>
            @endif
        </div>
        <div>
            @if($readiness['is_ready'])
                <form action="{{ route('teacher.partial-publications.publish', [$assignment, $partial]) }}" method="POST" onsubmit="return confirm('¿Está seguro de publicar oficialmente este parcial? Las calificaciones no podrán ser editadas posteriormente sin una reapertura administrativa.');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg px-4 fw-bold">
                        <i class="bi bi-send-check me-2"></i> {{ $publication->status->value === 'reopened' ? 'Republicar Parcial' : 'Publicar Parcial Oficial' }}
                    </button>
                </form>
            @else
                <button type="button" class="btn btn-secondary btn-lg px-4" disabled>
                    <i class="bi bi-lock me-2"></i> Publicar Parcial (Incompleto)
                </button>
            @endif
        </div>
    </div>
</div>
@endsection
