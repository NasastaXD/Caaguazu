=== Caaguazú Portal — Promotores Turísticos ===
Contributors: municipalidadcaaguazu
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 3.2.1
License: GPLv2 or later

Panel autenticado tipo app (PWA) bajo /turismo-panel, con enrutador propio, login propio, roles y flujo editorial para las tres cosas que la app muestra: fichas del inventario turístico, artículos y recorridos.

== Description ==

Plugin del Portal de Promotores Turísticos de Caaguazú. Panel autenticado tipo
app bajo `/turismo-panel`, con enrutador, login y sesión propios —no depende
de usuarios de WordPress, corre sobre el sistema de cuentas universal
`caaguazu-cuentas`—, instalable como PWA, con modo claro/oscuro y un sistema
de diseño propio (tokens, tipografía autohospedada): no hereda nada del theme
activo, y desencola su CSS en las rutas del panel.

El equipo escribe ahí las tres cosas que la app muestra —fichas del
inventario turístico, artículos y recorridos— con un mismo flujo editorial:
borrador → enviar → cola de revisión (asignarme) → aprobar/devolver con
feedback → publicado. wp-admin no interviene: ni la edición de contenido, ni
la cuenta, ni la galería, ni la estructura (categorías/zonas/etiquetas), ni el
equipo (roles, suspensión, invitaciones) tienen pantalla ahí. Lo único que
queda en wp-admin es el registro de auditoría y las actualizaciones del
plugin, y ninguna de las dos cosas la necesita nadie del equipo.

Roles: Promotor, Mini Promotor, Visitante (capabilities `promotur_*`).

== Instalación ==

1. Subir `caaguazu-portal` a `/wp-content/plugins/` y activar (necesita el
   plugin `caaguazu-cuentas` activo).
2. Se crean los roles y se vacían (flush) las rewrite rules automáticamente.
3. Entrar a `/turismo-panel/entrar`, con una cuenta creada por invitación.

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

= 3.2.1 =
* **Se apaga la pantalla nativa de wp-admin para Destinos, Artículos y
  Recorridos** (lista + editor de bloques) y para sus tres taxonomías —el
  panel es la única pantalla de edición desde la v3.0.0, pero la de
  wp-admin seguía viva por detrás sin que nadie la usara: cualquier usuario
  de WordPress con permiso de Autor podía editar contenido del panel sin
  pasar por el flujo editorial ni quedar en la auditoría.
* **`PROMOTUR_Audit::post_actions()` cubre los tres tipos.** Desde que
  Artículos y Recorridos entraron al flujo editorial (v3.2.0), sus eventos
  —creado, enviado, aprobado, publicado— quedaban afuera de la pestaña
  "Contenido" del registro de wp-admin: la lista de acciones sólo tenía las
  cinco de `destino_*`. Ahora se arma sola a partir de los tipos que declara
  `PROMOTUR_Editorial`.
* Dos funciones sin ningún llamador (`promotur_nav_items()`,
  `promotur_user_phone()`) y la descripción del plugin en este archivo, que
  todavía hablaba de tokens heredados del theme y de rutas de antes de la
  v3.0.0.

= 3.2.0 =
* **Artículos.** El panel gana la sección donde se escriben las notas que la app
  muestra: ante título, título, foto de portada con su pie, autor o autores,
  subtítulo, entradilla, cuerpo y fuentes, más categoría y etiquetas. Pasan por
  el mismo flujo de aprobación del staff que una ficha. El CPT
  `promotur_articulo` se muda desde `caaguazu-app-api` sin cambiar de nombre, así
  que no se pierde nada de lo ya cargado.
* **Recorridos.** Se arman eligiendo sitios del inventario turístico —no
  escribiendo lugares de nuevo—, hasta nueve, cada uno con el texto que lo
  acompaña (la historia, el dato curioso), un audio o video propio, y botones
  para subirlo o bajarlo en el orden del paseo. El recorrido entero puede llevar
  además sus audios y videos, y vincularse con artículos ya publicados. Mismo
  flujo editorial. La app recibe la ruta ya armada como enlace de Google Maps.
* **Inventario turístico.** Sección nueva: el catálogo de fichas publicadas del
  departamento, con sus datos. Es de donde los recorridos toman sus paradas, y
  hacía falta poder verlo entero antes de armar uno.
* **El flujo editorial deja de ser sólo de fichas.** Estados, checklist, cola de
  revisión, notificaciones, búsqueda, «Mis contenidos», reportes y portafolio
  miran ahora los tres tipos. Cada tipo declara qué campos tiene y qué mínimos
  exige; no hay un solo `if` por tipo en el flujo.
