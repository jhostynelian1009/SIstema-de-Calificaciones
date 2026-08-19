<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Sistema de Calificaciones'))</title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ Auth::check() ? route('dashboard') : url('/') }}">
                <i class="bi bi-mortarboard-fill fs-4"></i>
                <span>Sistema de Calificaciones</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        @if(Auth::user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Panel
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}" href="{{ route('admin.courses.index') }}">
                                    <i class="bi bi-building me-1"></i> Cursos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}" href="{{ route('admin.subjects.index') }}">
                                    <i class="bi bi-book me-1"></i> Asignaturas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.academic-periods.*') ? 'active' : '' }}" href="{{ route('admin.academic-periods.index') }}">
                                    <i class="bi bi-calendar3 me-1"></i> Períodos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}" href="{{ route('admin.enrollments.index') }}">
                                    <i class="bi bi-person-badge me-1"></i> Matrículas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.teaching-assignments.*') ? 'active' : '' }}" href="{{ route('admin.teaching-assignments.index') }}">
                                    <i class="bi bi-person-workspace me-1"></i> Asignaciones
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.partial-publications.*') ? 'active' : '' }}" href="{{ route('admin.partial-publications.index') }}">
                                    <i class="bi bi-card-checklist me-1"></i> Estados de Parciales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.activities.*') ? 'active' : '' }}" href="{{ route('admin.activities.index') }}">
                                    <i class="bi bi-list-task me-1"></i> Actividades
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.grades.*') ? 'active' : '' }}" href="{{ route('admin.grades.index') }}">
                                    <i class="bi bi-journal-check me-1"></i> Calificaciones
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" href="{{ route('admin.audit-logs.index') }}">
                                    <i class="bi bi-shield-check me-1"></i> Auditoría
                                </a>
                            </li>
                        @elseif(Auth::user()->isTeacher())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Panel Docente
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('teacher.assignments.*') ? 'active' : '' }}" href="{{ route('teacher.assignments.index') }}">
                                    <i class="bi bi-journal-bookmark me-1"></i> Mis Asignaciones
                                </a>
                            </li>
                        @elseif(Auth::user()->isStudent())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                                    <i class="bi bi-card-checklist me-1"></i> Mis Calificaciones
                                </a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">Inicio</a>
                        </li>
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-2">
                    @guest
                        <li class="nav-item">
                            <a class="btn btn-outline-light btn-sm px-3" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                            </a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a id="userDropdown" class="nav-link dropdown-toggle active d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="badge bg-light text-primary rounded-circle p-2">
                                    <i class="bi bi-person-fill fs-6"></i>
                                </span>
                                <span>{{ Auth::user()->name }}</span>
                                <span class="badge bg-info text-dark fs-8 ms-1">{{ Auth::user()->role->label() }}</span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                                <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person-gear me-2 text-primary"></i> Mi Perfil
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="py-4 flex-grow-1">
        <div class="container">
            @include('components.flash-messages')
            @include('components.validation-errors')

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer py-3 bg-white border-top text-muted mt-auto">
        <div class="container text-center text-md-between d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 fs-7">
            <div>
                &copy; {{ date('Y') }} <strong>Sistema de Calificaciones</strong>. Todos los derechos reservados.
            </div>
            <div>
                <span class="badge bg-secondary">v1.0.0</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
