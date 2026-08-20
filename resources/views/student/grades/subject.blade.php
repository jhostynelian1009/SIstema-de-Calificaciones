@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Navigation Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb fs-8">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('student.grades.period', $academicPeriod) }}">Mis Calificaciones</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $detail['assignment']?->subject?->name }}</li>
        </ol>
    </nav>

    <!-- Header Card -->
    <div class="card shadow-sm border-0 rounded-3 bg-white mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-12 col-md-8">
                    <span class="badge bg-primary-subtle text-primary mb-2 px-3 py-1 fs-8 text-uppercase fw-bold">
                        <i class="bi bi-book me-1"></i> Detalle por Asignatura
                    </span>
                    <h2 class="h3 fw-bold text-dark mb-1">{{ $detail['assignment']?->subject?->name }}</h2>
                    <p class="text-muted fs-7 mb-0">
                        Código: <strong>{{ $detail['assignment']?->subject?->code }}</strong> &bull; 
                        Docente: <strong>{{ $detail['assignment']?->teacher?->name ?? 'Sin asignar' }}</strong> &bull; 
                        Curso: <strong>{{ $detail['assignment']?->course?->name }}</strong> &bull; 
                        Período: <strong>{{ $academicPeriod->name }}</strong>
                    </p>
                </div>
                <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="p-3 bg-light rounded-3 d-inline-block text-center border">
                        <span class="text-uppercase fs-9 text-muted fw-bold d-block">Promedio Final Asignatura</span>
                        @if($detail['final_result'])
                            <div class="h3 fw-bold text-primary mb-0">
                                {{ $detail['final_result']['score_formatted'] }} <small class="fs-7 text-muted">/ 10,00</small>
                            </div>
                            <span class="badge bg-success-subtle text-success fs-9">Oficial</span>
                        @else
                            <div class="h6 fw-bold text-secondary mb-0 py-1">Pendiente</div>
                            <span class="badge bg-light text-muted border fs-9">Requiere P1 y P2 publicados</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Partial 1 Details -->
    <div class="card shadow-sm border-0 rounded-3 bg-white mb-4">
        <div class="card-header bg-light border-bottom p-3 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold text-dark fs-6">
                <i class="bi bi-1-circle me-2 text-primary"></i> Parcial 1
            </h5>
            @if($detail['p1_detail']['is_published'])
                <span class="badge bg-success-subtle text-success border border-success-subtle fs-8 px-3 py-1">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ $detail['p1_detail']['status_label'] }}
                    @if($detail['p1_detail']['published_at'])
                        <small class="ms-1">({{ \Carbon\Carbon::parse($detail['p1_detail']['published_at'])->format('d/m/Y') }})</small>
                    @endif
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fs-8 px-3 py-1">
                    <i class="bi bi-eye-slash me-1"></i> {{ $detail['p1_detail']['status_label'] }}
                </span>
            @endif
        </div>
        <div class="card-body p-0">
            @if($detail['p1_detail']['is_published'])
                <!-- Partial 1 Summary Header -->
                <div class="p-3 bg-success-subtle border-bottom d-flex align-items-center justify-content-between">
                    <span class="fw-semibold text-success fs-7">
                        <i class="bi bi-calculator me-1"></i> Promedio Oficial Parcial 1:
                    </span>
                    <span class="h4 fw-bold text-success mb-0">
                        {{ $detail['p1_detail']['partial_result']['score_formatted'] ?? '0.00' }} / 10,00
                    </span>
                </div>

                <!-- Partial 1 Activities Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="bg-light border-bottom text-uppercase text-muted fs-8">
                            <tr>
                                <th class="ps-4 py-3">Actividad Evaluativa</th>
                                <th class="py-3 text-center">Porcentaje</th>
                                <th class="py-3 text-center">Nota</th>
                                <th class="py-3 pe-4">Observación del Docente</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($detail['p1_detail']['activities'] as $act)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <span class="fw-bold text-dark d-block">{{ $act['name'] }}</span>
                                        @if($act['description'])
                                            <small class="text-muted d-block fs-8">{{ $act['description'] }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center fw-semibold text-secondary">
                                        {{ number_format((float)$act['percentage'], 2) }} %
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-dark fs-6">
                                            {{ number_format((float)$act['score'], 2) }}
                                        </span>
                                        <small class="text-muted fs-8">/ 10,00</small>
                                    </td>
                                    <td class="pe-4">
                                        @if(trim($act['observation'] ?? '') !== '')
                                            <span class="text-dark bg-light p-2 rounded-2 d-inline-block border fs-8">
                                                <i class="bi bi-chat-left-text me-1 text-primary"></i>
                                                {{ $act['observation'] }}
                                            </span>
                                        @else
                                            <span class="text-muted fs-8">Sin observaciones</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No existen actividades registrales para este parcial.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Hidden Partial 1 Message -->
                <div class="p-5 text-center bg-light">
                    <i class="bi bi-shield-lock text-muted display-4 mb-2"></i>
                    <h5 class="fw-bold text-dark mb-1">{{ $detail['p1_detail']['status_label'] }}</h5>
                    <p class="text-muted fs-7 mb-0">
                        Las actividades y calificaciones de Parcial 1 se mostrarán únicamente cuando hayan sido oficialmente publicadas por la institución.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Partial 2 Details -->
    <div class="card shadow-sm border-0 rounded-3 bg-white mb-4">
        <div class="card-header bg-light border-bottom p-3 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold text-dark fs-6">
                <i class="bi bi-2-circle me-2 text-primary"></i> Parcial 2
            </h5>
            @if($detail['p2_detail']['is_published'])
                <span class="badge bg-success-subtle text-success border border-success-subtle fs-8 px-3 py-1">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ $detail['p2_detail']['status_label'] }}
                    @if($detail['p2_detail']['published_at'])
                        <small class="ms-1">({{ \Carbon\Carbon::parse($detail['p2_detail']['published_at'])->format('d/m/Y') }})</small>
                    @endif
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fs-8 px-3 py-1">
                    <i class="bi bi-eye-slash me-1"></i> {{ $detail['p2_detail']['status_label'] }}
                </span>
            @endif
        </div>
        <div class="card-body p-0">
            @if($detail['p2_detail']['is_published'])
                <!-- Partial 2 Summary Header -->
                <div class="p-3 bg-success-subtle border-bottom d-flex align-items-center justify-content-between">
                    <span class="fw-semibold text-success fs-7">
                        <i class="bi bi-calculator me-1"></i> Promedio Oficial Parcial 2:
                    </span>
                    <span class="h4 fw-bold text-success mb-0">
                        {{ $detail['p2_detail']['partial_result']['score_formatted'] ?? '0.00' }} / 10,00
                    </span>
                </div>

                <!-- Partial 2 Activities Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="bg-light border-bottom text-uppercase text-muted fs-8">
                            <tr>
                                <th class="ps-4 py-3">Actividad Evaluativa</th>
                                <th class="py-3 text-center">Porcentaje</th>
                                <th class="py-3 text-center">Nota</th>
                                <th class="py-3 pe-4">Observación del Docente</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($detail['p2_detail']['activities'] as $act)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <span class="fw-bold text-dark d-block">{{ $act['name'] }}</span>
                                        @if($act['description'])
                                            <small class="text-muted d-block fs-8">{{ $act['description'] }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center fw-semibold text-secondary">
                                        {{ number_format((float)$act['percentage'], 2) }} %
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-dark fs-6">
                                            {{ number_format((float)$act['score'], 2) }}
                                        </span>
                                        <small class="text-muted fs-8">/ 10,00</small>
                                    </td>
                                    <td class="pe-4">
                                        @if(trim($act['observation'] ?? '') !== '')
                                            <span class="text-dark bg-light p-2 rounded-2 d-inline-block border fs-8">
                                                <i class="bi bi-chat-left-text me-1 text-primary"></i>
                                                {{ $act['observation'] }}
                                            </span>
                                        @else
                                            <span class="text-muted fs-8">Sin observaciones</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No existen actividades registrales para este parcial.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Hidden Partial 2 Message -->
                <div class="p-5 text-center bg-light">
                    <i class="bi bi-shield-lock text-muted display-4 mb-2"></i>
                    <h5 class="fw-bold text-dark mb-1">{{ $detail['p2_detail']['status_label'] }}</h5>
                    <p class="text-muted fs-7 mb-0">
                        Las actividades y calificaciones de Parcial 2 se mostrarán únicamente cuando hayan sido oficialmente publicadas por la institución.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
