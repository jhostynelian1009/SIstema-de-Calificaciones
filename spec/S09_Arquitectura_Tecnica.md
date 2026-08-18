# S09 — Arquitectura técnica

## Stack

- Laravel 12 y PHP 8.2+.
- MySQL 8 con `utf8mb4`.
- Blade para renderizado en servidor.
- Bootstrap 5 compilado mediante Vite.
- PHPUnit/Pest según la instalación del proyecto; no mezclar estilos sin necesidad.

## Capas

```text
Rutas + Middleware
        ↓
Controladores + Form Requests + Policies
        ↓
Servicios de aplicación/dominio
        ↓
Modelos Eloquent + MySQL
        ↓
Blade + Bootstrap
```

## Componentes recomendados

- `RoleMiddleware`: limita áreas por rol.
- Policies: `CoursePolicy`, `TeachingAssignmentPolicy`, `ActivityPolicy`, `GradePolicy`, `PublicationPolicy`.
- Form Requests por operación de escritura.
- `GradeCalculationService`: cálculo decimal de promedios.
- `PartialReadinessService`: determina porcentaje y calificaciones pendientes.
- `PartialPublicationService`: publica o reabre dentro de transacciones.
- `AuditService`: registra cambios sensibles.
- Scopes Eloquent: activos, período actual, asignados al docente, publicados.

## Organización orientativa

```text
app/
├── Enums/
├── Http/Controllers/Admin/
├── Http/Controllers/Teacher/
├── Http/Controllers/Student/
├── Http/Middleware/
├── Http/Requests/
├── Models/
├── Policies/
└── Services/Grades/
resources/views/
├── layouts/
├── components/
├── admin/
├── teacher/
└── student/
```

## Rutas y nombres

- Agrupar rutas por `auth`, rol y prefijo.
- Usar rutas con nombre: `admin.users.index`, `teacher.assignments.show`, `student.grades.index`.
- Aplicar model binding solo después de asegurar que la Policy impide acceso horizontal.

## Cálculo decimal

Para evitar errores binarios, convertir `score` y `percentage` a enteros escalados o utilizar una biblioteca decimal compatible. El servicio debe ofrecer un único resultado coherente en UI y pruebas, con redondeo `half up` a dos decimales.

## Datos y concurrencia

- Guardado masivo y publicación dentro de transacciones.
- Bloqueo o verificación de estado al publicar para evitar dobles acciones.
- Índices y eager loading según `S06`.
- Configuración sensible solo en `.env`; entregar `.env.example` sin secretos.

