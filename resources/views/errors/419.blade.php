@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <div class="card shadow-sm border-0 rounded-3 p-5 mx-auto max-w-lg bg-white">
        <div class="card-body">
            <i class="bi bi-clock-history text-secondary display-1 mb-3"></i>
            <h1 class="h3 fw-bold text-dark mb-2">419 — Sesión Expirada</h1>
            <p class="text-muted mb-4">
                Su sesión ha expirado por inactividad. Por favor, vuelva a cargar la página e intente de nuevo.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-arrow-clockwise me-1"></i> Iniciar Sesión de Nuevo
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
