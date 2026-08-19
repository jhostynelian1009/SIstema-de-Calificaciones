@extends('layouts.app')

@section('title', 'Estados de Parciales - Sistema de Calificaciones')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-card-checklist me-2"></i> Estados de Parciales por Asignación
        </h1>
        <p class="text-muted mb-0">Consulta del ciclo de estados persistidos (P1 y P2) por cada asignación docente</p>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm border-0 rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.partial-publications.index') }}" class="row g-2">
            <div class="col-12 col-md-2">
                <select name="academic_period_id" class="form-select fs-7" onchange="this.form.submit()">
                    <option value="">-- Todos los Períodos --</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ request('academic_period_id') == $period->id ? 'selected' : '' }}>
                            {{ $period->name }} {{ $period->active ? '(Activo)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select name="course_id" class="form-select fs-7" onchange="this.form.submit()">
                    <option value="">-- Todos los Cursos --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->code }} - {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select name="subject_id" class="form-select fs-7" onchange="this.form.submit()">
                    <option value="">-- Todas las Asignaturas --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select name="teacher_id" class="form-select fs-7" onchange="this.form.submit()">
                    <option value="">-- Todos los Docentes --</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select name="status" class="form-select fs-7" onchange="this.form.submit()">
                    <option value="">-- Todos los Estados --</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                    <option value="reopened" {{ request('status') == 'reopened' ? 'selected' : '' }}>Reabierto</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
                @if(request()->anyFilled(['academic_period_id', 'course_id', 'subject_id', 'teacher_id', 'status', 'partial_id']))
                    <a href="{{ route('admin.partial-publications.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light fs-7 text-uppercase text-muted">
                    <tr>
                        <th class="ps-4">Período</th>
                        <th>Curso</th>
                        <th>Asignatura</th>
                        <th>Docente</th>
                        <th>Parcial / Peso</th>
                        <th>Estado Persistido</th>
                        <th>Publicación</th>
                        <th>Reapertura</th>
                        <th class="pe-4 text-end">Acción</th>
                    </tr>
                </thead>
                <tbody class="fs-7">
                    @forelse($publications as $pub)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-semibold">{{ $pub->teachingAssignment?->academicPeriod?->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $pub->teachingAssignment?->course?->code }}
                                </span>
                                <small class="text-muted d-block">{{ $pub->teachingAssignment?->course?->name }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $pub->teachingAssignment?->subject?->name }}</span>
                                <small class="text-muted d-block">Cod: {{ $pub->teachingAssignment?->subject?->code }}</small>
                            </td>
                            <td>
                                <i class="bi bi-person-circle me-1 text-secondary"></i>
                                {{ $pub->teachingAssignment?->teacher?->name ?? 'No asignado' }}
                            </td>
                            <td>
                                <span class="fw-bold text-primary">{{ $pub->partial?->name }} (P{{ $pub->partial?->number }})</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">
                                    {{ number_format($pub->partial?->weight ?? 50, 0) }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $pub->status->badgeClass() }} px-2 py-1">
                                    {{ $pub->status->label() }}
                                </span>
                            </td>
                            <td>
                                @if($pub->published_at)
                                    <span class="d-block text-dark fw-semibold">{{ $pub->published_at->format('d/m/Y H:i') }}</span>
                                    <small class="text-muted">Por: {{ $pub->publishedBy?->name ?? 'Sistema' }}</small>
                                @else
                                    <span class="text-muted fs-8">Sin publicar</span>
                                @endif
                            </td>
                            <td>
                                @if($pub->reopened_at)
                                    <span class="d-block text-dark fw-semibold">{{ $pub->reopened_at->format('d/m/Y H:i') }}</span>
                                    <small class="text-muted">Por: {{ $pub->reopenedBy?->name ?? 'Admin' }}</small>
                                    @if($pub->reopen_reason)
                                        <i class="bi bi-info-circle ms-1" title="Motivo: {{ $pub->reopen_reason }}"></i>
                                    @endif
                                @else
                                    <span class="text-muted fs-8">-</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('admin.partial-publications.show', $pub) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                No se encontraron registros de estados de parciales con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($publications->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4">
            {{ $publications->links() }}
        </div>
    @endif
</div>
@endsection
