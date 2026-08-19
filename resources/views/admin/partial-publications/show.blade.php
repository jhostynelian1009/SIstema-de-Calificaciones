@extends('layouts.app')

@section('title', 'Detalle de Publicación de Parcial')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.partial-publications.index') }}">Estados de Parciales</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detalle {{ $partial->name }}</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Detalle y Gestión de Publicación - {{ $partial->name }}</h1>
        <p class="text-muted mb-0">Auditoría, visualización de promedios y reapertura administrativa.</p>
    </div>
    <a href="{{ route('admin.partial-publications.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a la Lista
    </a>
</div>

<!-- Publication Info Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="card-title fw-bold mb-0 text-primary">
            <i class="bi bi-info-circle me-2"></i> Información General del Parcial
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted d-block fs-7">Curso / Asignatura</small>
                <strong class="fs-6 text-dark">{{ $assignment->course?->name }}</strong>
                <div class="text-primary fw-semibold">{{ $assignment->subject?->name }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block fs-7">Docente Responsable</small>
                <strong class="fs-6 text-dark">{{ $assignment->teacher?->name }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block fs-7">Período Lectivo / Parcial</small>
                <strong class="fs-6 text-dark">{{ $assignment->academicPeriod?->name }}</strong>
                <div><span class="badge bg-secondary fs-7">{{ $partial->name }} (P{{ $partial->number }})</span></div>
            </div>
            <div class="col-md-3 text-md-end">
                <small class="text-muted d-block fs-7">Estado Actual Persistido</small>
                <span class="badge {{ $publication->status->badgeClass() }} px-3 py-2 fs-6">
                    {{ $publication->status->label() }}
                </span>
            </div>
        </div>

        <hr class="my-3">

        <div class="row g-3 fs-7">
            <div class="col-md-6">
                <div class="p-2 bg-light rounded border">
                    <strong class="text-dark d-block mb-1"><i class="bi bi-send me-1"></i> Datos de Última Publicación:</strong>
                    @if($publication->published_at)
                        <div><span class="text-muted">Publicado por:</span> <strong>{{ $publication->publishedBy?->name ?? 'Docente' }}</strong></div>
                        <div><span class="text-muted">Fecha de publicación:</span> <strong>{{ $publication->published_at->format('d/m/Y H:i:s') }}</strong></div>
                    @else
                        <div class="text-muted italic">Este parcial aún no ha sido publicado.</div>
                    @endif
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-2 bg-light rounded border">
                    <strong class="text-dark d-block mb-1"><i class="bi bi-arrow-counterclockwise me-1"></i> Datos de Última Reapertura:</strong>
                    @if($publication->reopened_at)
                        <div><span class="text-muted">Reabierto por:</span> <strong>{{ $publication->reopenedBy?->name ?? 'Administrador' }}</strong></div>
                        <div><span class="text-muted">Fecha de reapertura:</span> <strong>{{ $publication->reopened_at->format('d/m/Y H:i:s') }}</strong></div>
                        <div><span class="text-muted">Motivo:</span> <em class="text-dark">"{{ $publication->reopen_reason }}"</em></div>
                    @else
                        <div class="text-muted italic">No registra reaperturas administrativas.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reopen Form Card (ONLY IF PUBLISHED) -->
@if($publication->status->value === 'published')
    <div class="card border-warning shadow-sm mb-4">
        <div class="card-header bg-warning text-dark py-3">
            <h6 class="m-0 fw-bold">
                <i class="bi bi-exclamation-octagon me-2"></i> Solicitar Reapertura Administrativa
            </h6>
        </div>
        <div class="card-body">
            <p class="text-dark mb-3 fs-7">
                La reapertura permite que el docente responsable modifique o agregue calificaciones en este parcial. 
                Los resultados oficiales calculados permanecerán **ocultos** para consulta hasta que el docente vuelva a republicar el parcial.
            </p>

            <form action="{{ route('admin.partial-publications.reopen', $publication) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="reason" class="form-label fw-bold fs-7">Motivo de Reapertura (Obligatorio, min 10 caracteres) <span class="text-danger">*</span></label>
                    <textarea name="reason" id="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" placeholder="Describa justificadamente la razón por la cual se reabre la edición de calificaciones..." required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-warning fw-bold px-4" onsubmit="return confirm('¿Confirma reabrir este parcial publicado?');">
                        <i class="bi bi-unlock-fill me-1"></i> Reabrir Parcial
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- Student Calculated Averages Table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="card-title fw-bold mb-0 text-primary">
            <i class="bi bi-calculator me-2"></i> Promedios Calculados por Estudiante
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Estudiante</th>
                    <th>Correo Electrónico</th>
                    <th class="text-center">Promedio Parcial</th>
                    <th>Estado de Resultado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentResults as $index => $item)
                    @php
                        $calc = $item['calculation'];
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $index + 1 }}</td>
                        <td class="fw-bold text-dark">{{ $item['student']->name }}</td>
                        <td class="text-muted small">{{ $item['student']->email }}</td>
                        <td class="text-center">
                            @if($calc['calculable'])
                                <span class="badge bg-primary fs-6 px-3 py-2">
                                    {{ $calc['score_formatted'] }} / 10.00
                                </span>
                            @else
                                <span class="badge bg-secondary fs-7">No calculable</span>
                            @endif
                        </td>
                        <td>
                            @if($publication->status->value === 'published')
                                <span class="badge bg-success fs-7"><i class="bi bi-check-circle me-1"></i> Oficial Publicado</span>
                            @elseif($publication->status->value === 'reopened')
                                <span class="badge bg-warning text-dark fs-7"><i class="bi bi-hourglass-split me-1"></i> Oculto por Reapertura</span>
                            @else
                                <span class="badge bg-secondary fs-7"><i class="bi bi-pencil me-1"></i> Borrador</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No existen estudiantes matriculados en este curso.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Publication Audit Logs History -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="card-title fw-bold mb-0 text-primary">
            <i class="bi bi-shield-shaded me-2"></i> Historial de Auditoría de la Publicación
        </h5>
    </div>
    <div class="card-body p-0">
        @if($auditLogs->isEmpty())
            <div class="text-center py-4 text-muted fs-7">No se registraron entradas de auditoría para este parcial.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 fs-7">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Usuario Responsable</th>
                            <th>Acción Registrada</th>
                            <th>Valores Anteriores</th>
                            <th>Valores Nuevos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="fw-bold">{{ $log->user?->name ?? 'Sistema' }}</td>
                                <td><span class="badge bg-dark fs-8">{{ $log->action }}</span></td>
                                <td><code>{{ json_encode($log->old_values) }}</code></td>
                                <td><code>{{ json_encode($log->new_values) }}</code></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
