# Pruebas de Aceptación de Usuario (ACCEPTANCE_TESTS.md)

**Proyecto:** Sistema de Calificaciones Académicas  
**Estado:** Automatizado y Validado  

---

## Flujo 1: Administrador del Sistema

1. **Autenticación e Inicio:**
   - El administrador inicia sesión con correo y contraseña válidos.
   - Es redirigido a `/admin/dashboard` observando métricas globales (usuarios, matrículas, promedios y alertas de parciales por publicar).
2. **Gestión Estructural y Usuarios:**
   - Registra un nuevo usuario con rol docente (`/admin/users/create`).
   - Crea un período académico (se generan automáticamente Parcial 1 y Parcial 2 al 50% c/u).
   - Asigna al docente un curso y asignatura (`/admin/teaching-assignments/create`).
   - Matrícula a un estudiante en el curso.
3. **Supervisión y Reapertura:**
   - Modela el seguimiento de parciales publicados por docentes.
   - Ejecuta una reapertura motivada de parcial reabriendo la edición al docente con registro en la bitácora de auditoría.

---

## Flujo 2: Docente de Asignatura

1. **Autenticación y Carga de Trabajo:**
   - El docente inicia sesión e ingresa a `/teacher/dashboard`.
   - Visualiza únicamente sus asignaciones docentes en el período activo.
2. **Evaluación y Registro:**
   - Crea actividades evaluativas en el Parcial 1 hasta completar el 100% de ponderación.
   - Ingresa calificaciones individuales y masivas (soporta notas `0.00`).
   - Ingresa observaciones por estudiante.
3. **Publicación Oficial:**
   - Revisa la vista previa de promedios antes de publicar.
   - Ejecuta la publicación oficial del Parcial 1.
   - Comprueba que la edición de notas queda bloqueada tras la publicación.

---

## Flujo 3: Estudiante

1. **Autenticación y Consulta Académica:**
   - El estudiante ingresa a `/student/dashboard`.
   - Observa sus asignaturas matriculadas y resumen de calificaciones.
2. **Navegación e Historial:**
   - Accede a `/student/grades` y selecciona el período académico.
   - En el Parcial 1 (Publicado), observa sus calificaciones por actividad, observaciones y el promedio del parcial.
   - En el Parcial 2 (Borrador/Reabierto), observa la etiqueta "Resultados aún no publicados" sin acceso a notas parciales.
3. **Generación de Boletín:**
   - Accede a `/student/grades/periods/{id}/print`.
   - Visualiza el reporte limpio en formato imprimible (`@media print`).
   - Valida que no puede acceder a calificaciones de otros estudiantes (IDOR bloqueado con 403 Forbidden).
