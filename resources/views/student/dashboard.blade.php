@extends('layouts.app')

@section('title', 'Portal Estudiantil - Sistema de Calificaciones')
@section('header-title', 'Dashboard Estudiantil')

@section('content')
@if(!$metrics['has_enrollment'])
    <x-empty-state 
        icon="bi-journal-x" 
        title="Sin Matrícula Registrada" 
        text="Aún no posee una matrícula registrada ni registros históricos en ningún período académico."
    >
        <x-slot:action>
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-person me-1"></i> Ver Mi Perfil
            </a>
        </x-slot:action>
    </x-empty-state>
@else
    <!-- Welcome Header Banner -->
    <div class="card bg-brand-inst text-white mb-4 border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #1E3A5F 0%, #2563EB 100%);">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-white text-primary mb-2 px-3 py-1 fs-8 text-uppercase fw-bold">
                        <i class="bi bi-person-badge me-1"></i> Portal Estudiantil
                    </span>
                    <h1 class="h2 fw-bold text-white mb-2">¡Bienvenido(a), {{ Auth::user()->name }}!</h1>
                    <p class="mb-0 text-white-50 fs-6">
                        Curso: <strong>{{ $metrics['course']?->name ?? 'N/A' }}</strong> (Código: {{ $metrics['course']?->code }}) 
                        &bull; Período: <strong>{{ $metrics['active_period']?->name }}</strong>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="badge {{ $metrics['enrollment']?->active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} fs-7 px-3 py-2">
                        <i class="bi {{ $metrics['enrollment']?->active ? 'bi-check-circle-fill' : 'bi-clock-history' }} me-1"></i>
                        Matrícula {{ $metrics['enrollment']?->active ? 'Activa' : 'Histórica' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <x-stat-card icon="bi-book" color="primary" label="Asignaturas" :value="$metrics['subjects_count']" />
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <x-stat-card icon="bi-check-circle" color="green" label="Resultados Disponibles" :value="$metrics['available_results_count']" />
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <x-stat-card icon="bi-hourglass-split" color="amber" label="Resultados Pendientes" :value="$metrics['pending_results_count']" />
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <x-stat-card icon="bi-trophy" color="teal" label="Promedios Oficiales" :value="$metrics['official_final_count'] . ' / ' . $metrics['subjects_count']" />
        </div>
    </div>

    <!-- Promedio General Card -->
    <div class="card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1 fs-6">
                        <i class="bi bi-award me-2 text-brand-primary"></i> Promedio General del Período
                    </h5>
                    <p class="text-muted fs-7 mb-0">
                        Consolidado oficial del rendimiento académico en el período {{ $metrics['active_period']?->name }}
                    </p>
                </div>
                <div class="text-center text-md-end">
                    @if($metrics['is_general_official'] && $metrics['general_result'])
                        <div class="h2 fw-bold text-success mb-0 font-monospace-score">
                            {{ $metrics['general_result']['score_formatted'] }} <small class="fs-6 text-muted">/ 10,00</small>
                        </div>
                        <span class="badge bg-success-subtle text-success fs-8">
                            <i class="bi bi-shield-check me-1"></i> Promedio Oficial Confirmado
                        </span>
                    @else
                        <div class="h5 fw-bold text-secondary mb-1">
                            Promedio general pendiente
                        </div>
                        <span class="badge bg-warning-subtle text-warning-emphasis fs-8">
                            <i class="bi bi-info-circle me-1"></i> Pendiente de publicación completa
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects Summary Table -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold text-dark fs-6">
                <i class="bi bi-journal-text me-2 text-brand-primary"></i> Resumen de Asignaturas del Período
            </h5>
            <a href="{{ route('student.grades.index') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-eye me-1"></i> Ver Mis Calificaciones Detalladas
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3">Asignatura</th>
                            <th class="py-3">Docente</th>
                            <th class="py-3 text-center">Parcial 1</th>
                            <th class="py-3 text-center">Parcial 2</th>
                            <th class="py-3 text-center">Promedio Final</th>
                            <th class="text-end pe-4 py-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($metrics['subjects_summary'] as $item)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="fw-bold text-dark">{{ $item['subject']?->name }}</span>
                                    <small class="text-muted d-block fs-8">Cód: {{ $item['subject']?->code }}</small>
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold">{{ $item['teacher']?->name ?? 'Por asignar' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($item['p1_published'] && $item['p1_result'])
                                        <span class="badge bg-success-subtle text-success fs-7 px-2 py-1 font-monospace-score">
                                            {{ $item['p1_result']['score_formatted'] }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border fs-8">
                                            {{ $item['p1_status_label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item['p2_published'] && $item['p2_result'])
                                        <span class="badge bg-success-subtle text-success fs-7 px-2 py-1 font-monospace-score">
                                            {{ $item['p2_result']['score_formatted'] }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border fs-8">
                                            {{ $item['p2_status_label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">
                                    @if($item['final_result'])
                                        <span class="text-brand-primary fs-6 font-monospace-score">
                                            {{ $item['final_result']['score_formatted'] }}
                                        </span>
                                    @else
                                        <span class="text-muted fs-8 fw-normal">— Pendiente —</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('student.grades.subject', [$metrics['active_period'], $item['assignment']]) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-search me-1"></i> Detalle
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <x-empty-state 
                                        icon="bi-journal-x" 
                                        title="Sin asignaturas registradas" 
                                        text="No hay asignaturas registradas para este período."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
