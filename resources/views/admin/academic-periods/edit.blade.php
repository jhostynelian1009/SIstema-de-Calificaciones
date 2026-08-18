@extends('layouts.app')

@section('title', 'Editar Período Académico - Sistema de Calificaciones')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom p-4">
                <h1 class="h4 fw-bold mb-0 text-primary">
                    <i class="bi bi-pencil-square me-2"></i> Editar Período Académico
                </h1>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.academic-periods.update', $academicPeriod) }}" novalidate>
                    @csrf
                    @method('PUT')
                    @include('admin.academic-periods.form-fields')

                    <div class="card bg-light border-0 my-3 p-3 rounded-2">
                        <h6 class="fw-bold text-secondary mb-2"><i class="bi bi-diagram-2 me-1"></i> Parciales Configurados</h6>
                        <div class="d-flex gap-3">
                            @foreach($academicPeriod->partials as $partial)
                                <div class="badge bg-white text-dark border p-2 flex-grow-1 text-center font-monospace fs-7">
                                    <strong>{{ $partial->name }} (P{{ $partial->number }})</strong>: {{ number_format($partial->weight, 0) }}%
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="{{ route('admin.academic-periods.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Actualizar Período
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
