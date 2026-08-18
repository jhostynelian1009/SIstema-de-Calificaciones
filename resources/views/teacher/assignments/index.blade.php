@extends('layouts.app')

@section('title', 'Mis Asignaciones Docentes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Mis Asignaciones Académicas</h1>
        <p class="text-muted mb-0">Cursos y asignaturas bajo su responsabilidad docente en el período vigente.</p>
    </div>
</div>

<div class="row g-4">
    @forelse($assignments as $assignment)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7">
                            {{ $assignment->course?->code ?? 'N/A' }}
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            {{ $assignment->academicPeriod?->name ?? 'N/A' }}
                        </span>
                    </div>

                    <h5 class="card-title fw-bold text-dark mb-1">
                        {{ $assignment->subject?->name ?? 'Asignatura' }}
                    </h5>
                    <p class="card-text text-muted mb-3">
                        <i class="bi bi-building me-1"></i> {{ $assignment->course?->name ?? 'Curso' }}
                    </p>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">
                            <i class="bi bi-tag me-1"></i> Código: {{ $assignment->subject?->code }}
                        </span>
                        <a href="{{ route('teacher.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                            <span>Ver Estudiantes</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm p-5 text-center text-muted">
                <i class="bi bi-journal-x fs-1 d-block mb-3 text-muted"></i>
                <h5 class="fw-semibold">No posee asignaciones docentes activas</h5>
                <p class="mb-0">Actualmente no registra asignaciones activas asignadas para este período lectivo.</p>
            </div>
        </div>
    @endforelse
</div>

@if($assignments->hasPages())
    <div class="mt-4">
        {{ $assignments->links() }}
    </div>
@endif
@endsection
