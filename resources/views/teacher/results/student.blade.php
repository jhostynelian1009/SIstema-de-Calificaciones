@extends('layouts.app')

@section('title', "Boletín de {$student->name} - {$assignment->subject?->name}")

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-file-earmark-person me-2"></i> Boletín Académico de Estudiante
        </h1>
        <p class="text-muted mb-0">Detalle de actividades, ponderaciones, calificaciones y observaciones registradas</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('teacher.results.assignment', $assignment) }}" class="btn btn-outline-secondary fw-semibold px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver a Matriz
        </a>
    </div>
</div>

<!-- Student Header Card -->
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                        <i class="bi bi-person-fill fs-2"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-1">{{ $student->name }}</h4>
                        <div class="d-flex flex-wrap align-items-center gap-2 fs-7 text-muted">
                            <span><i class="bi bi-envelope me-1"></i>{{ $student->email }}</span>
                            <span>•</span>
                            <span><i class="bi bi-building me-1"></i>Curso: <strong>{{ $assignment->course?->name }} ({{ $assignment->course?->code }})</strong></span>
                            <span>•</span>
                            <span><i class="bi bi-journal-text me-1"></i>Asignatura: <strong>{{ $assignment->subject?->name }}</strong></span>
                            <span>•</span>
                            <span><i class="bi bi-calendar3 me-1"></i>Período: <strong>{{ $assignment->academicPeriod?->name }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <div class="p-3 bg-light rounded-3 border text-center">
                    <small class="text-secondary fw-semibold d-block text-uppercase fs-8 mb-1">Promedio Final Asignatura</small>
                    @if($is_final_official && $final_calc['calculable'])
                        <div class="h3 fw-bold mb-0 text-success">
                            {{ $final_calc['score_formatted'] }} <small class="fs-7 text-muted">/ 10.00</small>
                        </div>
                        <span class="badge bg-success text-white fs-9 mt-1">OFICIAL</span>
                    @elseif($final_calc['calculable'])
                        <div class="h3 fw-bold mb-0 text-warning-emphasis">
                            {{ $final_calc['score_formatted'] }} <small class="fs-7 text-muted">/ 10.00</small>
                        </div>
                        <span class="badge bg-warning text-dark fs-9 mt-1">PROVISIONAL</span>
                    @else
                        <div class="h5 fw-bold mb-0 text-muted">— Pendiente —</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Partials Breakdown (P1 and P2) -->
<div class="row g-4 mb-4">
    <!-- Parcial 1 -->
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0 rounded-3 bg-white h-100">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary fs-6">
                    Parcial 1 (Ponderación 50%)
                </h5>
                @if($p1_official)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-8">
                        <i class="bi bi-shield-check me-1"></i> Oficial
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fs-8">
                        <i class="bi bi-clock-history me-1"></i> Provisional
                    </span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($p1_calc && !empty($p1_calc['activities_breakdown']))
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-7">
                            <thead class="bg-light text-uppercase text-muted fs-8">
                                <tr>
                                    <th class="ps-3 py-2">Actividad</th>
                                    <th class="py-2 text-center">Peso</th>
                                    <th class="py-2 text-center">Nota</th>
                                    <th class="pe-3 py-2">Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($p1_calc['activities_breakdown'] as $act)
                                    <tr>
                                        <td class="ps-3 py-2 fw-semibold text-dark">{{ $act['activity_name'] }}</td>
                                        <td class="text-center py-2"><span class="badge bg-light text-dark border">{{ $act['weight_percentage'] }}%</span></td>
                                        <td class="text-center py-2">
                                            @if($act['has_grade'])
                                                <span class="fw-bold {{ $act['score'] >= 7.0 ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format($act['score'], 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted fs-8">—</span>
                                            @endif
                                        </td>
                                        <td class="pe-3 py-2 fs-8 text-muted">{{ $act['observation'] ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-light border-top text-center">
                        <small class="text-muted fs-8 d-block mb-1">Promedio de Parcial 1:</small>
                        @if($p1_calc['calculable'])
                            <strong class="fs-5 {{ $p1_calc['score_hundredths'] >= 700 ? 'text-success' : 'text-danger' }}">
                                {{ $p1_calc['score_formatted'] }} / 10.00
                            </strong>
                        @else
                            <span class="text-muted fs-7">— Calificaciones incompletas —</span>
                        @endif
                    </div>
                @else
                    <div class="p-4 text-center text-muted fs-7">
                        No hay actividades registradas en el Parcial 1.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Parcial 2 -->
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0 rounded-3 bg-white h-100">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary fs-6">
                    Parcial 2 (Ponderación 50%)
                </h5>
                @if($p2_official)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-8">
                        <i class="bi bi-shield-check me-1"></i> Oficial
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fs-8">
                        <i class="bi bi-clock-history me-1"></i> Provisional
                    </span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($p2_calc && !empty($p2_calc['activities_breakdown']))
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-7">
                            <thead class="bg-light text-uppercase text-muted fs-8">
                                <tr>
                                    <th class="ps-3 py-2">Actividad</th>
                                    <th class="py-2 text-center">Peso</th>
                                    <th class="py-2 text-center">Nota</th>
                                    <th class="pe-3 py-2">Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($p2_calc['activities_breakdown'] as $act)
                                    <tr>
                                        <td class="ps-3 py-2 fw-semibold text-dark">{{ $act['activity_name'] }}</td>
                                        <td class="text-center py-2"><span class="badge bg-light text-dark border">{{ $act['weight_percentage'] }}%</span></td>
                                        <td class="text-center py-2">
                                            @if($act['has_grade'])
                                                <span class="fw-bold {{ $act['score'] >= 7.0 ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format($act['score'], 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted fs-8">—</span>
                                            @endif
                                        </td>
                                        <td class="pe-3 py-2 fs-8 text-muted">{{ $act['observation'] ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-light border-top text-center">
                        <small class="text-muted fs-8 d-block mb-1">Promedio de Parcial 2:</small>
                        @if($p2_calc['calculable'])
                            <strong class="fs-5 {{ $p2_calc['score_hundredths'] >= 700 ? 'text-success' : 'text-danger' }}">
                                {{ $p2_calc['score_formatted'] }} / 10.00
                            </strong>
                        @else
                            <span class="text-muted fs-7">— Calificaciones incompletas —</span>
                        @endif
                    </div>
                @else
                    <div class="p-4 text-center text-muted fs-7">
                        No hay actividades registradas en el Parcial 2.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
