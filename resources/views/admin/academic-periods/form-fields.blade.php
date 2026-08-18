<div class="mb-3">
    <label for="name" class="form-label fw-semibold">Nombre del Período Académico <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $academicPeriod->name ?? '') }}" required placeholder="ej. Período académico 2026–2027">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <label for="starts_at" class="form-label fw-semibold">Fecha de Inicio <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at" name="starts_at" value="{{ old('starts_at', isset($academicPeriod) ? $academicPeriod->starts_at?->format('Y-m-d') : '') }}" required>
        @error('starts_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="ends_at" class="form-label fw-semibold">Fecha de Finalización <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('ends_at') is-invalid @enderror" id="ends_at" name="ends_at" value="{{ old('ends_at', isset($academicPeriod) ? $academicPeriod->ends_at?->format('Y-m-d') : '') }}" required>
        @error('ends_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3 form-check">
    <input type="hidden" name="active" value="0">
    <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $academicPeriod->active ?? false) ? 'checked' : '' }}>
    <label class="form-check-label fw-semibold" for="active">
        Establecer como Período Activo
    </label>
    <div class="form-text fs-8 text-muted">
        Nota: Solo puede existir un único período activo en el sistema. Al activar este período, cualquier otro período activo quedará inactivo automáticamente.
    </div>
</div>

@if(!isset($academicPeriod))
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3">
        <i class="bi bi-info-circle-fill fs-3 text-info"></i>
        <div class="fs-7">
            Al registrar este período, el sistema creará automáticamente sus <strong>dos parciales obligatorios (P1 y P2)</strong>, cada uno con una ponderación fija del <strong>50 %</strong>.
        </div>
    </div>
@endif
