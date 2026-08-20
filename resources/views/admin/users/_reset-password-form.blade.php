<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white border-bottom p-3">
        <h5 class="fw-bold mb-0 text-primary fs-6">
            <i class="bi bi-key-fill me-2"></i> Restablecer Contraseña Administrativa
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="alert alert-info border-0 fs-7 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock-fill fs-5"></i>
            <div>
                Esta acción establece una nueva contraseña para la cuenta de <strong>{{ $user->name }}</strong>.
                Por razones de seguridad, las contraseñas nunca se muestran ni se almacenan en texto plano.
            </div>
        </div>

        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="new_password" class="form-label fw-semibold">Nueva Contraseña <span class="text-danger">*</span></label>
                    <input type="password"
                           name="password"
                           id="new_password"
                           class="form-control @error('password') is-invalid @enderror"
                           required
                           placeholder="Mínimo 8 caracteres">
                    @error('password')
                        <div class="text-danger fs-8 mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="new_password_confirmation" class="form-label fw-semibold">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                    <input type="password"
                           name="password_confirmation"
                           id="new_password_confirmation"
                           class="form-control"
                           required
                           placeholder="Repita la nueva contraseña">
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-warning fw-semibold px-4" onclick="return confirm('¿Está seguro de restablecer la contraseña para este usuario?');">
                    <i class="bi bi-check-circle me-1"></i> Actualizar Contraseña
                </button>
            </div>
        </form>
    </div>
</div>
