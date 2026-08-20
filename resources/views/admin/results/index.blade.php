@extends('layouts.app')

@section('title', 'Consulta Consolidada de Resultados - Sistema de Calificaciones')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-journal-text me-2"></i> Resultados Académicos Consolidados
        </h1>
        <p class="text-muted mb-0">Supervisión general de promedios parciales y finales por estudiante</p>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.results.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <label class="form-label fs-8 fw-semibold text-secondary mb-1">Período Académico</label>
                <select name="academic_period_id" class="form-select bg-light">
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ (string) $selectedPeriodId === (string) $period->id ? 'selected' : '' }}>
                            {{ $period->name }} {{ $period->active ? '(Activo)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fs-8 fw-semibold text-secondary mb-1">Curso</label>
                <select name="course_id" class="form-select bg-light">
                    <option value="">Todos los Cursos</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ (string) $selectedCourseId === (string) $course->id ? 'selected' : '' }}>
                            {{ $course->name }} ({{ $course->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fs-8 fw-semibold text-secondary mb-1">Asignatura</label>
                <select name="subject_id" class="form-select bg-light">
                    <option value="">Todas las Asignaturas</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ (string) $selectedSubjectId === (string) $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fs-8 fw-semibold text-secondary mb-1">Estado de Publicación</label>
                <select name="status" class="form-select bg-light">
                    <option value="">Todos los Estados</option>
                    <option value="published" {{ $selectedStatus === 'published' ? 'selected' : '' }}>Publicado</option>
                    <option value="draft" {{ $selectedStatus === 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="reopened" {{ $selectedStatus === 'reopened' ? 'selected' : '' }}>Reabierto</option>
                </select>
            </div>
            <div class="col-12 text-end mt-2">
                <a href="{{ route('admin.results.index') }}" class="btn btn-light me-2">Limpiar Filtros</a>
                <button type="submit" class="btn btn-primary fw-semibold px-4">
                    <i class="bi bi-funnel me-1"></i> Consultar Resultados
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Results Grid -->
<div class="card shadow-sm border-0 rounded-3 bg-white">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary fs-6">
            <i class="bi bi-table me-2"></i> Matriz de Calificaciones por Estudiante
        </h5>
        <small class="text-muted fs-8">
            <span class="badge bg-success-subtle text-success me-1">Oficial</span> Publicado
            <span class="badge bg-warning-subtle text-warning-emphasis ms-2 me-1">Provisional</span> En elaboración / Reabierto
        </small>
    </div>
    <div class="card-body p-0">
        @if(empty($results))
            <div class="p-5 text-center text-muted">
                <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                <h5 class="fw-bold mb-1">No se encontraron resultados</h5>
                <p class="fs-7 mb-0">Seleccione los criterios de búsqueda requeridos para visualizar las calificaciones.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-secondary fs-8 text-uppercase">Estudiante</th>
                            <th class="py-3 text-secondary fs-8 text-uppercase">Curso</th>
                            <th class="py-3 text-secondary fs-8 text-uppercase">Asignatura</th>
                            <th class="py-3 text-center text-secondary fs-8 text-uppercase">Parcial 1 (50%)</th>
                            <th class="py-3 text-center text-secondary fs-8 text-uppercase">Parcial 2 (50%)</th>
                            <th class="py-3 text-center text-secondary fs-8 text-uppercase">Promedio Final</th>
                            <th class="text-end pe-4 py-3 text-secondary fs-8 text-uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($results as $item)
                            <tr>
                                <td class="ps-4 py-3">
                                    <a href="{{ route('admin.results.student', ['student' => $item['student']->id, 'academic_period_id' => $selectedPeriodId]) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $item['student']->name }}
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item['course']->code }}</span></td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $item['subject']->name }}</span>
                                    <small class="text-muted d-block fs-8">{{ $item['teacher']?->name }}</small>
                                </td>
                                <td class="text-center">
                                    @if($item['p1_calc'] && $item['p1_calc']['calculable'])
                                        <div class="fw-bold fs-6 {{ $item['p1_calc']['score_hundredths'] >= 700 ? 'text-success' : 'text-danger' }}">
                                            {{ $item['p1_calc']['score_formatted'] }}
                                        </div>
                                        @if($item['p1_official'])
                                            <span class="badge bg-success-subtle text-success fs-9"><i class="bi bi-shield-check"></i> Oficial</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis fs-9"><i class="bi bi-clock-history"></i> Provisional</span>
                                        @endif
                                    @else
                                        <span class="text-muted fs-8">— Incompleto —</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item['p2_calc'] && $item['p2_calc']['calculable'])
                                        <div class="fw-bold fs-6 {{ $item['p2_calc']['score_hundredths'] >= 700 ? 'text-success' : 'text-danger' }}">
                                            {{ $item['p2_calc']['score_formatted'] }}
                                        </div>
                                        @if($item['p2_official'])
                                            <span class="badge bg-success-subtle text-success fs-9"><i class="bi bi-shield-check"></i> Oficial</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis fs-9"><i class="bi bi-clock-history"></i> Provisional</span>
                                        @endif
                                    @else
                                        <span class="text-muted fs-8">— Incompleto —</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item['is_complete_official'] && $item['final_calc']['calculable'])
                                        <div class="fw-bold fs-6 p-1 bg-light rounded border {{ $item['final_calc']['score_hundredths'] >= 700 ? 'text-success' : 'text-danger' }}">
                                            {{ $item['final_calc']['score_formatted'] }}
                                        </div>
                                        <span class="badge bg-success text-white fs-9 mt-1">OFICIAL</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary fs-8">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.results.student', ['student' => $item['student']->id, 'academic_period_id' => $selectedPeriodId]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i> Ver Boletín
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
