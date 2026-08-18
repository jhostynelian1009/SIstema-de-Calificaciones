# K-011 — Módulo estudiante

## Objetivo

Construir una consulta clara y estrictamente privada de notas, observaciones y promedios publicados.

## Especificaciones

Leer RF-026 a RF-031 y RF-036 de `S03`, `S02`, RN-020A a RN-025 de `S05`, CU-08 de `S07`, interfaz de `S08` y CA-020 a CA-023A de `S10`.

## Instrucciones

1. Deriva siempre al estudiante desde `auth()->user()`; no aceptes otro estudiante como parámetro de consulta.
2. Crea dashboard con matrícula actual, curso, período y asignaturas disponibles.
3. Permite seleccionar períodos históricos donde tenga matrícula.
4. Muestra únicamente publicaciones `published` de la asignación correspondiente.
5. Para cada parcial visible muestra actividad, porcentaje, nota y observación.
6. Muestra promedio parcial oficial, promedio final por asignatura y promedio general del período solo cuando corresponda.
7. Si un parcial no está publicado, muestra “Resultados aún no publicados” sin revelar borradores.
8. Añade vista imprimible limpia; no conviertas la exportación PDF en requisito.
9. Incluye estados vacíos, mensajes claros y presentación responsive.
10. Verifica formato de dos decimales y consistencia con el servicio de cálculo.

## Pruebas

- Estudiante ve solo matrícula y resultados propios.
- No ve parciales draft, ready o reopened.
- P1 publicado aparece sin inventar promedio final.
- P1 y P2 publicados muestran promedio final correcto.
- Un intento de acceso a datos ajenos no filtra existencia ni contenido.
- Historial por período funciona.

## Criterio de salida

El estudiante obtiene sus calificaciones y retroalimentación sin poder modificar datos ni consultar información ajena.
