# S02 — Roles y permisos

## Roles

Los valores autorizados son `admin`, `teacher` y `student`. Todo usuario debe tener exactamente un rol y un estado `active` o `inactive`.

## Matriz de permisos

| Acción | Administrador | Docente | Estudiante |
|---|:---:|:---:|:---:|
| Iniciar/cerrar sesión | Sí | Sí | Sí |
| Gestionar usuarios | Sí | No | No |
| Gestionar cursos y asignaturas | Sí | No | No |
| Gestionar períodos y parciales | Sí | No | No |
| Matricular estudiantes | Sí | No | No |
| Asignar docentes | Sí | No | No |
| Ver estructura académica | Sí | Solo asignada | Solo matriculada |
| Crear/editar actividades | Consulta | Solo asignadas | No |
| Registrar notas y observaciones | Consulta | Solo asignadas | No |
| Publicar resultados | Consulta/reapertura controlada | Solo asignadas | No |
| Ver resultados individuales | Todos | Solo alumnos asignados | Solo propios |
| Ver promedios | Todos | Solo asignados | Solo propios publicados |
| Editar perfil propio/contraseña | Sí | Sí | Sí |

## Autorización de docente

Para modificar una actividad o calificación se debe demostrar en servidor que:

1. el usuario autenticado tiene rol `teacher` y está activo;
2. existe una `teaching_assignment` activa a su nombre;
3. la actividad pertenece exactamente a esa asignación;
4. el estudiante está matriculado activamente en el mismo curso y período;
5. el resultado no está bloqueado por publicación, salvo flujo explícito de reapertura.

## Autorización de estudiante

El identificador del estudiante se obtiene del usuario autenticado. No se admite escoger otro `student_id` en rutas o formularios para consultar resultados. Solo se muestran actividades y calificaciones marcadas como publicadas.

## Administrador

El administrador tiene alcance institucional, pero no debe modificar directamente notas desde formularios ordinarios. Puede consultar resultados y reabrir un parcial publicado dejando motivo y registro de auditoría.

## Implementación obligatoria

- Middleware de autenticación y rol.
- Policies para recursos académicos.
- Consultas acotadas por asignación o matrícula.
- Respuesta `403` para autenticados no autorizados y redirección segura para invitados.
- Menú y dashboard adaptados al rol; ocultar un enlace no reemplaza la autorización de servidor.

