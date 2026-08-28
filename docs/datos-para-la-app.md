# Los datos que la app tiene que clonar

<!--
  GENERADO. No editar a mano: se regenera con

      php tools/inventario-de-datos.php

  y `npm run verificar` falla si el archivo quedó viejo. Lo que hay que
  editar es tools/inventario-de-datos.php, que es donde está la decisión de
  cómo sale cada campo.
-->

Tres cosas se cargan en el panel y viajan a la app: la **ficha** (que puede ser
un sitio o un evento), el **artículo** y el **recorrido**. Este documento lista
todos sus datos: cómo se llaman adentro de WordPress, cómo salen por la API, y
cuáles son obligatorios para que algo se pueda publicar.

El contrato de los endpoints —paginación, caché, autenticación— está en
[contrato-app-contenido.md](contrato-app-contenido.md). Acá están los campos.

## Lo que vale para las tres

**Sólo se publica lo publicado.** El estado editorial vive en `_promotur_estado`
y sólo `publicado` llega a `post_status = publish`. La API nunca sirve otra
cosa, así que la app no tiene que filtrar por estado: lo que le llega, va.

**Fechas** en ISO 8601 con zona (`2026-03-14T19:00:00+00:00`). Las que la gente
carga se guardan en UTC.

**Imágenes** salen siempre con la misma forma, no como un ID suelto, y valen
`null` cuando no hay foto:

```json
{ "url": "https://…/foo.jpg", "w": 1600, "h": 1067,
  "credito": "Foto: Fulano", "alt": "Texto alternativo" }
```

**Términos** (categoría, zona, etiqueta):

```json
{ "id": 7, "slug": "naturaleza", "nombre": "Naturaleza", "color": "#2E7D32" }
```

`color` sólo viene si el término lo tiene cargado.

**Autor**: `{ "id": 12, "nombre": "…" }`, o `null`. Es la **cuenta** del sistema
de cuentas propio, no un usuario de WordPress: el `post_author` de todo el
contenido es un usuario de servicio y no sirve para firmar nada.

**Referencias a otro contenido** (`articulos_relacionados`, `articulos`) salen
con `id`, `titulo` y `portada`: lo justo para pintar la tarjeta y navegar. Para
el resto, la app pide ese `id` a su endpoint.

**Sincronizar.** `GET /sync?since=…` devuelve, por colección, qué IDs cambiaron
y cuáles se borraron. Las colecciones son `inventario`, `eventos`, `articulos` y
`recorridos`. Sin `since`, o con uno más viejo que la retención de lápidas, la
respuesta viene con `completo: true` y hay que recargar todo.

---

## Ficha — sitio o evento

Una ficha es un lugar. Si además pasa en una fecha, es un evento: el mismo
formulario, los mismos campos, más cuándo empieza y cuándo termina. `tipo_item`
dice cuál de las dos es.

`GET /inventario` · `GET /inventario/{id}` · `GET /mapa/markers`

### Campos del formulario

| Campo en el panel | Meta | Tipo | Obligatorio | Sale como | Dónde |
| --- | --- | --- | --- | --- | --- |
| Tipo | `_promotur_tipo_item` | opción (`sitio` · `evento`) | sí | `tipo_item` | los dos |
| Empieza | `_promotur_evento_inicio` | fecha y hora | sí, sólo en eventos | `fechas.inicio` | los dos |
| Termina | `_promotur_evento_fin` | fecha y hora | no, sólo en eventos | `fechas.fin` | los dos |
| Foto de portada | `_promotur_portada` | imagen | sí | `portada` | los dos |
| Crédito de las fotos | `_promotur_credito_fotos` | texto | sí | `portada.credito` | los dos |
| Video (URL, opcional) | `_promotur_video` | URL | no | `video` | detalle |
| Enlace de Google Maps | `_promotur_maps` | URL | no | `google_maps` | los dos — vía `PROMOTUR_Destinos::maps_url()` |
| Latitud (alternativa al enlace) | `_promotur_lat` | número | no | `coordenadas.lat` | los dos |
| Longitud (alternativa al enlace) | `_promotur_lng` | número | no | `coordenadas.lng` | los dos |
| Estado del camino | `_promotur_estado_camino` | opción (`asfalto` · `ripio` · `tierra`) | no | `acceso.estado_camino` | detalle |
| Horario | `_promotur_horario` | texto | sí | `practicos.horario` | detalle; en la lista, `horario_resumen` |
| Costo / entrada | `_promotur_costo` | texto | sí | `practicos.costo` | detalle |
| Rango de precio | `_promotur_rango_precio` | opción (`0` · `1` · `2` · `3` · `4`) | no | `rango_precio` | los dos (en el detalle, dentro de `practicos`) |
| Contacto del lugar | `_promotur_contacto` | texto | no | `practicos.contacto` | detalle |
| Fuentes / referencias | `_promotur_fuentes` | texto largo | no | `fuentes` | detalle |


