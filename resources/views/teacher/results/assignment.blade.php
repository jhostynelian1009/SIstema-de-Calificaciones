@extends('layouts.app')

@section('title', "Matriz de Resultados - {$assignment->subject?->name}")

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-table me-2"></i> Matriz de Resultados de Asignatura
        </h1>
        <p class="text-muted mb-0">
            <strong>Curso:</strong> {{ $assignment->course?->name }} ({{ $assignment->course?->code }}) |
            <strong>Asignatura:</strong> {{ $assignment->subject?->name }} |
            <strong>Período:</strong> {{ $assignment->academicPeriod?->name }}
        </p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('teacher.results.print', $assignment) }}" target="_blank" class="btn btn-outline-dark fw-semibold px-3 me-2">
            <i class="bi bi-printer me-1"></i> Vista Imprimible
        </a>
        <a href="{{ route('teacher.results.index') }}" class="btn btn-outline-secondary fw-semibold px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<!-- Header Statuses Card -->
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
    <div class="card-body p-3">
        <div class="row g-3 text-center fs-7">
            <div class="col-12 col-md-6 border-end-md">
                <span class="text-muted fs-8 d-block mb-1">Estado de Parcial 1 (50%):</span>
                @if($p1_official)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i> OFICIAL (Publicado el {{ $p1_pub?->published_at?->format('d/m/Y') }})
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">
                        <i class="bi bi-clock-history me-1"></i> PROVISIONAL ({{ $p1_pub?->status->label() ?? 'Borrador' }})
                    </span>
                @endif
            </div>

            <div class="col-12 col-md-6">
                <span class="text-muted fs-8 d-block mb-1">Estado de Parcial 2 (50%):</span>
                @if($p2_official)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i> OFICIAL (Publicado el {{ $p2_pub?->published_at?->format('d/m/Y') }})
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">
                        <i class="bi bi-clock-history me-1"></i> PROVISIONAL ({{ $p2_pub?->status->label() ?? 'Borrador' }})
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Results Grid -->
<div class="card shadow-sm border-0 rounded-3 bg-white">
    <div class="card-header bg-white border-bottom p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h5 class="fw-bold mb-0 text-primary fs-6">
            <i class="bi bi-person-lines-fill me-2"></i> Calificaciones por Estudiante
        </h5>
        <form method="GET" action="{{ route('teacher.results.assignment', $assignment) }}" class="d-flex gap-2">
            <input type="text"
                   name="search"
                   class="form-control form-control-sm bg-light"
                   placeholder="Buscar por nombre o correo..."
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">Buscar</button>
        </form>
    </div>
    <div class="card-body p-0">
        @if(empty($rows))
            <div class="p-5 text-center text-muted">
                <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                <h5 class="fw-bold mb-1">No se encontraron estudiantes</h5>
                <p class="fs-7 mb-0">No existen registros bajo los términos de búsqueda.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="bg-light border-bottom text-uppercase text-muted fs-8">
                        <tr>
                            <th class="ps-4 py-3">Estudiante</th>
                            <th class="py-3 text-center">Parcial 1 (50%)</th>
                            <th class="py-3 text-center">Parcial 2 (50%)</th>
                            <th class="py-3 text-center">Promedio Final</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($rows as $row)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="fw-bold text-dark">{{ $row['student']->name }}</span>
                                    <small class="text-muted d-block fs-8">{{ $row['student']->email }}</small>
                                </td>
                                <td class="text-center">
                                    @if($row['p1_calc'] && $row['p1_calc']['calculable'])
                                        <div class="fw-bold fs-6 {{ $row['p1_calc']['score_hundredths'] >= 700 ? 'text-success' : 'text-danger' }}">
                                            {{ $row['p1_calc']['score_formatted'] }}
                                        </div>
                                        @if($row['p1_official'])
                                            <span class="badge bg-success-subtle text-success fs-9"><i class="bi bi-shield-check me-1"></i> Oficial</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis fs-9"><i class="bi bi-clock-history me-1"></i> Provisional</span>
                                        @endif
                                    @else
                                        <span class="text-muted fs-8">— Incompleto —</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row['p2_calc'] && $row['p2_calc']['calculable'])
                                        <div class="fw-bold fs-6 {{ $row['p2_calc']['score_hundredths'] >= 700 ? 'text-success' : 'text-danger' }}">
                                            {{ $row['p2_calc']['score_formatted'] }}
                                        </div>
                                        @if($row['p2_official'])
                                            <span class="badge bg-success-subtle text-success fs-9"><i class="bi bi-shield-check me-1"></i> Oficial</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis fs-9"><i class="bi bi-clock-history me-1"></i> Provisional</span>
                                        @endif
                                    @else
                                        <span class="text-muted fs-8">— Incompleto —</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row['is_final_official'] && $row['final_calc']['calculable'])
                                        <div class="fw-bold fs-6 p-1 bg-light rounded border {{ $row['final_calc']['score_hundredths'] >= 700 ? 'text-success' : 'text-danger' }}">
                                            {{ $row['final_calc']['score_formatted'] }}
                                        </div>
                                        <span class="badge bg-success text-white fs-9 mt-1">OFICIAL</span>
                                    @elseif($row['final_calc']['calculable'])
                                        <div class="fw-bold fs-6 p-1 bg-light rounded border text-warning-emphasis">
                                            {{ $row['final_calc']['score_formatted'] }}
                                        </div>
                                        <span class="badge bg-warning text-dark fs-9 mt-1">PROVISIONAL</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary fs-8">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('teacher.results.student', ['assignment' => $assignment->id, 'student' => $row['student']->id]) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                        <i class="bi bi-eye me-1"></i> Ver Boletín
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top bg-light">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
