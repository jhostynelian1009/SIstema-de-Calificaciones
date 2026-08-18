# Sistema de Calificaciones — Spec as a Skill

Paquete de especificaciones e instrucciones de implementación para construir el sistema en **Laravel 12, PHP 8.2+, MySQL 8, Blade y Bootstrap 5**.

## Propósito

El sistema permite que:

- el administrador gestione la estructura académica, usuarios, matrículas y asignaciones docentes;
- el docente registre actividades, porcentajes, calificaciones y observaciones únicamente en sus asignaciones autorizadas;
- el estudiante consulte sus notas publicadas, observaciones, promedios parciales y promedio final;
- el sistema calcule dos parciales, cada uno con un peso del 50 % de la nota final.

No incluye entrega de tareas, archivos, aulas virtuales ni edición de notas por parte del estudiante.

## Instalación dentro del proyecto

1. Descomprimir este paquete en la raíz del repositorio Laravel.
2. Conservar `PromptMaster.md`, `Spec/` y `Skill/` al mismo nivel que `artisan`.
3. Leer primero `PromptMaster.md`.
4. Ejecutar las instrucciones de `Skill/` en orden, desde `K-001` hasta `K-012`.
5. No avanzar de una skill mientras sus criterios de salida no se cumplan.

## Estructura

```text
Sistema-de-Calificaciones/
├── artisan
├── app/
├── database/
├── resources/
├── routes/
├── PromptMaster.md
├── Spec/
│   ├── INDEX.md
│   └── S01_...S12_....md
└── Skill/
    ├── INDEX.md
    └── K-001_...K-012_....md
```

## Regla de cálculo

Dentro de cada parcial, las actividades de una asignatura deben sumar exactamente 100 %.

```text
promedio_parcial = Σ(nota_actividad × porcentaje_actividad / 100)
promedio_final   = (promedio_parcial_1 × 0.50) + (promedio_parcial_2 × 0.50)
```

Las notas aceptadas están entre `0.00` y `10.00` y se muestran con dos decimales.

