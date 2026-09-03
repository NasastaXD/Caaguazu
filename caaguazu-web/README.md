# caaguazu-web

Espejo web temporal de la app Android de turismo de Caaguazú
(`Turismo-app-czu`), pensado para darle algo a quien usa iPhone mientras no
exista una app nativa de iOS. No es un producto nuevo: es el mismo contenido,
la misma API y — hasta donde una pagina web lo permite — el mismo sistema
visual, empaquetado sin build.

## Que es y que no es

- **Es** HTML + CSS + JS sin transpilar, sin bundler, sin `npm install`. Se
  abre `index.html` con cualquier servidor de archivos estatico y funciona.
- **No** guarda contenido propio: todo sale en vivo de
  `https://caaguazu.net/wp-json/czu-app/v1/`, la misma API que consume la
  app. Sin API no hay pagina — no hay copia local mas alla de lo que el
  navegador cachea solo.
- **No** reemplaza a la app. El dia que exista una app nativa de iOS, esto se
  da de baja. Esta pensado para vivir semanas o meses, no anos.

## Estructura

```
index.html              cascaron, carga Leaflet + Google Fonts por CDN
css/estilo.css           tokens calcados de ui/tema/Tokens.kt y Tipografia.kt
js/api.js                cliente de la API, calcado de ApiHttp.kt
js/idioma.js             idioma actual + fusion de textos (piso es + servidor)
js/estado.js             favoritos y recorrido propio en localStorage
js/mapas.js               enlaces a Google Maps (limite de 9 paradas incluido)
js/piezas.js              piezas de UI reutilizables (tarjetas, chips, iconos)
js/app.js                 router por hash + barra inferior
js/pantallas/*.js         una pantalla por archivo: inicio, buscar, ficha,
                          articulos, articulo, recorridos, recorrido, mapa,
                          perfil
js/compartir.js           hoja de compartir: Web Share API o enlace + QR
js/qr.js                  envoltorio del generador de QR vendoreado
js/vendor/qrcode.js       qrcode-generator de Kazuhiko Arase (MIT), sin tocar
textos/{es,en,pt}.json    copia exacta de los respaldos de la app Android
manifest.webmanifest      para "agregar a inicio" en iOS/Android
assets/icon-*.png         isotipo oficial, copiado de caaguazu-theme
```

## Decisiones que vale la pena explicar

- **Sin build.** Un Vite o un webpack para tres pantallas y nueve modulos es
  ceremonia que nadie va a mantener despues de que esto se dé de baja.
- **Mapa con Leaflet + tiles raster de OSM**, no MapLibre + PMTiles offline
  como la app. La app precisa el mapa sin conexion porque un turista puede
  estar sin señal en medio del departamento; una pagina web ya asume
  internet, asi que cargar tiles en vivo es mas simple y no pesa nada en el
  primer arranque.
- **Los mismos tokens de color, radio y tipografia** que `Tono`/`Radio`/
  `Elevacion`/`Medida`/`Letra` de la app, copiados a mano a
  `css/estilo.css`. No hay forma de compartirlos automaticamente entre un
  proyecto Kotlin y uno JS sin un paso de build que esto explicitamente evita
  — si el sistema visual de la app cambia, esto se actualiza a mano.
- **Favoritos y recorrido propio en `localStorage`**, igual que `Guardado.kt`
  en la app: sin cuenta, sin servidor, sin sincronizar entre dispositivos.
- **Idioma**: mismo criterio que la app — el castellano embebido es el piso,
  se completa con el idioma elegido (persistido en `localStorage`) y encima
  se fusiona lo que traiga `/strings/{idioma}` del panel, sin reemplazar el
  respaldo. `/idiomas` decide que idiomas ofrecer, igual que en la app.
- **Compartir con `navigator.share` cuando existe** (la mayoría de los
  navegadores de teléfono, incluido Safari en iOS) y, cuando no, una hoja
  propia con el enlace para copiar y un código QR. El QR se genera en el
  propio navegador con una copia vendoreada de `qrcode-generator` — no hay
  ningún servicio externo que reciba la URL que se está compartiendo.

## Que falta a proposito

- Push/avisos: la app tampoco los tiene (ver CLAUDE.md de Turismo-app-czu,
  §7) — nada que replicar.
- Registro/diagnostico: es una herramienta de desarrollo de la app nativa,
  no algo que un espejo temporal necesite.
- Splits de idioma guarani: igual que la app, sale de la lista hasta que el
  panel tenga textos.

Ver `DESPLIEGUE.md` para como publicar esto.
