# Caaguazú Turismo

Plugin de WordPress con el módulo Turismo del portal [Caaguazú](https://caaguazu.net): 25 páginas de historia, oficio maderero, gastronomía y cultura guaraní del departamento, migradas del sitio de turismo original.

Requiere el theme [`caaguazu-theme`](../caaguazu-theme/) para verse con la identidad visual del portal. Convive (sin modificar su código) con los plugins **Caaguazú Locales** y **Caaguazú Portal** del repo [`nasastaxd/turismo`](https://github.com/nasastaxd/turismo): embebe sus shortcodes (`[caaguazu_locales]`, `[caaguazu_mapa]`, `[promotur_destacados]`) en el contenido que siembra, pero no depende de sus internals (login, moderación, reservas).

## Estructura

- `includes/tourism-content.php` — las 25 páginas (título, extracto, cuerpo HTML) en un array central.
- `includes/tourism-seeder.php` — siembra esas páginas al activar (con jerarquía padre/hijo real), resuelve los links internos entre ellas, y expone **Apariencia → Caaguazú** para reimportar sin pisar páginas ya editadas.
- `includes/nav-integration.php` — mega-menú de Turismo (agrupado por sección) y su registro en el nav/accesos rápidos del theme vía los filtros `caaguazu_quick_access_items`/`caaguazu_nav_items`.

Ver `caaguazu-theme/README.md` para la arquitectura completa de módulos y la integración con Locales/Portal.