### Lo demás que viaja

| De dónde sale | Sale como | Tipo | Nota |
| --- | --- | --- | --- |
| `post_title` | `titulo` | texto | El nombre del lugar o del evento. |
| `post_content` | `descripcion` | HTML | La descripción larga, ya renderizada. Se llamaba `articulo_html` —nombre copiado sin pensar de Artículos— hasta la 0.5.0; la app la buscaba como `descripcion` y no la encontraba. |
| `post_modified_gmt` | `actualizado` | ISO 8601 | Con esto y `/sync` la app sabe qué volver a bajar. |
| `_promotur_galeria` | `galeria[]` | imagen[] | IDs de adjuntos; sale resuelta a objetos imagen. |
| `_promotur_articulos_rel` | `articulos_relacionados[]` | ref[] | IDs de artículo; sale con id, título y portada. |
| `_caaguazu_owner` | `autor` | objeto | La cuenta dueña, no el `post_author` de WordPress. |
| `promotur_categoria` | `categoria` | término | Taxonomía. Una sola por ficha. |
| `promotur_zona` | `zona` | término | Taxonomía. El distrito o la zona. |
| `promotur_etiqueta` | `etiquetas[]` | término[] | Taxonomía. Varias. |
| _calculado_ | `google_maps` | URL | El enlace pegado; si sólo hay coordenadas, uno armado con el pin. Nunca las dos formas. |
| _calculado_ | `fechas.en_curso` | bool | Sólo en eventos. Un evento sin fecha de fin dura ese día. |
| _calculado_ | `fechas.terminado` | bool | Sólo en eventos. |


### Eventos

Un evento sale por los dos lados y es el mismo objeto:

- **`GET /inventario?tipo_item=evento`** — la colección completa, con la ficha
  entera. `tipo_item=sitio` es lo contrario, e incluye las fichas cargadas antes
  de que el tipo existiera (todas eran sitios).
- **`GET /eventos`** — la agenda: ordenada por fecha de inicio y, por defecto,
  sólo lo que todavía no terminó. Acepta `desde`, `hasta` y `categoria`.

`/eventos` mezcla dos fuentes y lo dice en `origen`:

| `origen` | Qué es | Detalle completo en |
| --- | --- | --- |
| `ficha` | Una ficha con `tipo_item = evento`. El camino de hoy. | `/inventario/{id}` |
| `evento_legado` | El CPT viejo `promotur_evento`, editable sólo desde wp-admin. No se carga más, pero lo que ya existe sigue sirviéndose. | `/eventos/{id}` |

Para la app la diferencia importa en un solo punto: **un evento de `origen: ficha`
sincroniza en la colección `inventario`, no en `eventos`**, porque es una ficha.
Lo más simple es clonar `inventario` entero y armar la agenda del lado del
teléfono filtrando por `tipo_item` y ordenando por `fechas.inicio` — así la
agenda funciona sin conexión, que es lo que `/eventos` no puede dar.

En `/mapa/markers` los pines de ficha vienen con `tipo: "destino"` y
`tipo_item` para distinguirlos; los del CPT viejo, con `tipo: "evento"`.

---

## Artículo

`GET /articulos` · `GET /articulos/{id}`

### Campos del formulario

| Campo en el panel | Meta | Tipo | Obligatorio | Sale como | Dónde |
| --- | --- | --- | --- | --- | --- |
| Ante título | `_articulo_antetitulo` | texto | no | `antetitulo` | los dos |
| Subtítulo | `_articulo_subtitulo` | texto | no | `subtitulo` | los dos |
| Autor / autores | `_articulo_autores` | texto | sí | `autores[]` | los dos |
| Foto | `_articulo_portada` | imagen | sí | `portada` | los dos |
| Pie de foto y crédito | `_articulo_pie_portada` | texto | sí | `portada.credito` | los dos |
| Fuentes | `_articulo_fuentes` | texto largo | sí | `fuentes[]` | detalle |


Los tres campos que no están en esa tabla son los nativos del post, y son los
que llevan el peso de la nota: **título**, **entradilla** (`post_excerpt`) y
**cuerpo** (`post_content`). Los tres son obligatorios para publicar.

### Lo demás que viaja

| De dónde sale | Sale como | Tipo | Nota |
| --- | --- | --- | --- |
| `post_title` | `titulo` | texto |  |
| `post_excerpt` | `entradilla` | texto | El párrafo de arranque. Se llamaba `bajada`. |
| `post_content` | `cuerpo_html` | HTML | El cuerpo de la nota, ya renderizado. |
| `post_date_gmt` | `publicado` | ISO 8601 |  |
| `post_modified_gmt` | `actualizado` | ISO 8601 |  |
| `_articulo_relacionados` | `relacionados[]` | ref[] | No se carga desde el panel: si está vacío, la API sugiere hasta 6 de la misma categoría. |
| `promotur_categoria` | `categoria` | término |  |
| `promotur_etiqueta` | `etiquetas[]` | término[] |  |


