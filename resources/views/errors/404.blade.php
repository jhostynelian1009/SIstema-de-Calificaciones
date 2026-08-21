@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <div class="card shadow-sm border-0 rounded-3 p-5 mx-auto max-w-lg bg-white">
        <div class="card-body">
            <i class="bi bi-file-earmark-x text-warning display-1 mb-3"></i>
            <h1 class="h3 fw-bold text-dark mb-2">404 — Página No Encontrada</h1>
            <p class="text-muted mb-4">
                La página o recurso que intenta consultar no existe o ha sido movido.
            </p>
            <div class="d-flex justify-content-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-house me-1"></i> Ir al Panel Principal
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
