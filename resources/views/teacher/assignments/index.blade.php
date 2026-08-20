@extends('layouts.app')

@section('title', 'Mis Asignaciones - Sistema de Calificaciones')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-journal-bookmark me-2"></i> Mis Asignaciones Académicas
        </h1>
        <p class="text-muted mb-0">Consulte y gestione las asignaturas y cursos bajo su responsabilidad</p>
    </div>
</div>

<!-- Search & Filters -->
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('teacher.assignments.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <input type="text"
                       name="search"
                       class="form-control bg-light"
                       placeholder="Buscar por curso o asignatura..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-2">
                <select name="academic_period_id" class="form-select bg-light">
                    <option value="">Todos los Períodos</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ (string) request('academic_period_id') === (string) $period->id ? 'selected' : '' }}>
                            {{ $period->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select name="course_id" class="form-select bg-light">
                    <option value="">Todos los Cursos</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ (string) request('course_id') === (string) $course->id ? 'selected' : '' }}>
                            {{ $course->code }} - {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select name="subject_id" class="form-select bg-light">
                    <option value="">Todas las Asignaturas</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-1">
                <select name="active" class="form-select bg-light">
                    <option value="">Estado</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Activas</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactivas</option>
                </select>
            </div>
            <div class="col-12 col-md-2 text-md-end">
                <a href="{{ route('teacher.assignments.index') }}" class="btn btn-light btn-sm me-1">Limpiar</a>
                <button type="submit" class="btn btn-primary btn-sm fw-semibold px-3">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assignments Table -->
<div class="card shadow-sm border-0 rounded-3 bg-white">
    <div class="card-body p-0">
        @if($assignments->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                <h5 class="fw-bold mb-1">No se encontraron asignaciones</h5>
                <p class="fs-7 mb-0">Intente modificar los filtros aplicados para visualizar otras asignaciones.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="bg-light border-bottom text-uppercase text-muted fs-8">
                        <tr>
                            <th class="ps-4 py-3">Curso</th>
                            <th class="py-3">Asignatura</th>
                            <th class="py-3">Período Académico</th>
                            <th class="py-3 text-center">Estado Asignación</th>
                            <th class="py-3 text-center">Parcial 1</th>
                            <th class="py-3 text-center">Parcial 2</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($assignments as $assignment)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="fw-bold text-dark">{{ $assignment->course?->code }}</span>
                                    <small class="text-muted d-block fs-8">{{ $assignment->course?->name }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $assignment->subject?->name }}</span>
                                    <small class="text-muted d-block fs-8">Cód: {{ $assignment->subject?->code }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $assignment->academicPeriod?->name }}</span>
                                </td>
                                <td class="text-center">
                                    @if($assignment->active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-8">
                                            <i class="bi bi-check-circle-fill me-1"></i> Activa
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-8">
                                            <i class="bi bi-eye-fill me-1"></i> Inactiva (Histórico)
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php $p1 = $assignment->partialPublications->firstWhere('partial.number', 1); @endphp
                                    @if($p1)
                                        <span class="badge {{ $p1->status->badgeClass() }} px-2 py-1 fs-8">
                                            {{ $p1->status->label() }}
                                        </span>
                                    @else
                                        <span class="text-muted fs-8">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php $p2 = $assignment->partialPublications->firstWhere('partial.number', 2); @endphp
                                    @if($p2)
                                        <span class="badge {{ $p2->status->badgeClass() }} px-2 py-1 fs-8">
                                            {{ $p2->status->label() }}
                                        </span>
                                    @else
                                        <span class="text-muted fs-8">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('teacher.assignments.show', $assignment) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                        <i class="bi bi-eye me-1"></i> Detalle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top bg-light">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
