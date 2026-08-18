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
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ url('/') }}">
                <i class="bi bi-mortarboard-fill fs-4"></i>
                <span>Sistema de Calificaciones</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ url('/') }}">Inicio</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <span class="navbar-text text-white-50 fs-7">
                        Entorno Base Inicializado
                    </span>
                </div>
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
