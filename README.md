# Sistema de Calificaciones — Spec as a Skill

Sistema web académico desarrollado sobre **Laravel 12, PHP 8.2+, MySQL/MariaDB compatible, Blade y Bootstrap 5**.

## Propósito

El sistema permite que:
- El **administrador** gestione la estructura académica, usuarios, matrículas y asignaciones docentes.
- El **docente** registre actividades, porcentajes, calificaciones y observaciones únicamente en sus asignaciones autorizadas.
- El **estudiante** consulte sus notas publicadas, observaciones, promedios parciales y promedio final.
- El sistema calcule dos parciales, cada uno con un peso del 50 % de la nota final.

No incluye entrega de tareas, almacenamiento de archivos, videoconferencias ni edición de notas por parte de los estudiantes.

---

## Requisitos del sistema

- **PHP**: 8.2 o superior (con extensiones pdo, pdo_mysql, mbstring, openssl)
- **Composer**: 2.x
- **Node.js**: 18+ / 22+ & npm
- **Base de Datos**: Persistencia relacional mediante MySQL/MariaDB compatible. El entorno local utiliza MariaDB 10.4 con el controlador `mysql` de Laravel. No se utilizará SQLite.

---

## Guía de Instalación y Ejecución Local

### 1. Clonar o descargar el proyecto

Asegúrate de que los archivos principales residan en la raíz del repositorio:
```text
Sistema-de-Calificaciones/
├── artisan
├── app/
├── database/
├── resources/
├── routes/
├── PromptMaster.md
├── Spec/
└── Skill/
```

### 2. Instalar dependencias de PHP y Node

```bash
composer install
npm install
```

### 3. Configuración de Entorno (.env)

Copia el archivo `.env.example` a `.env` y configura el acceso a MySQL/MariaDB:

```ini
APP_NAME="Sistema de Calificaciones"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=America/Guayaquil
APP_URL=http://localhost

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306  # Ajustar a tu puerto MySQL/MariaDB (ej. 3307 en XAMPP si aplica)
DB_DATABASE=sistema_calificaciones
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Genera la clave de la aplicación:
```bash
php artisan key:generate
```

### 4. Preparar Bases de Datos MySQL / MariaDB

Crea las bases de datos principal y de pruebas:

```sql
CREATE DATABASE sistema_calificaciones CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE sistema_calificaciones_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Ejecutar Migraciones y Seeders

```bash
php artisan config:clear
php artisan migrate
php artisan db:seed
```

### 6. Compilar Assets (Vite + Bootstrap 5)

```bash
npm run build
```

Para desarrollo en vivo:
```bash
npm run dev
```

### 7. Ejecutar Pruebas Automatizadas (MariaDB / MySQL Dedicated)

```bash
php artisan test
```

### 8. Iniciar el Servidor de Desarrollo

```bash
php artisan serve
```

Navega a `http://127.0.0.1:8000`.

---

## Usuarios y Credenciales Demo (Entorno Local Exclusivo)

Tras ejecutar `php artisan db:seed`, se encuentran disponibles las siguientes cuentas de prueba:

| Rol | Correo Electrónico | Contraseña | Estado |
|---|---|---|---|
| **Administrador** | `admin@calificaciones.local` | `Password123!` | Activo |
| **Docente** | `docente@calificaciones.local` | `Password123!` | Activo |
| **Estudiante** | `estudiante@calificaciones.local` | `Password123!` | Activo |
| **Prueba Inactivo** | `inactivo@calificaciones.local` | `Password123!` | Inactivo |

---

## Reglas de Cálculo Académico

Dentro de cada parcial, las actividades de una asignatura deben sumar exactamente 100 %.

```text
promedio_parcial = Σ(nota_actividad × porcentaje_actividad / 100)
promedio_final   = (promedio_parcial_1 × 0.50) + (promedio_parcial_2 × 0.50)
```

Las notas aceptadas están entre `0.00` y `10.00` y se presentan redondeadas a dos decimales.

---

## Estado de Desarrollo (Skill actual)

- **Skills completadas:**
  - `K-001 — Inicializar Laravel y entorno`
  - `K-002 — Autenticación, roles y autorización base`
- **Siguiente Skill:** `K-003 — Gestión administrativa de usuarios`
