@extends('layouts.app')

@section('title', 'Panel Docente - Sistema de Calificaciones')

@section('content')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1 text-primary">
                    <i class="bi bi-speedometer2 me-2"></i> Panel de Docente
                </h1>
                <p class="text-muted mb-0 fs-7">Bienvenido al área de gestión académica, <strong>{{ $user->name }}</strong></p>
            </div>
            <span class="badge bg-success px-3 py-2 fs-7"><i class="bi bi-check-circle me-1"></i> Docente Activo</span>
        </div>

        <!-- Summary Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                    <div class="card-body p-3 text-center">
                        <span class="text-muted fs-8 d-block mb-1">Asignaciones</span>
                        <h3 class="fw-bold mb-0 text-primary">{{ $active_assignments_count }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                    <div class="card-body p-3 text-center">
                        <span class="text-muted fs-8 d-block mb-1">Cursos</span>
                        <h3 class="fw-bold mb-0 text-info">{{ $courses_count }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                    <div class="card-body p-3 text-center">
                        <span class="text-muted fs-8 d-block mb-1">Estudiantes</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ $unique_students_count }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                    <div class="card-body p-3 text-center">
                        <span class="text-muted fs-8 d-block mb-1">Publicados</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $published_count }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                    <div class="card-body p-3 text-center">
                        <span class="text-muted fs-8 d-block mb-1">En Borrador</span>
                        <h3 class="fw-bold mb-0 text-warning-emphasis">{{ $draft_count + $reopened_count }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                    <div class="card-body p-3 text-center">
                        <span class="text-muted fs-8 d-block mb-1">Notas Pendientes</span>
                        <h3 class="fw-bold mb-0 text-danger">{{ $pending_grades_count }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Priority Actions Section -->
        @if(!empty($priority_actions))
            <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom p-3">
                    <h5 class="fw-bold mb-0 text-danger fs-6">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Acciones Prioritarias Pendientes ({{ count($priority_actions) }})
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        @foreach($priority_actions as $action)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge {{ $action['badge_class'] }} fs-8">Prioridad {{ $action['priority'] }}</span>
                                            <span class="badge bg-outline-secondary border text-dark fs-8">P{{ $action['partial']->number }}</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1 fs-7">{{ $action['title'] }}</h6>
                                        <p class="text-muted fs-8 mb-3">{{ $action['description'] }}</p>
                                    </div>
                                    <div>
                                        <a href="{{ $action['url'] }}" class="btn btn-sm btn-primary w-100 fw-semibold">
                                            {{ $action['button_text'] }} <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Active Assignments Card -->
        <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary fs-6">
                    <i class="bi bi-journal-text me-2"></i> Mis Asignaciones Activas
                </h5>
                <a href="{{ route('teacher.assignments.index') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                    Ver Listado Completo <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="bg-light text-uppercase text-muted fs-8">
                            <tr>
                                <th class="ps-4 py-3">Curso</th>
                                <th class="py-3">Asignatura</th>
                                <th class="py-3">Período</th>
                                <th class="py-3 text-center">Parcial 1 (50%)</th>
                                <th class="py-3 text-center">Parcial 2 (50%)</th>
                                <th class="text-end pe-4 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <span class="fw-bold text-dark">{{ $assignment->course?->code }}</span>
                                        <small class="text-muted d-block fs-8">{{ $assignment->course?->name }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $assignment->subject?->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $assignment->academicPeriod?->name }}</span>
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
                                        <a href="{{ route('teacher.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                                            <i class="bi bi-eye me-1"></i> Detalle Integral
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted fs-7">
                                        No registra asignaciones docentes activas en el período actual.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
