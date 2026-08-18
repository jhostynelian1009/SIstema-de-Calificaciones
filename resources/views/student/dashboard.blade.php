@extends('layouts.app')

@section('title', 'Panel Estudiante - Sistema de Calificaciones')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10">
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h1 class="h4 fw-bold mb-1 text-primary">
                            <i class="bi bi-person-badge me-2"></i> Mi Información Académica
                        </h1>
                        <p class="text-muted mb-0 fs-7">Bienvenido a tu panel personal de estudiante.</p>
                    </div>
                    <span class="badge bg-info text-dark px-3 py-2 fs-7">Estudiante</span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-0 bg-info bg-opacity-10 text-dark mb-4" role="alert">
                    <h2 class="h6 fw-bold mb-1">
                        <i class="bi bi-person-circle me-1"></i> Hola, {{ $user->name }}
                    </h2>
                    <p class="mb-0 fs-7">Has iniciado sesión como <strong>Estudiante</strong> ({{ $user->email }}).</p>
                </div>

                @if($activePeriod)
                    <div class="card border-0 bg-light shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <small class="text-muted text-uppercase fw-bold fs-8 d-block mb-1">Período Lectivo Vigente</small>
                                    <h5 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-calendar3 me-2 text-primary"></i>{{ $activePeriod->name }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase fw-bold fs-8 d-block mb-1">Estado de Matrícula</small>
                                    @if($enrollment)
                                        <div class="d-flex align-items-center gap-2">
                                            @if($enrollment->active)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle fs-7">
                                                    <i class="bi bi-check-circle me-1"></i> Matrícula Activa
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fs-7">
                                                    <i class="bi bi-slash-circle me-1"></i> Matrícula Inactiva
                                                </span>
                                            @endif
                                            <span class="fw-bold text-dark">
                                                Curso: {{ $enrollment->course?->name }} ({{ $enrollment->course?->code }})
                                            </span>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0 border-0 py-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Aún no posee una matrícula registrada para el período académico vigente. Por favor, contacte a la administración institucional.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-secondary border-0 text-center py-4 my-4">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-muted"></i>
                        No existe un período académico activo configurado actualmente.
                    </div>
                @endif

                <div class="p-4 bg-white border rounded-3 text-center my-4">
                    <i class="bi bi-award fs-1 text-muted d-block mb-2"></i>
                    <h3 class="h5 fw-bold mb-2">Consulta de Historial Académico</h3>
                    <p class="text-muted mb-0 fs-7">La visualización de asignaturas, calificaciones parciales y promedios finales estará disponible cuando se registren y publiquen las calificaciones correspondientes.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
