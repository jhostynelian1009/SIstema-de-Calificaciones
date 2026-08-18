@extends('layouts.app')

@section('title', 'Asignaciones Docentes')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Asignaciones Docentes</h1>
        <p class="text-muted mb-0">Gestión de docentes responsables por curso, asignatura y período académico.</p>
    </div>
    <div>
        <a href="{{ route('admin.teaching-assignments.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-person-workspace"></i>
            <span>Nueva Asignación</span>
        </a>
    </div>
</div>

<!-- Card Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.teaching-assignments.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="teacher_id" class="form-label text-muted small fw-semibold">Docente</label>
                <select name="teacher_id" id="teacher_id" class="form-select">
                    <option value="">Todos los docentes</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="course_id" class="form-label text-muted small fw-semibold">Curso</label>
                <select name="course_id" id="course_id" class="form-select">
                    <option value="">Todos los cursos</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }} ({{ $course->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="subject_id" class="form-label text-muted small fw-semibold">Asignatura</label>
                <select name="subject_id" id="subject_id" class="form-select">
                    <option value="">Todas las asignaturas</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }} ({{ $subject->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="period_id" class="form-label text-muted small fw-semibold">Período</label>
                <select name="period_id" id="period_id" class="form-select">
                    <option value="">Todos los períodos</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ request('period_id') == $period->id ? 'selected' : '' }}>
                            {{ $period->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <select name="active" id="active" class="form-select">
                    <option value="">Estado</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactivos</option>
                </select>
                <button type="submit" class="btn btn-outline-primary" title="Filtrar">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Teaching Assignments Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Docente Responsable</th>
                    <th>Curso</th>
                    <th>Asignatura</th>
                    <th>Período</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                    <tr>
                        <td class="fw-semibold">
                            <i class="bi bi-person-fill text-primary me-1"></i>
                            {{ $assignment->teacher?->name ?? 'Docente No Encontrado' }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $assignment->course?->name ?? 'N/A' }} ({{ $assignment->course?->code ?? 'N/A' }})
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                {{ $assignment->subject?->name ?? 'N/A' }} ({{ $assignment->subject?->code ?? 'N/A' }})
                            </span>
                        </td>
                        <td>{{ $assignment->academicPeriod?->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if($assignment->active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Activa</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactiva</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.teaching-assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-secondary" title="Reasignar Docente">
                                    <i class="bi bi-person-gear"></i>
                                </a>
                                <form action="{{ route('admin.teaching-assignments.toggle-status', $assignment) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($assignment->active)
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Desactivar" onclick="return confirm('¿Está seguro de desactivar esta asignación docente?');">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Activar">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                            No se encontraron asignaciones docentes registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($assignments->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $assignments->links() }}
        </div>
    @endif
</div>
@endsection
