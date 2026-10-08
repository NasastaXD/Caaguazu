=== Caaguazú Web (espejo iOS) ===
Contributors: municipalidadcaaguazu
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later

Espejo web de la app de turismo, servido en `caaguazu.net/ios/`, para quien usa iPhone mientras no exista una app nativa.

== Description ==

**Qué es.** El mismo contenido, la misma API y — hasta donde una página web
lo permite — el mismo sistema visual que la app Android de turismo
(`Turismo-app-czu`), empaquetado sin build: HTML, CSS y JS puro, sin
transpilar, sin `npm install`. Vive en `sitio/` tal cual saldría de un
`git clone`.

**Qué no es.** No guarda contenido propio, no tiene tablas, no tiene
opciones más allá de recordar su propia versión para saber cuándo
reflushear las rewrite rules. Todo el contenido sale en vivo de
`https://caaguazu.net/wp-json/czu-app/v1/` (`caaguazu-app-api`), con CORS ya
abierto. Sin esa API respondiendo, no hay página. Casi todo es lectura: lo
único que el espejo le manda a la API, desde la 1.3.0, es la pregunta al
asistente (`POST /asistente`), sin cookies y sin cuenta. La charla no se
guarda en ningún lado del lado del espejo: vive en la página abierta.

**Por qué es un plugin y no un sitio aparte.** La primera versión de esto se
pensó para hostearse sola —GitHub Pages, un subdominio propio—, pero eso pide
acceso a DNS y a un panel de hosting que nadie tenía a mano para algo
pensado para durar semanas o meses. Sirviéndolo desde acá, en el mismo
dominio, no hace falta ninguno de los dos: se instala como cualquier otro
plugin de este ecosistema y ya está online en `/ios/`. Y por qué no un
toggle del theme: el theme del sitio está para rehacerse entero: mezclar ahí
algo temporal lo deja enganchado a un cambio que va a pasar por otros
motivos.

== Cómo sirve los archivos ==

Dos reglas de reescritura nada más: `/ios/` (y cualquier ruta debajo) resuelve
a un archivo dentro de `sitio/`, con su Content-Type según la extensión —
`html`, `css`, `js`, `json`, `webmanifest`, `png`, ninguna otra. La app de
adentro rutea por hash (`#/ficha/123`), así que el navegador nunca le pide al
servidor una URL distinta de `/ios/` al navegar dentro de ella; sólo al abrirla
o recargarla.

Los archivos de `sitio/` usan rutas relativas (`css/estilo.css`, y
`"start_url": "./index.html"` en el manifest), así que mudarlos de un dominio
propio a un subdirectorio de éste no les tocó una línea.

== Instalación ==

1. Subir `caaguazu-web-ios` a `/wp-content/plugins/` y activar.
2. Entrar a `caaguazu.net/ios/` y confirmar que carga.
3. En un iPhone: abrir esa URL en Safari, «Compartir» → «Agregar a inicio».
   Tiene que abrir en modo standalone (sin la barra de Safari) con el ícono
   correcto.

No hace falta tocar Ajustes → Enlaces permanentes: el plugin flushea las
rewrite rules solo, al activarse y al detectar un cambio de versión.

== Auto-actualización ==

Desde 1.1.0 se actualiza desde wp-admin sin pasar por WordPress.org, con el
mismo mecanismo que `caaguazu-portal` y `caaguazu-app-api`:
plugin-update-checker (vendoreado en `vendor/`) contra los GitHub Releases de
`NasastaXD/Caaguazu`. El job `web-ios` de `.github/workflows/release.yml`
publica el release con el tag `web-ios-{version}` y el asset
`caaguazu-web-ios.zip` cada vez que sube la versión del header; el checker lo
detecta (~cada 12 h) y ofrece la actualización en **Plugins** y en
**Web iOS → Actualizaciones** (wp-admin, capability `update_plugins`).

