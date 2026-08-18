# S03 — Requisitos funcionales

## Autenticación

- **RF-001:** iniciar sesión mediante correo y contraseña.
- **RF-002:** cerrar sesión invalidando la sesión activa.
- **RF-003:** recuperar y cambiar contraseña mediante el mecanismo seguro de Laravel.
- **RF-004:** redirigir al panel correspondiente al rol.
- **RF-005:** impedir el acceso de usuarios inactivos.

## Administración

- **RF-006:** crear, consultar, editar, activar y desactivar usuarios.
- **RF-007:** asignar un único rol a cada usuario.
- **RF-008:** crear, editar, activar y desactivar cursos.
- **RF-009:** crear, editar, activar y desactivar asignaturas.
- **RF-010:** crear períodos académicos y establecer uno como activo.
- **RF-011:** generar exactamente dos parciales por período.
- **RF-012:** matricular un estudiante en un curso durante un período.
- **RF-013:** asignar un docente a una combinación curso–asignatura–período.
- **RF-014:** evitar matrículas y asignaciones duplicadas.
- **RF-015:** consultar el estado global de configuración y resultados.

## Docente

- **RF-016:** listar únicamente sus asignaciones activas.
- **RF-017:** visualizar estudiantes matriculados en el curso asignado.
- **RF-018:** crear actividades dentro de un parcial con nombre, descripción, fecha y porcentaje.
- **RF-019:** editar o desactivar actividades mientras el parcial no esté publicado.
- **RF-020:** validar que los porcentajes de actividades no superen 100 %.
- **RF-021:** registrar o actualizar una nota de `0.00` a `10.00` y una observación por estudiante.
- **RF-022:** realizar registro individual y registro masivo desde una matriz de calificaciones.
- **RF-023:** visualizar el porcentaje configurado, pendiente y el promedio provisional.
- **RF-024:** publicar un parcial únicamente cuando la configuración y calificaciones estén completas.
- **RF-025:** consultar promedios de estudiantes de sus asignaciones.

## Estudiante

- **RF-026:** consultar sus asignaturas matriculadas.
- **RF-027:** consultar las actividades publicadas, porcentajes, notas y observaciones.
- **RF-028:** consultar el promedio del primer y segundo parcial cuando estén publicados.
- **RF-029:** consultar el promedio final cuando ambos parciales estén publicados.
- **RF-030:** consultar el promedio general del período cuando todas las asignaturas aplicables tengan resultado final publicado.
- **RF-031:** consultar resultados históricos por período.

## Sistema

- **RF-032:** calcular promedios según `S05` sin entrada manual.
- **RF-033:** mostrar estados vacío, incompleto, listo y publicado.
- **RF-034:** registrar auditoría de publicaciones, reaperturas y cambios de notas publicadas.
- **RF-035:** permitir filtrar y paginar listados administrativos.
- **RF-036:** exportar una vista imprimible de resultados del estudiante; no se exige PDF en la primera versión.
