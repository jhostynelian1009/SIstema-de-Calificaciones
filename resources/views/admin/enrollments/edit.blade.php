@extends('layouts.app')

@section('title', 'Editar Matrícula')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold mb-0">Editar Matrícula</h1>
            <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver a la lista
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="card-title text-muted mb-0">Contexto de la Matrícula</h5>
            </div>
            <div class="card-body bg-light border-top border-bottom">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Estudiante</small>
                        <strong>{{ $enrollment->student?->name }}</strong> ({{ $enrollment->student?->email }})
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Período Académico</small>
                        <strong>{{ $enrollment->academicPeriod?->name }}</strong>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.enrollments.update', $enrollment) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-info mb-3 small">
                        <i class="bi bi-info-circle me-1"></i>
                        El estudiante y el período académico no pueden modificarse de manera accidental. Para mover al estudiante a otro curso dentro del mismo período, seleccione el nuevo curso a continuación.
                    </div>

                    <div class="mb-3">
                        <label for="course_id" class="form-label fw-semibold">Nuevo Curso <span class="text-danger">*</span></label>
                        <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id', $enrollment->course_id) == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }} ({{ $course->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="active" {{ old('active', $enrollment->active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="active">Matrícula Activa</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.enrollments.index') }}" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Matrícula</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