* **La ubicación de una ficha se carga con un enlace de Google Maps**, y las
  coordenadas quedan de alternativa. El pin se saca del enlace cuando el enlace
  lo trae (los cuatro formatos que escribe Google están cubiertos); si es un
  enlace corto, el panel lo dice y pide las coordenadas a mano. La app se apoya
  en Google Maps para llevar a la gente hasta el lugar, así que el enlace es el
  dato que de verdad se usa.
* **Se van cinco campos de la ficha**: cómo llegar, referencia, temporada ideal,
  servicios y duración sugerida. Se llenaban con frases genéricas que no ayudaban
  a decidir nada, y cómo llegar lo resuelve el enlace de Google Maps mejor que un
  párrafo. Los datos ya cargados **no se borran**: la ficha deja de pedirlos, de
  mostrarlos y de publicarlos, y el valor queda en su meta.
* **La sección App queda fuera de circulación.** Llamaba a tres métodos de
  `caaguazu-app-api` que existen desde su versión 0.2.0, contra la 0.1.0 que hay
  instalada — y `class_exists()` no distingue una versión de otra, así que la
  pantalla moría con un error fatal apenas se abría. El código queda en el repo,
  con su plantilla y una guarda, para volver a enchufarlo de una línea.
* **`tools/verificar-logica.php`**: comprobaciones de las dos funciones que
  transforman un dato en vez de moverlo —el parseo del enlace de Google Maps y la
  normalización de roles del CEAD—, que son las dos que fallan en silencio si se
  equivocan. `npm run verificar` las corre.
* La vista previa sin WordPress (`tools/vista-previa-panel.php`) acepta ahora el
  segmento de detalle, así las secciones que hacen de lista y de editor a la vez
  se pueden mirar de las dos formas. La auditoría móvil pasó de 15 pantallas a
  22 con eso, y destapó dos objetivos táctiles por debajo de 44px que nunca se
  habían medido: el «← Volver» de las vistas de detalle (21px) y los motivos de
  un clic de la revisión (30px). Los dos arreglados.

= 3.0.0 =
* **El panel entero se muda a `/turismo-panel`**: secciones, acceso (`/turismo-panel/entrar`), invitaciones (`/turismo-panel/i/<token>`) y PWA (`manifest.webmanifest`, `sw.js`, `icon-<n>.png`, `offline`) cuelgan de ahí. Las rutas viejas —`/turismo/panel`, `/czu-login`, `/registro`, `/recuperar`, `/salir`, `/i/<token>` y los archivos `promotur-*` de la raíz— responden 301 a su equivalente nueva, así no se rompen las invitaciones ya enviadas ni la PWA instalada.
* **Rediseño visual completo**: sistema propio de tokens (tinta única como acento, tres radios, una sombra, una tipografía), menú lateral agrupado con submenú, migas de pan, barra inferior en teléfono y modo oscuro. Sin framework y sin dependencias nuevas.
* **Tipografía servida desde el plugin** (Inter, 3 variantes, 76 KB): el panel deja de heredar las fuentes del theme y de depender de un CDN.
* **El panel deja de heredar el CSS del theme activo**: se desencola en las rutas del panel. El sitio público se rehace sin poder romper el panel.
* `PROMOTUR_Stats::serie_diaria()`: actividad editorial por día leída del log de auditoría (una sola consulta agrupada), para las barras del inicio.
* Atajo ⌘K / Ctrl+K para el buscador, y submenú plegable en el lateral.
* **Tarjetas con relieve**: las de cifra pasan a ser banda de título con trama + caja interior con el número y la flecha, y los rótulos arrancan en mayúscula. El pulso de actividad deja de ser un sparkline en un rincón y pasa a ser un trazo sobre reja punteada, con el día debajo de cada punto y el de hoy marcado.
* **Barra inferior nueva en teléfono**: cápsula flotante de vidrio, centrada y despegada del borde, con la etiqueta abriéndose sólo en el acceso activo.
* **El panel queda sólo en español**: se saca el selector de idioma de la barra superior y la capa que cambiaba el locale por cookie. Los idiomas que sí existen son los de la app (ES/EN/GN), y se editan en la sección App.
* **Repaso del panel en teléfono**: las 15 pantallas auditadas con `tools/auditar-movil.mjs` — sin desborde horizontal y con todo lo que se toca en 44px o más. En el editor, el checklist de mínimos sube antes del formulario cuando hay una sola columna.
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
