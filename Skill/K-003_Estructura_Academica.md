# K-003 — Estructura académica

## Objetivo

Crear cursos, asignaturas y su gestión administrativa preservando historial.

## Especificaciones

Leer RF-006 a RF-011 de `S03`, `S05`, `S06`, `S09` y CA-005/CA-008 de `S10`.

## Instrucciones

1. Crea modelos, migraciones, factories y seeders para `Course`, `Subject`, `AcademicPeriod` y `Partial`.
2. Define códigos únicos para cursos y asignaturas, campos descriptivos y estado activo.
3. Al crear un período, genera P1 y P2 en una transacción, ambos con peso `50.00`.
4. Impide más de un período activo mediante servicio y transacción; no confíes solo en la interfaz.
5. Valida que fecha final sea posterior a fecha inicial.
6. Implementa CRUD administrativo con Form Requests, Policies, filtros y paginación.
7. Desactiva recursos con historial; no borres en cascada datos académicos.
8. Muestra claramente el período activo y los dos parciales.

## Pruebas

- Solo admin gestiona recursos.
- Códigos duplicados se rechazan.
- Un período genera exactamente dos parciales 50/50.
- Al activar uno, el anterior queda inactivo de manera consistente.
- Fechas inválidas se rechazan.
- Un recurso referenciado no destruye historial al desactivarse.

## Criterio de salida

El administrador configura desde UI cursos, asignaturas y un período con P1/P2 sin tocar la base manualmente.

