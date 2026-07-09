# Caaguazú Turismo

Plugin de WordPress con el módulo Turismo del portal [Caaguazú](https://caaguazu.net): 22 páginas de historia, oficio maderero, gastronomía y cultura guaraní del departamento, migradas del sitio de turismo original.

Requiere el theme [`caaguazu-theme`](../caaguazu-theme/) para verse con la identidad visual del portal. Convive (sin modificar su código) con los plugins **Caaguazú Locales** y **Caaguazú Portal** del repo [`nasastaxd/turismo`](https://github.com/nasastaxd/turismo): embebe sus shortcodes (`[caaguazu_locales]`, `[caaguazu_mapa]`, `[promotur_destacados]`) en el contenido que siembra, pero no depende de sus internals (login, moderación, reservas).

## Estructura

- `includes/tourism-content.php` — las 22 páginas (título, extracto, cuerpo HTML) en un array central. La jerarquía es plana (todas las secciones cuelgan directo de `turismo`, sin páginas puente intermedias solo-de-navegación).
- `includes/tourism-seeder.php` — siembra esas páginas al activar (con jerarquía padre/hijo real), resuelve los links internos entre ellas, y expone **Apariencia → Caaguazú** para reimportar sin pisar páginas ya editadas. También incluye una rutina de migración (`caaguazu_tourism_flatten_hierarchy`) que reubica automáticamente, en sitios ya sembrados con la jerarquía anterior a v1.2.0, las páginas hijas de las antiguas páginas puente (`sabores-de-caaguazu`, `vivir-caaguazu`, `planifica-tu-visita`) y borra esas páginas puente ya vacías.
- `includes/nav-integration.php` — mega-menú de Turismo (agrupado por sección) y su registro en el nav/accesos rápidos del theme vía los filtros `caaguazu_quick_access_items`/`caaguazu_nav_items`. También registra `caaguazu_tourism_shell_items` (las 6 secciones de primer nivel + directorio de Locales si está activo + "Mapa", que apunta a la página `mapa-interactivo` ya sembrada), que el theme usa para poblar el header/tabbar/footer propios de Turismo (`caaguazu-theme/inc/ecosystem-shell.php`, el sistema genérico de ecosistemas de la 3.0) mientras se navega dentro del ecosistema — el theme no sabe nada de estas secciones, solo pinta lo que este plugin le pasa por el filtro.

Ver `caaguazu-theme/README.md` para la arquitectura completa de módulos y la integración con Locales/Portal.

## Actualizaciones

Desde la 1.8 el plugin se auto-actualiza desde los GitHub Releases del repo (`includes/updater.php`, misma clase que usa `caaguazu-modulos`), comparando su versión contra el `manifest.json` que cada release publica — ver `caaguazu-theme/README.md`, sección "Actualizaciones". El shell de Turismo (header/tabbar propios) ahora lo provee el sistema genérico de ecosistemas del theme 3.0 (`inc/ecosystem-shell.php`); este plugin sigue poblando sus secciones vía el mismo filtro `caaguazu_tourism_shell_items` de siempre, sin cambios.
