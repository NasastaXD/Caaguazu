# Caaguazú — Theme WordPress

Theme classic (no FSE) del portal oficial del departamento de Caaguazú, Paraguay. Sin dependencias de plugins.

## Instalación

1. Copiar/symlinkear esta carpeta (`caaguazu-theme/`) dentro de `wp-content/themes/` de tu instalación de WordPress (o generar un zip con `bin/build-zip.sh` desde la raíz del repo y subirlo desde **Apariencia → Temas → Añadir nuevo → Subir tema**).
2. Activarlo. Al activar se siembran automáticamente:
   - 5 noticias demo (si no hay ninguna publicada todavía) — ver `inc/demo-content.php`.
   - La sección **Turismo** completa: 25 páginas migradas del sitio de turismo, con jerarquía de secciones/subpáginas — ver `inc/tourism-seeder.php`. Reimportable sin desactivar el theme desde **Apariencia → Caaguazú**.
   - 4 eventos demo en la agenda (`inc/demo-events.php`) y 4 perfiles en el directorio de artesanos (`inc/demo-artisans.php`).
3. Ir a **Ajustes → Enlaces permanentes** y guardar (refresca las rewrite rules para el CPT `noticias` y las páginas anidadas de Turismo).
4. Crear las páginas con estos slugs exactos (en blanco, el theme las renderiza con su hero default):
   - `sobre-caaguazu`
   - `servicios`
   - `ecosistema`
   - `contacto` — asignarle el **Template "Página de contacto"** (Atributos de página) para que muestre el formulario con envío real de email.
   - `reportar` — asignarle el **Template "Reportá un problema"** para habilitar el formulario de reportes ciudadanos.
   *(No crear `noticias` ni `turismo` como página: el CPT de noticias y el seeder de turismo ya reservan esos slugs.)*
5. En **Apariencia → Menús**, crear un menú con esas páginas + Noticias + Turismo y asignarlo a "Menú principal" y "Menú móvil (drawer)". Si no se crea, el theme cae en un menú por defecto que ya incluye Turismo.
6. En **Ajustes → Lectura**, "Tu página de inicio muestra → Una página estática → Página de inicio: (crear una página vacía llamada Inicio)". El theme detecta `is_front_page()` y carga `front-page.php` con todas las secciones.

## Funcionalidades

- **Quiz del home**: pregunta con 5 opciones que resuelve, del lado del servidor, a dos links recomendados por perfil (residente/visita/inversor/estudiante/otro) — `front-page.php` + `assets/js/main.js`.
- **Reportá un problema**: formulario público sin login (bache, alumbrado, basura, etc.), guardado como CPT interno `caaguazu_report` (no público, gestión desde wp-admin) con notificación por email — `inc/cpt-report.php`, `inc/report-form.php`, template `page-reportar`.
- **Contacto**: formulario con envío real de email vía `wp_mail()` (antes solo texto estático) — `inc/contact-form.php`, template `page-contacto`.
- **Búsqueda**: filtros reales por tipo de contenido (Todos/Páginas/Noticias) y, dentro de Noticias, por categoría — `inc/search-filters.php`.
- **Idioma ES/GN**: selector funcional para strings fijas de interfaz (nav, footer, quiz, formularios, chips de búsqueda). Estrategia cache-safe: se sirve siempre el mismo HTML (ambos idiomas presentes, uno oculto por CSS) y el toggle ocurre 100% en el navegador, persistido en `localStorage` — `inc/i18n.php`. El botón **EN** queda visible pero inerte ("Próximamente"). No se traduce contenido editable vía Customizer ni contenido de páginas/noticias/Turismo (texto libre).
- **Formularios anti-spam**: honeypot + rate-limit por IP, compartidos por reporte, contacto y newsletter — `inc/spam-guard.php`, `inc/mailer.php`.
- **Glosario guaraní** (F1): shortcode `[gn]término[/gn]` con tooltip accesible (hover/foco). Diccionario en `inc/glossary.php`; la página "Guaraní en nuestra ciudad" (Turismo) lista los 10 términos completos.
- **Lightbox de galería** (F2): cualquier `.gallery-grid img` (la galería de Turismo) abre un `<dialog>` nativo con navegación por teclado, sin librerías externas.
- **Reportes en números** (F3): contador público de reportes recibidos/atendidos/del mes en la página de reportar — `caaguazu_report_stats()` en `inc/cpt-report.php`. No expone el contenido de los reportes.
- **SEO/Open Graph básico** (F4): meta description, canonical, OG y Twitter Card — `inc/seo.php`.
- **Agenda de eventos** (F5): CPT `caaguazu_event` público con fecha/lugar, archive `/agenda/`, card de "Próximo evento" en el home — `inc/cpt-event.php`.
- **Directorio de artesanos** (F6): CPT `caaguazu_artisan` público con oficio/zona/frase destacada, archive `/artesanos/` — `inc/cpt-artisan.php`. La página estática de Turismo enlaza al directorio en vez de listar perfiles fijos.
- **Mapa interactivo** (F7): shortcode `[caaguazu_mapa]` con Leaflet — única dependencia externa del theme (CDN), cargada solo en la página que usa el shortcode (`inc/map.php`).
- **Compartir** (F8): WhatsApp/X/Facebook/copiar link en noticias y eventos — `caaguazu_share_buttons()` en `inc/helpers.php`.
- **Búsqueda instantánea** (F9): sugerencias progresivas vía el endpoint core `/wp-json/wp/v2/search` (ya cubre Páginas/Noticias/Eventos/Artesanos por ser `show_in_rest`), sin endpoint propio.
- **Newsletter** (F10): captura de email en el footer, guardada en el CPT interno `caaguazu_subscriber` (sin integración con un ESP externo todavía) + link RSS visible.

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
- El mapa interactivo (`[caaguazu_mapa]`) usa coordenadas aproximadas del centro de Caaguazú (`inc/map.php`) — ajustar con ubicaciones reales cuando estén disponibles.

## Próximos pasos sugeridos

- Reemplazar las imágenes Unsplash y el video Pexels por assets oficiales subidos a Media Library.
- Ampliar el diccionario de `inc/i18n.php` si se decide traducir también el contenido editorial (hoy solo cubre strings fijas de interfaz).
- Conectar el newsletter (`caaguazu_subscriber`) a un proveedor de email real (Mailchimp, Brevo, etc.) cuando el departamento decida uno.
- Cargar coordenadas reales para el mapa interactivo y, si se suman más puntos, considerar agruparlos (clustering).
