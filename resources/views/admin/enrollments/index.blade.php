@extends('layouts.app')

@section('title', 'Gestión de Matrículas')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Matrículas Académicas</h1>
        <p class="text-muted mb-0">Gestión de la vinculación de estudiantes con cursos y períodos lectivos.</p>
    </div>
    <div>
        <a href="{{ route('admin.enrollments.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-person-plus-fill"></i>
            <span>Nueva Matrícula</span>
        </a>
    </div>
</div>

<!-- Card Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.enrollments.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label text-muted small fw-semibold">Buscar Estudiante</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Nombre o correo..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="period_id" class="form-label text-muted small fw-semibold">Período Académico</label>
                <select name="period_id" id="period_id" class="form-select">
                    <option value="">Todos los períodos</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ request('period_id') == $period->id ? 'selected' : '' }}>
                            {{ $period->name }} {{ $period->active ? '(Activo)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="course_id" class="form-label text-muted small fw-semibold">Curso</label>
                <select name="course_id" id="course_id" class="form-select">
                    <option value="">Todos los cursos</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }} ({{ $course->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="active" class="form-label text-muted small fw-semibold">Estado</label>
                <select name="active" id="active" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100" title="Filtrar">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Enrollments Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th>Correo</th>
                    <th>Curso</th>
                    <th>Período</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enrollment)
                    <tr>
                        <td class="fw-semibold">{{ $enrollment->student?->name ?? 'Estudiante No Encontrado' }}</td>
                        <td class="text-muted small">{{ $enrollment->student?->email ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $enrollment->course?->name ?? 'N/A' }} ({{ $enrollment->course?->code ?? 'N/A' }})
                            </span>
                        </td>
                        <td>{{ $enrollment->academicPeriod?->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if($enrollment->active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Activa</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactiva</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.enrollments.edit', $enrollment) }}" class="btn btn-sm btn-outline-secondary" title="Editar curso">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.enrollments.toggle-status', $enrollment) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($enrollment->active)
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Desactivar" onclick="return confirm('¿Está seguro de desactivar esta matrícula? Conservará el historial académico.');">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Activar">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                            No se encontraron matrículas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($enrollments->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $enrollments->links() }}
        </div>
    @endif
</div>
@endsection
