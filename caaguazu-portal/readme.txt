=== Caaguazú Portal — Promotores Turísticos ===
Contributors: municipalidadcaaguazu
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 3.0.0
License: GPLv2 or later

Panel autenticado tipo app (PWA) bajo /turismo-panel, con enrutador propio, login propio, roles y flujo editorial (fichas de destino: borrador → revisión → publicación).

== Description ==

Plugin del Portal de Promotores Turísticos de Caaguazú. Monta un panel sobre rutas propias
(no usa el theme para el panel) con sensación de aplicación: sidebar + topbar + contenido,
instalable como PWA, con modo claro/oscuro y los colores del sitio heredados vía tokens CSS.

**Fase 0 — Framework del panel**
* Enrutador propio (rewrite rules) con guards: `/czu-login`, `/registro`, `/recuperar`, `/recuperar/restablecer`, `/salir`, invitación `/i/{token}`, PWA (`/promotur-manifest.webmanifest`, `/promotur-sw.js`, `/promotur-icon-{n}.png`, `/promotur-offline`) y panel `/panel/...`.
* Shell único + contrato de página (`$page_title` + `$body` + `include shell.php`).
* Sidebar y topbar dinámicos, gateados por capability (no por rol).
* Tokens CSS que heredan del theme con fallback; modo claro/oscuro persistente.
* PWA instalable con lectura offline; override de templates desde el theme en `/<theme>/promotur/<ruta>.php`.
* Roles: Promotor, Mini Promotor, Visitante (capabilities `promotur_*`).

**Fase 1 — MVP editorial**
* CPT Destino (ficha guiada) + taxonomías (categoría, zona, etiqueta).
* Editor con checklist de mínimos en vivo (bloquea el envío si falta algo) + subida de fotos + geolocalización.
* Flujo: borrador → enviar → cola de revisión (asignarme) → aprobar/devolver con feedback → publicado.
* "Mis contenidos" (Mini Promotor) y "Cola de revisión" (Promotor) funcionando.

== Instalación ==

1. Subir `caaguazu-portal` a `/wp-content/plugins/` y activar.
2. Se crean los roles y se vacían (flush) las rewrite rules automáticamente.
3. Entrar a `/czu-login`, crear una cuenta o iniciar sesión, e ir a `/panel`.

Convive con el plugin `caaguazu-locales` sin colisiones (prefijos distintos).

== Auto-actualización ==

El plugin se actualiza desde wp-admin sin pasar por WordPress.org, usando
plugin-update-checker (vendoreado en `vendor/`) contra los GitHub Releases de
`NasastaXD/Caaguazu`. Al mergear a `main`, el job `portal` de
`.github/workflows/release.yml` lee la versión del header, empaqueta
`caaguazu-portal.zip` y publica el release `v{version}`; el checker lo detecta
(~cada 12 h) y ofrece la actualización.

En ese repositorio también se publica el theme del sitio, con su propia versión
y su propio zip. Para que no se confundan, el updater sólo considera un release
que traiga adjunto `caaguazu-portal.zip` — la regla no depende de cómo se
llamen los tags.

* Versión en un solo lugar: header `Version:` + constante `PROMOTUR_VERSION` (semver).
* Migraciones de BD: incrementar `PROMOTUR_DB_VERSION`; corren solas en `admin_init`
  vía `promotur_run_migrations()`.
* Repo privado: definir `PROMOTUR_GITHUB_TOKEN` (PAT de solo lectura) en `wp-config.php`.

== Changelog ==

= 3.0.0 =
* **El panel entero se muda a `/turismo-panel`**: secciones, acceso (`/turismo-panel/entrar`), invitaciones (`/turismo-panel/i/<token>`) y PWA (`manifest.webmanifest`, `sw.js`, `icon-<n>.png`, `offline`) cuelgan de ahí. Las rutas viejas —`/turismo/panel`, `/czu-login`, `/registro`, `/recuperar`, `/salir`, `/i/<token>` y los archivos `promotur-*` de la raíz— responden 301 a su equivalente nueva, así no se rompen las invitaciones ya enviadas ni la PWA instalada.
* **Rediseño visual completo**: sistema propio de tokens (tinta única como acento, tres radios, una sombra, una tipografía), menú lateral agrupado con submenú, migas de pan, barra inferior en teléfono y modo oscuro. Sin framework y sin dependencias nuevas.
* **Tipografía servida desde el plugin** (Inter, 3 variantes, 76 KB): el panel deja de heredar las fuentes del theme y de depender de un CDN.
* **El panel deja de heredar el CSS del theme activo**: se desencola en las rutas del panel. El sitio público se rehace sin poder romper el panel.
* `PROMOTUR_Stats::serie_diaria()`: actividad editorial por día leída del log de auditoría (una sola consulta agrupada), para las barras del inicio.
* Atajo ⌘K / Ctrl+K para el buscador, y submenú plegable en el lateral.
* **Poda del sitio viejo**: se fue todo lo que existía para alimentar la web pública que este plugin publicaba — la vitrina y sus 7 shortcodes, la ficha pública, las reseñas y consultas de visitantes, la curaduría de portada, el SEO/Open Graph, la integración con el nav del theme viejo, y las secciones Moderación y Curaduría del panel. Con eso se van también Leaflet por CDN y qrcode.js. El destino deja de ser una página web (`public => false`): su consumidor es la app, que lo lee por `/wp-json/czu-app/v1/inventario`.
* **Sección App**: el panel pasa a ser la cabina de mando de la aplicación móvil. Desde ahí se editan los textos de la app por idioma (ES/EN/GN), el manifiesto de medios y el icono y color de cada categoría — todo lo que la app lee del servidor y antes tenía endpoint pero no editor. Requiere `caaguazu-app-api` 0.2.0; sin ese plugin la sección no se registra.
* Repaso completo de textos: los 569 mensajes del panel revisados y reescritos por una persona (ver `docs/textos-del-panel.md` en el repo).
* Se sacan los `alert()` del navegador: los errores se dicen en la pantalla, donde pasó la cosa.
* **El auto-updater pasa a leer los releases de `NasastaXD/Caaguazu`**, donde vive ahora el codigo. Como en ese repo tambien se publica el theme del sitio, el updater filtra por release que traiga adjunto `caaguazu-portal.zip`: nunca se come un release del theme creyendo que es suyo.

= 1.1.3 =
* Integración con el shell propio de Turismo del theme Caaguazú (`caaguazu_tourism_shell_items`): agrega "Destinos" (desplegable con las categorías reales de `promotur_categoria`) y, solo para usuarios logueados con el permiso `promotur_view_panel`, un link directo al panel de promotor.

= 1.1.0 =
* Registro INVITE-ONLY con teléfono obligatorio; invitaciones en tabla custom con link corto /i/<token>.
* Gestión en wp-admin: Usuarios (editar/eliminar/suspender), Invitaciones y Logs (usuarios y posts) sobre una tabla de auditoría.
* Suspensión reversible de usuarios. Sección "Ayuda" en el panel. Barra de navegación inferior en móvil y pulido del modo claro.

= 1.0.0 =
* Fase 0 (framework del panel) + Fase 1 (MVP editorial).
