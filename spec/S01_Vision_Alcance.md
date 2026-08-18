# S01 — Visión y alcance

## Visión

El **Sistema de Calificaciones** centraliza el registro y la consulta de resultados académicos finales. Permite conocer cada actividad evaluada, su porcentaje, la nota obtenida, la observación del docente y los promedios calculados de forma transparente.

## Problema

Los estudiantes necesitan consultar sus resultados y retroalimentación sin depender de comunicaciones informales. Los docentes requieren un espacio controlado para registrar notas únicamente en los cursos y asignaturas autorizados. La administración necesita configurar la estructura académica y garantizar que cada usuario acceda solo a lo correspondiente.

## Actores

- **Administrador:** configura usuarios, cursos, asignaturas, períodos, matrículas y asignaciones docentes.
- **Docente:** administra actividades y calificaciones de sus asignaciones autorizadas.
- **Estudiante:** consulta únicamente sus resultados publicados.

## Alcance incluido

- Login, cierre de sesión, cambio y recuperación segura de contraseña.
- Administración de usuarios con roles.
- Gestión de cursos, paralelos representados por el código del curso y asignaturas.
- Gestión de períodos académicos con dos parciales.
- Matrícula de estudiantes en un curso por período.
- Asignación de docentes por curso, asignatura y período.
- Registro de actividades con porcentajes por parcial.
- Registro de nota y observación por estudiante y actividad.
- Publicación controlada de resultados.
- Cálculo de promedio de parcial y promedio final.
- Paneles y vistas específicas por rol.
- Historial académico consultable y auditoría básica.

## Fuera de alcance

- Entrega, recepción o almacenamiento de tareas y archivos.
- Clases virtuales, videoconferencias, chat o foros.
- Asistencia, conducta, matrículas financieras o pagos.
- Aplicación móvil nativa.
- Integraciones con sistemas externos en la primera versión.
- Edición o carga de notas por estudiantes.

## Éxito del producto

- Ningún docente puede acceder a asignaciones ajenas.
- Ningún estudiante puede consultar resultados de otro.
- Los promedios coinciden exactamente con las fórmulas definidas.
- El administrador puede configurar un período completo sin modificar la base manualmente.
- Los tres roles completan su flujo principal desde una interfaz responsive.

