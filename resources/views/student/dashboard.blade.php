@extends('layouts.app')

@section('title', 'Panel Estudiante - Sistema de Calificaciones')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h1 class="h4 fw-bold mb-1 text-primary">
                            <i class="bi bi-card-checklist me-2"></i> Mis Calificaciones
                        </h1>
                        <p class="text-muted mb-0 fs-7">Bienvenido a tu consulta académica personal.</p>
                    </div>
                    <span class="badge bg-info text-dark px-3 py-2 fs-7">Rol: Estudiante</span>
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                <div class="alert alert-info border-0 bg-info bg-opacity-10 text-dark mb-4" role="alert">
                    <h2 class="h6 fw-bold mb-1">
                        <i class="bi bi-person-circle me-1"></i> Hola, {{ $user->name }}
                    </h2>
                    <p class="mb-0 fs-7">Has iniciado sesión correctamente como <strong>Estudiante</strong> ({{ $user->email }}).</p>
                </div>

                <div class="p-4 bg-light rounded-3 border text-center my-4">
                    <i class="bi bi-award fs-1 text-muted d-block mb-2"></i>
                    <h3 class="h5 fw-bold mb-2">Consulta de Historial Académico</h3>
                    <p class="text-muted mb-0 fs-7">La visualización de asignaturas matriculadas, calificaciones de parciales publicadas y promedios finales estará disponible en el módulo de estudiante.</p>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                        <i class="bi bi-person-gear me-1"></i> Mi Perfil
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
