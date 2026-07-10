# Caaguazú Módulos

Plugin de WordPress con los módulos de contenido Noticias, Agenda, Ecosistema y Educación del portal [Caaguazú](https://caaguazu.net) — separados del theme para poder activarse/desactivarse y actualizarse de forma independiente.

Requiere el theme [`caaguazu-theme`](../caaguazu-theme/) para verse con la identidad visual del portal (templates, CSS, Customizer helpers). Sin él, las Entradas y el contenido siguen funcionando, solo que sin la presentación del theme.

Desde la 1.5.0, Noticias, Agenda y Educación **no** son custom post types propios: son Entradas nativas de WordPress (`post_type=post`) diferenciadas por Categoría, editables desde **Entradas → Todas las entradas** — ver `caaguazu-theme/README.md`, sección "Noticias/Agenda/Educación son Entradas, no CPTs", para el detalle de la arquitectura y la migración automática desde los CPTs viejos (`caaguazu_news`/`caaguazu_event`/`caaguazu_educacion`).

## Módulos incluidos

- **Noticias** (`includes/modules/module-noticias.php`) — categoría **Noticias** (+ 5 sub-categorías: Desarrollo/Cultura/Gobierno/Turismo/Comunidad) sobre Entradas nativas.
- **Agenda** (`includes/modules/module-agenda.php`) — categoría **Agenda** sobre Entradas nativas, con fecha/lugar como post meta.
- **Ecosistema** (`includes/modules/module-ecosistema.php`) — 3 tarjetas de sub-portales configurables desde **Apariencia → Personalizar → Contenido del Home**, más la página estática `ecosistema`.
- **Educación** (`includes/modules/module-educacion.php`) — categoría **Educación** (+ 4 sub-categorías: Escuelas/Becas/Programas/Estadísticas) sobre Entradas nativas, con un dato destacado opcional (`_caaguazu_edu_stat`, p. ej. "320 cupos"). Desde la 1.4 además se registra como **ecosistema** en el shell genérico del theme 3.0 (filtro `caaguazu_ecosystems`): dentro de Educación el sitio muestra header/tabbar propios con paleta tinta/pizarra, con las 4 sub-categorías como secciones.

**Desde la 1.9, ningún módulo siembra posts de demostración al activarse** (antes Noticias sembraba 5, Agenda 4, Educación 4) — un portal cívico no debería mostrar contenido inventado como si fuera real. Los tres siguen asegurando que sus categorías existan y migrando contenido de los CPTs viejos si corresponde; un sitio recién instalado arranca con esas categorías vacías, y el theme muestra un estado vacío honesto en vez de un fallback de relleno. Un sitio que ya tenía las demos viejas publicadas las recibe movidas a la Papelera (nunca borrado permanente) en su próxima visita a wp-admin — ver `caaguazu_modulos_trash_legacy_demo_content()` en `caaguazu-modulos.php` y la sección "Contenido honesto: sin posts demo, sin fallbacks inventados" de `caaguazu-theme/README.md`.

Cada módulo se registra solo en el nav y los accesos rápidos del theme vía los filtros `caaguazu_quick_access_items`/`caaguazu_nav_items` — ver `caaguazu-theme/README.md`, sección "Arquitectura de módulos".

Las tarjetas del hub Ecosistema del home son dinámicas desde la 1.4 (`caaguazu_modulos_ecosystem_cards()`): las de los ecosistemas internos (Turismo, Educación, los que vengan) salen solas del registry del theme, y los 3 slots del Customizer quedan para sub-portales externos (CEAD; el tercer slot arranca vacío = oculto). Ojo: los slots son posicionales — un sitio que haya personalizado el slot 0 cuando su default era Turismo verá esa tarjeta duplicada; se corrige vaciándola en el Customizer.

Desde la 1.7, la imagen de la tarjeta de Educación (y, del lado del theme, la portada del hub de Turismo + su propia tarjeta) son editables desde **Personalizar → Contenido del Home** en vez de estar fijas en el código — ver "Imágenes del sitio" en `caaguazu-theme/README.md`.

El plugin se auto-actualiza desde los GitHub Releases del repo (`includes/updater.php`), comparando su versión contra el `manifest.json` del release — ver `caaguazu-theme/README.md`, sección "Actualizaciones".

## Agregar un módulo nuevo

Copiar `includes/modules/module-ecosistema.php` como plantilla (es el más simple de los cuatro) y sumar su require en `caaguazu-modulos.php`. Si el módulo amerita identidad propia (header/tabbar/paleta), registrarlo además como ecosistema — `module-educacion.php` es el ejemplo completo.
