=== Caaguazú Web turismo ===
Contributors: municipalidadcaaguazu
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 2.0.2
License: GPLv2 or later

La guía de turismo de Caaguazú en una página web de acceso fácil, en `caaguazu.net/turismo/` (y en `/ios/`): se abre y se usa, sin instalar nada y sin crear una cuenta.

== Description ==

**Qué es.** La guía de turismo en una página: qué visitar, artículos y un
asistente para preguntar, con el mismo contenido y la misma API que la app
Android (`Turismo-app-czu`) y el mismo sistema visual que el panel. Está
pensada como la puerta de entrada fácil —el colegio la quiere así—: nada que
instalar, ninguna cuenta, modo claro y oscuro. Va empaquetada sin build: HTML,
CSS y JS puro, sin `npm install`. Vive en `sitio/` tal cual saldría de un
`git clone`.

**Qué hace la app que la web no.** Armar y seguir un recorrido propio, con el
mapa sin conexión. En la web «Recorridos» es una pantalla que explica eso y
lleva a la tienda de cada teléfono; los enlaces se cargan en wp-admin →
**Web turismo → Enlaces a la app**.

**Qué no es.** No guarda contenido propio y no tiene tablas. Sus únicas
opciones son su versión (para saber cuándo reflushear las rewrite rules) y los
dos enlaces a las tiendas. Todo el contenido sale en vivo de
`https://caaguazu.net/wp-json/czu-app/v1/` (`caaguazu-app-api`), con CORS ya
abierto. Sin esa API respondiendo, no hay página.

**Por qué es un plugin y no un sitio aparte.** La primera versión de esto se
pensó para hostearse sola —GitHub Pages, un subdominio propio—, pero eso pide
acceso a DNS y a un panel de hosting que nadie tenía a mano para algo
pensado para durar semanas o meses. Sirviéndolo desde acá, en el mismo
dominio, no hace falta ninguno de los dos: se instala como cualquier otro
plugin de este ecosistema y ya está online en `/turismo/`. Y por qué no un
toggle del theme: el theme del sitio está para rehacerse entero: mezclar ahí
algo temporal lo deja enganchado a un cambio que va a pasar por otros
motivos.

== Cómo sirve los archivos ==

Dos reglas de reescritura por dirección: `/turismo/` y `/ios/` (y cualquier
ruta debajo de cada una) resuelven a un archivo dentro de `sitio/`, con su
Content-Type según la extensión — `html`, `css`, `js`, `json`, `webmanifest`,
`png`, `woff2`, ninguna otra. Las dos direcciones sirven exactamente los mismos
archivos. `/turismo/` es la que se anuncia y la que enlaza el panel; `/ios/`
sigue andando para siempre, porque hay teléfonos que agregaron la web a su
pantalla de inicio con esa dirección y un ícono que deja de abrir no avisa
por qué. La web rutea por hash (`#/ficha/123`), así que el navegador nunca le
pide al servidor una URL distinta de la base al navegar; sólo al abrirla o
recargarla.

Las reglas exigen la barra justo después del nombre, así que `/turismo/` **no**
toca a `/turismo-panel/` (lo prueba `tools/verificar-web-ios.php`).

**Una página con esa dirección queda tapada.** Si en el sitio existe una
página de WordPress con la dirección `/turismo` —por ejemplo, de contenido que
se sembró antes—, la web la reemplaza: las reglas de este plugin van primero.
Si esa página hace falta, hay que moverla a otra dirección antes de activar
2.0.0.

Un archivo más lo arma el plugin en cada pedido y no está en `sitio/`:
`ajustes.json`, con la dirección de la API y los enlaces a las tiendas. Así la
misma copia de la web anda en un sitio de prueba sin tocar una línea.

El código no se sirve por PHP sino directo desde la carpeta del plugin (ver el
changelog de 2.0.2 por qué): la página arma las direcciones y un import map con
la versión de cada módulo. La ruta `/turismo/js/…` por PHP sigue andando —es la
de respaldo, y la que usa un servidor de archivos de prueba— y sus reglas de
caché son éstas. La caché tiene tres reglas. La página, los ajustes y los textos —`html`, `json`—
se revalidan en cada carga (con ETag: si no cambió, es un 304 sin cuerpo). El
código —`css`, `js`— se pide con la versión en la URL (`js/app.js?v=2.0.1`, y
también cada `import` entre módulos: lo estampa el plugin al servirlo, el
fuente en `sitio/` queda sin versiones) y por eso se guarda «para siempre»: no
puede cambiar sin que cambie la URL. Imágenes y fuentes se guardan una semana.
La página además trae un `<link rel="modulepreload">` por módulo, armado
leyendo los `import`, para que se bajen todos a la vez.

Los archivos de `sitio/` usan rutas relativas (`css/estilo.css`, y
`"start_url": "./index.html"` en el manifest), así que mudarlos de un dominio
propio a un subdirectorio de éste no les tocó una línea.

== Instalación ==

