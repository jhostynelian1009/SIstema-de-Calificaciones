@extends('layouts.app')

@section('title', 'Bienvenido - Sistema de Calificaciones')

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header text-white p-4 p-md-5 text-center border-0" style="background: linear-gradient(135deg, #1E3A5F 0%, #2563EB 100%);">
                <div class="brand-icon mx-auto mb-3" style="width: 64px; height: 64px; font-size: 2rem; background: rgba(255,255,255,0.15); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-2 text-white">Sistema de Calificaciones</h1>
                <p class="text-white-50 fs-6 mb-4">Plataforma Académica de Gestión y Consulta de Calificaciones</p>
                
                @guest
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4 py-2 fw-semibold text-primary shadow-sm hover-elevate">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg px-4 py-2 fw-semibold text-primary shadow-sm hover-elevate">
                        <i class="bi bi-speedometer2 me-2"></i> Ir al Panel Principal
                    </a>
                @endguest
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-4 text-center">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <div class="text-primary fs-3 mb-2"><i class="bi bi-shield-lock"></i></div>
                            <h3 class="h6 fw-bold mb-1">Administración</h3>
                            <p class="fs-8 text-muted mb-0">Gestión de usuarios, períodos académicos, cursos y auditoría.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-center">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <div class="text-primary fs-3 mb-2"><i class="bi bi-journal-bookmark"></i></div>
                            <h3 class="h6 fw-bold mb-1">Docentes</h3>
                            <p class="fs-8 text-muted mb-0">Creación de actividades, calificación y publicación de parciales.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-center">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <div class="text-primary fs-3 mb-2"><i class="bi bi-person-badge"></i></div>
                            <h3 class="h6 fw-bold mb-1">Estudiantes</h3>
                            <p class="fs-8 text-muted mb-0">Consulta detallada de calificaciones por asignaturas y períodos.</p>
                        </div>
                    </div>
                </div>

                <div class="row g-3 pt-3 border-top">
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <h2 class="h6 fw-bold text-primary mb-2">
                                <i class="bi bi-server me-1"></i> Entorno de Ejecución
                            </h2>
                            <ul class="list-unstyled mb-0 fs-7 text-muted">
                                <li><strong>Framework:</strong> Laravel {{ app()->version() }}</li>
                                <li><strong>Lenguaje:</strong> PHP {{ PHP_VERSION }}</li>
                                <li><strong>Persistencia:</strong> MySQL (utf8mb4)</li>
                                <li><strong>Interfaz:</strong> Blade + Bootstrap 5</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <h2 class="h6 fw-bold text-primary mb-2">
                                <i class="bi bi-gear-fill me-1"></i> Configuración del Sistema
                            </h2>
                            <ul class="list-unstyled mb-0 fs-7 text-muted">
                                <li><strong>Institución:</strong> {{ config('app.name') }}</li>
                                <li><strong>Idioma:</strong> {{ config('app.locale') }} (Español)</li>
                                <li><strong>Zona Horaria:</strong> {{ config('app.timezone') }}</li>
                                <li><strong>Estado:</strong> Sistema Operativo</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light text-center py-3 text-muted fs-8 border-top">
                Sistema de Calificaciones &copy; {{ date('Y') }} - Todos los derechos reservados.
            </div>
        </div>
    </div>
</div>
@endsection