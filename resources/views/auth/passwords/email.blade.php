@extends('layouts.app')

@section('title', 'Recuperar Contraseña - Sistema de Calificaciones')

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary text-white p-4 text-center rounded-top-3">
                <i class="bi bi-shield-lock fs-1 d-block mb-2"></i>
                <h1 class="h4 fw-bold mb-0">Recuperar Contraseña</h1>
                <p class="text-white-50 fs-7 mb-0">Enviaremos un enlace de restablecimiento a su correo</p>
            </div>

            <div class="card-body p-4 p-md-5">
                @if (session('status'))
                    <div class="alert alert-success role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" novalidate>
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="usuario@calificaciones.local">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fs-6">
                            <i class="bi bi-send me-1"></i> Enviar Enlace de Restablecimiento
                        </button>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Volver al Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
