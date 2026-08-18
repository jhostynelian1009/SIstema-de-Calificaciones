# K-009 — Módulo administrador

## Objetivo

Completar un panel administrativo central para configurar y supervisar el sistema desde la interfaz.

## Especificaciones

Leer todos los requisitos administrativos de `S03`, matriz de `S02`, pantallas de `S08` y `S10`.

## Instrucciones

1. Integra en `/admin` usuarios, cursos, asignaturas, períodos, matrículas y asignaciones docentes.
2. Construye dashboard con período activo, conteos, configuraciones pendientes y estados de publicación.
3. Añade filtros, búsqueda, paginación y confirmaciones para acciones sensibles.
4. Implementa creación/edición de usuarios sin exponer hashes; valida email y rol.
5. Prefiere activar/desactivar sobre borrado cuando existan relaciones.
6. Implementa consulta de resultados por período, curso, asignatura y estudiante.
7. Añade pantalla de reapertura con motivo y advertencia del efecto.
8. Añade auditoría paginada para publicación, reapertura y cambios posteriores.
9. Usa componentes Blade y mensajes en español consistentes.
10. Revisa accesibilidad, errores de formulario, estados vacíos y responsive.

## Pruebas

- Todas las rutas administrativas deniegan teacher/student.
- CRUD y filtros principales funcionan.
- No se elimina historial por acciones administrativas.
- Reapertura registra actor, motivo y fecha.
- Dashboard no genera N+1 apreciable.

## Criterio de salida

El administrador prepara y supervisa un período completo sin comandos ni edición manual de la base.

