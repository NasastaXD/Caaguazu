# Caaguazú — Theme WordPress

Theme classic (no FSE) del portal oficial del departamento de Caaguazú, Paraguay. Sin dependencias de plugins.

## Instalación

1. Copiar/symlinkear esta carpeta (`caaguazu-theme/`) dentro de `wp-content/themes/` de tu instalación de WordPress (o generar un zip con `bin/build-zip.sh` desde la raíz del repo y subirlo desde **Apariencia → Temas → Añadir nuevo → Subir tema**).
2. Activarlo. Al activar se siembran automáticamente, sin ningún paso manual — el sitio queda navegable de punta a punta sin 404s desde el primer momento:
   - Las páginas requeridas por el theme, en blanco (`sobre-caaguazu`, `servicios`, `ecosistema`, `contacto` con su template de formulario, `reportar` con su template de reportes) — ver `inc/core-pages-seeder.php`. Al estar vacías, `page.php` les pinta un hero default + "En construcción" hasta que el admin cargue el contenido real.
   - **Inicio** como página estática de portada (`Ajustes → Lectura`), solo si el sitio no tenía ya una portada configurada — mismo archivo. El theme detecta `is_front_page()` y carga `front-page.php` con todas las secciones.
   - 5 noticias demo (si no hay ninguna publicada todavía) — ver `inc/demo-content.php`.
   - La sección **Turismo** completa: 25 páginas migradas del sitio de turismo, con jerarquía de secciones/subpáginas — ver `inc/tourism-seeder.php`. Reimportable sin desactivar el theme desde **Apariencia → Caaguazú**.
   - 4 eventos demo en la agenda (`inc/demo-events.php`) y 4 perfiles en el directorio de artesanos (`inc/demo-artisans.php`).
3. Ir a **Ajustes → Enlaces permanentes** y guardar (refresca las rewrite rules para el CPT `noticias` y las páginas anidadas de Turismo).
4. En **Apariencia → Menús**, crear un menú con las páginas sembradas + Noticias + Turismo y asignarlo a "Menú principal" y "Menú móvil (drawer)". Si no se crea, el theme cae en un menú por defecto que ya incluye Turismo.

## Funcionalidades

- **Quiz del home**: pregunta con 5 opciones que resuelve, del lado del servidor, a dos links recomendados por perfil (residente/visita/inversor/estudiante/otro) — `front-page.php` + `assets/js/main.js`.
- **Reportá un problema**: formulario público sin login (bache, alumbrado, basura, etc.), guardado como CPT interno `caaguazu_report` (no público, gestión desde wp-admin) con notificación por email — `inc/cpt-report.php`, `inc/report-form.php`, template `page-reportar`.
- **Contacto**: formulario con envío real de email vía `wp_mail()` (antes solo texto estático) — `inc/contact-form.php`, template `page-contacto`.
- **Búsqueda**: filtros reales por tipo de contenido (Todos/Páginas/Noticias) y, dentro de Noticias, por categoría — `inc/search-filters.php`.
- **Idioma ES/GN**: selector funcional para strings fijas de interfaz (nav, footer, quiz, formularios, chips de búsqueda). Estrategia cache-safe: se sirve siempre el mismo HTML (ambos idiomas presentes, uno oculto por CSS) y el toggle ocurre 100% en el navegador, persistido en `localStorage` — `inc/i18n.php`. El botón **EN** queda visible pero inerte ("Próximamente"). No se traduce contenido editable vía Customizer ni contenido de páginas/noticias/Turismo (texto libre).
- **Formularios anti-spam**: honeypot + rate-limit por IP, compartidos por reporte, contacto y newsletter — `inc/spam-guard.php`, `inc/mailer.php`.
- **Accesos rápidos + tabbar móvil**: grid de accesos tipo dashboard de app en el home (`caaguazu_render_quick_access()`) y una barra fija inferior de navegación en móvil (`caaguazu_render_tabbar()`, sitewide vía `header.php`), para que el sitio se navegue como un portal de servicios y no como un blog. Ambos leen de `caaguazu_quick_access_items()` en `inc/helpers.php` — sumar una sección nueva (ej. educación, cuando tenga su propia página/CPT) es agregar una línea en ese array.
- **Glosario guaraní** (F1): shortcode `[gn]término[/gn]` con tooltip accesible (hover/foco). Diccionario en `inc/glossary.php`; la página "Guaraní en nuestra ciudad" (Turismo) lista los 10 términos completos.
- **Lightbox de galería** (F2): cualquier `.gallery-grid img` (la galería de Turismo) abre un `<dialog>` nativo con navegación por teclado, sin librerías externas.
- **Reportes en números** (F3): contador público de reportes recibidos/atendidos/del mes en la página de reportar — `caaguazu_report_stats()` en `inc/cpt-report.php`. No expone el contenido de los reportes.
- **SEO/Open Graph básico** (F4): meta description, canonical, OG y Twitter Card — `inc/seo.php`.
- **Agenda de eventos** (F5): CPT `caaguazu_event` público con fecha/lugar, archive `/agenda/`, card de "Próximo evento" en el home — `inc/cpt-event.php`.
- **Directorio de artesanos** (F6): CPT `caaguazu_artisan` público con oficio/zona/frase destacada, archive `/artesanos/` — `inc/cpt-artisan.php`. La página estática de Turismo enlaza al directorio en vez de listar perfiles fijos.
- **Mapa de puntos históricos** (F7): shortcode `[caaguazu_mapa_puntos]` con Leaflet — única dependencia externa del theme (CDN), cargada solo en la página que usa el shortcode (`inc/map.php`). Se llama distinto de `[caaguazu_mapa]` para no chocar con el shortcode homónimo del plugin Caaguazú Locales (ver sección "Ecosistema de turismo").
- **Compartir** (F8): WhatsApp/X/Facebook/copiar link en noticias y eventos — `caaguazu_share_buttons()` en `inc/helpers.php`.
- **Búsqueda instantánea** (F9): sugerencias progresivas vía el endpoint core `/wp-json/wp/v2/search` (ya cubre Páginas/Noticias/Eventos/Artesanos por ser `show_in_rest`), sin endpoint propio.
- **Newsletter** (F10): captura de email en el footer, guardada en el CPT interno `caaguazu_subscriber` (sin integración con un ESP externo todavía) + link RSS visible.

## Ecosistema de turismo (plugins Caaguazú Locales + Caaguazú Portal)

El theme está preparado para convivir en el mismo sitio con dos plugins que antes vivían en una instalación aparte (`turismo.caaguazu.net`), versionados en el repo [`nasastaxd/turismo`](https://github.com/nasastaxd/turismo) — **no se tocó su código, se instalan tal cual**:

- **Caaguazú Locales**: directorio de negocios turísticos con reservas por WhatsApp, mapa editable, reseñas con cuentas y panel de dueños.
- **Caaguazú Portal — Promotores Turísticos**: panel tipo app para que promotores publiquen destinos (flujo borrador → revisión → publicación), con vitrina pública embebible vía shortcodes.

Integración ya resuelta del lado del theme:

- Las páginas migradas de Turismo ya incluyen los shortcodes del plugin Locales: `[caaguazu_locales tipo="restaurante"]` en "Dónde comer", `tipo="hotel"]` en "Dónde alojarte", y `[caaguazu_mapa]` (mapa de negocios) junto al `[caaguazu_mapa_puntos]` propio en "Mapa interactivo". El hub de Turismo incluye `[promotur_destacados]` (destinos curados por promotores).
- `single-cgz_local.php` — perfil de un local con la identidad visual del theme (el plugin lo detecta solo vía `locate_template()`, sin configuración).
- El plugin Portal usa su propio template para `promotur_destino` (no lo pisamos); en cambio se agregaron 6 clases CSS de compatibilidad (`.container-wide`, `.section-y`, `.text-h1`, `.text-h3`, `.prose-content`) para que se vea bien sin forkear ese archivo.
- Tokens CSS aliasados en `main.css` (`--flag-green`, `--ink`, `--paper`, `--text-primary`, etc. → los propios del theme) para que ambos plugins hereden la paleta real en vez de sus fallbacks genéricos.
- `inc/seo.php` no duplica meta OG/Twitter en `promotur_destino` (el plugin ya las inyecta).
- El footer suma links a "Locales y reservas" y "Mi cuenta" **solo si el plugin Locales está activo** (`post_type_exists('cgz_local')`).

Cosas a tener en cuenta al instalar ambos plugins en el sitio:

- El plugin Portal redirige `wp-login.php` a `/login` para usuarios no-admin (afecta a todo el sitio, no solo a sus rutas) y reserva los slugs `/login`, `/registro`, `/recuperar`, `/salir`, `/i/{token}`, `/panel(/...)` — evitar crear páginas con esos slugs.
- El plugin Locales crea sus propias páginas `/cuenta/` y `/panel-de-mi-local/` al activarse — no hace falta crearlas a mano.
- Ambos plugins traen su propio auto-updater apuntando a `nasastaxd/turismo` (independiente del `inc/updater.php` de este theme, que apunta a este repo).

## Actualizaciones

El theme se actualiza solo desde GitHub, sin plugins de terceros: `inc/updater.php` chequea `github.com/NasastaXD/Caaguazu/releases/latest` y, si hay una versión más nueva que la instalada, aparece en **Escritorio → Actualizaciones** como cualquier otro theme ("Actualizar ahora").

Para publicar una actualización:
1. Subir el número de `Version:` en `caaguazu-theme/style.css`.
2. Mergear ese cambio a `main`.
3. Listo — `.github/workflows/release.yml` arma `caaguazu-theme.zip` (con `bin/build-zip.sh`) y crea el Release en GitHub solo, sin pasos manuales de tag.

Los sitios con el theme instalado lo detectan en su próximo chequeo (cron cada 12h, o al abrir Actualizaciones y tocar "Buscar de nuevo").

## Edición de contenido

- **Home** (hero, identidad, números, ecosistema, audiencias, footer/contacto): **Apariencia → Personalizar → Contenido del Home**.
- **Noticias**: menú lateral **Noticias** (CPT propio). Cada noticia tiene categoría (taxonomía `caaguazu_news_cat`) y un campo "Minutos de lectura" en la sidebar.
- **Turismo**: páginas normales de WordPress bajo `/turismo/...`; editables como cualquier página. Re-importar desde **Apariencia → Caaguazú** no pisa páginas ya editadas.
- **Reportes ciudadanos**: menú lateral **Reportes** — no público en el sitio, solo gestión interna. Estado de triage = estado nativo de WP (Pendiente de revisión → Publicar/Papelera).
- **Agenda**: menú lateral **Eventos** (CPT público). Cada evento tiene fecha y lugar en la sidebar.
- **Artesanos**: menú lateral **Artesanos** (CPT público). Cada perfil tiene oficio, zona y una frase destacada en la sidebar.
- **Suscriptores**: menú lateral **Suscriptores** — lista de emails del newsletter, no pública.
- **Logo**: Personalizar → Identidad del sitio → Logotipo.

## Notas

- **`<body data-page="...">`** se mantiene; lo setea `header.php` vía `caaguazu_current_page_slug()`. El JS lo usa para activar sticky/parallax solo en home.
- El **buscador del header** apunta a `/?s=` (buscador nativo de WP).
- Los CPT de noticias, eventos y artesanos están expuestos en REST (`show_in_rest: true`); los de reportes y suscriptores **no**, a propósito (gestión interna).
- `wp_mail()` en hosting compartido sin SMTP configurado puede fallar o caer en spam — para producción se recomienda un plugin de SMTP (no es una dependencia del theme, solo de la entrega del correo).
- El mapa de puntos históricos (`[caaguazu_mapa_puntos]`) usa coordenadas aproximadas del centro de Caaguazú (`inc/map.php`) — ajustar con ubicaciones reales cuando estén disponibles.
- **Nav con mega-menú**: `caaguazu_render_nav()`/`caaguazu_render_fallback_nav()` (`inc/helpers.php`) pintan un dropdown por cada item de nivel 1 con hijos, para llegar a destinos específicos (ej. Ykua La Patria) en un clic desde cualquier página. Turismo usa un mega-menú agrupado (`caaguazu_turismo_menu_groups()`) con accesos directos reales, no solo el hub. En el drawer móvil el submenú va siempre expandido e indentado (sin acordeón/JS extra).

## Próximos pasos sugeridos

- Reemplazar las imágenes Unsplash y el video Pexels por assets oficiales subidos a Media Library.
- Ampliar el diccionario de `inc/i18n.php` si se decide traducir también el contenido editorial (hoy solo cubre strings fijas de interfaz).
- Conectar el newsletter (`caaguazu_subscriber`) a un proveedor de email real (Mailchimp, Brevo, etc.) cuando el departamento decida uno.
- Cargar coordenadas reales para el mapa interactivo y, si se suman más puntos, considerar agruparlos (clustering).
