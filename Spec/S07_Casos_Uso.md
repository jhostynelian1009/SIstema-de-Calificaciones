# S07 — Casos de uso

## CU-01 — Iniciar sesión

**Actor:** cualquier usuario activo.  
**Flujo:** ingresa correo y contraseña; el sistema valida credenciales, regenera la sesión y redirige según rol.  
**Alternos:** credenciales inválidas muestran mensaje genérico; usuario inactivo no accede; demasiados intentos activan limitación temporal.

## CU-02 — Configurar estructura académica

**Actor:** administrador.  
**Precondición:** sesión administrativa.  
**Flujo:** crea período, cursos y asignaturas; el sistema genera P1 y P2 con 50 %; activa el período requerido.  
**Resultado:** estructura disponible para matrículas y asignaciones.

## CU-03 — Matricular estudiante

**Actor:** administrador.  
**Flujo:** selecciona estudiante, curso y período; el sistema valida rol, estado y ausencia de matrícula previa; registra la matrícula.  
**Alterno:** si ya existe una matrícula en ese período, se rechaza la operación y se informa el curso actual.

## CU-04 — Asignar docente

**Actor:** administrador.  
**Flujo:** selecciona docente, curso, asignatura y período; valida estados y duplicidad; registra la asignación.  
**Resultado:** la asignación aparece en el panel del docente.

## CU-05 — Configurar actividades del parcial

**Actor:** docente asignado.  
**Flujo:** abre una asignación y parcial; crea actividades con porcentajes; el sistema muestra total utilizado y restante.  
**Alternos:** rechaza porcentaje que haga superar 100 %; impide cambios si está publicado.

## CU-06 — Registrar calificaciones

**Actor:** docente asignado.  
**Precondición:** actividad activa y parcial abierto.  
**Flujo:** visualiza estudiantes matriculados, ingresa nota y observación, guarda; el sistema valida y recalcula el promedio provisional.  
**Alternos:** rechaza nota fuera de rango, observación insuficiente o estudiante no matriculado.

## CU-07 — Publicar parcial

**Actor:** docente asignado.  
**Flujo:** solicita publicación; el sistema verifica total de 100 %, actividades y notas completas; muestra confirmación; al aceptar registra fecha y bloquea edición.  
**Alterno:** presenta una lista concreta de pendientes y no publica.

## CU-08 — Consultar calificaciones

**Actor:** estudiante.  
**Flujo:** selecciona período y asignatura; visualiza solo parciales publicados con actividad, porcentaje, nota, observación y promedio. Cuando ambos están publicados, visualiza el promedio final.  
**Alterno:** muestra estado vacío si aún no existen resultados publicados.

## CU-09 — Reabrir parcial

**Actor:** administrador.  
**Flujo:** selecciona publicación, escribe motivo y confirma; el sistema registra auditoría y devuelve el estado a abierto.  
**Resultado:** el docente puede corregir y debe publicar nuevamente.

