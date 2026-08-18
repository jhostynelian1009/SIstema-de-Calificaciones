# K-010 — Módulo docente

## Objetivo

Completar el flujo del docente desde sus asignaciones hasta la publicación de resultados.

## Especificaciones

Leer las funciones docentes de `S02`, `S03`, casos CU-05 a CU-07 de `S07`, pantallas de `S08` y criterios de `S10`.

## Instrucciones

1. Crea dashboard con tarjetas por curso/asignatura/período y estado de P1/P2.
2. En cada asignación muestra estudiantes, actividades, porcentaje usado/restante, notas pendientes y promedio provisional.
3. Integra CRUD de actividades y matriz de calificaciones con navegación clara.
4. Ofrece guardado individual y masivo sin debilitar validaciones.
5. Muestra lista concreta de pendientes antes de publicar.
6. Añade confirmación de publicación indicando que se bloquearán ediciones.
7. Una publicación reabierta debe indicar el estado y que requiere republicación.
8. No muestres controles de otras asignaciones ni identificadores manipulables innecesarios.
9. Optimiza carga de estudiantes, actividades y calificaciones para evitar N+1.
10. Verifica uso cómodo en portátil y dispositivo móvil.

## Pruebas

- Docente solo ve y opera sus asignaciones.
- Flujos completos de P1 y P2.
- Matriz muestra exactamente estudiantes matriculados activos.
- Pendientes y porcentajes coinciden con servicios.
- UI y backend bloquean edición publicada.
- Manipulación de IDs recibe `403` o validación segura.

## Criterio de salida

Un docente demo puede configurar, calificar y publicar ambos parciales sin intervención técnica.

