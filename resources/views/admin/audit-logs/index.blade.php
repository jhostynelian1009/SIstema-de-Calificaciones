@extends('layouts.app')

@section('title', 'Registro de Auditoría del Sistema')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">
            <i class="bi bi-shield-check me-2 text-primary"></i>Registro General de Auditoría
        </h1>
        <p class="text-muted mb-0">Trazabilidad completa e inmutable de acciones críticas del sistema (Solo lectura).</p>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold text-primary">
            <i class="bi bi-funnel me-1"></i> Filtros de Auditoría
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="action" class="form-label fs-7 fw-bold">Acción Registrada</label>
                <input type="text" name="action" id="action" class="form-control form-control-sm" placeholder="Ej: partial.published, grade.created..." value="{{ request('action') }}">
            </div>

            <div class="col-md-3">
                <label for="user_id" class="form-label fs-7 fw-bold">Usuario Actor</label>
                <select name="user_id" id="user_id" class="form-select form-select-sm">
                    <option value="">-- Todos los Usuarios --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->role->label() }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="auditable_type" class="form-label fs-7 fw-bold">Tipo de Entidad</label>
                <input type="text" name="auditable_type" id="auditable_type" class="form-control form-control-sm" placeholder="Ej: PartialPublication, Grade..." value="{{ request('auditable_type') }}">
            </div>

            <div class="col-md-3">
                <label for="date_from" class="form-label fs-7 fw-bold">Fecha Desde</label>
                <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-3">
                <label for="date_to" class="form-label fs-7 fw-bold">Fecha Hasta</label>
                <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-search me-1"></i> Filtrar Auditoría
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-x-circle me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        @if($logs->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 text-secondary mb-2 d-block"></i>
                No se encontraron registros de auditoría con los criterios seleccionados.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 fs-7">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Actor / Usuario</th>
                            <th>Acción</th>
                            <th>Entidad / ID</th>
                            <th>Valores Anteriores</th>
                            <th>Valores Nuevos</th>
                            <th>Dirección IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>
                                    <small class="fw-bold text-dark">{{ $log->created_at->format('d/m/Y H:i:s') }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $log->user?->name ?? 'Sistema' }}</div>
                                    <small class="text-muted">{{ $log->user?->email }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary fs-8">{{ $log->action }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ class_basename($log->auditable_type) }}</div>
                                    <small class="text-muted">ID: {{ $log->auditable_id }}</small>
                                </td>
                                <td>
                                    <div class="text-wrap text-break font-monospace fs-8" style="max-width: 250px;">
                                        {{ json_encode($log->old_values) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-wrap text-break font-monospace fs-8" style="max-width: 250px;">
                                        {{ json_encode($log->new_values) }}
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted font-monospace">{{ $log->ip_address ?? '127.0.0.1' }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
