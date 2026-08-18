# K-007 — Calificaciones y observaciones

## Objetivo

Registrar notas de 0 a 10 y observaciones obligatorias, individualmente o en matriz, solo para estudiantes autorizados.

## Especificaciones

Leer RF-021 a RF-023 de `S03`, RN-011 a RN-015 de `S05`, `S06`, CU-06 de `S07` y CA-011 a CA-014 de `S10`.

## Instrucciones

1. Crea `Grade` con `score decimal(4,2)`, observación, autor y fecha de calificación.
2. Declara unicidad actividad–estudiante y relaciones necesarias.
3. Implementa Form Requests para guardado individual y masivo.
4. Valida score `0.00..10.00` y observación de 3 a 500 caracteres.
5. Antes de guardar, prueba que actividad, asignación, parcial, matrícula y usuario autenticado son coherentes.
6. Implementa `updateOrCreate` controlado dentro de transacción, sin aceptar propietario ni `graded_by` desde el navegador.
7. En la matriz docente, lista solo alumnos activos del curso/período y conserva errores por fila.
8. Define una estrategia atómica para el guardado masivo: si una fila es inválida, no confirmar una mezcla silenciosa; devuelve errores claros.
9. Bloquea cambios en publicación `published`.
10. Audita toda modificación posterior a una reapertura, almacenando valor anterior y nuevo.

## Pruebas

- Límites 0 y 10 aceptados; fuera de rango rechazado.
- Observación inválida rechazada.
- Duplicado actualiza sin crear segunda fila.
- Estudiante ajeno al curso no puede ser calificado.
- Docente ajeno y parcial publicado reciben `403`.
- Fallo masivo no deja cambios parciales.

## Criterio de salida

El docente registra notas y retroalimentación de sus alumnos con integridad, autorización y trazabilidad.

