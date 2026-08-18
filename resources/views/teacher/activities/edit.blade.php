@extends('layouts.app')

@section('title', 'Editar Actividad Evaluativa - ' . $activity->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 fs-7">
                <li class="breadcrumb-item"><a href="{{ route('teacher.assignments.index') }}">Mis Asignaciones</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.assignments.show', $assignment) }}">{{ $assignment->subject?->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.assignments.partials.activities.index', [$assignment, $partial]) }}">Parcial {{ $partial->number }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Actividad</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Editar Actividad Evaluativa</h1>
        <p class="text-muted mb-0 fs-7">Parcial {{ $partial->number }} | {{ $assignment->course?->name }} | {{ $assignment->subject?->name }}</p>
    </div>
    <a href="{{ route('teacher.assignments.partials.activities.index', [$assignment, $partial]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Cancelar y Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 text-primary">Modificar Actividad</h5>
                    <span class="badge {{ $summary['badge_class'] }} px-3 py-1 fs-8">
                        Disponible: {{ $summary['remaining_percentage'] }}%
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('teacher.activities.update', $activity) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @include('teacher.activities._form', ['activity' => $activity])

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('teacher.assignments.partials.activities.index', [$assignment, $partial]) }}" class="btn btn-light px-4">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
