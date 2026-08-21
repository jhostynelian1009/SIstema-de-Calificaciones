# Guía de Despliegue en Producción (DEPLOYMENT.md)

**Proyecto:** Sistema de Calificaciones Académicas  
**Plataforma Base:** Laravel 12 / PHP 8.2+ / MariaDB 10.4+  
**Versión de Entrega:** 1.0.0 (K-012)  

---

## 1. Requisitos del Servidor

### 1.1 Entorno de Ejecución
- **Servidor Web:** Nginx 1.20+ o Apache 2.4+ con módulo `mod_rewrite` habilitado.
- **PHP:** Versión 8.2 o superior.
- **Base de Datos:** MariaDB 10.4+ o MySQL 8.0+.
- **Node.js / NPM:** Node 18+ y NPM 9+ (solo para la compilación de assets).

### 1.2 Extensiones PHP Obligatorias
- `ext-bcmath`
- `ext-ctype`
- `ext-curl`
- `ext-dom`
- `ext-fileinfo`
- `ext-json`
- `ext-mbstring`
- `ext-openssl`
- `ext-pdo_mysql`
- `ext-tokenizer`
- `ext-xml`

---

## 2. Configuración de Variables de Entorno (`.env`)

Copiar el archivo de plantilla y ajustar los parámetros de producción:

```bash
cp .env.example .env
```

Configuración obligatoria para producción:

```ini
APP_NAME="Sistema de Calificaciones"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://calificaciones.tu-dominio.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_calificaciones_prod
DB_USERNAME=usuario_prod_seguro
DB_PASSWORD=ContrasenaSeguraDeBaseDeDatos123!

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.com
MAIL_PORT=587
MAIL_USERNAME=notificaciones@tu-dominio.com
MAIL_PASSWORD=ContrasenaMailSegura
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="notificaciones@tu-dominio.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Generar la clave de aplicación:

```bash
php artisan key:generate
```

---

## 3. Permisos de Archivos y Carpetas

Asegurar que el usuario del servidor web (`www-data`, `nginx` o similar) tenga permisos de escritura en los directorios de almacenamiento y caché:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 4. Instalación de Dependencias y Compilación

### 4.1 PHP (Composer)
Instalar solo dependencias de producción y optimizar el cargador automático:

```bash
composer install --no-dev --optimize-autoloader
```

### 4.2 Assets (Vite & Bootstrap)
Compilar los paquetes CSS/JS para distribución:

```bash
npm ci
npm run build
```

---

## 5. Migraciones y Creación del Primer Administrador

### 5.1 Ejecución de Migraciones
Ejecutar las migraciones de base de datos de manera forzada en entorno de producción:

```bash
php artisan migrate --force
```

> **¡IMPORTANTE!** No ejecute `php artisan db:seed` en producción. Los datos demostrativos están bloqueados por seguridad.

### 5.2 Creación del Administrador Inicial Real
Utilice el comando interactivo seguro para dar de alta la primera cuenta administrativa:

```bash
php artisan app:create-admin
```

El comando le solicitará:
1. Nombre completo del administrador.
2. Correo electrónico institucional.
3. Contraseña (mínimo 8 caracteres, entrada oculta).
4. Confirmación de contraseña.

---

## 6. Optimización y Cachés de Producción

Ejecute los comandos de optimización nativos de Laravel:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Para revertir las cachés en mantenimientos futuros:

```bash
php artisan optimize:clear
```

---

## 7. Configuración del Servidor Web (Nginx)

Ejemplo de bloque de servidor Nginx con HTTPS habilitado:

```nginx
server {
    listen 80;
    server_name calificaciones.tu-dominio.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name calificaciones.tu-dominio.com;

    root /var/www/sistema-calificaciones/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/calificaciones.tu-dominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/calificaciones.tu-dominio.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 8. Checklist Posterior al Despliegue

- [ ] `APP_ENV=production` y `APP_DEBUG=false` confirmados en `.env`.
- [ ] La base de datos objetivo es la de producción (`sistema_calificaciones_prod`).
- [ ] Se creó exitosamente el primer administrador con `php artisan app:create-admin`.
- [ ] El inicio de sesión funciona correctamente con HTTPS habilitado.
- [ ] El registro público está inhabilitado (`/register` devuelve 404).
- [ ] Los estudiantes solo visualizan parciales con estado `Published`.
- [ ] Las vistas de error `403`, `404`, `419` y `500` se muestran sin exponer trazas.
