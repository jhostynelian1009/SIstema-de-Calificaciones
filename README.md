# Sistema de Calificaciones Académicas — Spec as a Skill

Sistema web de gestión académica desarrollado con **Laravel 12, PHP 8.2+, MariaDB 10.4 / MySQL compatible, Blade y Bootstrap 5**, mediante la metodología **Spec as a Skill** (K-001 a K-012).

---

## 1. Propósito y Alcance

El sistema gestiona de forma integral el ciclo evaluativo escolar:
- **Administrador:** Configuración del sistema, usuarios, oferta de cursos/asignaturas, matrículas, asignaciones docentes, monitoreo de auditoría y reapertura justificada de parciales. No puede ingresar ni modificar calificaciones directamente.
- **Docente:** Gestión de actividades evaluativas (ponderación sumatoria al 100%), registro de calificaciones (0.00 a 10.00) y observaciones por parcial, vista previa y publicación oficial.
- **Estudiante:** Consulta de calificaciones oficiales publicadas, observaciones, promedios parciales y promedio final. Acceso estricto protegido contra vulnerabilidades IDOR.
- **Regla del 50%:** Todo período académico posee automáticamente 2 parciales (Parcial 1 y Parcial 2), cada uno con un peso exacto del 50.00% en la nota final de la asignatura.

---

## 2. Tecnologías y Stack

- **Framework Web:** Laravel 12.67 (PHP 8.2+)
- **Base de Datos:** MariaDB 10.4 / MySQL Compatible (Persistencia Relacional)
- **Frontend / Styling:** Vanilla CSS, Bootstrap 5.3, Vite 6
- **Autenticación / Seguridad:** Laravel Auth (Session / Bcrypt), Middleware de Roles, Security Headers HTTP.
- **Pruebas:** PHPUnit 11/12 con DB independiente `sistema_calificaciones_test`.

---

## 3. Guía de Instalación Rápida (Desarrollo Local)

### 3.1 Clonar Repositorio e Instalar Dependencias

```bash
git clone <URL_REPOSITORIO>
cd Sistema-de-Calificaciones
composer install
npm install
```

### 3.2 Configurar Entorno (`.env`)

Copiar el archivo de plantilla:

```bash
copy .env.example .env
php artisan key:generate
```

Asegurarse de tener configurada la conexión a MariaDB/MySQL en `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_calificaciones
DB_USERNAME=root
DB_PASSWORD=
```

### 3.3 Bases de Datos y Siembra Local

Crear las bases de datos en MariaDB/MySQL:

```sql
CREATE DATABASE sistema_calificaciones CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE sistema_calificaciones_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ejecutar migraciones y datos demostrativos:

```bash
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

Navegar a `http://127.0.0.1:8000`.

---

## 4. Credenciales de Prueba (Entorno Local / Testing)

| Rol | Correo Electrónico | Contraseña | Estado |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@calificaciones.local` | `Password123!` | Activo |
| **Docente 1** | `docente@calificaciones.local` | `Password123!` | Activo |
| **Docente 2** | `docente2@calificaciones.local` | `Password123!` | Activo |
| **Estudiante 1** | `estudiante1@calificaciones.local` | `Password123!` | Activo |
| **Estudiante Demo** | `estudiante@calificaciones.local` | `Password123!` | Activo |
| **Usuario Inactivo** | `inactivo@calificaciones.local` | `Password123!` | Inactivo |

> **ADVERTENCIA DE SEGURIDAD:** Los seeders de datos demostrativos están **bloqueados y deshabilitados en entorno de producción** (`APP_ENV=production`) para prevenir la creación de credenciales conocidas en servidores reales.

---

## 5. Despliegue en Producción y Creación Inicial de Administrador

### 5.1 Ajustes de Producción

En el servidor de producción:
1. Configurar `.env` con `APP_ENV=production` y `APP_DEBUG=false`.
2. Ejecutar las migraciones de base de datos de forma forzada:
   ```bash
   php artisan migrate --force
   ```

### 5.2 Comando CLI Seguro para Administrador Inicial

Para dar de alta al primer usuario administrador sin usar seeders ficticios:

```bash
php artisan app:create-admin
```

El comando solicitará de forma interactiva e implícita:
- Nombre completo del administrador.
- Correo electrónico válido y no registrado.
- Contraseña (mínimo 8 caracteres, entrada oculta).

---

## 6. Fórmulas de Cálculo Académico

El sistema **no almacena promedios en la base de datos**. Todos los promedios son derivados dinámicamente:

1. **Promedio de Parcial (Docente / Estudiante):**
   $$\text{Promedio Parcial} = \sum \left( \text{Nota Actividad} \times \frac{\text{Porcentaje Actividad}}{100} \right)$$
2. **Promedio Final de Asignatura:**
   $$\text{Promedio Final} = (\text{Promedio P1} \times 0.50) + (\text{Promedio P2} \times 0.50)$$
3. **Promedio General del Período:**
   $$\text{Promedio General} = \frac{\sum \text{Promedio Final Asignaturas}}{\text{Total Asignaturas Matriculadas}}$$

Todas las notas se expresan con precisión de 2 decimales y rango válido entre `0.00` y `10.00`.

---

## 7. Pruebas Automatizadas y Calidad

Para ejecutar la suite completa de pruebas (142 pruebas en verde, 485 aserciones):

```bash
php artisan test
```

Para verificar formato de código con Laravel Pint:

```bash
vendor/bin/pint --test
```

Para verificar auditoría de dependencias:

```bash
composer validate --strict
composer audit
npm audit --omit=dev
```

---

## 8. Documentación del Proyecto (`docs/`)

- [`docs/TRACEABILITY.md`](docs/TRACEABILITY.md): Matriz de trazabilidad de requisitos (RF-001 a RF-036).
- [`docs/QA_REPORT.md`](docs/QA_REPORT.md): Informe de aseguramiento de calidad y revisión de DB.
- [`docs/SECURITY_REVIEW.md`](docs/SECURITY_REVIEW.md): Revisión de seguridad OWASP y cabeceras HTTP.
- [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md): Guía paso a paso para despliegue en servidores web.
- [`docs/ACCEPTANCE_TESTS.md`](docs/ACCEPTANCE_TESTS.md): Flujos de prueba de aceptación por rol.
