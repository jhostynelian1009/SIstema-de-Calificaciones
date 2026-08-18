# S12 — Entrega e implementación

## Fases

1. Entorno Laravel, MySQL, Bootstrap y autenticación.
2. Roles, middleware y Policies.
3. Estructura académica, matrículas y asignaciones.
4. Actividades, porcentajes, notas y observaciones.
5. Cálculos, publicación y auditoría.
6. Panel administrador.
7. Panel docente.
8. Panel estudiante.
9. Pruebas, seguridad, datos demo y documentación.

Las fases se ejecutan mediante `Skill/K-001` a `Skill/K-012`.

## Datos demostrativos

El seeder idempotente debe crear, como mínimo:

- 1 administrador.
- 2 docentes.
- 6 estudiantes.
- 1 período académico activo con P1 y P2.
- 2 cursos.
- 3 asignaturas.
- Matrículas y asignaciones coherentes.
- Una asignatura completamente publicada en ambos parciales.
- Otra asignatura en estado incompleto para demostrar validaciones.
- Actividades, calificaciones y observaciones realistas.

Las credenciales demo se documentan solo para el entorno local y nunca se utilizan en producción.

## Documentación mínima

- Requisitos de PHP, extensiones, Composer, Node y MySQL.
- Instalación, `.env`, generación de clave y enlace de base.
- Migraciones y seeders.
- Compilación de assets.
- Ejecución local y pruebas.
- Roles y credenciales demo.
- Fórmulas académicas.
- Decisiones de arquitectura y limitaciones conocidas.

## Definición de terminado

- Todos los RF tienen implementación y prueba asociada.
- Todos los criterios de aceptación críticos pasan.
- Migraciones funcionan desde una base MySQL vacía.
- No existen accesos horizontales conocidos.
- Los tres flujos principales funcionan con datos demo.
- Los promedios coinciden con `S05`.
- No hay secretos en el repositorio.
- La interfaz es responsive y muestra estados de carga, vacío, error y éxito.
- El README permite reproducir el proyecto en otra computadora.

## Entregables

- Código fuente Laravel.
- Migraciones, factories y seeders.
- Suite de pruebas.
- Documentación `Spec/`, `Skill/`, `PromptMaster.md` y README.
- Respaldo o script de inicialización; se prefiere migración + seeder sobre un volcado rígido.

