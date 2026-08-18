<div class="row g-3">
    <div class="col-md-8">
        <label for="name" class="form-label fw-semibold">Nombre de la Actividad <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control @error('name') is-invalid @enderror" 
               id="name" 
               name="name" 
               value="{{ old('name', $activity->name ?? '') }}" 
               maxlength="150" 
               placeholder="Ej. Tarea 1, Evaluación Parcial, Trabajo Práctico" 
               required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="percentage" class="form-label fw-semibold">Porcentaje (%) <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="number" 
                   step="0.01" 
                   min="0.01" 
                   max="100" 
                   class="form-control @error('percentage') is-invalid @enderror" 
                   id="percentage" 
                   name="percentage" 
                   value="{{ old('percentage', isset($activity) ? number_format($activity->percentage, 2) : '') }}" 
                   placeholder="Ej. 25.00" 
                   required>
            <span class="input-group-text">%</span>
            @error('percentage')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-text fs-8">Disponible: <strong>{{ $summary['remaining_percentage'] }}%</strong></div>
    </div>

    <div class="col-md-6">
        <label for="due_date" class="form-label fw-semibold">Fecha de Entrega (Opcional)</label>
        <input type="date" 
               class="form-control @error('due_date') is-invalid @enderror" 
               id="due_date" 
               name="due_date" 
               value="{{ old('due_date', isset($activity) && $activity->due_date ? $activity->due_date->format('Y-m-d') : '') }}">
        @error('due_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if(isset($assignment) && $assignment->academicPeriod)
            <div class="form-text fs-8">Período: {{ $assignment->academicPeriod->starts_at?->format('d/m/Y') }} - {{ $assignment->academicPeriod->ends_at?->format('d/m/Y') }}</div>
        @endif
    </div>

    <div class="col-12">
        <label for="description" class="form-label fw-semibold">Descripción u Criterios de Evaluación (Opcional)</label>
        <textarea class="form-control @error('description') is-invalid @enderror" 
                  id="description" 
                  name="description" 
                  rows="3" 
                  placeholder="Detalles sobre las instrucciones, recursos o rúbrica de la actividad...">{{ old('description', $activity->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
