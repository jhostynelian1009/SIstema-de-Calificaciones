# K-004 — Matrículas y asignaciones docentes

## Objetivo

Relacionar estudiantes con cursos y docentes con curso–asignatura–período bajo restricciones de unicidad y autorización.

## Especificaciones

Leer RF-012 a RF-017 de `S03`, `S02`, `S05`, `S06`, CU-03/CU-04 de `S07` y CA-006/CA-007/CA-009 de `S10`.

## Instrucciones

1. Crea `Enrollment` y `TeachingAssignment` con migraciones, relaciones e índices compuestos.
2. Restringe matrícula a usuarios `student` activos y asignación a usuarios `teacher` activos.
3. Garantiza una sola matrícula por estudiante y período.
4. Garantiza unicidad por curso–asignatura–período (un solo docente responsable por combinación).
5. Implementa CRUD administrativo con filtros por período, curso, asignatura, docente y estado.
6. Antes de desactivar, muestra el impacto; conserva registros históricos.
7. Implementa scope `assignedTo($teacher)` y Policy para impedir acceso horizontal.
8. Muestra al docente solo asignaciones activas y estudiantes con matrícula activa del curso/período.
9. Evita N+1 en listados y pagina donde corresponda.

## Pruebas

- Roles incompatibles son rechazados.
- Duplicados son rechazados por validación y base.
- Docente A no consulta asignación de docente B.
- La lista docente contiene solo estudiantes del curso/período correcto.
- Una matrícula histórica permanece consultable al desactivarse.

## Criterio de salida

El administrador puede completar asignaciones y el docente ve únicamente sus cursos, asignaturas y alumnos autorizados.

