# K-008 — Promedios, publicación y reapertura

## Objetivo

Calcular resultados oficiales, validar integridad, publicar parciales y controlar reaperturas.

## Especificaciones

Leer RF-024, RF-025, RF-030 y RF-032 a RF-034 de `S03`, RN-016 a RN-025 de `S05`, `S09`, CU-07/CU-09 de `S07` y CA-015 a CA-023A de `S10`.

## Instrucciones

1. Implementa `GradeCalculationService` con aritmética decimal determinista y redondeo half up a dos decimales.
2. Calcula promedio parcial como suma ponderada, final como P1×0.50 + P2×0.50 y general como media de resultados finales aplicables.
3. Implementa `PartialReadinessService` que entregue total porcentual, actividades sin completar y alumnos/notas pendientes.
4. Implementa `PartialPublicationService` transaccional y autorizado.
5. Publica solo con 100.00 % y todas las calificaciones/observaciones completas.
6. Al publicar registra actor/fecha, cambia estado y bloquea recursos relacionados.
7. Implementa reapertura exclusiva de administrador con motivo obligatorio; registra auditoría y oculta el resultado como oficial hasta republicación.
8. No aceptes promedios enviados por formularios ni los guardes como verdad primaria.
9. Prepara métodos de consulta reutilizables por docente y estudiante.

## Pruebas

- Ejemplos exactos de CA-015 y CA-016.
- Diferentes órdenes de actividades producen el mismo resultado.
- 99.99, 100.01 o nota faltante impiden publicación.
- Una publicación válida bloquea edición.
- Reapertura exige admin y motivo, y queda auditada.
- El promedio final no existe con un parcial pendiente.
- El promedio general no existe mientras una asignatura aplicable esté incompleta.

## Criterio de salida

La publicación es atómica, los cálculos son reproducibles y ningún resultado incompleto llega al estudiante.
