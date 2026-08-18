# K-001 — Inicializar Laravel y entorno

## Objetivo

Dejar una base ejecutable de Laravel 12 conectada exclusivamente a MySQL, con Blade, Bootstrap 5, pruebas y configuración local documentada.

## Especificaciones

Leer `S01`, `S04`, `S09` y `S12`.

## Instrucciones

1. Inspecciona el repositorio. Si Laravel ya existe, no lo reinstales ni sobrescribas trabajo previo.
2. Verifica PHP 8.2+, Composer, Node y acceso a MySQL 8.
3. Crea o completa `.env.example` con variables neutrales y `DB_CONNECTION=mysql`.
4. Configura `APP_NAME="Sistema de Calificaciones"`, locale español y zona `America/Guayaquil`.
5. Instala Bootstrap 5 y sus dependencias de Vite; elimina dependencias visuales incompatibles solo si no tienen uso existente.
6. Crea un layout Blade base con navegación, mensajes flash, errores y contenedor responsive.
7. Configura una base MySQL separada para pruebas; no uses SQLite.
8. Añade una página inicial segura que redirija a login o dashboard.
9. Documenta instalación y comandos en el README principal del proyecto.

## No hacer

- No implementar todavía entidades académicas.
- No guardar claves o contraseñas reales.
- No introducir React, Vue, Tailwind ni una API separada.

## Verificación

- La aplicación inicia sin errores.
- Vite compila Bootstrap.
- `php artisan migrate` funciona contra MySQL.
- La prueba inicial usa MySQL y pasa.
- El layout se ve correctamente en escritorio y 360 px.

## Salida

Reporta versiones detectadas, configuración creada, archivos modificados y comandos ejecutados.

