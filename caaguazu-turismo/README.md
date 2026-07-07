# Caaguazú Turismo

Plugin de WordPress con el módulo Turismo del portal [Caaguazú](https://caaguazu.net): 22 páginas de historia, oficio maderero, gastronomía y cultura guaraní del departamento, migradas del sitio de turismo original.

Requiere el theme [`caaguazu-theme`](../caaguazu-theme/) para verse con la identidad visual del portal. Convive (sin modificar su código) con los plugins **Caaguazú Locales** y **Caaguazú Portal** del repo [`nasastaxd/turismo`](https://github.com/nasastaxd/turismo): embebe sus shortcodes (`[caaguazu_locales]`, `[caaguazu_mapa]`, `[promotur_destacados]`) en el contenido que siembra, pero no depende de sus internals (login, moderación, reservas).

## Estructura

- `includes/tourism-content.php` — las 22 páginas (título, extracto, cuerpo HTML) en un array central. La jerarquía es plana (todas las secciones cuelgan directo de `turismo`, sin páginas puente intermedias solo-de-navegación).
- `includes/tourism-seeder.php` — siembra esas páginas al activar (con jerarquía padre/hijo real), resuelve los links internos entre ellas, y expone **Apariencia → Caaguazú** para reimportar sin pisar páginas ya editadas. También incluye una rutina de migración (`caaguazu_tourism_flatten_hierarchy`) que reubica automáticamente, en sitios ya sembrados con la jerarquía anterior a v1.2.0, las páginas hijas de las antiguas páginas puente (`sabores-de-caaguazu`, `vivir-caaguazu`, `planifica-tu-visita`) y borra esas páginas puente ya vacías.
- `includes/nav-integration.php` — mega-menú de Turismo (agrupado por sección) y su registro en el nav/accesos rápidos del theme vía los filtros `caaguazu_quick_access_items`/`caaguazu_nav_items`.

Ver `caaguazu-theme/README.md` para la arquitectura completa de módulos y la integración con Locales/Portal.
