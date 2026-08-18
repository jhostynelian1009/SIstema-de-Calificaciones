# K-012 — Pruebas, seguridad y entrega

## Objetivo

Cerrar el proyecto con verificación funcional, de seguridad, datos demo y documentación reproducible.

## Especificaciones

Leer `S04`, `S10`, `S11`, `S12` y revisar trazabilidad completa de `S03`.

## Instrucciones

1. Construye una matriz `RF → ruta/clase/vista → prueba` y corrige requisitos sin cobertura.
2. Completa pruebas unitarias, feature e integración sobre MySQL.
3. Ejecuta la matriz de acceso invitado/rol incorrecto/recurso ajeno/recurso propio.
4. Prueba migraciones desde una base vacía, seeders idempotentes y flujo completo de los tres roles.
5. Crea datos demo definidos en `S12`, incluyendo caso publicado y caso incompleto.
6. Revisa CSRF, IDOR, XSS, asignación masiva, validaciones, rate limiting, sesiones y exposición de errores.
7. Audita dependencias PHP y JavaScript; corrige vulnerabilidades dentro del alcance y documenta cualquier riesgo restante.
8. Ejecuta formateador, suite completa y compilación de producción.
9. Revisa consultas N+1 y páginas críticas en 360 px y escritorio.
10. Completa README con instalación, variables, comandos, credenciales demo, fórmulas y alcance.
11. Confirma que no existan secretos, archivos `.env`, respaldos reales o datos personales en el repositorio.

## Comandos esperados

Adapta los comandos a las herramientas realmente instaladas, pero como mínimo valida:

```text
composer install
npm install
php artisan migrate:fresh --seed --env=testing
php artisan test
npm run build
```

## Criterio de salida

- Todos los CA críticos pasan.
- Suite en verde sobre MySQL.
- Build de producción correcto.
- Instalación reproducida desde README.
- Trazabilidad completa.
- Informe final de archivos, pruebas, cobertura funcional y riesgos residuales.

