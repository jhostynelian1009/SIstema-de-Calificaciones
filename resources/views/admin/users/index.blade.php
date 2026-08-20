@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Sistema de Calificaciones')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-people me-2"></i> Gestión de Usuarios
        </h1>
        <p class="text-muted mb-0">Administre el acceso, roles y estados de los usuarios del sistema</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary fw-semibold px-3">
            <i class="bi bi-person-plus me-1"></i> Registrar Nuevo Usuario
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Search & Filters -->
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text"
                           name="search"
                           class="form-control border-start-0 ps-0 bg-light"
                           placeholder="Buscar por nombre o correo..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="role" class="form-select bg-light">
                    <option value="">Todos los Roles</option>
                    @foreach($roles as $roleOption)
                        <option value="{{ $roleOption->value }}" {{ request('role') === $roleOption->value ? 'selected' : '' }}>
                            {{ $roleOption->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="active" class="form-select bg-light">
                    <option value="">Todos los Estados</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            <div class="col-12 col-md-2 text-md-end">
                <button type="submit" class="btn btn-secondary w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow-sm border-0 rounded-3 bg-white">
    <div class="card-body p-0">
        @if($users->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-people fs-1 text-secondary opacity-50 d-block mb-2"></i>
                <h5 class="fw-bold mb-1">No se encontraron usuarios</h5>
                <p class="fs-7 mb-0">Intente modificar los términos de búsqueda o filtros aplicados.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-secondary fs-8 text-uppercase">Usuario</th>
                            <th class="py-3 text-secondary fs-8 text-uppercase">Correo</th>
                            <th class="py-3 text-secondary fs-8 text-uppercase">Rol</th>
                            <th class="py-3 text-secondary fs-8 text-uppercase">Estado</th>
                            <th class="py-3 text-secondary fs-8 text-uppercase">Registro</th>
                            <th class="text-end pe-4 py-3 text-secondary fs-8 text-uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($users as $user)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="bi bi-person-fill fs-6"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.users.show', $user) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $user->name }}
                                            </a>
                                            @if(Auth::id() === $user->id)
                                                <span class="badge bg-info-subtle text-info border border-info-subtle ms-1 fs-9">Tú</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="text-muted fs-7">{{ $user->email }}</span></td>
                                <td>
                                    @if($user->isAdmin())
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-8">
                                            <i class="bi bi-shield-lock-fill me-1"></i> Administrador
                                        </span>
                                    @elseif($user->isTeacher())
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-8">
                                            <i class="bi bi-person-workspace me-1"></i> Docente
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-8">
                                            <i class="bi bi-person-badge me-1"></i> Estudiante
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-8">
                                            <i class="bi bi-check-circle-fill me-1"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white px-2 py-1 fs-8">
                                            <i class="bi bi-x-circle-fill me-1"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td><span class="text-muted fs-8">{{ $user->created_at?->format('d/m/Y') }}</span></td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info btn-sm px-2" title="Ver Detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary btn-sm px-2" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if(Auth::id() !== $user->id)
                                            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                @if($user->active)
                                                    <button type="submit" class="btn btn-outline-warning btn-sm px-2" title="Desactivar" onclick="return confirm('¿Está seguro de desactivar a este usuario?');">
                                                        <i class="bi bi-person-x"></i>
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-outline-success btn-sm px-2" title="Activar" onclick="return confirm('¿Está seguro de activar a este usuario?');">
                                                        <i class="bi bi-person-check"></i>
                                                    </button>
                                                @endif
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top bg-light">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
