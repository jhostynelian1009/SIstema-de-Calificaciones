<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="name" class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text"
                   name="name"
                   id="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name ?? '') }}"
                   required
                   maxlength="255"
                   placeholder="Ej. Juan Pérez">
        </div>
        @error('name')
            <div class="text-danger fs-8 mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="email" class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email"
                   name="email"
                   id="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email ?? '') }}"
                   required
                   maxlength="255"
                   placeholder="ejemplo@calificaciones.local">
        </div>
        @error('email')
            <div class="text-danger fs-8 mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="role" class="form-label fw-semibold">Rol de Usuario <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
            @php
                $isRoleLocked = false;
                $lockReason = '';
                if (isset($user) && $user->id) {
                    if ($user->isStudent() && ($hasStudentHistory ?? false)) {
                        $isRoleLocked = true;
                        $lockReason = 'No se puede modificar el rol de un estudiante con historial de matrículas o calificaciones.';
                    } elseif ($user->isTeacher() && ($hasTeacherHistory ?? false)) {
                        $isRoleLocked = true;
                        $lockReason = 'No se puede modificar el rol de un docente con asignaciones académicas o calificaciones registradas.';
                    } elseif ($user->isAdmin() && Auth::id() === $user->id) {
                        $isRoleLocked = true;
                        $lockReason = 'No puede degradar el rol de su propia cuenta de administrador.';
                    }
                }
            @endphp

            @if($isRoleLocked)
                <input type="text" class="form-control bg-light" value="{{ $user->role->label() }}" readonly disabled>
                <input type="hidden" name="role" value="{{ $user->role->value }}">
            @else
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="" disabled {{ old('role', $user->role->value ?? '') === '' ? 'selected' : '' }}>Seleccione un rol...</option>
                    @foreach($roles as $roleOption)
                        <option value="{{ $roleOption->value }}" {{ old('role', $user->role->value ?? '') === $roleOption->value ? 'selected' : '' }}>
                            {{ $roleOption->label() }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
        @if($isRoleLocked)
            <div class="form-text text-warning-emphasis fs-8 mt-1">
                <i class="bi bi-info-circle me-1"></i>{{ $lockReason }}
            </div>
        @endif
        @error('role')
            <div class="text-danger fs-8 mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="active" class="form-label fw-semibold">Estado de la Cuenta</label>
        <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
            <div>
                <span class="fw-semibold d-block">Usuario Activo</span>
                <small class="text-muted fs-8">Permite el acceso al sistema con las credenciales asignadas.</small>
            </div>
            <div class="form-check form-switch fs-4 mb-0 ms-3">
                <input class="form-check-input"
                       type="checkbox"
                       role="switch"
                       id="active"
                       name="active"
                       value="1"
                       {{ old('active', isset($user) ? $user->active : true) ? 'checked' : '' }}
                       {{ (isset($user) && Auth::id() === $user->id) ? 'disabled' : '' }}>
                @if(isset($user) && Auth::id() === $user->id)
                    <input type="hidden" name="active" value="1">
                @endif
            </div>
        </div>
        @if(isset($user) && Auth::id() === $user->id)
            <div class="form-text text-muted fs-8 mt-1">
                <i class="bi bi-info-circle me-1"></i> No puede desactivar su propia cuenta.
            </div>
        @endif
        @error('active')
            <div class="text-danger fs-8 mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    @if(!isset($user) || !$user->id)
        <div class="col-12 col-md-6">
            <label for="password" class="form-label fw-semibold">Contraseña Inicial <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-key"></i></span>
                <input type="password"
                       name="password"
                       id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required
                       placeholder="Mínimo 8 caracteres">
            </div>
            @error('password')
                <div class="text-danger fs-8 mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6">
            <label for="password_confirmation" class="form-label fw-semibold">Confirmar Contraseña <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                <input type="password"
                       name="password_confirmation"
                       id="password_confirmation"
                       class="form-control"
                       required
                       placeholder="Repita la contraseña">
            </div>
        </div>
    @endif
</div>
