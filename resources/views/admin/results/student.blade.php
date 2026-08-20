@extends('layouts.app')

@section('title', "Boletín de Calificaciones - {$student->name}")

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-file-earmark-text me-2"></i> Boletín Académico de Estudiante
        </h1>
        <p class="text-muted mb-0">Supervisión detallada de notas y promedios por asignatura</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.results.index', ['academic_period_id' => $selectedPeriodId]) }}" class="btn btn-outline-secondary fw-semibold px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver a Consolidado
        </a>
    </div>
</div>

<!-- Student Header Card -->
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-person-fill fs-2"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">{{ $student->name }}</h4>
                        <p class="text-muted fs-7 mb-1"><i class="bi bi-envelope me-1"></i>{{ $student->email }}</p>
                        @if($enrollment)
                            <span class="badge bg-primary me-1"><i class="bi bi-building me-1"></i>{{ $enrollment->course?->name }} ({{ $enrollment->course?->code }})</span>
                            <span class="badge bg-secondary"><i class="bi bi-calendar3 me-1"></i>Período: {{ $enrollment->academicPeriod?->name }}</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Sin matrícula activa en el período seleccionado</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-5 text-md-end">
                @if($overallResult)
                    <div class="p-3 bg-light rounded-3 border d-inline-block text-center">
                        <small class="text-secondary fw-semibold d-block text-uppercase fs-8 mb-1">Promedio General del Estudiante</small>
                        @if($overallResult['is_official'])
                            <div class="h2 fw-bold mb-0 text-success">{{ $overallResult['overall_average_formatted'] }}</div>
                            <span class="badge bg-success px-2 py-1 fs-9 mt-1"><i class="bi bi-shield-check me-1"></i> RESULTADO OFICIAL</span>
                        @else
                            <div class="h2 fw-bold mb-0 text-warning-emphasis">{{ $overallResult['overall_average_formatted'] }}</div>
                            <span class="badge bg-warning text-dark px-2 py-1 fs-9 mt-1"><i class="bi bi-clock-history me-1"></i> PROVISIONAL / EN PROGRESO</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Subjects Detailed Breakdowns -->
@if(empty($subjectResults))
    <div class="card shadow-sm border-0 rounded-3 bg-white p-5 text-center text-muted">
        <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
        <h5 class="fw-bold mb-1">No se encontraron calificaciones</h5>
        <p class="fs-7 mb-0">El estudiante no posee asignaturas registradas para el período seleccionado.</p>
    </div>
@else
    <div class="row g-4">
        @foreach($subjectResults as $item)
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3 bg-white">
                    <div class="card-header bg-white border-bottom p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <h5 class="fw-bold mb-0 text-primary fs-6">
                                <i class="bi bi-book me-2"></i> {{ $item['subject']->name }}
                            </h5>
                            <small class="text-muted">Docente: {{ $item['teacher']?->name ?? 'No asignado' }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($item['p1_official'] && $item['p2_official'] && $item['final_calc']['calculable'])
                                <div class="badge bg-success px-3 py-2 fs-7">
                                    Promedio Final Oficial: <strong>{{ $item['final_calc']['score_formatted'] }}</strong>
                                </div>
                            @else
                                <div class="badge bg-secondary-subtle text-secondary px-3 py-2 fs-7">
                                    Promedio Final Pendiente de Publicación
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <!-- Parcial 1 -->
                            <div class="col-12 col-md-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-dark fs-7">Parcial 1 (50%)</span>
                                        @if($item['p1_official'])
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-8">Publicado / Oficial</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fs-8">Provisional / Borrador</span>
                                        @endif
                                    </div>
                                    @if($item['p1_calc'] && $item['p1_calc']['calculable'])
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-muted fs-8">Nota Calculada:</span>
                                            <span class="fw-bold fs-5 {{ $item['p1_calc']['score_hundredths'] >= 700 ? 'text-success' : 'text-danger' }}">
                                                {{ $item['p1_calc']['score_formatted'] }} / 10.00
                                            </span>
                                        </div>
                                    @else
                                        <p class="text-muted fs-8 mb-0 mt-2">Parcial incompleto o en desarrollo.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Parcial 2 -->
                            <div class="col-12 col-md-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-dark fs-7">Parcial 2 (50%)</span>
                                        @if($item['p2_official'])
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-8">Publicado / Oficial</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fs-8">Provisional / Borrador</span>
                                        @endif
                                    </div>
                                    @if($item['p2_calc'] && $item['p2_calc']['calculable'])
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-muted fs-8">Nota Calculada:</span>
                                            <span class="fw-bold fs-5 {{ $item['p2_calc']['score_hundredths'] >= 700 ? 'text-success' : 'text-danger' }}">
                                                {{ $item['p2_calc']['score_formatted'] }} / 10.00
                                            </span>
                                        </div>
                                    @else
                                        <p class="text-muted fs-8 mb-0 mt-2">Parcial incompleto o en desarrollo.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