En ese mismo repositorio se publican también el theme, el panel, la API y el
SSO del CEAD, cada uno con su propio tag y su propio zip. Para que el updater
de este plugin no agarre el release de otro componente, sólo considera un
release que traiga adjunto `caaguazu-web-ios.zip` — no depende de cómo se
llame el tag.

Que sea temporal —se retira el día que exista una app nativa de iOS— no era
motivo para dejarlo sin esto: sin auto-updater, la corrección de la 1.0.1
—que sacó al sitio de una pantalla negra— hubiera dependido de que alguien
con acceso al hosting bajara un zip y lo subiera a mano.

* Versión en un solo lugar: header `Version:` + constante `CZUWIOS_VERSION` (semver).
* Repo privado: definir `CZUWIOS_GITHUB_TOKEN` (PAT de solo lectura) en
  `wp-config.php`, o cargarlo desde **Web iOS → Actualizaciones**.

== Cuándo se retira ==

Cuando exista una app nativa de iOS. Desactivar el plugin y borrar la
carpeta: no hay nada más que limpiar — sin tablas, sin opciones más allá de
la que borra `czuwios_desactivar()` sola, sin usuarios registrados. Lo único
que guarda cada visitante es su propio `localStorage` (favoritos, recorrido
propio, idioma elegido), y eso vive en su navegador, no acá.

== Changelog ==

= 1.3.0 =
* **El asistente, como en la app.** El botón redondo del medio de la barra
  —entre Buscar y Artículos— abre una charla que responde con lo publicado
  en el panel, y debajo de cada respuesta van las fichas, los artículos y los
  recorridos de donde salió. El botón aparece **sólo si `GET /asistente` dice
  `disponible`**: con una `caaguazu-app-api` anterior a la 0.9.0 ese pedido
  da 404 y el botón no se dibuja, que es lo correcto — un botón que no puede
  hacer nada no se dibuja. Para verlo hace falta la API 0.9.0 instalada, con
  el asistente encendido y su key cargada (wp-admin → Caaguazú API →
  Asistente; sin una sesión ya abierta en wp-admin, para llegar ahí hace
  falta el panel 3.11.0 o posterior, que dejó de redirigir `wp-login.php`).
* **La charla se abre sin la barra**, con el campo abajo, y el teclado de iOS
  sube hasta él: Safari no achica la página con el teclado, así que la charla
  se ajusta a mano con `visualViewport`. El campo va a 16px para que iOS no
  haga zoom al enfocarlo. Dura lo que dura la página abierta: sobrevive a
  abrir una fuente y a cerrar y volver a abrir la charla, y se pierde al
  recargar o al cambiar de idioma.
* **Las fuentes abren la misma ficha, artículo o recorrido** que el resto del
  espejo, y volver regresa a la charla. Hasta acá, volver desde un artículo o
  un recorrido llevaba siempre a su lista; abiertos desde la charla, ahora
  vuelven a ella. Abiertos desde cualquier otro lado, siguen como antes.
* La respuesta se pinta siempre como texto, nunca como HTML: es la salida de
  un modelo, y no se le cree que sea texto plano porque el servidor lo diga.
* `js/pantallas/asistente.js` se carga con `import()` dinámico y no con un
  import estático: si ese archivo falla —un caché desparejo, un error—, el
  resto del espejo sigue andando sin el botón, en vez de quedar en blanco
  como en la 1.0.1.
* Cada navegación pinta en un lienzo nuevo: una pantalla que termina de
  cargar tarde ya no pisa a la que se abrió después.
* **Qué probar**, en un iPhone, en Safari y con el espejo agregado a inicio:
  que el botón aparezca con la API 0.9.0 y el asistente encendido, y no
  aparezca con la API anterior; que al tocarlo suba el teclado sin zoom y el
  campo quede pegado arriba del teclado, sin hueco; que la última pregunta
  quede arriba al llegar la respuesta; que las fuentes abran y vuelvan a la
  charla con lo que se había escrito; cambiar el idioma en Perfil y
  preguntar; apagar el asistente desde wp-admin con la charla abierta y
  preguntar de nuevo — tiene que mostrar el error y, al volver, el botón ya
  no está. Y la barra a 360px en los tres idiomas.

