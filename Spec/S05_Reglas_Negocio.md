# S05 — Reglas de negocio

## Estructura académica

- **RN-001:** un período académico contiene exactamente dos parciales: `P1` y `P2`.
- **RN-002:** cada parcial representa el 50 % del promedio final.
- **RN-003:** un estudiante puede tener una sola matrícula activa por período.
- **RN-004:** una asignación docente identifica de forma única la combinación curso, asignatura y período académico; cada combinación tiene un único docente responsable.
- **RN-005:** solo usuarios activos participan en nuevas asignaciones o matrículas.

## Actividades y porcentajes

- **RN-006:** cada actividad pertenece a una asignación docente y a un parcial del mismo período.
- **RN-007:** el porcentaje de una actividad debe ser mayor que 0 y menor o igual que 100.
- **RN-008:** la suma de actividades activas por asignación y parcial no puede superar 100 %.
- **RN-009:** para publicar un parcial la suma debe ser exactamente 100 %.
- **RN-010:** una actividad desactivada no participa en cálculos; no puede desactivarse tras publicar sin reapertura.

## Calificaciones

- **RN-011:** una nota es decimal entre `0.00` y `10.00`, ambos incluidos.
- **RN-012:** cada pareja actividad–estudiante tiene como máximo una calificación.
- **RN-013:** toda nota registrada requiere una observación de 3 a 500 caracteres.
- **RN-014:** solo se califica a estudiantes con matrícula activa en el curso y período de la asignación.
- **RN-015:** no se admite una nota vacía al publicar; un caso no evaluado debe resolverse antes según la política institucional, sin convertirlo automáticamente en cero.

## Cálculos

Sea `nᵢ` la nota y `wᵢ` el porcentaje de cada actividad activa:

```text
promedio_parcial = Σ(nᵢ × wᵢ / 100)
promedio_final   = (P1 × 0.50) + (P2 × 0.50)
promedio_general = Σ(promedio_final_asignatura) / total_asignaturas_aplicables
```

- **RN-016:** el cálculo interno conserva precisión decimal suficiente.
- **RN-017:** el resultado visible se redondea a dos decimales con criterio `half up`.
- **RN-018:** el promedio parcial oficial existe cuando el parcial está publicado.
- **RN-019:** el promedio final oficial existe cuando P1 y P2 están publicados.
- **RN-020:** no se permite escribir manualmente el promedio parcial o final.
- **RN-020A:** el promedio general del período es la media aritmética de los promedios finales de todas las asignaciones activas aplicables al curso del estudiante.
- **RN-020B:** el promedio general oficial solo se muestra cuando todas esas asignaturas tienen P1 y P2 publicados; no se promedian resultados incompletos.

Ejemplo: si `P1 = 8.05` y `P2 = 9.10`, el promedio final es `8.58`.

## Publicación y reapertura

- **RN-021:** el estado persistido de un parcial por asignación en `partial_publications.status` se limita a `draft` (borrador), `published` (publicado) y `reopened` (reabierto). Los estados de preparación (vacío, incompleto, listo) son calculados dinámicamente y no se persisten en base de datos.
- **RN-022:** la publicación exige 100 % de ponderación y una calificación completa para cada actividad activa y estudiante activo matriculado.
- **RN-023:** el docente no modifica un parcial publicado.
- **RN-024:** el administrador puede reabrirlo indicando un motivo; la acción y cambios posteriores quedan auditados.
- **RN-025:** un estudiante nunca ve borradores, promedios provisionales ni resultados de terceros.
- **RN-026:** para publicar se consideran los estudiantes con matrícula activa en el momento de la validación de preparación.
- **RN-027:** una calificación o parcial ya publicado se conserva de forma íntegra como historial aunque posteriormente se desactive la matrícula del estudiante.
- **RN-028:** un estudiante con matrícula histórica conserva el derecho de consultar los resultados oficiales que fueron publicados durante su período lectivo correspondiente.
- **RN-029:** la desactivación de un estudiante o matrícula no elimina calificaciones registradas ni altera los resultados oficiales ya publicados.
- **RN-030:** una reapertura administrativa oculta temporalmente el resultado como oficial hasta que el docente realice las correcciones y vuelva a republicar. Los resultados se calculan en tiempo real sin almacenar tablas ni snapshots de promedios.
