@extends('layouts.app')

@section('title', 'Detalle de Usuario - Sistema de Calificaciones')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-person-lines-fill me-2"></i> Detalle de Usuario
        </h1>
        <p class="text-muted mb-0">Información detallada de la cuenta y actividad del usuario</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary fw-semibold px-3 me-2">
            <i class="bi bi-pencil me-1"></i> Editar Usuario
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary fw-semibold px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 rounded-3 bg-white text-center p-4">
            <div class="bg-primary-subtle text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i class="bi bi-person-fill fs-1"></i>
            </div>
            <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
            <p class="text-muted fs-7 mb-2">{{ $user->email }}</p>

            <div class="mb-3">
                @if($user->isAdmin())
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-7">
                        <i class="bi bi-shield-lock-fill me-1"></i> Administrador
                    </span>
                @elseif($user->isTeacher())
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-7">
                        <i class="bi bi-person-workspace me-1"></i> Docente
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-7">
                        <i class="bi bi-person-badge me-1"></i> Estudiante
                    </span>
                @endif

                @if($user->active)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-7 ms-1">
                        <i class="bi bi-check-circle-fill me-1"></i> Activo
                    </span>
                @else
                    <span class="badge bg-secondary text-white px-3 py-2 fs-7 ms-1">
                        <i class="bi bi-x-circle-fill me-1"></i> Inactivo
                    </span>
                @endif
            </div>

            <div class="border-top pt-3 text-start fs-7 text-muted">
                <div class="d-flex justify-content-between mb-1">
                    <span>ID de Usuario:</span>
                    <strong class="text-dark">#{{ $user->id }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Fecha de Registro:</span>
                    <strong class="text-dark">{{ $user->created_at?->format('d/m/Y H:i') }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Última Actualización:</span>
                    <strong class="text-dark">{{ $user->updated_at?->format('d/m/Y H:i') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        @if($user->isStudent())
            <div class="card shadow-sm border-0 rounded-3 bg-white mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <h5 class="fw-bold mb-0 text-primary fs-6">
                        <i class="bi bi-journal-check me-2"></i> Historial de Matrículas
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($user->studentEnrollments->isEmpty())
                        <p class="p-4 text-muted mb-0">No se registran matrículas activas o históricas para este estudiante.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 fs-7">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Curso</th>
                                        <th>Período Académico</th>
                                        <th>Estado</th>
                                        <th>Fecha Matrícula</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->studentEnrollments as $enrollment)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $enrollment->course?->name }} ({{ $enrollment->course?->code }})</td>
                                            <td>{{ $enrollment->academicPeriod?->name }}</td>
                                            <td>
                                                @if($enrollment->active)
                                                    <span class="badge bg-success-subtle text-success">Activa</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactiva</span>
                                                @endif
                                            </td>
                                            <td>{{ $enrollment->created_at?->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($user->isTeacher())
            <div class="card shadow-sm border-0 rounded-3 bg-white mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <h5 class="fw-bold mb-0 text-primary fs-6">
                        <i class="bi bi-person-workspace me-2"></i> Asignaciones Académica
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($user->teachingAssignments->isEmpty())
                        <p class="p-4 text-muted mb-0">No se registran asignaciones académicas asociadas a este docente.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 fs-7">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Curso</th>
                                        <th>Asignatura</th>
                                        <th>Período Académico</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->teachingAssignments as $assignment)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $assignment->course?->name }} ({{ $assignment->course?->code }})</td>
                                            <td>{{ $assignment->subject?->name }}</td>
                                            <td>{{ $assignment->academicPeriod?->name }}</td>
                                            <td>
                                                @if($assignment->active)
                                                    <span class="badge bg-success-subtle text-success">Activa</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactiva</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card shadow-sm border-0 rounded-3 bg-white mb-4 p-4 text-center text-muted">
                <i class="bi bi-shield-check fs-1 text-primary mb-2"></i>
                <h5 class="fw-bold text-dark">Cuenta de Administrador</h5>
                <p class="mb-0 fs-7">Este usuario cuenta con privilegios administrativos para configurar y supervisar la plataforma.</p>
            </div>
        @endif
    </div>
</div>
@endsection