1. Subir `caaguazu-web-ios` a `/wp-content/plugins/` y activar.
2. Entrar a `caaguazu.net/turismo/` y confirmar que carga. `caaguazu.net/ios/`
   tiene que mostrar lo mismo.
3. En wp-admin → **Web turismo → Enlaces a la app**, pegar el enlace de
   Google Play (y el de App Store cuando exista). Sin enlaces, «Recorridos»
   explica que están en la app pero no muestra botones de descarga.
4. En un teléfono: abrir la URL, «Compartir» → «Agregar a inicio». Tiene que
   abrir en modo standalone (sin la barra del navegador) con el ícono correcto.

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

= 2.0.2 =
* **Por fin, la causa de la pantalla en blanco.** Con el informe que 2.0.1 le
  muestra a quien la abre —«No cargó: …/css/estilo.css, …/pantallas/app.js,
  …/compartir.js, …/pantallas/asistente.js, …/qrcode.js»— se midió en producción:
  cada archivo de la web (el CSS, cada uno de los ~20 módulos) se pedía por
  `/turismo/…` y pasaba por WordPress completo, con su conexión a la base de
  datos. Un teléfono los pide casi a la vez; el hosting tiene un tope de
  conexiones y a los que sobraban les contestaba **«Database Error» (500)**. Con
  22 archivos en paralelo, más de la mitad volvían con 500, y a la web le
  faltaban archivos al azar: nunca era el mismo teléfono ni el mismo archivo, y
  desde afuera no se veía ningún error. La precarga de módulos de 2.0.1 pedía
  todo junto y lo empeoraba.
* **El código se sirve directo, sin WordPress.** La página (que sí pasa por PHP:
  es lo único) apunta el CSS, los módulos, las fuentes, los íconos, `qrcode.js`
  y los textos a la carpeta del plugin
  (`/wp-content/plugins/caaguazu-web-ios/sitio/…`), que el servidor web contesta
  solo. Los mismos 22 archivos en paralelo por esa ruta: 200 todos, tres tandas
  seguidas. Un **import map** (que arma el plugin con la lista de módulos) le
  pone la versión a cada `import`, así que lo que dura una semana en el teléfono
  no se puede mezclar entre versiones. Un navegador sin import maps (iOS < 16.4)
  usa la ruta de siempre por PHP, que sigue andando con la versión en cada URL.
* **Los ajustes viajan en la página** (la API y los enlaces de las tiendas, como
  JSON adentro del HTML): un pedido menos, y justo uno de los que pasaban por
  WordPress. La página ahora se revalida por lo que envía, no por la fecha del
  archivo: si cambian los enlaces de las tiendas en wp-admin, el 304 no la deja
  con los viejos.
* **La API reintenta.** Los pedidos a `/wp-json/czu-app/v1/` también pasan por
  WordPress y también pueden recibir ese 500 de «el momento». Ahora, ante un 5xx
  o un corte, se vuelve a pedir a los 0,6 s y a los 1,8 s (un 404 o un 400 no se
  reintentan). Sin eso Inicio, que arma lo que le llega, perdía una sección
  entera —categorías, lugares— sin mostrar ningún error.
* Pruebas: `tools/web-prueba/router.php` ahora imita al hosting (la carpeta del
  plugin directa, con los tipos que da; y `WEB_LIMITE_PHP=N`, que devuelve el
  «Database Error» cuando hay más de N pedidos a la vez por «WordPress»), y
  `probar-web.mjs` comprueba que con el hosting al límite el código no falla, que
  Inicio termina completo, y la ruta de respaldo sin import maps. Con el código
  servido por PHP como en 2.0.1, siete de esas comprobaciones fallan.

= 2.0.1 =
* **La página ya no puede quedar vacía.** Hasta 2.0.0 el HTML salía sin nada
  adentro y todo lo dibujaba JavaScript: si tardaba, fallaba o no corría, la
  persona veía un fondo liso y una pastillita huérfana abajo, sin ninguna pista.
  Ahora el HTML trae una pantalla de «Cargando…», la barra vacía no se dibuja,
  y un vigilante en línea avisa si la app no arranca: a los 5 segundos dice que
  está tardando, y si un script no carga o pasan 15 segundos sin arrancar, muestra
  qué pasó, un botón para reintentar y un informe técnico (qué archivo no cargó,
  qué error, qué navegador) que se puede copiar o sacar en captura. En español,
  inglés o portugués según el idioma elegido.
* **Arranque en paralelo.** Los ajustes y los textos embebidos se pedían uno
  después del otro y recién ahí se dibujaba; ahora van juntos. Los textos que
  edita el panel (`/strings`) dependen de la API: se esperan, pero como mucho un
  segundo y medio, y si llegan tarde se ven en la próxima pantalla. Una pantalla
  que revienta ya no deja la página vacía: dice que falló y ofrece reintentar.
