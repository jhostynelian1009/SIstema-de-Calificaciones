<div class="mb-3">
    <label for="name" class="form-label fw-semibold">Nombre del Curso <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $course->name ?? '') }}" required placeholder="ej. Octavo A">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="code" class="form-label fw-semibold">Código Único <span class="text-danger">*</span></label>
    <input type="text" class="form-control text-uppercase @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $course->code ?? '') }}" required placeholder="ej. 8VO-A">
    <div class="form-text fs-8">Se convertirá automáticamente a mayúsculas sin espacios externos. Debe ser único.</div>
    @error('code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label fw-semibold">Descripción (Opcional)</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Información adicional sobre la paralelo o nivel">{{ old('description', $course->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 form-check">
    <input type="hidden" name="active" value="0">
    <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $course->active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label fw-semibold" for="active">
        Curso Activo
    </label>
</div>
