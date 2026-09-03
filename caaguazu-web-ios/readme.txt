=== Caaguazú Web (espejo iOS) ===
Contributors: municipalidadcaaguazu
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.0.1
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
abierto. Sin esa API respondiendo, no hay página.

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

== Cuándo se retira ==

Cuando exista una app nativa de iOS. Desactivar el plugin y borrar la
carpeta: no hay nada más que limpiar — sin tablas, sin opciones más allá de
la que borra `czuwios_desactivar()` sola, sin usuarios registrados. Lo único
que guarda cada visitante es su propio `localStorage` (favoritos, recorrido
propio, idioma elegido), y eso vive en su navegador, no acá.

== Changelog ==

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
