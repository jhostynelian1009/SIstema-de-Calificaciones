# K-005 — Estados de períodos y parciales

## Objetivo

Consolidar el ciclo académico de dos parciales y preparar estados de trabajo por asignación docente.

## Especificaciones

Leer `S05`, `S06`, `S07`, `S08` y criterios de publicación de `S10`.

## Instrucciones

1. Define estados controlados para publicaciones: `draft`, `ready`, `published`, `reopened`.
2. Crea `partial_publications` con unicidad asignación–parcial y campos de publicación/reapertura.
3. Genera o recupera los dos registros de estado al acceder a una asignación, sin duplicarlos.
4. Verifica que parcial y asignación pertenezcan al mismo período.
5. Implementa vistas del docente con P1 y P2, peso 50 %, estado, progreso y acciones disponibles.
6. Implementa vista administrativa de estados por curso/asignatura/docente.
7. No habilites aún la publicación definitiva; prepara servicios y Policies para K-008.
8. Audita cambios de estado sensibles desde el inicio.

## Pruebas

- Solo existen dos parciales por período.
- No se relaciona una asignación con un parcial de otro período.
- La inicialización de estados es idempotente.
- Docente y administrador ven únicamente las acciones permitidas.

## Criterio de salida

Cada asignación posee un ciclo independiente para P1 y P2 y puede progresar sin afectar otras asignaturas.

