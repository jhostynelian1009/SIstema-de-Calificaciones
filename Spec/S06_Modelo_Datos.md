# S06 — Modelo de datos

## Entidades principales

| Tabla | Campos esenciales | Restricciones destacadas |
|---|---|---|
| `users` | id, name, email, password, role, active | email único; role enumerado |
| `courses` | id, name, code, description, active | code único |
| `subjects` | id, name, code, description, active | code único |
| `academic_periods` | id, name, starts_at, ends_at, active | fechas válidas; máximo uno activo |
| `partials` | id, academic_period_id, number, name, weight | único período+número; número 1 o 2; weight 50.00 |
| `enrollments` | id, student_id, course_id, academic_period_id, active | único estudiante+período |
| `teaching_assignments` | id, teacher_id, course_id, subject_id, academic_period_id, active | único curso+asignatura+período |
| `activities` | id, teaching_assignment_id, partial_id, name, description, due_date, percentage, active | percentage decimal(5,2) |
| `grades` | id, activity_id, student_id, score, observation, graded_by, graded_at | único actividad+estudiante; score decimal(4,2) |
| `partial_publications` | id, teaching_assignment_id, partial_id, status, published_by, published_at, reopened_by, reopened_at, reopen_reason | único asignación+parcial; status enum (draft, published, reopened) |
| `audit_logs` | id, user_id, action, auditable_type, auditable_id, old_values, new_values, ip_address, user_agent, created_at | JSON para valores |

## Relaciones

- `User(student)` tiene muchas `Enrollment`.
- `User(teacher)` tiene muchas `TeachingAssignment`.
- `Course` y `Subject` participan en muchas asignaciones docentes.
- `AcademicPeriod` tiene dos `Partial`, muchas matrículas y asignaciones.
- `TeachingAssignment` tiene muchas `Activity` y dos estados de publicación.
- `Activity` tiene muchas `Grade`.
- `Grade` pertenece al estudiante calificado y al docente que registró el dato.

## Decisiones de integridad

- Usar `foreignId()->constrained()` e índices para columnas de filtrado frecuente.
- `courses`, `subjects`, `activities` y relaciones académicas conservan historial mediante `active`; puede añadirse `softDeletes` donde la eliminación administrativa sea necesaria.
- La publicación se modela por **asignación docente + parcial**, no globalmente por parcial.
- La publicación se modela en `partial_publications` por asignación docente.
- `partial_publications.status` almacena únicamente los estados persistidos (`draft`, `published`, `reopened`). Los estados de preparación se calculan en runtime.
- No crear columnas `partial_average` ni `final_average` como fuente primaria.

## Índices mínimos

- `enrollments(student_id, academic_period_id)` único.
- `enrollments(course_id, academic_period_id, active)` de consulta.
- `teaching_assignments(course_id, subject_id, academic_period_id)` único.
- `activities(teaching_assignment_id, partial_id, active)`.
- `grades(activity_id, student_id)` único.
- `partial_publications(teaching_assignment_id, partial_id)` único.
- `audit_logs(auditable_type, auditable_id, created_at)`.

## Eliminación

No aplicar borrado en cascada sobre calificaciones o publicaciones en producción. Una entidad referenciada se desactiva o se elimina lógicamente para conservar trazabilidad.
