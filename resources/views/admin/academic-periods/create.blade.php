@extends('layouts.app')

@section('title', 'Nuevo Período Académico - Sistema de Calificaciones')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom p-4">
                <h1 class="h4 fw-bold mb-0 text-primary">
                    <i class="bi bi-plus-circle me-2"></i> Crear Período Académico
                </h1>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.academic-periods.store') }}" novalidate>
                    @csrf
                    @include('admin.academic-periods.form-fields')

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="{{ route('admin.academic-periods.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar y Generar P1/P2
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
