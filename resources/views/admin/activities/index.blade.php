@extends('layouts.app')

@section('title', 'Monitoreo General de Actividades Evaluativas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 fs-7">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administración</a></li>
                <li class="breadcrumb-item active" aria-current="page">Actividades Evaluativas</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Monitoreo General de Actividades Evaluativas</h1>
        <p class="text-muted mb-0 fs-7">Consulta de actividades y ponderaciones registradas por los docentes (Modo Lectura).</p>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.activities.index') }}" class="row g-2 align-items-end fs-7">
            <div class="col-md-2">
                <label for="academic_period_id" class="form-label text-muted fw-semibold fs-8 mb-1">Período Académico</label>
                <select name="academic_period_id" id="academic_period_id" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ request('academic_period_id') == $period->id ? 'selected' : '' }}>
                            {{ $period->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="course_id" class="form-label text-muted fw-semibold fs-8 mb-1">Curso</label>
                <select name="course_id" id="course_id" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="subject_id" class="form-label text-muted fw-semibold fs-8 mb-1">Asignatura</label>
                <select name="subject_id" id="subject_id" class="form-select form-select-sm">
                    <option value="">-- Todas --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="teacher_id" class="form-label text-muted fw-semibold fs-8 mb-1">Docente</label>
                <select name="teacher_id" id="teacher_id" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1">
                <label for="partial_id" class="form-label text-muted fw-semibold fs-8 mb-1">Parcial</label>
                <select name="partial_id" id="partial_id" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    @foreach($partials as $partial)
                        <option value="{{ $partial->id }}" {{ request('partial_id') == $partial->id ? 'selected' : '' }}>
                            P{{ $partial->number }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1">
                <label for="active" class="form-label text-muted fw-semibold fs-8 mb-1">Estado</label>
                <select name="active" id="active" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Activas</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactivas</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-filter me-1"></i> Filtrar
                </button>
                <a href="{{ route('admin.activities.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar Filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light fs-7 text-uppercase text-muted">
                <tr>
                    <th style="width: 50px;" class="ps-4">#</th>
                    <th>Actividad</th>
                    <th>Curso & Asignatura</th>
                    <th>Docente Responsable</th>
                    <th class="text-center">Parcial</th>
                    <th>Entrega</th>
                    <th class="text-center">Porcentaje (%)</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="fs-7">
                @forelse($activities as $index => $act)
                    <tr class="{{ !$act->active ? 'bg-light text-muted' : '' }}">
                        <td class="ps-4 text-muted">{{ $activities->firstItem() + $index }}</td>
                        <td>
                            <strong class="{{ $act->active ? 'text-dark' : 'text-muted text-decoration-line-through' }}">
                                {{ $act->name }}
                            </strong>
                            @if($act->description)
                                <small class="d-block text-muted text-truncate" style="max-width: 250px;">
                                    {{ $act->description }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <div>{{ $act->teachingAssignment?->course?->name }}</div>
                            <small class="text-muted">{{ $act->teachingAssignment?->subject?->name }}</small>
                        </td>
                        <td>
                            <span>{{ $act->teachingAssignment?->teacher?->name }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                P{{ $act->partial?->number }}
                            </span>
                        </td>
                        <td>
                            @if($act->due_date)
                                {{ $act->due_date->format('d/m/Y') }}
                            @else
                                <span class="text-muted fs-8">Sin fecha</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="fw-bold {{ $act->active ? 'text-primary' : 'text-muted' }}">
                                {{ number_format($act->percentage, 2) }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @if($act->active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-8">
                                    Activa
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-8">
                                    Inactiva
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-search fs-1 d-block mb-2"></i>
                            <h6 class="fw-semibold">No se encontraron actividades registradas</h6>
                            <p class="mb-0 fs-7">Ajuste los criterios de búsqueda o verifique que los docentes hayan estructurado sus parciales.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($activities->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $activities->links() }}
        </div>
    @endif
</div>
@endsection
