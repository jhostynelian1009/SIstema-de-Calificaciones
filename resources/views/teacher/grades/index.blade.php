@extends('layouts.app')

@section('title', 'Registro de Calificaciones - ' . $assignment->subject->name)

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teacher.assignments.index') }}">Mis Asignaciones</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.assignments.show', $assignment) }}">{{ $assignment->course->name }} - {{ $assignment->subject->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Calificaciones {{ $partial->name }}</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">
            <i class="bi bi-journal-check me-2 text-primary"></i>Captura de Calificaciones: {{ $partial->name }}
        </h1>
        <p class="text-muted mb-0">
            <strong>Curso:</strong> {{ $assignment->course->name }} |
            <strong>Asignatura:</strong> {{ $assignment->subject->name }} |
            <strong>Período:</strong> {{ $assignment->academicPeriod->name }}
        </p>
    </div>
    <div class="col-auto">
        <a href="{{ route('teacher.assignments.show', $assignment) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a la Asignación
        </a>
    </div>
</div>

<!-- Header Summary Card -->
<div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-md-3 border-end-md">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="text-muted small">Estado de Publicación:</span>
                    <span class="badge {{ $publication->status->badgeClass() }} fs-7">
                        {{ $publication->status->label() }}
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Estado de Captura:</span>
                    <span class="badge {{ $partialMetrics['status_badge_class'] }} fs-7">
                        {{ $partialMetrics['status_label'] }}
                    </span>
                </div>
            </div>

            <div class="col-md-6 border-end-md">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold fs-7">Avance General del Parcial:</span>
                    <span class="fw-bold text-primary fs-7">{{ $partialMetrics['completed_grades'] }} / {{ $partialMetrics['expected_grades'] }} ({{ $partialMetrics['completion_percentage'] }}%)</span>
                </div>
                <div class="progress" style="height: 12px;">
                    <div class="progress-bar {{ $partialMetrics['completion_percentage'] == 100 ? 'bg-success' : 'bg-primary' }} progress-bar-striped"
                         role="progressbar"
                         style="width: {{ $partialMetrics['completion_percentage'] }}%;"
                         aria-valuenow="{{ $partialMetrics['completion_percentage'] }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-1 fs-8 text-muted">
                    <span>{{ $partialMetrics['active_students_count'] }} Estudiantes activos</span>
                    <span>{{ $partialMetrics['active_activities_count'] }} Actividades activas</span>
                    <span>{{ $partialMetrics['pending_grades'] }} Notas pendientes</span>
                </div>
            </div>

            <div class="col-md-3 text-center">
                <a href="{{ route('teacher.assignments.partials.activities.index', [$assignment, $partial]) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="bi bi-sliders me-1"></i> Gestionar Actividades ({{ $activitySummary['total_percentage'] }}%)
                </a>
            </div>
        </div>
    </div>
</div>

@if($isPublished)
    <div class="alert alert-warning d-flex align-items-center gap-3 shadow-sm mb-4" role="alert">
        <i class="bi bi-lock-fill fs-2 text-warning"></i>
        <div>
            <h5 class="alert-heading mb-1 fw-bold">Parcial Publicado - Calificaciones Bloqueadas</h5>
            <p class="mb-0 fs-7">
                Este parcial ya ha sido publicado oficialmente. El registro y la modificación de calificaciones se encuentran <strong>bloqueados</strong>. Para efectuar correcciones se requiere una reapertura administrativa.
            </p>
        </div>
    </div>
@endif

<!-- Activity Selector Navigation Tabs -->
<div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">
                <i class="bi bi-list-task me-1"></i> Seleccione una Actividad Evaluativa
            </h6>
        </div>
    </div>
    <div class="card-body p-2 bg-light border-bottom">
        @if($activities->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="bi bi-exclamation-circle fs-3 text-warning mb-2 d-block"></i>
                No se han registrado actividades evaluativas activas en este parcial.<br>
                <a href="{{ route('teacher.assignments.partials.activities.create', [$assignment, $partial]) }}" class="btn btn-sm btn-primary mt-2">
                    <i class="bi bi-plus-circle me-1"></i> Crear Primera Actividad
                </a>
            </div>
        @else
            <ul class="nav nav-pills nav-fill gap-2">
                @foreach($activities as $act)
                    @php
                        $isSelected = $selectedActivity && $selectedActivity->id === $act->id;
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link text-start py-2 px-3 {{ $isSelected ? 'active bg-primary' : 'bg-white border text-dark' }}"
                           href="{{ route('teacher.assignments.partials.grades.index', array_filter([
                               'assignment' => $assignment->id,
                               'partial' => $partial->id,
                               'activity_id' => $act->id,
                               'search' => request('search'),
                           ])) }}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold {{ $isSelected ? 'text-white' : 'text-primary' }}">{{ $act->name }}</span>
                                <span class="badge {{ $isSelected ? 'bg-light text-primary' : 'bg-secondary text-white' }} fs-8">
                                    {{ number_format($act->percentage, 2) }}%
                                </span>
                            </div>
                            @if($act->due_date)
                                <div class="fs-8 {{ $isSelected ? 'text-white-50' : 'text-muted' }}">
                                    <i class="bi bi-calendar-event me-1"></i>Entrega: {{ $act->due_date->format('d/m/Y') }}
                                </div>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

