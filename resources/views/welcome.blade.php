@extends('layouts.app')

@section('title', 'Inicio - Sistema de Calificaciones')

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-3" style="width: 72px; height: 72px;">
                        <i class="bi bi-mortarboard-fill fs-1"></i>
                    </div>
                    <h1 class="h3 fw-bold mb-2">Sistema de Calificaciones</h1>
                    <p class="text-muted">Plataforma Académica de Gestión y Consulta de Calificaciones</p>
                    <span class="badge bg-success px-3 py-2 fs-7">
                        <i class="bi bi-check-circle-fill me-1"></i> Entorno Base Instalado Exitosamente (K-001)
                    </span>
                </div>

                <hr class="my-4">

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <h2 class="h6 fw-bold text-primary mb-2">
                                <i class="bi bi-server me-1"></i> Stack Técnico
                            </h2>
                            <ul class="list-unstyled mb-0 fs-7 text-muted">
                                <li><strong>Framework:</strong> Laravel {{ app()->version() }}</li>
                                <li><strong>Lenguaje:</strong> PHP {{ PHP_VERSION }}</li>
                                <li><strong>Persistencia:</strong> MySQL (utf8mb4)</li>
                                <li><strong>Interfaz:</strong> Blade + Bootstrap 5</li>
                                <li><strong>Bundler:</strong> Vite</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <h2 class="h6 fw-bold text-primary mb-2">
                                <i class="bi bi-gear-fill me-1"></i> Configuración Local
                            </h2>
                            <ul class="list-unstyled mb-0 fs-7 text-muted">
                                <li><strong>Nombre:</strong> {{ config('app.name') }}</li>
                                <li><strong>Idioma:</strong> {{ config('app.locale') }} (Español)</li>
                                <li><strong>Zona Horaria:</strong> {{ config('app.timezone') }}</li>
                                <li><strong>Entorno:</strong> {{ config('app.env') }}</li>
                                <li><strong>Pruebas:</strong> MySQL Dedicated DB</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-4 mb-0 d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill fs-4 me-3 flex-shrink-0"></i>
                    <div class="fs-7">
                        <strong>Próxima Skill (K-002):</strong> El módulo de autenticación, roles de usuario y control de acceso seguro será configurado en la siguiente fase de desarrollo.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection