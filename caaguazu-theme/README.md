# Caaguazú — Theme WordPress

Theme classic (no FSE) del portal oficial del departamento de Caaguazú, Paraguay.

El theme se ocupa de presentación (templates, Customizer de Hero/Identidad, formularios de contacto/reporte). Los módulos de contenido (Noticias, Agenda, Ecosistema, Turismo) viven en dos plugins hermanos en la raíz del repo — [`caaguazu-modulos/`](../caaguazu-modulos/) y [`caaguazu-turismo/`](../caaguazu-turismo/) — para poder activarse/desactivarse y actualizarse sin tocar el theme. Ver "Arquitectura de módulos" más abajo.

## Instalación

1. Copiar/symlinkear `caaguazu-theme/`, `caaguazu-modulos/` y `caaguazu-turismo/` dentro de `wp-content/themes/` y `wp-content/plugins/` respectivamente (o generar los tres zips con `bin/build-zip.sh` desde la raíz del repo y subirlos desde **Apariencia → Temas → Añadir nuevo → Subir tema** / **Plugins → Añadir nuevo → Subir plugin**).
2. Activar el theme **y** los dos plugins. Al activarlos se siembra todo automáticamente, sin pasos manuales — el sitio queda navegable de punta a punta sin 404s:
   - El theme siembra `sobre-caaguazu` y `contacto` (con su template de formulario) en blanco, y configura **Inicio** como portada estática si el sitio no tenía ya una configurada — ver `inc/core-pages-seeder.php`.
   - `caaguazu-modulos` siembra 5 noticias demo, 4 eventos demo en la agenda, y la página `ecosistema` en blanco.
   - `caaguazu-turismo` siembra las 25 páginas de Turismo (historia, gastronomía, cultura guaraní) con su jerarquía de secciones/subpáginas. Reimportable sin desactivar el plugin desde **Apariencia → Caaguazú**.
   - Todas estas siembras corren tanto al activar (`register_activation_hook`/`after_switch_theme`) como, con un flag para no repetirse, en `admin_init` — así un sitio que ya tenía el theme o los plugins activos antes de que existiera alguna de estas siembras la recibe igual en la próxima actualización, sin tener que reactivar nada.