@if($selectedActivity)
    <!-- Active Activity Grading Form -->
    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-header bg-primary text-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h5 class="card-title mb-0 fw-bold fs-6">
                    <i class="bi bi-pencil-square me-2"></i>Matriz de Calificaciones: {{ $selectedActivity->name }} ({{ number_format($selectedActivity->percentage, 2) }}%)
                </h5>
                @if($selectedActivity->description)
                    <small class="text-white-50 fs-8">{{ $selectedActivity->description }}</small>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($activityMetrics)
                    <span class="badge bg-light text-primary fs-7">
                        Avance Global: {{ $activityMetrics['completed_grades'] }} / {{ $activityMetrics['active_students_count'] }} ({{ $activityMetrics['completion_percentage'] }}%)
                    </span>
                @endif
            </div>
        </div>

        <!-- Student Search Filter Bar -->
        <div class="p-3 bg-light border-bottom">
            <form method="GET" action="{{ route('teacher.assignments.partials.grades.index', [$assignment, $partial]) }}" class="row g-2 align-items-center">
                <input type="hidden" name="activity_id" value="{{ $selectedActivity->id }}">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Buscar estudiante por nombre o correo..."
                               value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('teacher.assignments.partials.grades.index', [$assignment, $partial, 'activity_id' => $selectedActivity->id]) }}" class="btn btn-outline-secondary">Limpiar</a>
                        @endif
                        <button type="submit" class="btn btn-primary fw-semibold">Buscar</button>
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <small class="text-muted fs-8">Mostrando 25 estudiantes por página</small>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if($enrolledStudents->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 text-secondary mb-2 d-block"></i>
                    No se encontraron estudiantes matriculados bajo los criterios de búsqueda.
                </div>
            @else
                <form action="{{ route('teacher.assignments.partials.grades.bulk-upsert', [$assignment, $partial]) }}" method="POST" id="bulkGradesForm">
                    @csrf
                    <input type="hidden" name="activity_id" value="{{ $selectedActivity->id }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0 fs-7">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th style="width: 250px;">Estudiante</th>
                                    <th style="width: 150px;" class="text-center">Nota (0.00 - 10.00)</th>
                                    <th>Observación / Retroalimentación <span class="text-danger">*</span></th>
                                    <th style="width: 140px;" class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrolledStudents as $index => $student)
                                    @php
                                        $existingGrade = $gradesMap->get($student->id);
                                        $scoreValue = old("grades.{$index}.score", $existingGrade !== null ? number_format($existingGrade->score, 2) : '');
                                        $obsValue = old("grades.{$index}.observation", $existingGrade ? $existingGrade->observation : '');
                                        $isGraded = $existingGrade !== null;
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-bold text-muted">
                                            {{ (($studentsPaginated->currentPage() - 1) * $studentsPaginated->perPage()) + $loop->iteration }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $student->name }}</div>
                                            <small class="text-muted fs-8">{{ $student->email }}</small>
                                            <input type="hidden" name="grades[{{ $index }}][student_id]" value="{{ $student->id }}">
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       max="10"
                                                       class="form-control text-center fw-bold @error("grades.{$index}.score") is-invalid @enderror"
                                                       name="grades[{{ $index }}][score]"
                                                       value="{{ $scoreValue }}"
                                                       placeholder="0.00"
                                                       {{ $isPublished ? 'disabled' : '' }}>
                                                <span class="input-group-text">/ 10</span>
                                            </div>
                                            @error("grades.{$index}.score")
                                                <div class="invalid-feedback d-block fs-8">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="form-control form-control-sm @error("grades.{$index}.observation") is-invalid @enderror"
                                                   name="grades[{{ $index }}][observation]"
                                                   value="{{ $obsValue }}"
                                                   maxlength="500"
                                                   placeholder="Desempeño y observaciones (mín. 3 caracteres)"
                                                   {{ $isPublished ? 'disabled' : '' }}>
                                            @error("grades.{$index}.observation")
                                                <div class="invalid-feedback d-block fs-8">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-center">
                                            @if($isGraded)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 fs-8">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Registrada
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-1 px-2 fs-8">
                                                    <i class="bi bi-clock me-1"></i> Pendiente
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top bg-light d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <div>
                            {{ $studentsPaginated->links() }}
                        </div>
                        @if(! $isPublished)
                            <div>
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-save me-1"></i> Guardar Calificaciones de la Página Visible
                                </button>
                            </div>
                        @endif
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let formDirty = false;
        const form = document.getElementById('bulkGradesForm');
        if (form) {
            form.querySelectorAll('input[name^="grades"]').forEach(input => {
                input.addEventListener('change', () => formDirty = true);
                input.addEventListener('input', () => formDirty = true);
            });
            form.addEventListener('submit', () => formDirty = false);
            window.addEventListener('beforeunload', function (e) {
                if (formDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        }
    });
</script>
@endsection
