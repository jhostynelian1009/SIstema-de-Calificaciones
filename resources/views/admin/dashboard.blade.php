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
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-7 d-block mb-1">Cursos Registrados</span>
                    <h3 class="fw-bold mb-0 text-primary">{{ $coursesCount }}</h3>
                </div>
                <div class="bg-primary-subtle text-primary rounded-circle p-3 fs-3">
                    <i class="bi bi-building"></i>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2 px-4 text-end">
                <a href="{{ route('admin.courses.index') }}" class="text-decoration-none fs-7 fw-semibold">
                    Gestionar <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-7 d-block mb-1">Asignaturas</span>
                    <h3 class="fw-bold mb-0 text-info">{{ $subjectsCount }}</h3>
                </div>
                <div class="bg-info-subtle text-info rounded-circle p-3 fs-3">
                    <i class="bi bi-book"></i>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2 px-4 text-end">
                <a href="{{ route('admin.subjects.index') }}" class="text-decoration-none fs-7 fw-semibold text-info">
                    Gestionar <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-7 d-block mb-1">Períodos Lectivos</span>
                    <h3 class="fw-bold mb-0 text-success">{{ $periodsCount }}</h3>
                </div>
                <div class="bg-success-subtle text-success rounded-circle p-3 fs-3">
                    <i class="bi bi-calendar3"></i>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2 px-4 text-end">
                <a href="{{ route('admin.academic-periods.index') }}" class="text-decoration-none fs-7 fw-semibold text-success">
                    Gestionar <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-7 d-block mb-1">Usuarios Totales</span>
                    <h3 class="fw-bold mb-0 text-secondary">{{ $usersCount }}</h3>
                </div>
                <div class="bg-secondary-subtle text-secondary rounded-circle p-3 fs-3">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2 px-4 text-end">
                <span class="text-muted fs-7">Base de Usuarios</span>
            </div>
        </div>
    </div>
</div>

<!-- Active Period Detail Card -->
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
                            <p class="mb-0 fs-7">Es necesario activar un período lectivo para habilitar la matrículas y asignaciones académicas.</p>
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
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-outline-primary text-start p-3">
                        <i class="bi bi-plus-circle me-2"></i> Registrar Nuevo Curso
                    </a>
                    <a href="{{ route('admin.subjects.create') }}" class="btn btn-outline-info text-start p-3 text-dark">
                        <i class="bi bi-plus-circle me-2 text-info"></i> Registrar Nueva Asignatura
                    </a>
                    <a href="{{ route('admin.academic-periods.create') }}" class="btn btn-outline-success text-start p-3">
                        <i class="bi bi-plus-circle me-2"></i> Crear Período Académico
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
