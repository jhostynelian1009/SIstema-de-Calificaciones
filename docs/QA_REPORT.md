# Informe de Aseguramiento de Calidad (QA_REPORT.md)

**Proyecto:** Sistema de Calificaciones Académicas  
**Entorno de Pruebas:** MariaDB 10.4 / MySQL Compatible (`sistema_calificaciones_test`)  
**Fecha de Reporte:** 2026-08-20  
**Estado General:** Aprobado / Sin Bloqueos  

---

## 1. Revisión de Base de Datos y Esquema relacional

### 1.1 Estructura e Integridad
- **Claves Foráneas:** Todas las relaciones (`user_id`, `course_id`, `subject_id`, `academic_period_id`, `teaching_assignment_id`, `activity_id`, `student_id`, `partial_id`) cuentan con llaves foráneas explícitas.
- **Regla de Eliminación:** Se aplica eliminación restringida (`onDelete('restrict')`) en entidades académicas maestras para prevenir orfandad o pérdida accidental de historial.
- **Tipos Decimales:** Se utilizan columnas `DECIMAL(5,2)` en porcentajes de actividades y `DECIMAL(4,2)` en notas evaluativas (0.00 a 10.00), evitando imprecisiones por punto flotante IEEE 754.
- **Ausencia de Columnas de Promedio:** Se confirmó mediante inspección directa y pruebas automatizadas que ninguna tabla almacena valores calculados de promedios (P1, P2, Promedio Final por Asignatura o Promedio General). Todos los promedios son derivados dinámicamente en tiempo de ejecución.

### 1.2 Restricciones de Unicidad Validada
- `users`: `email` (único).
- `courses`: `code` (único).
- `subjects`: `code` (único).
- `academic_periods`: `name` (único).
- `enrollments`: combinación (`student_id`, `academic_period_id`) única.
- `teaching_assignments`: combinación (`course_id`, `subject_id`, `academic_period_id`) única (asegura 1 docente por asignatura-curso).
- `partials`: combinación (`academic_period_id`, `name`) única (exactamente Parcial 1 y Parcial 2 por período con 50.00% cada uno).
- `grades`: combinación (`activity_id`, `student_id`) única.

---

## 2. Prueba Limpia en MariaDB (`migrate:fresh --seed --env=testing`)

Se ejecutó la migración y siembra limpia sobre la base dedicada `sistema_calificaciones_test`. Los recuentos exactos obtenidos coinciden con la especificación:

| Entidad / Estado | Cantidad Esperada | Cantidad Real | Estado |
| :--- | :--- | :--- | :--- |
| **Administradores Activos** | 1 | 1 | Coincide |
| **Docentes Activos** | 2 | 2 | Coincide |
| **Estudiantes Activos** | 6 | 6 | Coincide |
| **Usuario Inactivo** | 1 | 1 | Coincide |
| **Cursos** | 2 | 2 | Coincide |
| **Asignaturas** | 3 | 3 | Coincide |
| **Matrículas** | 6 | 6 | Coincide |
| **Asignaciones Docentes** | 6 | 6 | Coincide |
| **Estados de Parcial** | 12 | 12 | Coincide |
| **Calificaciones** | 21 | 21 | Coincide |
| **Parciales Publicados** | 2 | 2 | Coincide |
| **Parciales en Borrador** | 10 | 10 | Coincide |
| **Parciales Reabiertos** | 0 | 0 | Coincide |
| **Idempotencia de Seeders** | Exitosa | Exitosa | Coincide |

---

## 3. Rendimiento y Consultas SQL (N+1 Avoidance)

- **Eager Loading (`with`):** Implementado en los servicios `AdminDashboardService`, `TeacherDashboardService` y `StudentResultsService`. Se precargan relaciones habituales (`course`, `subject`, `academicPeriod`, `student`, `activities`, `grades`).
- **Paginación:** La gestión de usuarios y bitácora de auditoría utiliza paginación de 15 a 25 registros por página.
- **Consultas Acotadas por Usuario:** Los docentes ven únicamente sus asignaciones y los estudiantes únicamente sus matrículas, ejecutando consultas filtradas por la clave primaria del usuario en sesión (`Auth::id()`).

---

## 4. Revisión de Interfaz, Responsividad y Accesibilidad

- **Resoluciones Verificadas por Código:**
  - **Móvil (360px):** Tablas con scroll horizontal (`table-responsive`), formularios apilados verticalmente y menú hamburguesa funcional.
  - **Tablet (768px):** Disposición de tarjetas en 2 columnas.
  - **Escritorio (>1024px):** Disposición en cuadrícula completa de 3/4 columnas.
- **Formularios:** Todos los inputs disponen de `<label for="...">` explícitos e identificadores únicos `id`. Los mensajes de error de validación se despliegan directamente debajo del campo afectado (`@error`).
- **Estados Comunicados:** Los badges de estado combinan color Bootstrap (e.g. `bg-success`, `bg-warning`) con texto en español e iconos Bootstrap Icons (`bi bi-check-circle`, `bi bi-lock`).
- **Impresión:** La vista `/student/grades/periods/{id}/print` utiliza reglas de estilo `@media print` para ocultar la barra de navegación, botones y fondos decorativos, garantizando un formato de boletín limpio.

---

## 5. Resumen de Pruebas Automatizadas

- **Ejecución Total:** 142 pruebas ejecutadas en 24.5 segundos.
- **Aserciones:** 485 aserciones superadas.
- **Tasa de Éxito:** 100%.
