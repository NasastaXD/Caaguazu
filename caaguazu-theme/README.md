# Caaguazú — Theme WordPress

Theme classic (no FSE) del portal oficial del departamento de Caaguazú, Paraguay. Sin dependencias de plugins.

## Instalación

1. Copiar/symlinkear esta carpeta (`caaguazu-theme/`) dentro de `wp-content/themes/` de tu instalación de WordPress (o generar un zip con `bin/build-zip.sh` desde la raíz del repo y subirlo desde **Apariencia → Temas → Añadir nuevo → Subir tema**).
2. Activarlo. Al activar se siembran automáticamente:
   - 5 noticias demo (si no hay ninguna publicada todavía) — ver `inc/demo-content.php`.
   - La sección **Turismo** completa: 25 páginas migradas del sitio de turismo, con jerarquía de secciones/subpáginas — ver `inc/tourism-seeder.php`. Reimportable sin desactivar el theme desde **Apariencia → Caaguazú**.
3. Ir a **Ajustes → Enlaces permanentes** y guardar (refresca las rewrite rules para el CPT `noticias` y las páginas anidadas de Turismo).
4. Crear las páginas con estos slugs exactos (en blanco, el theme las renderiza con su hero default):
   - `sobre-caaguazu`
   - `servicios`
   - `transparencia`
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
- **Formularios anti-spam**: honeypot + rate-limit por IP, compartidos por reporte y contacto — `inc/spam-guard.php`, `inc/mailer.php`.

## Edición de contenido

- **Home** (hero, identidad, ecosistema, audiencias, transparencia, footer/contacto): **Apariencia → Personalizar → Contenido del Home**.
- **Noticias**: menú lateral **Noticias** (CPT propio). Cada noticia tiene categoría (taxonomía `caaguazu_news_cat`) y un campo "Minutos de lectura" en la sidebar.
- **Turismo**: páginas normales de WordPress bajo `/turismo/...`; editables como cualquier página. Re-importar desde **Apariencia → Caaguazú** no pisa páginas ya editadas.
- **Reportes ciudadanos**: menú lateral **Reportes** — no público en el sitio, solo gestión interna. Estado de triage = estado nativo de WP (Pendiente de revisión → Publicar/Papelera).
- **Logo**: Personalizar → Identidad del sitio → Logotipo.

## Notas

- **`<body data-page="...">`** se mantiene; lo setea `header.php` vía `caaguazu_current_page_slug()`. El JS lo usa para activar sticky/parallax solo en home.
- El **buscador del header** apunta a `/?s=` (buscador nativo de WP).
- El **CPT noticias** está expuesto en REST (`show_in_rest: true`); el CPT de reportes (`caaguazu_report`) **no** lo está, a propósito.
- `wp_mail()` en hosting compartido sin SMTP configurado puede fallar o caer en spam — para producción se recomienda un plugin de SMTP (no es una dependencia del theme, solo de la entrega del correo).

## Próximos pasos sugeridos

- Reemplazar las imágenes Unsplash y el video Pexels por assets oficiales subidos a Media Library.
- Cargar contenido real de transparencia/trámites/licitaciones cuando esté disponible (fuera de este esfuerzo por depender de aprobación institucional).
- Ampliar el diccionario de `inc/i18n.php` si se decide traducir también el contenido editorial (hoy solo cubre strings fijas de interfaz).
