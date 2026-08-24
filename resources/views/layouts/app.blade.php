<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Sistema de Calificaciones'))</title>
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    @auth
        <div class="app-wrapper">
            <!-- Sidebar (Desktop) -->
            <aside class="app-sidebar">
                <div class="sidebar-brand">
                    <div class="brand-icon">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div>
                        <div class="brand-title">Calificaciones</div>
                        <div class="brand-subtitle">Gestión Académica</div>
                    </div>
                </div>

                <div class="sidebar-content">
                    @if(Auth::user()->isAdmin())
                        <div class="sidebar-category">Menú Principal</div>
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>

                        <div class="sidebar-category">Estructura Académica</div>
                        <a class="nav-link {{ request()->routeIs('admin.academic-periods.*') ? 'active' : '' }}" href="{{ route('admin.academic-periods.index') }}">
                            <i class="bi bi-calendar3"></i> Períodos
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}" href="{{ route('admin.courses.index') }}">
                            <i class="bi bi-building"></i> Cursos
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}" href="{{ route('admin.subjects.index') }}">
                            <i class="bi bi-book"></i> Asignaturas
                        </a>

                        <div class="sidebar-category">Gestión y Operaciones</div>
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people"></i> Usuarios
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}" href="{{ route('admin.enrollments.index') }}">
                            <i class="bi bi-person-badge"></i> Matrículas
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.teaching-assignments.*') ? 'active' : '' }}" href="{{ route('admin.teaching-assignments.index') }}">
                            <i class="bi bi-person-workspace"></i> Asignaciones
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.partial-publications.*') ? 'active' : '' }}" href="{{ route('admin.partial-publications.index') }}">
                            <i class="bi bi-card-checklist"></i> Parciales
                        </a>

                        <div class="sidebar-category">Monitoreo</div>
                        <a class="nav-link {{ request()->routeIs('admin.activities.*') ? 'active' : '' }}" href="{{ route('admin.activities.index') }}">
                            <i class="bi bi-list-task"></i> Actividades
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.grades.*') ? 'active' : '' }}" href="{{ route('admin.grades.index') }}">
                            <i class="bi bi-journal-check"></i> Calificaciones
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.results.*') ? 'active' : '' }}" href="{{ route('admin.results.index') }}">
                            <i class="bi bi-journal-text"></i> Resultados
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" href="{{ route('admin.audit-logs.index') }}">
                            <i class="bi bi-shield-check"></i> Auditoría
                        </a>

                    @elseif(Auth::user()->isTeacher())
                        <div class="sidebar-category">Panel Docente</div>
                        <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Inicio
                        </a>
                        <a class="nav-link {{ request()->routeIs('teacher.assignments.*') ? 'active' : '' }}" href="{{ route('teacher.assignments.index') }}">
                            <i class="bi bi-journal-bookmark"></i> Mis Asignaciones
                        </a>
                        <a class="nav-link {{ request()->routeIs('teacher.results.*') ? 'active' : '' }}" href="{{ route('teacher.results.index') }}">
                            <i class="bi bi-journal-text"></i> Resultados
                        </a>

                    @elseif(Auth::user()->isStudent())
                        <div class="sidebar-category">Portal Estudiantil</div>
                        <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link {{ request()->routeIs('student.grades.*') ? 'active' : '' }}" href="{{ route('student.grades.index') }}">
                            <i class="bi bi-journal-check"></i> Mis Calificaciones
                        </a>
                    @endif
                </div>

                <div class="sidebar-footer">
                    <div class="d-flex align-items-center justify-content-between">
                        <span>v1.0.0</span>
                        <span class="badge bg-primary-subtle text-primary">Spec as a Skill</span>
                    </div>
                </div>
            </aside>

            <!-- Topbar (Desktop & Mobile trigger) -->
            <header class="app-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Abrir Menú">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <h2 class="page-title me-auto">
                        @yield('header-title', 'Sistema de Calificaciones')
                    </h2>
                </div>

                <div class="topbar-right">
                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none text-dark p-0 d-flex align-items-center gap-2 border-0" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="text-start d-none d-sm-block">
                                <div class="fw-bold text-dark fs-7 leading-tight">{{ Auth::user()->name }}</div>
                                <span class="badge bg-secondary-subtle fs-8">{{ Auth::user()->role->label() }}</span>
                            </div>
                            <i class="bi bi-chevron-down fs-8 text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person-gear me-2 text-primary"></i> Mi Perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Mobile Offcanvas Drawer -->
            <div class="offcanvas offcanvas-start offcanvas-sidebar d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
                <div class="offcanvas-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="brand-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h5 class="offcanvas-title text-white fw-bold" id="mobileSidebarLabel">Calificaciones</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                </div>
                <div class="offcanvas-body p-3">
                    @if(Auth::user()->isAdmin())
                        <div class="sidebar-category">Menú Principal</div>
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>

                        <div class="sidebar-category">Estructura Académica</div>
                        <a class="nav-link {{ request()->routeIs('admin.academic-periods.*') ? 'active' : '' }}" href="{{ route('admin.academic-periods.index') }}"><i class="bi bi-calendar3"></i> Períodos</a>
                        <a class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}" href="{{ route('admin.courses.index') }}"><i class="bi bi-building"></i> Cursos</a>
                        <a class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}" href="{{ route('admin.subjects.index') }}"><i class="bi bi-book"></i> Asignaturas</a>

                        <div class="sidebar-category">Gestión</div>
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> Usuarios</a>
                        <a class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}" href="{{ route('admin.enrollments.index') }}"><i class="bi bi-person-badge"></i> Matrículas</a>
                        <a class="nav-link {{ request()->routeIs('admin.teaching-assignments.*') ? 'active' : '' }}" href="{{ route('admin.teaching-assignments.index') }}"><i class="bi bi-person-workspace"></i> Asignaciones</a>
                        <a class="nav-link {{ request()->routeIs('admin.partial-publications.*') ? 'active' : '' }}" href="{{ route('admin.partial-publications.index') }}"><i class="bi bi-card-checklist"></i> Parciales</a>

                        <div class="sidebar-category">Monitoreo</div>
                        <a class="nav-link {{ request()->routeIs('admin.activities.*') ? 'active' : '' }}" href="{{ route('admin.activities.index') }}"><i class="bi bi-list-task"></i> Actividades</a>
                        <a class="nav-link {{ request()->routeIs('admin.grades.*') ? 'active' : '' }}" href="{{ route('admin.grades.index') }}"><i class="bi bi-journal-check"></i> Calificaciones</a>
                        <a class="nav-link {{ request()->routeIs('admin.results.*') ? 'active' : '' }}" href="{{ route('admin.results.index') }}"><i class="bi bi-journal-text"></i> Resultados</a>
                        <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" href="{{ route('admin.audit-logs.index') }}"><i class="bi bi-shield-check"></i> Auditoría</a>
                    @elseif(Auth::user()->isTeacher())
                        <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}"><i class="bi bi-speedometer2"></i> Inicio</a>
                        <a class="nav-link {{ request()->routeIs('teacher.assignments.*') ? 'active' : '' }}" href="{{ route('teacher.assignments.index') }}"><i class="bi bi-journal-bookmark"></i> Mis Asignaciones</a>
                        <a class="nav-link {{ request()->routeIs('teacher.results.*') ? 'active' : '' }}" href="{{ route('teacher.results.index') }}"><i class="bi bi-journal-text"></i> Resultados</a>
                    @elseif(Auth::user()->isStudent())
                        <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                        <a class="nav-link {{ request()->routeIs('student.grades.*') ? 'active' : '' }}" href="{{ route('student.grades.index') }}"><i class="bi bi-journal-check"></i> Mis Calificaciones</a>
                    @endif
                </div>
            </div>

            <!-- Main Workspace Container -->
            <main class="app-main animate-fade-in">
                @include('components.flash-messages')
                @include('components.validation-errors')
                @yield('content')
            </main>
        </div>
    @else
        <!-- Guest Views (Login, Password Reset) -->
        <main class="min-vh-100 d-flex align-items-center justify-content-center py-5 bg-brand-bg">
            <div class="container">
                @include('components.flash-messages')
                @include('components.validation-errors')
                @yield('content')
            </div>
        </main>
    @endauth

    @stack('scripts')
</body>
</html>
