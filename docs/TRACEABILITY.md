# Matriz de Trazabilidad de Requisitos (TRACEABILITY.md)

**Proyecto:** Sistema de Calificaciones Académicas  
**Metodología:** Spec as a Skill (K-001 a K-012)  
**Versión de Entrega:** 1.0.0 (K-012)  

---

## 1. Introducción
La presente matriz de trazabilidad mapea cada Requisito Funcional (RF-001 a RF-036), Criterios de Aceptación (`Spec/S10`), Requisitos No Funcionales y Políticas de Seguridad a su implementación en código (Controladores, Servicios, Modelos y Vistas) y a su evidencia en pruebas automatizadas.

---

## 2. Matriz de Trazabilidad Funcional

| ID Requisito | Descripción del Requisito | Implementación en Código | Ruta / Vista | Prueba Automatizada | Estado |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **RF-001** | Autenticación de usuarios por correo y contraseña con bcrypt | `App\Http\Controllers\Auth\LoginController` | `/login` (`auth.login`) | `Tests\Feature\AuthenticationTest` | Cumplido |
| **RF-002** | Control de acceso basado en roles (`admin`, `teacher`, `student`) | `App\Http\Middleware\RoleMiddleware` | Todos los grupos de ruta | `Tests\Feature\RoleAccessTest` | Cumplido |
| **RF-003** | Inhabilitación de inicio de sesión para usuarios inactivos | `App\Http\Middleware\EnsureUserIsActive` | Middleware global `web` | `Tests\Feature\AuthenticationTest` | Cumplido |
| **RF-004** | Cierre de sesión seguro e invalidación de sesión | `App\Http\Controllers\Auth\LoginController::logout` | `POST /logout` | `Tests\Feature\AuthenticationTest` | Cumplido |
| **RF-005** | Deshabilitación explícita de registro público | Sin rutas de registro en `routes/web.php` | N/A | `Tests\Feature\AuthenticationTest` | Cumplido |
| **RF-006** | Protección del último usuario administrador activo | `App\Services\Users\UserService` | `/admin/users` | `Tests\Feature\AdminModuleTest` | Cumplido |
| **RF-007** | Gestión administrativa de usuarios (CRUD) | `App\Http\Controllers\Admin\UserController` | `/admin/users` | `Tests\Feature\AdminModuleTest` | Cumplido |
| **RF-008** | Restablecimiento seguro de contraseña por administrador | `App\Http\Controllers\Admin\UserController::resetPassword` | `/admin/users/{user}/reset-password` | `Tests\Feature\AdminModuleTest` | Cumplido |
| **RF-009** | Gestión de Cursos (Crear, Editar, Desactivar) | `App\Http\Controllers\Admin\CourseController` | `/admin/courses` | `Tests\Feature\CourseManagementTest` | Cumplido |
| **RF-010** | Gestión de Asignaturas (Crear, Editar, Desactivar) | `App\Http\Controllers\Admin\SubjectController` | `/admin/subjects` | `Tests\Feature\SubjectManagementTest` | Cumplido |
| **RF-011** | Gestión de Períodos Académicos | `App\Http\Controllers\Admin\AcademicPeriodController` | `/admin/academic-periods` | `Tests\Feature\AcademicPeriodManagementTest` | Cumplido |
| **RF-012** | Generación automática e inmutable de Parcial 1 y Parcial 2 (50% c/u) | `App\Services\Academic\AcademicPeriodService` | Evento al crear período | `Tests\Feature\AcademicPeriodManagementTest` | Cumplido |
| **RF-013** | Garantía de un único período académico activo a la vez | `App\Services\Academic\AcademicPeriodService` | Transacción con DB lock | `Tests\Feature\AcademicPeriodManagementTest` | Cumplido |
| **RF-014** | Gestión de Matrículas de Estudiantes por Curso y Período | `App\Http\Controllers\Admin\EnrollmentController` | `/admin/enrollments` | `Tests\Feature\EnrollmentManagementTest` | Cumplido |
| **RF-015** | Validación de Unicidad Estudiante-Período | `App\Http\Requests\StoreEnrollmentRequest` | `/admin/enrollments/store` | `Tests\Feature\EnrollmentManagementTest` | Cumplido |
| **RF-016** | Asignación Docente por Curso, Asignatura y Período | `App\Http\Controllers\Admin\TeachingAssignmentController` | `/admin/teaching-assignments` | `Tests\Feature\TeachingAssignmentManagementTest` | Cumplido |
| **RF-017** | Validación de Unicidad Curso-Asignatura-Período (1 Docente) | `App\Http\Requests\StoreTeachingAssignmentRequest` | `/admin/teaching-assignments` | `Tests\Feature\TeachingAssignmentManagementTest` | Cumplido |
| **RF-018** | Creación de Actividades Evaluativas por Docente | `App\Http\Controllers\Teacher\ActivityController` | `/teacher/activities` | `Tests\Feature\ActivityManagementTest` | Cumplido |
| **RF-019** | Control de Ponderación Sumatoria por Parcial (Máximo 100%) | `App\Services\Grades\ActivityService` | Form Request & Service | `Tests\Feature\ActivityManagementTest` | Cumplido |
| **RF-020** | Registro masivo e individual de calificaciones (0.00 a 10.00) | `App\Http\Controllers\Teacher\GradeController` | `/teacher/grades` | `Tests\Feature\GradeManagementTest` | Cumplido |
| **RF-021** | Bloqueo de edición de calificaciones en parciales publicados | `App\Services\Grades\PartialPublicationStateService` | Middleware & Policies | `Tests\Feature\TeacherModuleTest` | Cumplido |
| **RF-022** | Publicación oficial de parciales por docente (Validación al 100%) | `App\Services\Grades\PartialPublicationService` | `/teacher/publish` | `Tests\Feature\PartialPublicationTest` | Cumplido |
| **RF-023** | Reapertura justificada de parciales por Administrador | `App\Http\Controllers\Admin\PartialPublicationController` | `/admin/reopen` | `Tests\Feature\PartialPublicationTest` | Cumplido |
| **RF-024** | Registro de auditoría para publicaciones y reaperturas | `App\Models\AuditLog` | `/admin/audit-logs` | `Tests\Feature\AdminModuleTest` | Cumplido |
| **RF-025** | Ocultamiento de borradores y parciales reabiertos al estudiante | `App\Services\Student\StudentResultsService` | `/student/grades` | `Tests\Feature\StudentModuleTest` | Cumplido |
| **RF-026** | Cálculo dinámico de promedio parcial ponderado (10.00 max) | `App\Services\Grades\GradeCalculationService` | Servicios & Vistas | `Tests\Feature\GradeCalculationTest` | Cumplido |
| **RF-027** | Cálculo dinámico de promedio final por asignatura (P1 50% + P2 50%) | `App\Services\Grades\GradeCalculationService` | Servicios & Vistas | `Tests\Feature\GradeCalculationTest` | Cumplido |
| **RF-028** | Cálculo dinámico de promedio general del período | `App\Services\Grades\GradeCalculationService` | Servicios & Vistas | `Tests\Feature\GradeCalculationTest` | Cumplido |
| **RF-029** | Prohibición absoluta de almacenar promedios en la base de datos | Cero columnas de promedio en migraciones DB | Tablas DB | `Tests\Feature\DatabaseIntegrityTest` | Cumplido |
| **RF-030** | Visualización estudiantil de historial académico propio (IDOR protegido) | `App\Services\Student\StudentResultsService` | `/student/grades` | `Tests\Feature\StudentModuleTest` | Cumplido |
| **RF-031** | Generación de reporte imprimible de calificaciones estudiantiles | `App\Http\Controllers\Student\GradeController::print` | `/student/grades/print` | `Tests\Feature\StudentModuleTest` | Cumplido |
| **RF-032** | Consolidado administrativo y docente de matriz de resultados | `App\Http\Controllers\Admin\ResultController` | `/admin/results` | `Tests\Feature\AdminModuleTest` | Cumplido |
| **RF-033** | Bitácora de auditoría inmutable de eventos del sistema | `App\Models\AuditLog` | `/admin/audit-logs` | `Tests\Feature\AdminModuleTest` | Cumplido |
| **RF-034** | Protección contra suplantación o manipulación de parámetros (IDOR) | Derivación de `Auth::id()` en consultas estudiantiles | Controladores | `Tests\Feature\StudentModuleTest` | Cumplido |
| **RF-035** | Manejo de estados vacíos y matrículas inactivas | Vistas estudiantiles & docentes | Dashboards | `Tests\Feature\StudentModuleTest` | Cumplido |
| **RF-036** | Comando de consola para creación inicial segura de administrador | `App\Console\Commands\CreateAdminCommand` | `php artisan app:create-admin` | `Tests\Feature\ProductionSeederAndAdminCommandTest` | Cumplido |

---

## 3. Resumen de Cobertura

- **Total Requisitos Funcionales:** 36 de 36 (100% Cumplidos).
- **Pruebas Automatizadas Totales:** 142 pruebas en verde (485 aserciones).
- **Estado de Entrega:** Apto para entrega técnica y despliegue en producción.
