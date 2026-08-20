@extends('layouts.app')

@section('title', "Asignación - {$assignment->subject?->name} ({$assignment->course?->code})")

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-journal-check me-2"></i> Detalle Integral de Asignación
        </h1>
        <p class="text-muted mb-0">Gestión de parciales, actividades, calificaciones y estudiantes matriculados</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('teacher.assignments.index') }}" class="btn btn-outline-secondary fw-semibold px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver a Asignaciones
        </a>
    </div>
</div>

@if(!$assignment->active)
    <div class="alert alert-warning border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
        <div>
            <strong>Asignación Inactiva (Histórico de Solo Lectura):</strong> Esta asignación pertenece a un ciclo culminado o inactivado. Puede consultar los registros y calificaciones, pero no realizar modificaciones.
        </div>
    </div>
@endif

<!-- Assignment Header Card -->
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-book-fill fs-2"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-1">{{ $assignment->subject?->name }}</h4>
                        <div class="d-flex flex-wrap align-items-center gap-2 fs-7 text-muted">
                            <span><i class="bi bi-building me-1"></i>Curso: <strong>{{ $assignment->course?->name }} ({{ $assignment->course?->code }})</strong></span>
                            <span>•</span>
                            <span><i class="bi bi-calendar3 me-1"></i>Período: <strong>{{ $assignment->academicPeriod?->name }}</strong></span>
                            <span>•</span>
                            <span><i class="bi bi-person me-1"></i>Docente: <strong>{{ $assignment->teacher?->name }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-7 mb-1 d-block d-md-inline-block">
                    <i class="bi bi-people-fill me-1"></i> {{ $enrollments->total() }} Estudiantes Enrolados
                </span>
                @if($assignment->active)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-7 ms-md-1 d-block d-md-inline-block">
                        <i class="bi bi-check-circle-fill me-1"></i> Asignación Activa
                    </span>
                @else
                    <span class="badge bg-secondary text-white px-3 py-2 fs-7 ms-md-1 d-block d-md-inline-block">
                        <i class="bi bi-eye-fill me-1"></i> Inactiva
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Partials Breakdown Cards (P1 and P2) -->
<h5 class="fw-bold text-dark mb-3">
    <i class="bi bi-layers-half me-2 text-primary"></i> Estado y Gestión por Parcial
</h5>

<div class="row g-4 mb-4">
    @foreach($assignment->partialPublications->sortBy(fn ($p) => $p->partial->number) as $pub)
        @php
            $partial = $pub->partial;
            $summary = $partialSummaries[$partial->id] ?? null;
            $readiness = $readinessMap[$partial->id] ?? null;
            $provAvg = $provisionalAverages[$partial->id] ?? null;
            $status = $pub->status;
        @endphp

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 rounded-3 bg-white h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-primary fs-6">
                            Parcial {{ $partial->number }} (Poderación 50%)
                        </h6>
                        <small class="text-muted fs-8">Período: {{ $assignment->academicPeriod?->name }}</small>
                    </div>
                    <div>
                        <span class="badge {{ $status->badgeClass() }} px-3 py-2 fs-7">
                            {{ $status->label() }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if($status === \App\Enums\PublicationStatus::Reopened && $pub->reopen_reason)
                        <div class="alert alert-warning border-0 p-3 mb-3 fs-8 d-flex align-items-start gap-2 rounded-3">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
                            <div>
                                <strong>Motivo de Reapertura Administrativa:</strong><br>
                                {{ $pub->reopen_reason }}
                            </div>
                        </div>
                    @endif

                    <!-- Visual 4-Step Flow Sequence -->
                    <div class="bg-light p-3 rounded-3 mb-4 border">
                        <small class="text-secondary fw-semibold d-block text-uppercase fs-9 mb-2">Secuencia del Parcial</small>
                        <div class="row g-2 text-center fs-8">
                            <!-- Step 1: Actividades -->
                            <div class="col-3">
                                @php
                                    $step1Done = ($readiness['total_percentage'] ?? 0) == 100.0;
                                @endphp
                                <div class="p-2 rounded border {{ $step1Done ? 'bg-success-subtle text-success border-success-subtle' : 'bg-white text-muted' }}">
                                    <i class="bi bi-list-task d-block fs-6 mb-1"></i>
                                    1. Actividades
                                    <span class="d-block fs-9 fw-bold">{{ $readiness['total_percentage'] ?? 0 }}%</span>
                                </div>
                            </div>
                            <!-- Step 2: Calificaciones -->
                            <div class="col-3">
                                @php
                                    $step2Done = ($readiness['missing_grades'] ?? 1) === 0 && ($readiness['total_activities'] ?? 0) > 0;
                                @endphp
                                <div class="p-2 rounded border {{ $step2Done ? 'bg-success-subtle text-success border-success-subtle' : 'bg-white text-muted' }}">
                                    <i class="bi bi-journal-check d-block fs-6 mb-1"></i>
                                    2. Notas
                                    <span class="d-block fs-9 fw-bold">{{ $readiness['recorded_grades'] ?? 0 }}/{{ $readiness['expected_grades'] ?? 0 }}</span>
                                </div>
                            </div>
                            <!-- Step 3: Revisión -->
                            <div class="col-3">
                                @php
                                    $step3Done = ($readiness['is_ready'] ?? false);
                                @endphp
                                <div class="p-2 rounded border {{ $step3Done ? 'bg-success-subtle text-success border-success-subtle' : 'bg-white text-muted' }}">
                                    <i class="bi bi-clipboard-check d-block fs-6 mb-1"></i>
                                    3. Revisión
                                    <span class="d-block fs-9 fw-bold">{{ $step3Done ? 'Listo' : 'Pendiente' }}</span>
                                </div>
                            </div>
                            <!-- Step 4: Publicación -->
                            <div class="col-3">
                                <div class="p-2 rounded border {{ $status === \App\Enums\PublicationStatus::Published ? 'bg-success text-white' : 'bg-white text-muted' }}">
                                    <i class="bi bi-send-check d-block fs-6 mb-1"></i>
                                    4. Publicación
                                    <span class="d-block fs-9 fw-bold">{{ $status->label() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="row g-3 mb-3 fs-7">
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <span class="text-muted fs-8 d-block">Actividades Configuradas:</span>
                                <strong class="text-dark">{{ $readiness['total_activities'] ?? 0 }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <span class="text-muted fs-8 d-block">Porcentaje Total:</span>
                                <strong class="{{ ($readiness['total_percentage'] ?? 0) == 100.0 ? 'text-success' : 'text-danger' }}">
                                    {{ $readiness['total_percentage'] ?? 0 }}% / 100%
                                </strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <span class="text-muted fs-8 d-block">Notas Registradas:</span>
                                <strong class="text-dark">{{ $readiness['recorded_grades'] ?? 0 }} / {{ $readiness['expected_grades'] ?? 0 }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <span class="text-muted fs-8 d-block">Notas Pendientes:</span>
                                <strong class="{{ ($readiness['missing_grades'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $readiness['missing_grades'] ?? 0 }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- Promedio Info -->
                    <div class="p-3 border rounded-3 bg-light mb-4 text-center">
                        <small class="text-secondary fw-semibold d-block text-uppercase fs-8 mb-1">
                            Estado General del Parcial
                        </small>
                        @if($status === \App\Enums\PublicationStatus::Published)
                            <span class="badge bg-success-subtle text-success fs-7 px-3 py-2"><i class="bi bi-shield-check me-1"></i> Publicado el {{ $pub->published_at?->format('d/m/Y H:i') }}</span>
                        @elseif($status === \App\Enums\PublicationStatus::Reopened)
                            <span class="badge bg-warning-subtle text-warning-emphasis fs-7 px-3 py-2"><i class="bi bi-arrow-counterclockwise me-1"></i> Reabierto (Edición Habilitada)</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary fs-7 px-3 py-2"><i class="bi bi-pencil-square me-1"></i> En Borrador ({{ $readiness['missing_grades'] ?? 0 }} notas faltantes)</span>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        @if($assignment->active && $status !== \App\Enums\PublicationStatus::Published)
                            <a href="{{ route('teacher.assignments.partials.activities.index', [$assignment, $partial]) }}" class="btn btn-outline-primary fw-semibold">
                                <i class="bi bi-list-task me-1"></i> Gestionar Actividades ({{ $readiness['total_percentage'] ?? 0 }}%)
                            </a>
                            <a href="{{ route('teacher.assignments.partials.grades.index', [$assignment, $partial]) }}" class="btn btn-outline-success fw-semibold">
                                <i class="bi bi-pencil-square me-1"></i> Registrar / Editar Calificaciones
                            </a>
                            <a href="{{ route('teacher.partial-publications.preview', [$assignment, $partial]) }}" class="btn btn-primary fw-semibold">
                                <i class="bi bi-send-check me-1"></i> Revisar y Publicar Parcial {{ $partial->number }}
                            </a>
                        @else
                            <a href="{{ route('teacher.results.assignment', $assignment) }}" class="btn btn-outline-info fw-semibold">
                                <i class="bi bi-eye me-1"></i> Ver Resultados Oficiales del Parcial
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Enrolled Students Section (Section 8) -->
<div class="card shadow-sm border-0 rounded-3 bg-white">
    <div class="card-header bg-white border-bottom p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h5 class="fw-bold mb-0 text-primary fs-6">
            <i class="bi bi-people me-2"></i> Estudiantes Matriculados en la Asignación
        </h5>
        <form method="GET" action="{{ route('teacher.assignments.show', $assignment) }}" class="d-flex gap-2">
            <input type="text"
                   name="search"
                   class="form-control form-control-sm bg-light"
                   placeholder="Buscar estudiante..."
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">Buscar</button>
        </form>
    </div>
    <div class="card-body p-0">
        @if($enrollments->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-person-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                <h6 class="fw-bold mb-1">No se encontraron estudiantes</h6>
                <p class="fs-7 mb-0">No hay matrículas activas o históricas registradas bajo los criterios de búsqueda.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="bg-light border-bottom text-uppercase text-muted fs-8">
                        <tr>
                            <th class="ps-4 py-3">Estudiante</th>
                            <th class="py-3">Correo Electrónico</th>
                            <th class="py-3 text-center">Estado de Matrícula</th>
                            <th class="py-3 text-center">Fecha Registro</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($enrollments as $enrollment)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                            <i class="bi bi-person-fill fs-6"></i>
                                        </div>
                                        <span class="fw-bold text-dark">{{ $enrollment->student?->name }}</span>
                                    </div>
                                </td>
                                <td><span class="text-muted">{{ $enrollment->student?->email }}</span></td>
                                <td class="text-center">
                                    @if($enrollment->active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-8">
                                            <i class="bi bi-check-circle-fill me-1"></i> Matrícula Activa
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-8">
                                            <i class="bi bi-clock-history me-1"></i> Histórica / Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center"><span class="text-muted fs-8">{{ $enrollment->created_at?->format('d/m/Y') }}</span></td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('teacher.results.student', ['assignment' => $assignment->id, 'student' => $enrollment->student_id]) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                        <i class="bi bi-file-earmark-person me-1"></i> Ver Notas
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top bg-light">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