* **Cada archivo de código lleva su versión en la URL** (`js/app.js?v=2.0.1`),
  también los `import` entre módulos. Una actualización cambia todas las URLs de
  golpe, así que un teléfono no puede juntar un módulo nuevo con uno viejo que
  tenía guardado —lo que dejaba la página en blanco sin ningún error a la vista
  después de actualizar—. Y como esas URLs no pueden cambiar de contenido, se
  guardan «para siempre» (`immutable`): la segunda visita no vuelve a pedir ni un
  archivo de código. La página, los ajustes y los textos siguen revalidándose.
* **Los módulos se piden todos a la vez** (`<link rel="modulepreload">`, que arma
  el plugin leyendo los `import`). Antes el navegador se enteraba de cada módulo
  recién al terminar de leer el anterior y los bajaba en fila, una vuelta de red
  por escalón: con mala señal eran segundos de pantalla vacía.
* **La revalidación ahora contesta 304.** El hosting (y los que comprimen) le
  vuelven débil el ETag —`W/"…"`, o le pegan `-gzip`—, y como se comparaba
  exacto, ninguna revalidación coincidía jamás: la página se bajaba entera en
  cada visita. Ahora se compara como corresponde (RFC 7232).
* Prueba nueva, con el plugin de verdad y sin WordPress (`tools/web-prueba/`):
  `node tools/probar-web.mjs` ahora arranca el servidor PHP con la API real
  guardada y comprueba la pantalla de arranque, el vigilante, las versiones, la
  precarga y la segunda visita.

= 2.0.0 =
* **La web deja de ser «lo de iPhone» y pasa a ser la guía de acceso fácil.**
  Mismo contenido, pero ya no calca la app Android: habla el sistema de diseño
  del panel —los mismos colores, la misma letra (Inter, servida desde el
  plugin y no desde Google), los mismos radios—, sin degradados ni emojis
  haciendo de íconos.
* **Modo claro y oscuro.** Sigue al teléfono, y se puede elegir en Ajustes. El
  tema se decide antes de dibujar, así que no hay un destello del otro.
* **Animaciones cortas, con sentido**: entrar a una pantalla, abrir una hoja,
  guardar un favorito. Con «reducir movimiento» activado en el teléfono, nada
  se anima.
* **Sin cuenta.** Los favoritos viven en el teléfono. Se dice en Ajustes.
* **Nueva dirección: `/turismo/`.** `/ios/` sigue funcionando y sirve lo mismo.
  Ver «Una página con esa dirección queda tapada» arriba.
* **Recorridos se mudan a la app.** Armar un recorrido propio y seguirlo sin
  conexión es cosa de la app. La pestaña pasa a explicarlo y a llevar a la
  tienda de cada teléfono (el que entra desde un iPhone ve primero la de
  Apple), con enlaces que se cargan en **Web turismo → Enlaces a la app**.
  Los enlaces viejos (`#/recorridos`, `#/recorrido/12`) llevan a esa pantalla.
* **El asistente (CEADI).** Una pestaña para preguntar, que contesta con lo
  publicado y cita las fuentes. Aparece sólo si la API lo tiene prendido
  (`GET /asistente`), así que esta versión se puede instalar antes que la
  API que lo trae. Muestra cuánto esperar cuando hay demasiadas preguntas
  seguidas.
* **Guardados**, Ajustes (tema, idioma, compartir) y búsqueda con los filtros
  en la dirección: al volver de una ficha se encuentran los mismos
  resultados.
* **Las categorías muestran su foto y su descripción** (API 0.7.0).
* **Arreglo: la descripción de una ficha no se mostraba nunca.** La web leía
  los campos con los nombres de los modelos Kotlin de la app (`articuloHtml`,
  `tipoItem`, `googleMaps`), que en el JSON no existen: la API manda
  `descripcion`, `tipo_item`, `google_maps`. Venía así desde 1.0.0.
* **Arreglo: una pantalla lenta podía pisar a la que ya estabas viendo** al
  navegar rápido. Cada pantalla ahora pregunta si sigue vigente antes de
  dibujar.
* **Arreglo: sin buena señal la página quedaba en blanco.** El arranque
  esperaba sin límite de tiempo a los ajustes y a los textos. Ahora cada uno
  tiene un tope y la web abre igual con lo que tiene.
* **Más liviana al abrir:** Leaflet (~150 KB) se pide recién al abrir el mapa;
  antes se cargaba en cada visita. En modo oscuro el mapa se oscurece.
* **Cache.** Hasta 1.2.0 todo se guardaba una hora. Los módulos JS se importan
  entre sí, así que tras una actualización un teléfono podía quedarse con la
  página nueva y un módulo viejo, y romperse sin ningún error a la vista. El
  código se revalida en cada carga y sólo imágenes y fuentes se guardan.
* En wp-admin el menú pasa a llamarse **Web turismo**, con dos pantallas:
  *Enlaces a la app* y *Actualizaciones*.
* El QR de «Compartir» va siempre sobre fondo blanco, también en modo oscuro:
  un lector de cámara no encuentra un código claro sobre fondo oscuro.

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
