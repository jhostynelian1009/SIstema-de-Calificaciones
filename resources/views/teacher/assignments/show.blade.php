@extends('layouts.app')

@section('title', 'Detalle de Asignación - ' . ($assignment->subject?->name ?? 'Asignatura'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teacher.assignments.index') }}">Mis Asignaciones</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $assignment->subject?->name }}</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">{{ $assignment->subject?->name }} ({{ $assignment->subject?->code }})</h1>
    </div>
    <a href="{{ route('teacher.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Context Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4 mb-2 mb-md-0">
                <small class="text-muted d-block">Curso Académico</small>
                <strong class="fs-6 text-dark">{{ $assignment->course?->name }}</strong>
                <span class="badge bg-light text-dark border ms-1">{{ $assignment->course?->code }}</span>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <small class="text-muted d-block">Período Lectivo</small>
                <strong class="fs-6 text-dark">{{ $assignment->academicPeriod?->name }}</strong>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-muted d-block">Total Estudiantes Matriculados</small>
                <span class="badge bg-primary fs-6 px-3 py-2">{{ $students->count() }} Estudiantes</span>
            </div>
        </div>
    </div>
</div>

<!-- Partials Status Overview Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="card-title fw-bold mb-0 text-primary">
            <i class="bi bi-calendar-range me-2"></i> Estructura de Parciales y Estados de Publicación
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @forelse($assignment->partialPublications->sortBy(fn($p) => $p->partial->number) as $pub)
                <div class="col-12 col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                {{ $pub->partial?->name ?? 'Parcial '.$loop->iteration }} (P{{ $pub->partial?->number }})
                            </span>
                            <span class="badge bg-primary fs-7">
                                Peso: {{ number_format($pub->partial?->weight ?? 50, 0) }}%
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fs-7">Estado de Publicación:</span>
                            <span class="badge {{ $pub->status->badgeClass() }} px-3 py-2 fs-7">
                                {{ $pub->status->label() }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted">
                    No se han inicializado los parciales para esta asignación.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Enrolled Students List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">Nómina de Estudiantes Matriculados Activos</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Estudiante</th>
                    <th>Correo Electrónico</th>
                    <th class="text-center">Estado de Matrícula</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $index => $student)
                    <tr>
                        <td class="text-muted">{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $student->name }}</td>
                        <td class="text-muted small">{{ $student->email }}</td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Matriculado Activo</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-person-x fs-2 d-block mb-2 text-muted"></i>
                            No existen estudiantes con matrícula activa registrados en este curso y período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
