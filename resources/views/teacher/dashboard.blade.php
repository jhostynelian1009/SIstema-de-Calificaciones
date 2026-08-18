@extends('layouts.app')

@section('title', 'Panel Docente - Sistema de Calificaciones')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-11">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1 text-primary">
                    <i class="bi bi-speedometer2 me-2"></i> Panel de Docente
                </h1>
                <p class="text-muted mb-0 fs-7">Bienvenido al área de gestión académica, <strong>{{ $user->name }}</strong></p>
            </div>
            <span class="badge bg-success px-3 py-2 fs-7">Docente Activo</span>
        </div>

        <!-- Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 d-block mb-1">Mis Asignaciones</span>
                            <h4 class="fw-bold mb-0 text-primary">{{ $assignments->count() }}</h4>
                        </div>
                        <div class="bg-primary-subtle text-primary rounded-circle p-2 fs-4">
                            <i class="bi bi-journal-bookmark"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 d-block mb-1">Parciales en Borrador</span>
                            <h4 class="fw-bold mb-0 text-secondary">{{ $draftCount }}</h4>
                        </div>
                        <div class="bg-secondary-subtle text-secondary rounded-circle p-2 fs-4">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 d-block mb-1">Parciales Publicados</span>
                            <h4 class="fw-bold mb-0 text-success">{{ $publishedCount }}</h4>
                        </div>
                        <div class="bg-success-subtle text-success rounded-circle p-2 fs-4">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 d-block mb-1">Parciales Reabiertos</span>
                            <h4 class="fw-bold mb-0 text-warning-emphasis">{{ $reopenedCount }}</h4>
                        </div>
                        <div class="bg-warning-subtle text-warning-emphasis rounded-circle p-2 fs-4">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments with Partial Publication States -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary fs-6">
                    <i class="bi bi-journal-text me-2"></i> Mis Asignaciones Activas y Estado de Parciales
                </h5>
                <a href="{{ route('teacher.assignments.index') }}" class="btn btn-outline-primary btn-sm">
                    Ver Asignaciones <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light fs-7 text-uppercase text-muted">
                            <tr>
                                <th class="ps-4">Curso</th>
                                <th>Asignatura</th>
                                <th>Período</th>
                                <th>Parcial 1 (50%)</th>
                                <th>Parcial 2 (50%)</th>
                                <th class="text-end pe-4">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            @forelse($assignments as $assignment)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark">{{ $assignment->course?->code }}</span>
                                        <small class="text-muted d-block">{{ $assignment->course?->name }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $assignment->subject?->name }}</span>
                                        <small class="text-muted d-block">Cod: {{ $assignment->subject?->code }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $assignment->academicPeriod?->name }}</span>
                                    </td>
                                    <td>
                                        @php $p1 = $assignment->partialPublications->firstWhere('partial.number', 1); @endphp
                                        @if($p1)
                                            <span class="badge {{ $p1->status->badgeClass() }} px-2 py-1">
                                                {{ $p1->status->label() }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $p2 = $assignment->partialPublications->firstWhere('partial.number', 2); @endphp
                                        @if($p2)
                                            <span class="badge {{ $p2->status->badgeClass() }} px-2 py-1">
                                                {{ $p2->status->label() }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('teacher.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Detalles
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No registra asignaciones activas para el período vigente.
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
