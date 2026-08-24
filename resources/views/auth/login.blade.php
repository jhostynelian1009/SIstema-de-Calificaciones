@extends('layouts.app')

@section('title', 'Iniciar Sesión - Sistema de Calificaciones')

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-brand-inst text-white p-4 text-center border-0" style="background: linear-gradient(135deg, #1E3A5F 0%, #2563EB 100%);">
                <div class="brand-icon mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.5rem; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h1 class="h4 fw-bold mb-1 text-white">Sistema de Calificaciones</h1>
                <p class="text-white-50 fs-7 mb-0">Ingrese sus credenciales de acceso institucional</p>
            </div>

            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="correo@institucional.edu">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label fs-7" for="remember">
                                Recordarme en este equipo
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <a class="text-decoration-none fs-7 text-brand-primary" href="{{ route('password.request') }}">
                                ¿Olvidó su contraseña?
                            </a>
                        @endif
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fs-6">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-3 text-muted fs-8 border-top">
                Acceso restringido a personal autorizado y estudiantes matriculados.
            </div>
        </div>
    </div>
</div>
@endsection
