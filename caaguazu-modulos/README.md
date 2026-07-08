# Caaguazú Módulos

Plugin de WordPress con los módulos de contenido Noticias, Agenda, Ecosistema y Educación del portal [Caaguazú](https://caaguazu.net) — separados del theme para poder activarse/desactivarse y actualizarse de forma independiente.

Requiere el theme [`caaguazu-theme`](../caaguazu-theme/) para verse con la identidad visual del portal (templates, CSS, Customizer helpers). Sin él, los CPTs y el contenido siguen funcionando, solo que sin la presentación del theme.

## Módulos incluidos

- **Noticias** (`includes/modules/module-noticias.php`) — CPT `caaguazu_news` + taxonomía `caaguazu_news_cat`, con demo de 5 noticias al activar.
- **Agenda** (`includes/modules/module-agenda.php`) — CPT `caaguazu_event` con fecha/lugar, con demo de 4 eventos al activar.
- **Ecosistema** (`includes/modules/module-ecosistema.php`) — 3 tarjetas de sub-portales configurables desde **Apariencia → Personalizar → Contenido del Home**, más la página estática `ecosistema`.
- **Educación** (`includes/modules/module-educacion.php`) — CPT `caaguazu_educacion` con taxonomía `caaguazu_edu_tipo` (Escuelas/Becas/Programas/Estadísticas) y un dato destacado opcional (`_caaguazu_edu_stat`, p. ej. "320 cupos"), con demo de 4 entradas (una por tipo) al activar.

Cada módulo se registra solo en el nav y los accesos rápidos del theme vía los filtros `caaguazu_quick_access_items`/`caaguazu_nav_items` — ver `caaguazu-theme/README.md`, sección "Arquitectura de módulos".

## Agregar un módulo nuevo

Copiar `includes/modules/module-ecosistema.php` como plantilla (es el más simple de los tres) y sumar su require en `caaguazu-modulos.php`.
