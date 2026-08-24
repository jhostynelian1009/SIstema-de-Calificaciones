@extends('layouts.app')

@section('title', 'Panel Docente - Sistema de Calificaciones')
@section('header-title', 'Panel de Docente')

@section('content')
<x-page-header 
    title="Panel de Docente" 
    subtitle="Bienvenido al área de gestión académica, {{ $user->name }}" 
    icon="bi-speedometer2"
>
    <x-slot:actions>
        <span class="badge bg-success-subtle p-2 fs-7"><i class="bi bi-check-circle-fill me-1"></i> Docente Activo</span>
    </x-slot:actions>
</x-page-header>

<!-- Summary Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <x-stat-card icon="bi-journal-bookmark" color="primary" label="Asignaciones" :value="$active_assignments_count" />
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <x-stat-card icon="bi-building" color="teal" label="Cursos" :value="$courses_count" />
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <x-stat-card icon="bi-people" color="purple" label="Estudiantes" :value="$unique_students_count" />
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <x-stat-card icon="bi-check-circle" color="green" label="Publicados" :value="$published_count" />
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <x-stat-card icon="bi-pencil-square" color="amber" label="En Borrador" :value="$draft_count + $reopened_count" />
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <x-stat-card icon="bi-clock-history" color="red" label="Notas Pendientes" :value="$pending_grades_count" />
    </div>
</div>

<!-- Priority Actions Section -->
@if(!empty($priority_actions))
    <div class="card mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="fw-bold mb-0 text-danger fs-6">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Acciones Prioritarias Pendientes ({{ count($priority_actions) }})
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($priority_actions as $action)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge {{ $action['badge_class'] }} fs-8">Prioridad {{ $action['priority'] }}</span>
                                    <span class="badge bg-secondary-subtle fs-8">P{{ $action['partial']->number }}</span>
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
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark fs-6">
            <i class="bi bi-journal-text me-2 text-brand-primary"></i> Mis Asignaciones Activas
        </h5>
        <a href="{{ route('teacher.assignments.index') }}" class="btn btn-outline-primary btn-sm">
            Ver Listado Completo <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Curso</th>
                        <th>Asignatura</th>
                        <th>Período</th>
                        <th class="text-center">Parcial 1 (50%)</th>
                        <th class="text-center">Parcial 2 (50%)</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td class="ps-4">
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
                                <a href="{{ route('teacher.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> Detalle Integral
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <x-empty-state 
                                    icon="bi-journal-x" 
                                    title="Sin asignaciones activas" 
                                    text="No registra asignaciones docentes activas en el período lectivo actual."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
