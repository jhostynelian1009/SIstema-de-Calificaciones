@extends('layouts.app')

@section('title', 'Panel de Administración - Sistema de Calificaciones')
@section('header-title', 'Dashboard Principal')

@section('content')
<x-page-header 
    title="Panel de Administración" 
    subtitle="Centro de gestión integral y monitoreo del Sistema de Calificaciones" 
    icon="bi-speedometer2"
>
    <x-slot:actions>
        @if($activePeriod)
            <span class="badge bg-success-subtle p-2 fs-7">
                <i class="bi bi-calendar-check-fill me-1"></i> Período Activo: <strong>{{ $activePeriod->name }}</strong>
            </span>
        @else
            <span class="badge bg-warning-subtle p-2 fs-7">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Sin período activo
            </span>
        @endif
    </x-slot:actions>
</x-page-header>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <a href="{{ route('admin.courses.index') }}" class="text-decoration-none">
            <x-stat-card icon="bi-building" color="primary" label="Cursos" :value="$coursesCount" desc="Gestión de aulas" />
        </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <a href="{{ route('admin.subjects.index') }}" class="text-decoration-none">
            <x-stat-card icon="bi-book" color="teal" label="Asignaturas" :value="$subjectsCount" desc="Malla curricular" />
        </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <a href="{{ route('admin.academic-periods.index') }}" class="text-decoration-none">
            <x-stat-card icon="bi-calendar3" color="green" label="Períodos" :value="$periodsCount" desc="Calendario lectivo" />
        </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
        <a href="{{ route('admin.enrollments.index') }}" class="text-decoration-none">
            <x-stat-card icon="bi-person-badge" color="amber" label="Matrículas Activas" :value="$activeEnrollmentsCount" desc="Estudiantes activos" />
        </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
        <a href="{{ route('admin.teaching-assignments.index') }}" class="text-decoration-none">
            <x-stat-card icon="bi-person-workspace" color="purple" label="Asignaciones" :value="$activeAssignmentsCount" desc="Carga docente activa" />
        </a>
    </div>
</div>

<!-- Partial Publication Status Summary Card -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-card-checklist text-brand-primary fs-5"></i>
            <h5 class="fw-bold mb-0 text-dark fs-6">Monitoreo de Estados de Parciales</h5>
        </div>
        <a href="{{ route('admin.partial-publications.index') }}" class="btn btn-outline-primary btn-sm">
            Ver Todos <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold fs-7 d-block">Borradores (Edición Docente)</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ $draftCount }}</h3>
                    </div>
                    <span class="badge bg-secondary-subtle px-3 py-2">draft</span>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-success fw-semibold fs-7 d-block">Publicados (Oficiales)</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $publishedCount }}</h3>
                    </div>
                    <span class="badge bg-success-subtle px-3 py-2">published</span>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-warning-emphasis fw-semibold fs-7 d-block">Reabiertos (Excepcionales)</span>
                        <h3 class="fw-bold mb-0 text-warning-emphasis">{{ $reopenedCount }}</h3>
                    </div>
                    <span class="badge bg-warning-subtle px-3 py-2">reopened</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Period Detail Card & Quick Actions -->
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="fw-bold mb-0 text-dark fs-6">
                    <i class="bi bi-info-circle me-2 text-brand-primary"></i> Estado del Período Académico Vigente
                </h5>
            </div>
            <div class="card-body">
                @if($activePeriod)
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 pb-3 border-bottom gap-2">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">{{ $activePeriod->name }}</h4>
                            <span class="text-muted fs-7">
                                <i class="bi bi-calendar-range me-1"></i>
                                {{ $activePeriod->starts_at?->format('d/m/Y') }} — {{ $activePeriod->ends_at?->format('d/m/Y') }}
                            </span>
                        </div>
                        <div>
                            <span class="badge bg-success-subtle px-3 py-2 fs-7"><i class="bi bi-check-circle-fill me-1"></i> ACTIVO</span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary mb-3 fs-7 uppercase tracking-wider">Estructura Evaluativa Configurada:</h6>
                    <div class="row g-3">
                        @foreach($activePeriod->partials as $partial)
                            <div class="col-12 col-sm-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-brand-primary">Parcial {{ $partial->number }} (P{{ $partial->number }})</span>
                                        <span class="badge bg-primary-subtle text-primary fs-7">{{ number_format($partial->weight, 0) }}%</span>
                                    </div>
                                    <p class="text-muted fs-8 mb-0 mt-1">{{ $partial->name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state 
                        icon="bi-exclamation-triangle" 
                        title="Sin período activo configurado" 
                        text="No hay un período académico activo en este momento. Active uno para habilitar matrículas y asignaciones."
                    />
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="fw-bold mb-0 text-dark fs-6">
                    <i class="bi bi-lightning-charge me-2 text-brand-primary"></i> Acciones Rápidas
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
