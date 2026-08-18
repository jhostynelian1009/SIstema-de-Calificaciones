# K-006 — Actividades y porcentajes

## Objetivo

Permitir que el docente configure las actividades evaluadas de cada parcial sin exceder 100 %.

## Especificaciones

Leer RF-018 a RF-020 de `S03`, RN-006 a RN-010 de `S05`, `S06`, CU-05 de `S07` y CA-010/CA-018 de `S10`.

## Instrucciones

1. Crea modelo, migración, factory y Policy de `Activity`.
2. Incluye nombre, descripción opcional, fecha, porcentaje decimal y estado activo.
3. Deriva `teaching_assignment_id` y valida `partial_id` en servidor.
4. Implementa un servicio transaccional que calcule total actual, nuevo total y porcentaje restante.
5. Rechaza cualquier creación/edición que supere 100.00 %.
6. Permite valores decimales y muestra siempre total utilizado y restante.
7. Bloquea crear, editar o desactivar cuando la publicación esté `published`.
8. Implementa CRUD docente acotado por Policy; admin dispone de consulta, no de edición ordinaria.
9. Audita cambios realizados después de una reapertura.

## Pruebas

- Porcentaje 0 o negativo se rechaza.
- Total 100.00 se admite; 100.01 se rechaza.
- Editar considera el porcentaje anterior sin contarlo dos veces.
- Docente ajeno recibe `403`.
- Un parcial publicado permanece inmutable.

## Criterio de salida

El docente configura actividades válidas y entiende visualmente si el parcial está incompleto o alcanza 100 %.