* Sin señal, el botón del asistente no desaparece: sólo se va ante un 404 o
  si la API dice que no está disponible, igual que la app.
* Una pregunta que no recibe respuesta en 50 segundos se corta y ofrece
  reintentar, como en la app. «Pensando…» se ve también en una charla larga.
* Una ficha, un artículo o un recorrido que no cargan ofrecen volver, además de
  reintentar: antes, desde la charla, no había salida sin señal.
* En 320 px (iPhone SE, o el zoom de pantalla) la barra entra. Ahí sólo se
  muestra la etiqueta de la sección activa, como en la app.

= 1.2.0 =
* **Compartir una ficha, un artículo o un recorrido**, con un botón en cada
  una de esas pantallas; y desde Perfil, compartir el espejo entero. Usa la
  hoja de compartir del teléfono
  (`navigator.share`, que Safari en iOS tiene) y, donde no existe, abre una
  hoja propia con el enlace para copiar y un código QR. El QR se genera en el
  navegador con una copia de `qrcode-generator` (MIT) en
  `sitio/js/vendor/`: ningún servicio externo recibe la URL compartida.
* No es nuevo: se había hecho en `caaguazu-web/` (PR #65) un día después de
  que este plugin copiara esa carpeta, así que nunca llegó al espejo que se
  sirve en `/ios/`. Se porta tal cual, y `caaguazu-web/` deja de existir —
  era la copia vieja, ya sin uso desde que el espejo lo sirve este plugin.
  Su README, que explica cómo está hecha la página, pasa a `ESPEJO.md`.

= 1.1.0 =
* **Auto-updater.** Hasta acá era el único de los cinco componentes que se
  instalaba a mano — cada corrección exigía pedirle a quien tiene acceso al
  hosting que bajara un zip y lo subiera. Mismo mecanismo que
  `caaguazu-portal` y `caaguazu-app-api`. Ver «Auto-actualización» arriba.
* Nueva pantalla wp-admin → **Web iOS → Actualizaciones**.

= 1.0.1 =
* **Pantalla en blanco en producción, sin ningún error a la vista.** Todo
  archivo bajo `/ios/` (`js/app.js`, `css/estilo.css`, el manifest…) devolvía
  un 301 antes del 200: `redirect_canonical()` de WordPress corre en el mismo
  hook que el despachador de este plugin, ve `/ios/js/app.js` como si le
  faltara la barra final de su estructura de permalinks, y la agrega. El
  navegador seguía el redirect y el archivo llegaba igual — pero `app.js` es
  un módulo ES con imports relativos (`from "./idioma.js"`), y esos se
  resuelven contra la URL final, CON la barra puesta: el navegador pedía
  `js/app.js/idioma.js`, que no existe. El import fallaba, el módulo entero
  no cargaba, y la pantalla quedaba negra. El único síntoma estaba en las
  cabeceras (301 antes del 200 en cada archivo), no en la consola ni en la
  pantalla — nadie que abriera el sitio podía saber por qué.
* Se cancela con el filtro que `redirect_canonical()` ya expone para esto,
  sólo para pedidos de este plugin.
* `tools/verificar-web-ios.php`, nuevo — prueba que el filtro esté
  efectivamente enganchado (no sólo que la función esté bien escrita) y que
  cancele el redirect exactamente para las URLs de este plugin.

= 1.0.0 =
* Primera versión: sirve el espejo completo (inicio, buscar, ficha,
  artículos, recorridos, mapa, perfil) en `/ios/`, con manifest para
  «agregar a inicio» y los tres idiomas (`es`/`en`/`pt`) que ya sirve la API.
