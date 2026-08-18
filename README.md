# Sistema de Calificaciones — Spec as a Skill

Sistema web académico desarrollado sobre **Laravel 12, PHP 8.2+, MySQL 8, Blade y Bootstrap 5**.

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
- **Base de Datos**: MySQL 8.0+ / MariaDB 10.4+ (utf8mb4)

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
├── spec/
└── Skill/
```

### 2. Instalar dependencias de PHP y Node

```bash
composer install
npm install
```

### 3. Configuración de Entorno (.env)

Copia el archivo `.env.example` a `.env` y configura el acceso a MySQL:

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
DB_PORT=3306  # Ajustar a tu puerto MySQL (ej. 3307 en XAMPP si aplica)
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

### 4. Preparar Bases de Datos MySQL

Crea las bases de datos principal y de pruebas en MySQL:

```sql
CREATE DATABASE sistema_calificaciones CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE sistema_calificaciones_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Ejecutar Migraciones

```bash
php artisan config:clear
php artisan migrate
```

### 6. Compilar Assets (Vite + Bootstrap 5)

```bash
npm run build
```

Para desarrollo en vivo:
```bash
npm run dev
```

### 7. Ejecutar Pruebas Automatizadas (MySQL Dedicated)

```bash
php artisan test
```

### 8. Iniciar el Servidor de Desarrollo

```bash
php artisan serve
```

Navega a `http://127.0.0.1:8000`.

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

- **Skill completada:** `K-001 — Inicializar Laravel y entorno`
- **Siguiente Skill:** `K-002 — Autenticación, roles y autorización`
