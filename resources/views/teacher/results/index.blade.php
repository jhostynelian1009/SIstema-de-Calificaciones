@extends('layouts.app')

@section('title', 'Resultados por Asignatura - Docente')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-journal-text me-2"></i> Resultados por Asignatura
        </h1>
        <p class="text-muted mb-0">Consulte las matrices de calificaciones finales y parciales de sus asignaciones</p>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-3 bg-white">
    <div class="card-header bg-white border-bottom p-3">
        <h5 class="fw-bold mb-0 text-primary fs-6">
            <i class="bi bi-list-check me-2"></i> Mis Asignaciones Activas
        </h5>
    </div>
    <div class="card-body p-0">
        @if($assignments->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                <h5 class="fw-bold mb-1">No posee asignaciones activas</h5>
                <p class="fs-7 mb-0">No se encontraron asignaciones docentes activas en el sistema.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="bg-light text-uppercase text-muted fs-8">
                        <tr>
                            <th class="ps-4 py-3">Curso</th>
                            <th class="py-3">Asignatura</th>
                            <th class="py-3">Período Académico</th>
                            <th class="py-3 text-center">Estado P1</th>
                            <th class="py-3 text-center">Estado P2</th>
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
                                    <a href="{{ route('teacher.results.assignment', $assignment) }}" class="btn btn-primary btn-sm fw-semibold">
                                        <i class="bi bi-table me-1"></i> Ver Matriz de Resultados
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
