# K-005 — Estados de períodos y parciales por asignación docente

## Objetivo

Consolidar el ciclo académico de dos parciales y preparar estados de trabajo por asignación docente diferenciando estados persistidos y calculados.

## Especificaciones

Leer `S05`, `S06`, `S07`, `S08` y criterios de publicación de `S10`.

## Instrucciones

1. Define estados persistidos de publicación (`draft`, `published`, `reopened`) controlados por `App\Enums\PublicationStatus`. Los estados calculados de preparación (vacío, incompleto, listo) no se persisten en base de datos.
2. Crea `partial_publications` con unicidad asignación–parcial (`teaching_assignment_id`, `partial_id`) y campos de publicación/reapertura.
3. Genera o recupera automáticamente los dos registros de estado (`draft`) al crear o procesar asignaciones docentes.
4. Verifica que el parcial y la asignación pertenezcan estrictamente al mismo período académico.
5. Implementa la vista docente para mostrar P1 y P2 (peso 50 % cada uno) y su estado en español (Borrador).
6. Implementa vista administrativa de estados de parciales por curso/asignatura/docente/período.
7. No habilites aún la publicación ni la reapertura definitiva; prepara servicios (`PartialPublicationStateService`, `AuditService`) y `PartialPublicationPolicy`.
8. Audita eventos mediante la tabla inmutable `audit_logs`.

## Pruebas

- Solo existen dos parciales por período (P1 y P2 con peso 50.00).
- No se relaciona una asignación con un parcial de otro período.
- La inicialización de estados es idempotente.
- Docente y administrador ven únicamente la información y acciones permitidas.

## Criterio de salida

Cada asignación posee un ciclo independiente de estados persistidos para P1 y P2 (12 registros demo en `draft`).
