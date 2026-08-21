# Revisión de Seguridad OWASP (SECURITY_REVIEW.md)

**Proyecto:** Sistema de Calificaciones Académicas  
**Metodología:** OWASP Top 10 Security Verification  
**Fecha:** 2026-08-20  
**Evaluador:** Equipo de Desarrollo / Antigravity AI  

---

## 1. Resumen Ejecutivo

Se realizó una revisión integral de seguridad sobre el código fuente, la arquitectura de autenticación/autorización, el manejo de datos, cabeceras HTTP y el almacenamiento de secretos. No se detectaron vulnerabilidades críticas ni de severidad alta abiertas en la versión final K-012.

---

## 2. Evaluación por Categórica OWASP

### 2.1 Control de Acceso (Broken Access Control & IDOR)
- **Mitigación IDOR:** En las rutas estudiantiles (`/student/*`), los controladores obtienen la identidad del usuario a través de `$request->user()`, sin aceptar IDs de estudiante por parámetro de URL.
- **Validación de Pertenencia:** En el detalle de asignaturas estudiantiles, se verifica que la asignación docente pertenezca a la carrera/curso donde el estudiante está efectivamente matriculado. De lo contrario, se retorna `403 Forbidden`.
- **Aislamiento Docente:** Las políticas (`ActivityPolicy`, `GradePolicy`) impiden que un docente cree actividades o califique estudiantes en asignaturas no asignadas.
- **Protección de Usuarios Inactivos:** El middleware `EnsureUserIsActive` revoca el acceso a cualquier usuario desactivado en la siguiente petición HTTP.
- **Deshabilitación de Registro Público:** No existen rutas ni controladores de autoregistro. El alta de usuarios es exclusiva del Administrador o mediante el CLI `app:create-admin`.

### 2.2 Autenticación y Gestión de Sesiones (Identification and Authentication Failures)
- **Algoritmo de Hash:** Todas las contraseñas se almacenan procesadas con `bcrypt` (vía `Hash::make`).
- **Regeneración de Sesión:** El controlador de Login ejecuta `$request->session()->regenerate()` inmediatamente tras validar credenciales para prevenir Session Fixation.
- **Logout Seguro:** El cierre de sesión invalida el token de sesión y regenera el token CSRF.
- **Rate Limiting:** El inicio de sesión está protegido con `ThrottleRequests` (máximo 5 intentos fallidos por minuto por IP/usuario).
- **Protección del Administrador:** El servicio `UserService` impide la desactivación o eliminación del último usuario activo con rol `admin`.

### 2.3 Validación de Entradas y Prevención de Inyecciones (Injection & Input Handling)
- **Form Requests:** Cada formulario sensible (creación de usuarios, cursos, asignaturas, actividades, calificaciones y reaperturas) cuenta con un `FormRequest` tipado.
- **Validaciones Académicas:**
  - Ponderación de actividades: La suma por parcial no puede exceder el 100.00%.
  - Rango de notas: Numérico entre 0.00 y 10.00.
  - Fechas de período: `end_date` posterior a `start_date`.
- **Inyección SQL:** Consultas construidas mediante el ORM Eloquent y Query Builder con binding automático de parámetros.

### 2.4 Prevención de XSS y Salidas de Datos (Cross-Site Scripting)
- **Escapado Blade:** Todas las impresiones de datos dinámicos (nombres, observaciones de docente, motivos de reapertura) utilizan la sintaxis segura `{{ $variable }}`.
- **Prohibición de `{!! !!}`:** Se confirmó que ninguna vista utiliza la sintaxis no escapada `{!! !!}` con datos provistos por usuarios.
- **Manejo de Errores en Producción:** Con `APP_DEBUG=false`, el sistema muestra vistas de error personalizadas (`403`, `404`, `419`, `500`) en español sin exponer SQL, trazas ni variables de entorno.

### 2.5 Cabeceras de Seguridad y Privacidad
- **Middleware `SecurityHeaders`:** Inyecta en todas las respuestas web:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- **Privacidad Estudiantil:** Las páginas de resultados e historial estudiantil incluyen:
  - `Cache-Control: private, no-store`
  - `Pragma: no-cache`
  - `X-Robots-Tag: noindex, nofollow`

### 2.6 Protección de Secretos y Auditoría de Dependencias
- **Ignorado Git:** `.env` y credenciales locales están registrados en `.gitignore`.
- **Composer Audit:** `composer audit` → 0 vulnerabilidades.
- **NPM Audit:** `npm audit --omit=dev` → 0 vulnerabilidades.
- **Auditoría Interna:** `AuditLog` registra acciones de inicio de sesión, cambios de estado, publicaciones y reaperturas omitiendo contraseñas o hashes.

---

## 3. Clasificación de Hallazgos y Estado de Corrección

| Clasificación | Descripción del Hallazgo | Estado | Acción Realizada |
| :--- | :--- | :--- | :--- |
| **Crítico** | Riesgo de ejecución accidental de seeders demo con contraseñas conocidas en entorno de producción. | **Corregido** | Se agregó restricción en `DatabaseSeeder` permitiendo ejecución exclusiva en `local` y `testing`. |
| **Alto** | Inexistencia de un mecanismo seguro para crear el primer administrador en producción sin usar contraseñas por defecto. | **Corregido** | Se implementó el comando de consola interactivo `php artisan app:create-admin`. |
| **Medio** | Ausencia de cabeceras HTTP defensivas globales. | **Corregido** | Se creó y registró el middleware `SecurityHeaders`. |
| **Bajo** | Advertencias de PHPUnit 12 sobre doc-comments de metadatos en tests existentes. | **Corregido** | Se migraron los atributos a la sintaxis nativa `#[Test]` de PHP. |
| **Informativo** | Recomendación de habilitar HSTS únicamente cuando la app cuente con HTTPS en servidor final. | **Documentado** | Incluido en la guía `docs/DEPLOYMENT.md`. |

---

## 4. Conclusión
El Sistema de Calificaciones cumple los estándares de seguridad requeridos por la especificación y OWASP para un entorno académico de producción.
