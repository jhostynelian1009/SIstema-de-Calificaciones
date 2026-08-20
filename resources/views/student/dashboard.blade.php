@extends('layouts.app')

@section('content')
<div class="container py-4">
    @if(!$metrics['has_enrollment'])
        <!-- Empty State for Unenrolled Student -->
        <div class="card shadow-sm border-0 rounded-3 p-5 text-center bg-white">
            <div class="card-body">
                <i class="bi bi-journal-x text-muted display-1 mb-3"></i>
                <h3 class="fw-bold text-dark mb-2">Sin Matrícula Registrada</h3>
                <p class="text-muted max-w-md mx-auto mb-4">
                    Aún no posee una matrícula registrada ni registros históricos en ningún período académico.
                </p>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-person me-1"></i> Ver Mi Perfil
                </a>
            </div>
        </div>
    @else
        <!-- Welcome Header -->
        <div class="card shadow-sm border-0 rounded-3 bg-gradient-primary text-white mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <span class="badge bg-white text-primary mb-2 px-3 py-1 fs-8 text-uppercase fw-bold">
                            <i class="bi bi-person-badge me-1"></i> Panel Estudiantil
                        </span>
                        <h1 class="h2 fw-bold mb-2">¡Bienvenido(a), {{ Auth::user()->name }}!</h1>
                        <p class="mb-0 text-white-50 fs-6">
                            Curso: <strong>{{ $metrics['course']?->name ?? 'N/A' }}</strong> (Código: {{ $metrics['course']?->code }}) 
                            &bull; Período: <strong>{{ $metrics['active_period']?->name }}</strong>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge {{ $metrics['enrollment']?->active ? 'bg-success' : 'bg-secondary' }} fs-7 px-3 py-2 border border-white">
                            <i class="bi {{ $metrics['enrollment']?->active ? 'bi-check-circle-fill' : 'bi-clock-history' }} me-1"></i>
                            Matrícula {{ $metrics['enrollment']?->active ? 'Activa' : 'Histórica (Inactiva)' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-3 bg-white h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-primary-subtle text-primary me-3">
                            <i class="bi bi-book fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-8 text-uppercase fw-semibold d-block">Asignaturas</span>
                            <strong class="h4 fw-bold mb-0 text-dark">{{ $metrics['subjects_count'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-3 bg-white h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-success-subtle text-success me-3">
                            <i class="bi bi-check-circle fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-8 text-uppercase fw-semibold d-block">Resultados Disponibles</span>
                            <strong class="h4 fw-bold mb-0 text-dark">{{ $metrics['available_results_count'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-3 bg-white h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-warning-subtle text-warning-emphasis me-3">
                            <i class="bi bi-hourglass-split fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-8 text-uppercase fw-semibold d-block">Resultados Pendientes</span>
                            <strong class="h4 fw-bold mb-0 text-dark">{{ $metrics['pending_results_count'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-3 bg-white h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-info-subtle text-info me-3">
                            <i class="bi bi-trophy fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-8 text-uppercase fw-semibold d-block">Promedios Oficiales</span>
                            <strong class="h4 fw-bold mb-0 text-dark">{{ $metrics['official_final_count'] }} / {{ $metrics['subjects_count'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promedio General Card -->
        <div class="card shadow-sm border-0 rounded-3 bg-white mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-award me-2 text-primary"></i> Promedio General del Período
                        </h5>
                        <p class="text-muted fs-7 mb-0">
                            Consolidado oficial del rendimiento académico en el período {{ $metrics['active_period']?->name }}
                        </p>
                    </div>
                    <div class="text-center text-md-end">
                        @if($metrics['is_general_official'] && $metrics['general_result'])
                            <div class="h2 fw-bold text-success mb-0">
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
                                <i class="bi bi-info-circle me-1"></i> Pendiente de publicación completa de asignaturas
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Subjects Summary Table -->
        <div class="card shadow-sm border-0 rounded-3 bg-white">
            <div class="card-header bg-light border-bottom p-3 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-dark fs-6">
                    <i class="bi bi-journal-text me-2 text-primary"></i> Resumen de Asignaturas del Período
                </h5>
                <a href="{{ route('student.grades.index') }}" class="btn btn-primary btn-sm fw-semibold">
                    <i class="bi bi-eye me-1"></i> Ver Mis Calificaciones Detalladas
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="bg-light border-bottom text-uppercase text-muted fs-8">
                            <tr>
                                <th class="ps-4 py-3">Asignatura</th>
                                <th class="py-3">Docente</th>
                                <th class="py-3 text-center">Parcial 1</th>
                                <th class="py-3 text-center">Parcial 2</th>
                                <th class="py-3 text-center">Promedio Final</th>
                                <th class="text-end pe-4 py-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
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
                                            <span class="badge bg-success-subtle text-success fs-7 px-2 py-1">
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
                                            <span class="badge bg-success-subtle text-success fs-7 px-2 py-1">
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
                                            <span class="text-primary fs-6">
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
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No hay asignaturas registradas para este período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
