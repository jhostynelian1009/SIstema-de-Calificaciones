@extends('layouts.app')

@section('title', 'Reasignar Docente')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold mb-0">Reasignar Docente Responsable</h1>
            <a href="{{ route('admin.teaching-assignments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver a la lista
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="card-title text-muted mb-0">Contexto de la Asignación Académica</h5>
            </div>
            <div class="card-body bg-light border-top border-bottom">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Curso</small>
                        <strong>{{ $teachingAssignment->course?->name }}</strong> ({{ $teachingAssignment->course?->code }})
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Asignatura</small>
                        <strong>{{ $teachingAssignment->subject?->name }}</strong> ({{ $teachingAssignment->subject?->code }})
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Período Académico</small>
                        <strong>{{ $teachingAssignment->academicPeriod?->name }}</strong>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.teaching-assignments.update', $teachingAssignment) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-warning mb-4 small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Esta acción reasigna al docente responsable de esta combinación única de curso, asignatura y período académico. La asignación existente será actualizada conservando el historial.
                    </div>

                    <div class="mb-3">
                        <label for="teacher_id" class="form-label fw-semibold">Nuevo Docente Responsable <span class="text-danger">*</span></label>
                        <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id', $teachingAssignment->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }} ({{ $teacher->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="active" {{ old('active', $teachingAssignment->active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="active">Asignación Activa</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.teaching-assignments.index') }}" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Reasignación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
