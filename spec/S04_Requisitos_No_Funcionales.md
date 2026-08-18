# S04 — Requisitos no funcionales

## Seguridad

- **RNF-001:** todas las funciones privadas requieren autenticación.
- **RNF-002:** autorización aplicada en servidor mediante middleware y Policies.
- **RNF-003:** contraseñas almacenadas únicamente con el hash provisto por Laravel.
- **RNF-004:** formularios protegidos con CSRF y entradas validadas con Form Requests.
- **RNF-005:** consultas mediante Eloquent o Query Builder parametrizado; sin SQL concatenado.
- **RNF-006:** evitar asignación masiva de `role`, estados, propietarios y claves foráneas sensibles.
- **RNF-007:** cookies de sesión seguras según el entorno y limitación de intentos de login.

## Integridad y precisión

- **RNF-008:** notas y porcentajes usan `decimal`; no se utiliza `float` para persistencia ni cálculo final.
- **RNF-009:** los cálculos se redondean a dos decimales únicamente en la salida definida.
- **RNF-010:** operaciones masivas de notas y publicación se ejecutan en transacciones.
- **RNF-011:** claves foráneas e índices impiden referencias y duplicados inválidos.

## Rendimiento

- **RNF-012:** vistas ordinarias deben responder en menos de 2 segundos con 1.000 estudiantes y 100.000 calificaciones en un entorno razonable de producción.
- **RNF-013:** listados con más de 25 registros deben paginarse.
- **RNF-014:** evitar consultas N+1 mediante carga anticipada y seleccionar solo columnas necesarias.

## Usabilidad y accesibilidad

- **RNF-015:** interfaz responsive desde 360 px de ancho.
- **RNF-016:** formularios con etiquetas, errores junto al campo y conservación de datos válidos.
- **RNF-017:** no comunicar estados solo mediante color; incluir texto o icono con etiqueta accesible.
- **RNF-018:** navegación por teclado y contraste legible en componentes principales.
- **RNF-019:** notas, promedios y porcentajes deben mostrar unidad y dos decimales.

## Mantenibilidad

- **RNF-020:** seguir convenciones PSR-12 y organización estándar de Laravel.
- **RNF-021:** reglas académicas centralizadas en servicios de dominio y probadas unitariamente.
- **RNF-022:** migraciones reversibles y seeders idempotentes para datos demostrativos.
- **RNF-023:** documentación de instalación y variables de entorno sin secretos reales.

## Compatibilidad

- **RNF-024:** compatible con versiones actuales de Chrome, Edge y Firefox.
- **RNF-025:** aplicación desplegable en Linux con PHP 8.2+, MySQL 8 y Node para compilar assets.

