# K-002 — Autenticación, roles y autorización base

## Objetivo

Implementar login seguro, cierre de sesión, recuperación/cambio de contraseña, roles y paneles protegidos.

## Especificaciones

Leer `S02`, RF-001 a RF-005 de `S03`, `S04`, `S09`, CA-001 a CA-004 de `S10` y `S11`.

## Instrucciones

1. Implementa autenticación Blade compatible con Bootstrap. No dependas de vistas Tailwind.
2. Amplía `users` con `role` y `active`, usando un enum o casteo controlado.
3. Crea middleware de usuario activo y rol; registra alias según la versión real de Laravel.
4. Crea rutas agrupadas para `/admin`, `/teacher` y `/student` con dashboards mínimos.
5. Regenera la sesión al autenticar e invalídala al cerrar sesión.
6. Añade rate limiting al login y recuperación.
7. Implementa edición del perfil propio y cambio de contraseña.
8. Crea factories y usuarios demo para los tres roles.
9. Oculta menús ajenos al rol sin omitir la protección de servidor.

## Pruebas

- Credenciales válidas e inválidas.
- Redirección por rol.
- Usuario inactivo bloqueado.
- Invitado redirigido a login.
- Rol incorrecto recibe `403`.
- Logout invalida la sesión.
- Cambio de contraseña exige la contraseña actual cuando corresponda.

## Criterio de salida

Cada rol puede iniciar sesión y solo abrir su área. Las pruebas de acceso pasan en MySQL.