3. Ir a **Ajustes → Enlaces permanentes** y guardar (refresca las rewrite rules para los CPTs y las páginas anidadas de Turismo).
4. En **Apariencia → Menús**, crear un menú con las páginas sembradas + Noticias + Agenda + Turismo + Ecosistema y asignarlo a "Menú principal" y "Menú móvil (drawer)". Si no se crea, el theme cae en un menú de fallback armado con lo que cada plugin activo va registrando (ver abajo) — Sobre Caaguazú y Contacto son lo único que el theme trae de por sí.
5. El directorio de **Artesanos** (CPT `caaguazu_artisan`, `inc/cpt-artisan.php`) y las funciones de **Servicios**/**Reportá un problema** siguen viviendo en el theme, pero no se lanzan todavía a pedido del departamento (ver "Funcionalidades").

## Arquitectura de módulos (plugins)

Cada módulo de contenido (Noticias, Agenda, Ecosistema, Turismo) es un plugin que se registra solo en dos puntos de extensión que expone el theme (`inc/helpers.php`), sin que el theme necesite saber que ese módulo existe:

- **`apply_filters( 'caaguazu_quick_access_items', array() )`** — cada plugin agrega su propia tarjeta al grid de accesos rápidos del home. Item: `array( 'icon', 'label', 'url' )` — `icon` es una clave del sistema de íconos SVG del theme (`inc/icons.php`, ver más abajo), no un emoji.
- **`apply_filters( 'caaguazu_nav_items', array() )`** — cada plugin agrega su propio link al nav de fallback (el que se usa si el admin no configuró un menú real en Apariencia → Menús). Item: `array( 'slug', 'label', 'url', 'dropdown_cb' opcional )` — `dropdown_cb` es un callable que pinta un mega-menú propio (ver `caaguazu_render_turismo_dropdown()` en `caaguazu-turismo/includes/nav-integration.php` como ejemplo).

El theme solo aporta `sobre-caaguazu` (primero) y `contacto` (siempre último) de forma directa; todo lo demás lo suman los plugins con un simple `add_filter()`. Si un plugin se desactiva, el theme no fatal-ea: cada sección que depende de él (Ecosistema, la card de "Próximo evento", el grid de Noticias en el home) está detrás de un `function_exists()`/`post_type_exists()` en `front-page.php`, y simplemente no se muestra.

**Para agregar un módulo nuevo**: copiar `caaguazu-modulos/includes/modules/module-ecosistema.php` (el más simple de los tres) como plantilla — registrar su CPT/Customizer si aplica, y sumar sus dos `add_filter()` al final.

## Funcionalidades

- **Quiz del home**: pregunta con 5 opciones que resuelve, del lado del servidor, a dos links recomendados por perfil (residente/visita/inversor/estudiante/otro) — `front-page.php` + `assets/js/main.js`.
- **Reportá un problema**: formulario público sin login (bache, alumbrado, basura, etc.), guardado como CPT interno `caaguazu_report` (no público, gestión desde wp-admin) con notificación por email — `inc/cpt-report.php`, `inc/report-form.php`, template `page-reportar`.
- **Contacto**: formulario con envío real de email vía `wp_mail()` (antes solo texto estático) — `inc/contact-form.php`, template `page-contacto`.
- **Búsqueda**: filtros reales por tipo de contenido (Todos/Páginas/Noticias) y, dentro de Noticias, por categoría — `inc/search-filters.php`.
- **Idioma ES/GN**: selector funcional para strings fijas de interfaz (nav, footer, quiz, formularios, chips de búsqueda). Estrategia cache-safe: se sirve siempre el mismo HTML (ambos idiomas presentes, uno oculto por CSS) y el toggle ocurre 100% en el navegador, persistido en `localStorage` — `inc/i18n.php`. El botón **EN** queda visible pero inerte ("Próximamente"). No se traduce contenido editable vía Customizer ni contenido de páginas/noticias/Turismo (texto libre).
- **Formularios anti-spam**: honeypot + rate-limit por IP, compartidos por reporte, contacto y newsletter — `inc/spam-guard.php`, `inc/mailer.php`.
- **Accesos rápidos + tabbar móvil**: grid de accesos tipo dashboard de app en el home (`caaguazu_render_quick_access()`) y una barra fija inferior de navegación en móvil (`caaguazu_render_tabbar()`, sitewide vía `header.php`), para que el sitio se navegue como un portal de servicios y no como un blog. El grid se arma con `caaguazu_quick_access_items()` en `inc/helpers.php`, que combina lo que el theme trae (Contacto) con lo que cada plugin de módulo suma vía filtro (ver "Arquitectura de módulos"). El tabbar es una selección curada fija (Inicio/Buscar/Noticias/Turismo/Menú), no extensible por plugins.
- **Sistema de íconos propio (`inc/icons.php`)**: `caaguazu_icon( $clave )` devuelve un SVG de trazo simple (mismo estilo del ícono del banner de instalación) para una clave corta (`home`, `search`, `tree`, `wood`, `mail`, ...) — reemplaza los emojis que usaban antes el header, el tabbar, los accesos rápidos y el shell de Turismo, que no eran acordes a la identidad visual del sitio (colores/estilo de emoji fuera de nuestro control, y no reaccionan a los tokens de color). Como los SVG usan `stroke="currentColor"`, heredan el color de su contenedor — dentro de Turismo, por ejemplo, se ven en tono madera automáticamente sin ningún código extra. Los plugins de módulos pasan la clave del ícono (no un emoji) en el campo `icon` de sus items; si se pasa algo que no está en el mapa, se trata como HTML/emoji literal (compatibilidad con algún plugin de terceros).
- **Instalable como app (PWA)**: manifest y service worker mínimo servidos por rewrite rules en la raíz del sitio (`/caaguazu-manifest.webmanifest`, `/caaguazu-sw.js`) — `inc/pwa.php`. Mismo patrón que usa `caaguazu-portal` para su panel, pero para el sitio completo (`start_url`/`scope` = `/`). Un banner discreto (`wp_footer`) ofrece el prompt nativo de instalación en Chrome/Android (`beforeinstallprompt`) o el tip manual en iOS Safari (Compartir → Agregar a inicio), y se recuerda en `localStorage` si ya se cerró o instaló. Íconos en `assets/icons/` (192/512/180px). El service worker es intencionalmente mínimo: solo cachea la home como fallback offline, no pretende ser una app 100% offline.
- **Carrusel de Turismo en el home**: `caaguazu_render_turismo_carousel()` en `inc/helpers.php`, scroll horizontal con tarjetas que se asoman al borde (`.turismo-carousel`/`.turismo-card` en `main.css`). Depende de `caaguazu_tourism_page_url()` (plugin Caaguazú Turismo) — si el plugin no está activo, la sección no se imprime.
- **Hub de Turismo rediseñado**: la página raíz `turismo` (slug) recibe un tratamiento propio en `page.php` (`$is_tourism_hub`) en vez del hero angosto genérico — hero oscuro a sangre (`.tourism-hero-full`) con foto real e imagen de fondo con degradado, franja de estadísticas (`.stats-grid--compact`, reutilizada de `page-reportar.php`) y la grilla de secciones a ancho completo del `.container` en vez de acotada a los 760px de `.entry-content` (override vía body class `tourism-hub`). Inspirado en el landing del sitio de turismo independiente original (`nasastaxd/turismo`), adaptado a la paleta e identidad visual actuales. El contenido en sí vive en `caaguazu-turismo/includes/tourism-content.php`.
- **Shell propio de Turismo — patrón de "ecosistema" para reusar en el próximo módulo**: `inc/tourism-shell.php` define `caaguazu_is_tourism_context()` como único punto de verdad de "¿esto es parte de Turismo?" (páginas sembradas por el plugin, perfiles de Caaguazú Locales `cgz_local`, fichas de destino de Caaguazú Portal `promotur_destino`). Mientras ese contexto es verdadero, `header.php`/`footer.php` dejan de renderizar el chrome institucional y en su lugar pintan un header/tabbar/footer propios (`caaguazu_render_tourism_header()`/`caaguazu_render_tourism_tabbar()`), con su propio wordmark ("← Caaguazú" + "Turismo") y su propia navegación — poblada por el plugin vía el filtro `caaguazu_tourism_shell_items` (mismo patrón que `caaguazu_nav_items`/`caaguazu_quick_access_items`), nunca hardcodeada en el theme. La identidad visual distinta ("madera" en vez de "verde institucional") se logra reescribiendo `--green-deep`/`--green-forest` a `--wood-deep`/`--earth-bark` dentro de `body.tourism-page` — como todo el sistema de header/nav/tabbar/botones ya está armado sobre esas custom properties, el reskin es automático y no duplica CSS. `--accent-gold` se mantiene compartido a propósito, para que Turismo se sienta distinto pero no un sitio aparte. **Si se agrega un próximo "ecosistema"**: el patrón a copiar es exactamente este archivo (detector + header/tabbar propios + filtro de items + override de tokens con un color propio), no un framework genérico — hoy solo existe un caso real, así que no vale la pena abstraer más hasta tener un segundo.
- **`caaguazu_tourism_shell_items` también lo pueblan Locales y Portal** (repo `nasastaxd/turismo`), no solo `caaguazu-turismo`: `caaguazu-locales/includes/nav-integration.php` agrega "Dónde ir" (un link por tipo de local — restaurante/hotel/comercio/atracción, más filtrado real `?tipo=` en el archivo de `cgz_local` vía `pre_get_posts`); `caaguazu-portal/includes/nav-integration.php` agrega "Destinos" (desplegable con las categorías reales de la taxonomía `promotur_categoria`, editables en wp-admin) y, solo si el usuario logueado tiene el capability `promotur_view_panel` (mismo check que usa el guard real del panel), un link directo al panel de promotor — nunca se muestra un link que después dé 403. Cada ítem con `dropdown_cb` reusa el mismo `.nav-dropdown` de siempre; no hizo falta tocar el theme para esto, solo el nuevo ícono `user` en `inc/icons.php`. `caaguazu-turismo` mismo agrega, además de sus 6 secciones, un ítem "Mapa" (ícono `target`, no confundir con `map` que ya usa "Destinos") apuntando a la página `mapa-interactivo` ya sembrada — sin coordenadas nuevas ni lógica de mapa propia, solo un acceso directo desde el nav a lo que ya existía enterrado en "Planificá tu visita". Cualquier configuración real de puntos/coordenadas queda a cargo de quien administre el sitio (o un plugin de mapas dedicado), no del theme.
- **Servicios y Directorio de artesanos** existen en el código del theme (CPT `caaguazu_artisan`, formularios, templates) pero **no se lanzan todavía** a pedido del departamento — no están en el nav, el footer, los accesos rápidos ni el tabbar. Para reactivarlos: sumarles sus propios `add_filter( 'caaguazu_quick_access_items', ... )` / `add_filter( 'caaguazu_nav_items', ... )` (podés hacerlo directo en el theme o migrarlos a su propio plugin siguiendo el patrón de `caaguazu-modulos`).
- **Glosario guaraní** (F1): shortcode `[gn]término[/gn]` con tooltip accesible (hover/foco). Diccionario en `inc/glossary.php`; la página "Guaraní en nuestra ciudad" (Turismo) lista los 10 términos completos.
- **Lightbox de galería** (F2): cualquier `.gallery-grid img` (la galería de Turismo) abre un `<dialog>` nativo con navegación por teclado, sin librerías externas.
- **Reportes en números** (F3): contador público de reportes recibidos/atendidos/del mes en la página de reportar — `caaguazu_report_stats()` en `inc/cpt-report.php`. No expone el contenido de los reportes.
- **SEO/Open Graph básico** (F4): meta description, canonical, OG y Twitter Card — `inc/seo.php`.
- **Agenda de eventos** (F5): CPT `caaguazu_event` público con fecha/lugar, archive `/agenda/`, card de "Próximo evento" en el home — plugin `caaguazu-modulos` (`includes/modules/module-agenda.php`); el theme solo aporta `archive-caaguazu_event.php`/`single-caaguazu_event.php`.
- **Directorio de artesanos** (F6): CPT `caaguazu_artisan` público con oficio/zona/frase destacada, archive `/artesanos/` — `inc/cpt-artisan.php` (theme; ver nota de "no lanzado todavía" arriba).
- **Mapa de puntos históricos** (F7): shortcode `[caaguazu_mapa_puntos]` con Leaflet — única dependencia externa del theme (CDN), cargada solo en la página que usa el shortcode (`inc/map.php`). Se llama distinto de `[caaguazu_mapa]` para no chocar con el shortcode homónimo del plugin Caaguazú Locales (ver sección "Ecosistema de turismo").
- **Compartir** (F8): WhatsApp/X/Facebook/copiar link en noticias y eventos — `caaguazu_share_buttons()` en `inc/helpers.php`.
- **Búsqueda instantánea** (F9): sugerencias progresivas vía el endpoint core `/wp-json/wp/v2/search` (ya cubre Páginas/Noticias/Eventos/Artesanos por ser `show_in_rest`), sin endpoint propio.
- **Newsletter** (F10): captura de email en el footer, guardada en el CPT interno `caaguazu_subscriber` (sin integración con un ESP externo todavía) + link RSS visible.

## Ecosistema de turismo (plugins Caaguazú Locales + Caaguazú Portal)

El plugin `caaguazu-turismo` (que aporta el módulo Turismo) está preparado para convivir en el mismo sitio con dos plugins que antes vivían en una instalación aparte (`turismo.caaguazu.net`), versionados en el repo [`nasastaxd/turismo`](https://github.com/nasastaxd/turismo) — **no se toca su código, se instalan tal cual**:

- **Caaguazú Locales**: directorio de negocios turísticos con reservas por WhatsApp, mapa editable, reseñas con cuentas y panel de dueños.
- **Caaguazú Portal — Promotores Turísticos**: panel autenticado tipo app (login/roles/moderación propios) para que promotores publiquen destinos (flujo borrador → revisión → publicación), con vitrina pública embebible vía shortcodes.

Son plugins sustancialmente más grandes que un simple CPT (Portal en particular es una aplicación con su propio sistema de cuentas y moderación) — por eso `caaguazu-turismo` los **orquesta en vez de fusionar su código**: siembra sus propias 25 páginas de contenido y embebe los shortcodes de ambos donde corresponde, pero no toca ni depende de sus internals.

Integración ya resuelta:

- Las páginas de Turismo (sembradas por `caaguazu-turismo/includes/tourism-seeder.php`) ya incluyen los shortcodes del plugin Locales: `[caaguazu_locales tipo="restaurante"]` en "Dónde comer", `tipo="hotel"]` en "Dónde alojarte", y `[caaguazu_mapa]` (mapa de negocios) junto al `[caaguazu_mapa_puntos]` propio en "Mapa interactivo". El hub de Turismo incluye `[promotur_destacados]` (destinos curados por promotores).
- `caaguazu-theme/single-cgz_local.php` — perfil de un local con la identidad visual del theme (el plugin lo detecta solo vía `locate_template()`, sin configuración).
- El plugin Portal usa su propio template para `promotur_destino` (no lo pisamos); en cambio el theme trae 6 clases CSS de compatibilidad (`.container-wide`, `.section-y`, `.text-h1`, `.text-h3`, `.prose-content`) para que se vea bien sin forkear ese archivo.
- Tokens CSS aliasados en `main.css` (`--flag-green`, `--ink`, `--paper`, `--text-primary`, etc. → los propios del theme) para que ambos plugins hereden la paleta real en vez de sus fallbacks genéricos.
- `inc/seo.php` no duplica meta OG/Twitter en `promotur_destino` (el plugin ya las inyecta).
- El footer suma links a "Locales y reservas" y "Mi cuenta" **solo si el plugin Locales está activo** (`post_type_exists('cgz_local')`).

Cosas a tener en cuenta al instalar ambos plugins en el sitio:

- El plugin Portal redirige `wp-login.php` a `/login` para usuarios no-admin (afecta a todo el sitio, no solo a sus rutas) y reserva los slugs `/login`, `/registro`, `/recuperar`, `/salir`, `/i/{token}`, `/panel(/...)` — evitar crear páginas con esos slugs.
- El plugin Locales crea sus propias páginas `/cuenta/` y `/panel-de-mi-local/` al activarse — no hace falta crearlas a mano.
- Ambos plugins traen su propio auto-updater apuntando a `nasastaxd/turismo` (independiente del `inc/updater.php` de este theme, que apunta a este repo).

## Actualizaciones

El theme se actualiza solo desde GitHub, sin plugins de terceros: `inc/updater.php` chequea `github.com/NasastaXD/Caaguazu/releases/latest` y, si hay una versión más nueva que la instalada, aparece en **Escritorio → Actualizaciones** como cualquier otro theme ("Actualizar ahora"). Los plugins `caaguazu-modulos` y `caaguazu-turismo` **todavía no tienen auto-updater propio** — se reinstalan a mano bajando su zip del release (ver abajo) y subiéndolo desde **Plugins → Añadir nuevo → Subir plugin**.

Para publicar una actualización:
1. Subir el número de `Version:` en `caaguazu-theme/style.css`.
2. Mergear ese cambio a `main`.
3. Listo — `.github/workflows/release.yml` arma `caaguazu-theme.zip`, `caaguazu-modulos.zip` y `caaguazu-turismo.zip` (con `bin/build-zip.sh`) y crea el Release en GitHub con los tres como assets, sin pasos manuales de tag.

Los sitios con el theme instalado lo detectan en su próximo chequeo de WP (cron cada 12h) o al forzarlo a mano: el botón nativo "Volver a comprobar" de **Escritorio → Actualizaciones**, o el atajo **⟳ Buscar actualización** en la barra de admin (visible en cualquier pantalla), fuerzan un chequeo inmediato contra GitHub sin esperar el cache interno de 12h de `inc/updater.php` (antes de esto, el botón nativo no servía de nada porque ese cache seguía sirviendo el release viejo). Los plugins hay que resubirlos a mano cuando cambien.

## Edición de contenido

- **Home** (hero, identidad, números, footer/contacto): **Apariencia → Personalizar → Contenido del Home**. La sección Ecosistema tiene su propio panel ahí mismo, registrado por el plugin `caaguazu-modulos`.
- **Noticias**: menú lateral **Noticias** (CPT del plugin `caaguazu-modulos`). Cada noticia tiene categoría (taxonomía `caaguazu_news_cat`) y un campo "Minutos de lectura" en la sidebar.
- **Turismo**: páginas normales de WordPress bajo `/turismo/...`; editables como cualquier página. Re-importar desde **Apariencia → Caaguazú** no pisa páginas ya editadas.
- **Reportes ciudadanos**: menú lateral **Reportes** — no público en el sitio, solo gestión interna. Estado de triage = estado nativo de WP (Pendiente de revisión → Publicar/Papelera).
- **Agenda**: menú lateral **Eventos** (CPT del plugin `caaguazu-modulos`). Cada evento tiene fecha y lugar en la sidebar.
- **Artesanos**: menú lateral **Artesanos** (CPT del theme, no lanzado todavía). Cada perfil tiene oficio, zona y una frase destacada en la sidebar.
- **Suscriptores**: menú lateral **Suscriptores** — lista de emails del newsletter, no pública.
- **Logo**: Personalizar → Identidad del sitio → Logotipo.

## Notas

- **`<body data-page="...">`** se mantiene; lo setea `header.php` vía `caaguazu_current_page_slug()`. El JS lo usa para activar sticky/parallax solo en home.
- El **buscador del header** apunta a `/?s=` (buscador nativo de WP).
- Los CPT de noticias, eventos y artesanos están expuestos en REST (`show_in_rest: true`); los de reportes y suscriptores **no**, a propósito (gestión interna).
- `wp_mail()` en hosting compartido sin SMTP configurado puede fallar o caer en spam — para producción se recomienda un plugin de SMTP (no es una dependencia del theme, solo de la entrega del correo).
- El mapa de puntos históricos (`[caaguazu_mapa_puntos]`) usa coordenadas aproximadas del centro de Caaguazú (`inc/map.php`) — ajustar con ubicaciones reales cuando estén disponibles.
- **Nav con mega-menú**: `caaguazu_render_nav()`/`caaguazu_render_fallback_nav()` (`inc/helpers.php`) pintan un dropdown por cada item de nivel 1 con hijos (vía `dropdown_cb`), para llegar a destinos específicos (ej. Ykua La Patria) en un clic desde cualquier página. El plugin `caaguazu-turismo` suma un mega-menú agrupado (`caaguazu_render_turismo_dropdown()`) con accesos directos reales, no solo el hub — cada grupo es un `<details class="nav-dropdown-col" open>`: en desktop se queda abierto (nada lo toca, mismo comportamiento de siempre al hacer hover) y en el drawer móvil un script en `main.js` le saca el `open` en viewports angostos (`matchMedia('(min-width: 1024px)')`) para que arranque colapsado — se toca cada grupo para expandirlo, en vez de volcar las ~20 páginas de Turismo como una lista larga sin jerarquía. Sin JS, el drawer simplemente se ve como antes (todo expandido) — no hay regresión funcional.
- **UI de Turismo con cards y botones**: `caaguazu-turismo/includes/tourism-content.php` usa `.eco-card`/`.eco-grid` para grillas de atractivos con imagen (ej. "Qué hacer"), `.btn`/`.btn-primary`/`.btn-outline` para los CTA de navegación entre sub-páginas, y `.info-grid`/`.info-card` para datos cortos (ej. especies madereras) — todas clases CSS que ya vivían en el theme, reusadas para que Turismo se sienta como el resto del portal y no como un blog.

## Próximos pasos sugeridos

- Reemplazar las imágenes Unsplash y el video Pexels por assets oficiales subidos a Media Library.
- Ampliar el diccionario de `inc/i18n.php` si se decide traducir también el contenido editorial (hoy solo cubre strings fijas de interfaz).
- Conectar el newsletter (`caaguazu_subscriber`) a un proveedor de email real (Mailchimp, Brevo, etc.) cuando el departamento decida uno.
- Cargar coordenadas reales para el mapa interactivo y, si se suman más puntos, considerar agruparlos (clustering).
- Sumar auto-updater propio a `caaguazu-modulos` y `caaguazu-turismo` (mismo patrón que `inc/updater.php` del theme) para no tener que reinstalarlos a mano en cada release.
