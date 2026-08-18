@extends('layouts.app')

@section('title', 'Panel de Administración - Sistema de Calificaciones')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-speedometer2 me-2"></i> Panel de Administración
        </h1>
        <p class="text-muted mb-0">Bienvenido al centro de control del Sistema de Calificaciones</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        @if($activePeriod)
            <div class="badge bg-success-subtle text-success border border-success-subtle p-2 fs-7">
                <i class="bi bi-calendar-check-fill me-1"></i> Período Activo: <strong>{{ $activePeriod->name }}</strong>
            </div>
        @else
            <div class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle p-2 fs-7">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Sin período activo configurado
            </div>
        @endif
    </div>
</div>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-8 d-block mb-1">Cursos</span>
                    <h4 class="fw-bold mb-0 text-primary">{{ $coursesCount }}</h4>
                </div>
                <div class="bg-primary-subtle text-primary rounded-circle p-2 fs-4">
                    <i class="bi bi-building"></i>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2 px-3 text-end">
                <a href="{{ route('admin.courses.index') }}" class="text-decoration-none fs-8 fw-semibold">
                    Gestionar <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-8 d-block mb-1">Asignaturas</span>
                    <h4 class="fw-bold mb-0 text-info">{{ $subjectsCount }}</h4>
                </div>
                <div class="bg-info-subtle text-info rounded-circle p-2 fs-4">
                    <i class="bi bi-book"></i>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2 px-3 text-end">
                <a href="{{ route('admin.subjects.index') }}" class="text-decoration-none fs-8 fw-semibold text-info">
                    Gestionar <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-8 d-block mb-1">Períodos</span>
                    <h4 class="fw-bold mb-0 text-success">{{ $periodsCount }}</h4>
                </div>
                <div class="bg-success-subtle text-success rounded-circle p-2 fs-4">
                    <i class="bi bi-calendar3"></i>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2 px-3 text-end">
                <a href="{{ route('admin.academic-periods.index') }}" class="text-decoration-none fs-8 fw-semibold text-success">
                    Gestionar <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-8 d-block mb-1">Matrículas Activas</span>
                    <h4 class="fw-bold mb-0 text-warning">{{ $activeEnrollmentsCount }}</h4>
                </div>
                <div class="bg-warning-subtle text-warning-emphasis rounded-circle p-2 fs-4">
                    <i class="bi bi-person-badge"></i>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2 px-3 text-end">
                <a href="{{ route('admin.enrollments.index') }}" class="text-decoration-none fs-8 fw-semibold text-warning-emphasis">
                    Ver Matrículas <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-8 d-block mb-1">Asignaciones Docentes</span>
                    <h4 class="fw-bold mb-0 text-indigo" style="color: #6610f2;">{{ $activeAssignmentsCount }}</h4>
                </div>
                <div class="bg-primary-subtle text-primary rounded-circle p-2 fs-4">
                    <i class="bi bi-person-workspace"></i>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2 px-3 text-end">
                <a href="{{ route('admin.teaching-assignments.index') }}" class="text-decoration-none fs-8 fw-semibold text-primary">
                    Ver Asignaciones <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Partial Publication Status Summary Card -->
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary fs-6">
            <i class="bi bi-card-checklist me-2"></i> Monitoreo de Estados de Parciales por Asignaciones Docentes
        </h5>
        <a href="{{ route('admin.partial-publications.index') }}" class="btn btn-outline-primary btn-sm">
            Ver Todos los Estados <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-secondary-subtle border border-secondary-subtle d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary fw-semibold fs-7 d-block">Parciales en Borrador</span>
                        <h3 class="fw-bold mb-0 text-secondary">{{ $draftCount }}</h3>
                    </div>
                    <span class="badge bg-secondary px-3 py-2 fs-7">draft</span>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-success fw-semibold fs-7 d-block">Parciales Publicados</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $publishedCount }}</h3>
                    </div>
                    <span class="badge bg-success px-3 py-2 fs-7">published</span>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-warning-emphasis fw-semibold fs-7 d-block">Parciales Reabiertos</span>
                        <h3 class="fw-bold mb-0 text-warning-emphasis">{{ $reopenedCount }}</h3>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2 fs-7">reopened</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Period Detail Card & Quick Actions -->
<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-info-circle me-2"></i> Estado del Período Académico Vigente
                </h5>
            </div>
            <div class="card-body p-4">
                @if($activePeriod)
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 pb-3 border-bottom gap-2">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">{{ $activePeriod->name }}</h4>
                            <span class="text-muted fs-7">
                                <i class="bi bi-calendar-range me-1"></i>
                                {{ $activePeriod->starts_at?->format('d/m/Y') }} a {{ $activePeriod->ends_at?->format('d/m/Y') }}
                            </span>
                        </div>
                        <div>
                            <span class="badge bg-success px-3 py-2 fs-7">ACTIVO</span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary mb-3">Estructura de Evaluación Configurada:</h6>
                    <div class="row g-3">
                        @foreach($activePeriod->partials as $partial)
                            <div class="col-12 col-sm-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary">Parcial {{ $partial->number }} (P{{ $partial->number }})</span>
                                        <span class="badge bg-primary fs-7">{{ number_format($partial->weight, 0) }}%</span>
                                    </div>
                                    <p class="text-muted fs-8 mb-0 mt-1">{{ $partial->name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning border-0 mb-0 d-flex align-items-center gap-3">
                        <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                        <div>
                            <strong>No hay un período académico activo en este momento.</strong>
                            <p class="mb-0 fs-7">Es necesario activar un período lectivo para habilitar las matrículas y asignaciones académicas.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-lightning-charge me-2"></i> Acciones Rápidas
                </h5>
            </div>
            <div class="card-body p-3">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.partial-publications.index') }}" class="btn btn-outline-primary text-start p-3">
                        <i class="bi bi-card-checklist me-2"></i> Monitorear Parciales
                    </a>
                    <a href="{{ route('admin.enrollments.create') }}" class="btn btn-outline-primary text-start p-3">
                        <i class="bi bi-person-badge me-2"></i> Nueva Matrícula
                    </a>
                    <a href="{{ route('admin.teaching-assignments.create') }}" class="btn btn-outline-primary text-start p-3">
                        <i class="bi bi-person-workspace me-2"></i> Nueva Asignación Docente
                    </a>
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-outline-secondary text-start p-3">
                        <i class="bi bi-plus-circle me-2"></i> Registrar Nuevo Curso
                    </a>
                    <a href="{{ route('admin.subjects.create') }}" class="btn btn-outline-secondary text-start p-3">
                        <i class="bi bi-plus-circle me-2"></i> Registrar Nueva Asignatura
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
