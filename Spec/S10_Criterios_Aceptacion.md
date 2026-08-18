# S10 — Criterios de aceptación

## Autenticación y autorización

- **CA-001:** dado un usuario activo con credenciales válidas, al iniciar sesión accede al panel de su rol.
- **CA-002:** dado un usuario inactivo, aunque su contraseña sea correcta, no obtiene una sesión autorizada.
- **CA-003:** dado un docente, al intentar abrir por URL una asignación ajena recibe `403`.
- **CA-004:** dado un estudiante, no existe una ruta autorizada que permita consultar notas de otro estudiante.

## Configuración administrativa

- **CA-005:** al crear un período se crean P1 y P2, cada uno con peso 50.00.
- **CA-006:** el sistema rechaza una segunda matrícula del mismo estudiante en el mismo período.
- **CA-007:** una asignación duplicada para la combinación curso–asignatura–período es rechazada; el cambio de docente actualiza la asignación existente.
- **CA-008:** una entidad académica con historial no puede eliminarse destruyendo calificaciones.

## Estados de parciales por asignación (K-005)

- **CA-008A:** cada asignación docente genera exactamente dos filas de estado en `partial_publications`, correspondientes a P1 y P2 con estado inicial `draft`.
- **CA-008B:** la relación asignación–parcial valida que ambos pertenezcan al mismo período académico.
- **CA-008C:** los estados persistidos de publicación (`draft`, `published`, `reopened`) son independientes de los estados calculados de preparación (vacío, incompleto, listo).
- **CA-008D:** la reasignación de docente o desactivación de asignación conserva las filas de `partial_publications` sin reiniciarlas ni destruirlas.

## Actividades y notas

- **CA-009:** el docente ve únicamente las asignaciones propias activas.
- **CA-010:** una actividad que elevaría el total del parcial a más de 100 % es rechazada.
- **CA-011:** se admite una nota 0.00 y una 10.00; se rechazan -0.01 y 10.01.
- **CA-012:** una nota sin observación válida no se guarda.
- **CA-013:** un docente no puede calificar a un estudiante ajeno al curso asignado.
- **CA-014:** el registro masivo inválido no deja guardados parciales incoherentes.

## Cálculos

- **CA-015:** notas 8.00 al 20 %, 9.00 al 30 % y 7.50 al 50 % producen P1 = 8.05.
- **CA-016:** P1 = 8.05 y P2 = 9.10 producen promedio final = 8.58.
- **CA-017:** cambiar una nota en un parcial abierto actualiza el promedio provisional sin editar un promedio manual.

## Publicación

- **CA-018:** con porcentajes que suman 99.99 %, el parcial no se publica.
- **CA-019:** con 100 % pero una calificación faltante, el parcial no se publica y se identifica el pendiente.
- **CA-020:** al publicar, el estudiante visualiza actividades, notas, observaciones y promedio del parcial.
- **CA-021:** tras publicar, el docente no puede editar actividades ni notas.
- **CA-022:** al reabrir como administrador se exige motivo, se audita y se oculta temporalmente el resultado oficial hasta una nueva publicación.
- **CA-023:** el promedio final aparece solo cuando ambos parciales están publicados.
- **CA-023A:** el promedio general aparece solo cuando todas las asignaturas aplicables tienen resultado final; su valor es la media aritmética de esos resultados.

## Interfaz y operación

- **CA-024:** los listados extensos están paginados y los filtros conservan el contexto.
- **CA-025:** las vistas principales son utilizables a 360 px sin desplazamiento horizontal general.
- **CA-026:** una instalación limpia puede migrar, sembrar datos demo, compilar assets y ejecutar pruebas siguiendo el README.
