@extends('layouts.app')

@section('title', 'Nueva Matrícula')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold mb-0">Nueva Matrícula</h1>
            <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver a la lista
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.enrollments.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="student_id" class="form-label fw-semibold">Estudiante <span class="text-danger">*</span></label>
                        <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">-- Seleccionar Estudiante --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }} ({{ $student->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="academic_period_id" class="form-label fw-semibold">Período Académico <span class="text-danger">*</span></label>
                        <select name="academic_period_id" id="academic_period_id" class="form-select @error('academic_period_id') is-invalid @enderror" required>
                            <option value="">-- Seleccionar Período Lectivo --</option>
                            @foreach($periods as $period)
                                <option value="{{ $period->id }}" {{ old('academic_period_id') == $period->id ? 'selected' : '' }}>
                                    {{ $period->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_period_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="course_id" class="form-label fw-semibold">Curso <span class="text-danger">*</span></label>
                        <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                            <option value="">-- Seleccionar Curso --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }} ({{ $course->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="active" {{ old('active', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="active">Matrícula Activa</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.enrollments.index') }}" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Registrar Matrícula</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
