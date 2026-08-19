@extends('layouts.app')

@section('title', 'Monitoreo General de Calificaciones')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">
            <i class="bi bi-journal-check me-2 text-primary"></i>Monitoreo General de Calificaciones
        </h1>
        <p class="text-muted mb-0">Consulta general e historial de calificaciones registradas por el personal docente (Solo lectura).</p>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold text-primary">
            <i class="bi bi-funnel me-1"></i> Filtros de Búsqueda
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.grades.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="academic_period_id" class="form-label fs-7 fw-bold">Período Académico</label>
                <select name="academic_period_id" id="academic_period_id" class="form-select form-select-sm">
                    <option value="">-- Todos los Períodos --</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ request('academic_period_id') == $period->id ? 'selected' : '' }}>
                            {{ $period->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="course_id" class="form-label fs-7 fw-bold">Curso</label>
                <select name="course_id" id="course_id" class="form-select form-select-sm">
                    <option value="">-- Todos los Cursos --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="subject_id" class="form-label fs-7 fw-bold">Asignatura</label>
                <select name="subject_id" id="subject_id" class="form-select form-select-sm">
                    <option value="">-- Todas las Asignaturas --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="teacher_id" class="form-label fs-7 fw-bold">Docente Responsable</label>
                <select name="teacher_id" id="teacher_id" class="form-select form-select-sm">
                    <option value="">-- Todos los Docentes --</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="partial_id" class="form-label fs-7 fw-bold">Parcial</label>
                <select name="partial_id" id="partial_id" class="form-select form-select-sm">
                    <option value="">-- Todos los Parciales --</option>
                    @foreach($partials as $partial)
                        <option value="{{ $partial->id }}" {{ request('partial_id') == $partial->id ? 'selected' : '' }}>
                            {{ $partial->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-search me-1"></i> Filtrar Results
                </button>
                <a href="{{ route('admin.grades.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-x-circle me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Grades Table Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        @if($grades->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 text-secondary mb-2 d-block"></i>
                No se encontraron calificaciones registradas con los criterios seleccionados.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Estudiante</th>
                            <th>Curso / Asignatura</th>
                            <th>Parcial</th>
                            <th>Actividad (Peso)</th>
                            <th class="text-center">Nota</th>
                            <th>Observación</th>
                            <th>Docente Auditor</th>
                            <th>Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grades as $grade)
                            @php
                                $assignment = $grade->activity->teachingAssignment;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $grade->student->name }}</div>
                                    <small class="text-muted">{{ $grade->student->email }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $assignment->course->name }}</div>
                                    <small class="text-muted">{{ $assignment->subject->name }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary fs-8">{{ $grade->activity->partial->name }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $grade->activity->name }}</div>
                                    <span class="badge bg-info text-dark fs-8">{{ number_format($grade->activity->percentage, 2) }}%</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6 px-3 py-2">
                                        {{ number_format($grade->score, 2) }} / 10.00
                                    </span>
                                </td>
                                <td>
                                    <div class="text-wrap fs-7" style="max-width: 300px;">
                                        {{ $grade->observation }}
                                    </div>
                                </td>
                                <td>
                                    <small class="fw-bold text-dark">{{ $grade->gradedBy->name }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $grade->graded_at ? $grade->graded_at->format('d/m/Y H:i') : '-' }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white py-3">
                {{ $grades->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
