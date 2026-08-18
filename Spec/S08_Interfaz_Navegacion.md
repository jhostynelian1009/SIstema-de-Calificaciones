# S08 — Interfaz y navegación

## Diseño general

- Layout Blade común con barra superior, navegación lateral adaptable y área de alertas.
- Bootstrap 5, diseño responsive y componentes consistentes.
- Nombre del sistema visible: **Sistema de Calificaciones**.
- Cada página incluye título, ruta de navegación y acción principal clara.

## Rutas públicas y comunes

| Método/ruta | Pantalla |
|---|---|
| `GET /login` | Inicio de sesión |
| `POST /login` | Procesar acceso |
| `POST /logout` | Cerrar sesión |
| `GET /profile` | Perfil y cambio de contraseña |
| `GET /dashboard` | Redirección/panel por rol |

## Administrador `/admin`

- Dashboard: conteos de usuarios, cursos, asignaciones y estado del período activo.
- Usuarios: tabla paginada, filtros por nombre, correo, rol y estado.
- Cursos y asignaturas: CRUD con activación/desactivación.
- Períodos: configuración, activación y visualización de P1/P2.
- Matrículas: filtro por período/curso y asignación de estudiantes.
- Asignaciones docentes: filtro por docente/curso/asignatura/período.
- Resultados: vista de consulta y acción controlada de reapertura.
- Auditoría: eventos sensibles con filtros básicos.

## Docente `/teacher`

- Dashboard: tarjetas de asignaciones activas y progreso de P1/P2.
- Detalle de asignación: estudiantes, parciales y porcentajes configurados.
- Actividades: tabla con porcentaje, estado y total acumulado.
- Matriz de notas: filas de estudiantes; columnas o sección por actividad; nota y observación editables.
- Vista previa: promedios provisionales y lista de pendientes.
- Publicación: resumen, validaciones y confirmación explícita.

## Estudiante `/student`

- Dashboard: período actual, curso y asignaturas.
- Calificaciones: selector de período y tarjetas por asignatura.
- Detalle: tablas separadas para P1 y P2 con actividad, porcentaje, nota y observación.
- Resumen: promedio de cada parcial publicado, promedio final por asignatura y promedio general del período cuando todos los resultados estén completos.
- Vista de impresión con encabezado, estudiante, curso, período y resultados.

## Estados de interfaz

- **Vacío:** explicación y siguiente paso, no una tabla en blanco.
- **Incompleto:** porcentaje pendiente o calificaciones pendientes, visible solo a personal autorizado.
- **Listo:** configuración completa y habilitada para publicar.
- **Publicado:** insignia, fecha y bloqueo visual de edición.
- **Error:** mensaje específico sin revelar detalles internos.

## Formato

- Nota: `8,50 / 10,00` en interfaz española.
- Porcentaje: `25,00 %`.
- Promedio: dos decimales.
- Fechas: formato local legible; almacenamiento conforme a configuración del sistema.
