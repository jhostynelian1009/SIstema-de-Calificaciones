@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Header & Period Filter -->
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-7">
            <h1 class="h3 fw-bold text-primary mb-1">
                <i class="bi bi-journal-check me-2"></i> Mis Calificaciones
            </h1>
            <p class="text-muted mb-0">Consulte sus resultados académicos oficiales y promedios por período</p>
        </div>
        <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
            @if($has_periods && $selected_period && $summary)
                <a href="{{ route('student.grades.print', $selected_period) }}" class="btn btn-outline-secondary btn-sm me-2" target="_blank">
                    <i class="bi bi-printer me-1"></i> Vista Imprimible
                </a>
            @endif
        </div>
    </div>

    @if(!$has_periods || !$selected_period)
        <!-- Empty State: No periods enrolled -->
        <div class="card shadow-sm border-0 rounded-3 p-5 text-center bg-white">
            <div class="card-body">
                <i class="bi bi-calendar-x text-muted display-1 mb-3"></i>
                <h4 class="fw-bold text-dark mb-2">Sin Histórico de Períodos</h4>
                <p class="text-muted mb-0">No se encontraron matrículas ni calificaciones registradas a su nombre.</p>
            </div>
        </div>
    @else
        <!-- Period Selector Card -->
        <div class="card shadow-sm border-0 rounded-3 bg-white mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('student.grades.index') }}" class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <label for="period_id" class="form-label fs-8 text-uppercase fw-bold text-muted mb-1">Seleccionar Período Académico:</label>
                        <select name="period_id" id="period_id" class="form-select bg-light" onchange="this.form.submit()">
                            @foreach($periods as $p)
                                <option value="{{ $p->id }}" {{ $selected_period->id === $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} {{ $p->active ? '(Período Activo)' : '(Histórico)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-8 text-md-end pt-md-3">
                        <span class="badge bg-light text-dark border p-2 fs-8">
                            <i class="bi bi-building me-1"></i> Curso: <strong>{{ $summary['course']?->name }}</strong> ({{ $summary['course']?->code }})
                        </span>
                        <span class="badge {{ $summary['enrollment']?->active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} p-2 fs-8 ms-2">
                            <i class="bi {{ $summary['enrollment']?->active ? 'bi-check-circle' : 'bi-clock-history' }} me-1"></i>
                            Matrícula {{ $summary['enrollment']?->active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                </form>
            </div>
        </div>

        <!-- General Period Average Banner -->
        <div class="card shadow-sm border-0 rounded-3 bg-white mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-12 col-md-8">
                        <span class="badge bg-primary-subtle text-primary mb-2 px-3 py-1 fs-8 text-uppercase fw-bold">
                            <i class="bi bi-award me-1"></i> Consolidado del Período
                        </span>
                        <h4 class="fw-bold text-dark mb-1">{{ $selected_period->name }}</h4>
                        <p class="text-muted fs-7 mb-0">
                            Promedio ponderado oficial derivado exclusivamente de parciales 1 y 2 publicados.
                        </p>
                    </div>
                    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                        @if($summary['is_general_official'] && $summary['general_result'])
                            <div class="h2 fw-bold text-success mb-0">
                                {{ $summary['general_result']['score_formatted'] }} <small class="fs-6 text-muted">/ 10,00</small>
                            </div>
                            <span class="badge bg-success-subtle text-success fs-8">
                                <i class="bi bi-shield-check me-1"></i> Promedio General Oficial
                            </span>
                        @else
                            <div class="h5 fw-bold text-secondary mb-1">
                                Promedio general pendiente
                            </div>
                            <span class="badge bg-warning-subtle text-warning-emphasis fs-8">
                                <i class="bi bi-exclamation-triangle me-1"></i> Requiere que todas las asignaturas publiquen P1 y P2
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Subjects Grade Table -->
        <div class="card shadow-sm border-0 rounded-3 bg-white">
            <div class="card-header bg-light border-bottom p-3">
                <h5 class="mb-0 fw-bold text-dark fs-6">
                    <i class="bi bi-list-stars me-2 text-primary"></i> Asignaturas y Resultantes Académicos
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="bg-light border-bottom text-uppercase text-muted fs-8">
                            <tr>
                                <th class="ps-4 py-3">Asignatura</th>
                                <th class="py-3">Docente</th>
                                <th class="py-3 text-center">Parcial 1</th>
                                <th class="py-3 text-center">Parcial 2</th>
                                <th class="py-3 text-center">Promedio Final</th>
                                <th class="text-end pe-4 py-3">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($summary['subjects'] as $item)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <span class="fw-bold text-dark">{{ $item['subject']?->name }}</span>
                                        <small class="text-muted d-block fs-8">Código: {{ $item['subject']?->code }}</small>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold">{{ $item['teacher']?->name ?? 'Sin asignar' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($item['p1_published'] && $item['p1_result'])
                                            <div class="fw-bold text-dark fs-6">{{ $item['p1_result']['score_formatted'] }}</div>
                                            <span class="badge bg-success-subtle text-success fs-9"><i class="bi bi-check2 me-1"></i> Disponible</span>
                                        @else
                                            <span class="badge bg-light text-muted border fs-8 px-2 py-1">
                                                {{ $item['p1_status_label'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item['p2_published'] && $item['p2_result'])
                                            <div class="fw-bold text-dark fs-6">{{ $item['p2_result']['score_formatted'] }}</div>
                                            <span class="badge bg-success-subtle text-success fs-9"><i class="bi bi-check2 me-1"></i> Disponible</span>
                                        @else
                                            <span class="badge bg-light text-muted border fs-8 px-2 py-1">
                                                {{ $item['p2_status_label'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item['final_result'])
                                            <div class="h5 fw-bold text-primary mb-0">{{ $item['final_result']['score_formatted'] }}</div>
                                            <span class="badge bg-primary-subtle text-primary fs-9">Oficial</span>
                                        @else
                                            <span class="text-muted fs-8">— Pendiente —</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('student.grades.subject', [$selected_period, $item['assignment']]) }}" 
                                           class="btn btn-outline-primary btn-sm fw-semibold">
                                            <i class="bi bi-journal-text me-1"></i> Ver Actividades
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No se encontraron asignaturas configuradas para el período {{ $selected_period->name }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