**Autores.** `autores` es una lista, no una cadena: una nota puede tener dos
firmas. Cada una es `{ "nombre": "…", "cuenta": 12 | null }` — `cuenta` viene
cuando quien firma es una cuenta del sistema. Si el artículo no tiene firma
escrita, se cae a la cuenta que lo cargó.

**Fuentes.** Se cargan una por línea y salen como lista de cadenas, sin las
líneas vacías.

---

## Recorrido

Un camino de hasta **nueve paradas** en un orden que importa. Las paradas se
eligen del inventario turístico ya cargado —no se escriben— y cada una puede
llevar un texto propio (la historia del lugar, un dato curioso) y un audio o un
video.

`GET /recorridos` · `GET /recorridos/{id}` · `GET /mis-recorridos` (los que arma
la gente en la app; requieren sesión)

### Campos del formulario

| Campo en el panel | Meta | Tipo | Obligatorio | Sale como | Dónde |
| --- | --- | --- | --- | --- | --- |
| Foto de portada | `_recorrido_portada` | imagen | sí | `portada` | los dos |
| Duración estimada | `_recorrido_duracion` | texto | sí | `duracion_estimada` | los dos |


Más el **título**, el **resumen** (`post_excerpt`) y la **introducción**
(`post_content`).

### Lo demás que viaja

| De dónde sale | Sale como | Tipo | Nota |
| --- | --- | --- | --- |
| `post_title` | `titulo` | texto |  |
| `post_excerpt` | `resumen` | texto |  |
| `post_content` | `articulo_html` | HTML | La introducción del recorrido. |
| `_recorrido_tipo` | `tipo` | texto | `prehecho` (lo arma el panel) o `usuario` (lo arma alguien en la app). |
| `_recorrido_paradas` | `paradas[]` | objeto[] | Ver la forma abajo. El orden del array ES el recorrido. |
| `_recorrido_medios` | `medios[]` | objeto[] | Audios y videos del recorrido entero. |
| `_recorrido_articulos` | `articulos[]` | ref[] | Artículos vinculados, sólo los publicados. |
| `_recorrido_historia` | `historia` | objeto | Bloque heredado: introducción, correlación, personas, curiosidades. |
| _calculado_ | `cantidad_paradas` | entero |  |
| _calculado_ | `costo_total` | objeto | `{ hay_pago, detalle[] }` — los costos son texto libre, así que no se suman: se listan. |
| _calculado_ | `fechas` | objeto | `{ compatible, conflictos[] }` — si dos paradas son eventos que no se solapan, el recorrido no se hace en una salida. |
| _calculado_ | `google_maps` | URL | La ruta entera con los waypoints en orden, lista para abrir. |


### Una parada

El orden del array es el recorrido. `orden` viene renumerado de 1 a N: si
alguien borró la parada 3, no queda un hueco.

```json
{
  "orden": 2,
  "ref_tipo": "destino",
  "ref_id": 118,
  "disponible": true,
  "titulo": "Salto Cristal",
  "portada": { "…": "…" },
  "categoria": { "…": "…" },
  "coordenadas": { "lat": -25.6, "lng": -56.1 },
  "google_maps": "https://…",
  "costo": "5.000 Gs",
  "horario": "8 a 17",
  "inicio": null,
  "fin": null,
  "texto": "Lo que el promotor escribió sobre esta parada.",
  "medio": { "tipo": "audio", "url": "https://…" }
}
```

`disponible: false` es una parada cuyo sitio dejó de estar publicado. Viene
igual, con `orden`, `ref_id` y `texto` y sin el resto: el recorrido no se
renumera solo cuando una ficha se despublica, y la app tiene que poder mostrar
que ahí falta algo en vez de saltearlo en silencio.

Una parada puede ser un sitio o un evento: si es un evento, `inicio` y `fin`
vienen cargados. Las dos claves vienen siempre —en `null` cuando es un sitio—
así la app lee todas las paradas igual.

### Un medio

```json
{ "tipo": "audio", "url": "https://…", "titulo": "La leyenda del salto" }
```

`tipo` es `audio` o `video`. Los del recorrido entero van en `medios[]`; el de
cada parada, en `parada.medio`.

---

## Campos que ya no existen

Estaban en la ficha y se sacaron: se llenaban con frases genéricas que no
ayudaban a decidir nada. **La API no los sirve.** El valor cargado sigue en su
meta por si alguna vez hace falta recuperarlo, pero nada lo lee.

- `_promotur_como_llegar`
- `_promotur_referencia`
- `_promotur_temporada`
- `_promotur_servicios`
- `_promotur_duracion`
- `_promotur_gancho`
- `_promotur_accesibilidad`
