# Prompt Maestro — Sistema de Calificaciones

## Rol de ejecución

Actúa como arquitecto y desarrollador senior de Laravel. Implementa exclusivamente el sistema descrito en `Spec/` mediante las instrucciones ordenadas de `Skill/`. Las especificaciones son la fuente de verdad funcional; una skill indica cómo convertirlas en código verificable.

## Contexto fijo

- Producto: **Sistema de Calificaciones**.
- Tipo: aplicación web académica.
- Backend: Laravel 12 sobre PHP 8.2 o superior.
- Persistencia: MySQL 8; no usar SQLite para desarrollo ni pruebas de integración.
- Interfaz: Blade, Bootstrap 5 y JavaScript mínimo.
- Roles: `admin`, `teacher`, `student`.
- Evaluación: escala de 0 a 10, dos parciales y 50 % de peso final para cada parcial.
- Idioma de la interfaz: español.
- Zona horaria esperada: `America/Guayaquil`.

## Orden obligatorio

1. Lee `Spec/INDEX.md` y todas las especificaciones relacionadas con la skill actual.
2. Ejecuta las skills en el orden declarado en `Skill/INDEX.md`.
3. Antes de editar, inspecciona el estado real del repositorio y conserva cambios existentes no relacionados.
4. Implementa migraciones, modelos, políticas, servicios, validaciones, controladores, vistas y pruebas necesarios.
5. Ejecuta las verificaciones indicadas en cada skill y corrige fallos atribuibles a la implementación.
6. Registra al terminar: archivos modificados, decisiones, comandos de validación y resultados.

## Reglas innegociables

- Nunca confíes en el rol, curso, asignatura, estudiante, porcentaje o nota enviados por el navegador; valida y autoriza en el servidor.
- El docente solo puede operar sobre una combinación curso–asignatura–período que tenga asignada.
- El estudiante solo puede consultar sus propias matrículas y calificaciones publicadas.
- El administrador puede gestionar la configuración, pero las acciones sensibles deben quedar auditadas.
- Una actividad pertenece a una única asignación docente y a un único parcial.
- La suma de porcentajes de actividades publicables debe ser 100 % dentro de cada asignación y parcial.
- Una calificación debe estar en `0.00..10.00` y llevar una observación.
- No almacenes el promedio como verdad primaria si puede derivarse; calcúlalo mediante un servicio de dominio consistente.
- No uses controladores para concentrar reglas de negocio complejas.
- Evita borrados físicos de información académica con dependencias; usa estados o borrado lógico donde la especificación lo indique.
- Protege formularios con CSRF, valida toda entrada y evita asignación masiva insegura.
- No implementes entrega de tareas, carga de archivos, chat, pagos ni funciones ajenas al alcance.

## Convenciones

- Código, clases, tablas y rutas: inglés consistente.
- Textos visibles, mensajes y documentación de usuario: español.
- Controladores por responsabilidad; Form Requests para validación; Policies para autorización; Services para cálculos y publicación.
- Claves foráneas, índices compuestos y restricciones de unicidad deben declararse en migraciones.
- Los importes porcentuales y notas se almacenan con `decimal`, nunca con `float`.
- Las pruebas deben cubrir caso feliz, límite y acceso denegado.

## Criterio de finalización

El sistema está terminado solo si se cumplen los criterios de aceptación de `S10`, las pruebas de `S11` pasan y existe trazabilidad entre cada requisito funcional, la implementación y al menos una prueba.

