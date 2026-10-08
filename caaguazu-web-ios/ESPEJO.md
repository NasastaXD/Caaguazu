# La web de turismo por dentro

Los archivos de `sitio/` —lo que el plugin sirve en `/turismo/` y en
`/ios/`—. Cómo se instala y se publica el plugin está en `readme.txt`; esto es
cómo está hecha la página.

Es la guía de turismo de Caaguazú de acceso fácil: se abre y se usa, sin
instalar nada y **sin cuenta**. Nació como espejo temporal de la app Android
para quien usa iPhone (de ahí el nombre de la carpeta), y desde 2.0.0 es la
puerta de entrada para cualquiera. La app sigue siendo lo que arma y sigue
recorridos.

## Qué es y qué no es

- **Es** HTML + CSS + JS sin transpilar, sin bundler, sin `npm install`. Se
  abre `index.html` con cualquier servidor de archivos estático y funciona
  (sin el plugin no hay `ajustes.json`: usa la API de producción y no muestra
  botones de tienda).
- **No** guarda contenido propio. Todo sale en vivo de la API de la app
  (`/wp-json/czu-app/v1/`). Lo único que guarda es del lado del teléfono:
  favoritos, idioma, tema y, mientras dura la visita, la charla con el
  asistente. Sin cuenta, sin servidor, sin sincronizar entre dispositivos.
- **No** arma recorridos: la pestaña «Recorridos» explica eso y lleva a la
  tienda.

## Estructura

```
index.html               cascarón; el tema se decide ahí, antes de dibujar
ajustes.json             NO está en disco: lo arma el plugin en cada pedido
                         (API + enlaces a las tiendas)
css/estilo.css           el sistema de diseño del panel, con claro y oscuro
fuentes/inter-*.woff2    la letra del panel (Inter), servida desde acá
js/app.js                router por hash, barra inferior, delegados de clic
js/config.js             ajustes.json, fetch con tope de tiempo, plataforma
js/tema.js               claro / oscuro / como el teléfono
js/api.js                cliente de la API (campos con los nombres del JSON)
js/idioma.js             idioma actual + fusión de textos (piso es + servidor)
js/estado.js             favoritos en localStorage
js/piezas.js             tarjetas, íconos, estados, hoja inferior
js/mapas.js              enlace a Google Maps
js/compartir.js, qr.js   Web Share API, o enlace + QR hecho en el navegador
js/pantallas/*.js        inicio, buscar, ficha, articulos, articulo, mapa,
                         asistente, app, ajustes, guardados
js/vendor/qrcode.js      qrcode-generator de Kazuhiko Arase (MIT), sin tocar
textos/{es,en,pt}.json   respaldo embebido de los textos de la interfaz
manifest.webmanifest     para «agregar a inicio» en iOS y Android
assets/icon-*.png        isotipo oficial, copiado de caaguazu-theme
```

## Decisiones que vale la pena explicar

- **El estilo es el del panel**, no el de la app. Hasta 1.2.0 calcaba los
  tokens Kotlin de la app (oscuro fijo, Poppins, degradados sobre las fotos).
  Ahora son los mismos tokens que `caaguazu-portal/assets/css`, copiados a mano
  —sin paso de build no hay otra forma—: si cambian allá, se cambian acá. El
  texto va debajo de la foto y nunca encima con un velo oscuro.
- **Movimiento corto, y la pantalla sólo hace fundido.** Una pantalla no puede
  animar `transform`: un ancestro con `transform` —aunque termine en `none`—
  pasa a ser el punto de referencia de sus hijos `position: fixed`, y la barra
  para escribir al asistente y la capa del mapa viven adentro. El movimiento
  está en tarjetas y globos. Con «reducir movimiento» nada se anima.
- **Los campos son los del JSON**, no los de los modelos Kotlin:
  `tipo_item`, `google_maps`, `descripcion`, `cuerpo_html`. Hasta 1.2.0 se
  leían los de Kotlin y la descripción de una ficha no se mostraba nunca.
- **Cada pantalla pregunta `vigente()` antes de dibujar**, y el arranque pone
  tope de tiempo a lo que espera. Sin lo primero, una pantalla lenta pisa a la
  que ya se está viendo; sin lo segundo, una conexión mala deja la página en
  blanco.
- **Mapa con Leaflet + teselas de OpenStreetMap**, pedido sólo al abrir el
  mapa. La app necesita el mapa sin conexión porque un turista puede estar sin
  señal en medio del departamento; una página web ya asume internet.
- **El asistente aparece sólo si `GET /asistente` dice que está disponible.**
  Así esta web se puede instalar antes que la API que lo trae.
- **Compartir con `navigator.share`** cuando existe y, si no, una hoja con el
  enlace y un QR hecho en el navegador con una copia de `qrcode-generator`:
  ningún servicio externo recibe la URL que se comparte.
- **Una pestaña de la barra por idea:** Principal, Buscar, Artículos,
  Asistente (si está) y Recorridos. Ajustes se abre desde el ícono de arriba.

## Que falta a propósito

- Registro o cuenta: la web no los necesita ni los va a necesitar.
- Push/avisos: la app tampoco los tiene.
- Splits de idioma guaraní: igual que la app, sale de la lista hasta que el
  panel tenga textos.

## Cómo se probó sin WordPress

Las pantallas se probaron en un navegador real con la API simulada, a 390 y
360 px, claro y oscuro: que no desborde nada, que lo que se toca llegue a
44 px, que no queden claves sin traducir, y los flujos —guardar un favorito,
cambiar de tema, preguntarle al asistente, el error de «demasiadas
preguntas», las direcciones de 1.x—. `tools/verificar-web-ios.php` cubre el
plugin: las dos direcciones, que `/turismo/` no toque `/turismo-panel/`, los
enlaces de tienda y la caché.
