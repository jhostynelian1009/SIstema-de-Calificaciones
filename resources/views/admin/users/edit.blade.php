@extends('layouts.app')

@section('title', 'Editar Usuario - Sistema de Calificaciones')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-pencil-square me-2"></i> Editar Usuario: {{ $user->name }}
        </h1>
        <p class="text-muted mb-0">Actualice la información de perfil, correo y rol del usuario</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary fw-semibold px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver a la Lista
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card shadow-sm border-0 rounded-3 bg-white mb-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.users._form')

            <div class="mt-4 text-end border-top pt-3">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-semibold px-4">
                    <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Password Reset Subform -->
@include('admin.users._reset-password-form')

@endsection
