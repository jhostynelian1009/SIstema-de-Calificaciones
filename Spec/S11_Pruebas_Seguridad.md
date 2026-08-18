# S11 — Pruebas y seguridad

## Pirámide de pruebas

### Unitarias

- Fórmula de promedio parcial.
- Fórmula 50/50 del promedio final.
- Redondeo a dos decimales.
- Validación de suma porcentual.
- Determinación de calificaciones pendientes.

### Feature/integración

- Login, logout, bloqueo de inactivos y rate limiting.
- CRUD administrativo autorizado y denegado por rol.
- Matrículas y asignaciones únicas.
- Alcance horizontal del docente.
- Registro individual y masivo de notas.
- Publicación, bloqueo, reapertura y auditoría.
- Consulta del estudiante limitada a resultados propios publicados.
- Restricciones reales sobre MySQL/MariaDB de prueba.

### Navegación

- Renderizado de dashboards por rol.
- Formularios y errores de validación.
- Flujo completo: configuración → notas → publicación → consulta.
- Comprobación responsive manual de páginas críticas.

## Datos límite obligatorios

- Notas: `0`, `0.01`, `9.99`, `10`, `-0.01`, `10.01`.
- Porcentajes: suma `99.99`, `100.00`, `100.01`.
- Observación: vacía, 2 caracteres, 3 caracteres y más de 500.
- Usuario activo e inactivo.
- Parcial abierto, publicado y reabierto.

## Matriz mínima de acceso

Cada ruta privada sensible se prueba con:

1. invitado;
2. rol incorrecto;
3. rol correcto pero recurso ajeno;
4. rol correcto y recurso propio.

## Controles de seguridad

- Protección CSRF y regeneración de sesión al autenticar.
- Prevención de IDOR mediante Policies y consultas acotadas.
- Validación de identificadores y propiedades en servidor.
- Escapado Blade predeterminado para nombres y observaciones.
- Sin `{!! !!}` para contenido proporcionado por usuarios.
- Rate limiting en autenticación y recuperación de contraseña.
- Cabeceras de seguridad razonables en producción.
- Mensajes de error sin trazas, SQL ni secretos.
- Logs sin contraseñas, tokens ni datos de sesión.
- Dependencias auditadas y actualizadas dentro de versiones compatibles.

## Calidad

- Ejecutar formateador del proyecto.
- Ejecutar toda la suite antes de entregar.
- Probar migración desde base vacía y rollback cuando sea seguro.
- Revisar consultas N+1 en dashboards y matriz de notas.

