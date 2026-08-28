# Sitios turísticos, artículos y recorridos — cómo los lee la app

**Para:** quien construye la app Android.
**De:** el lado del panel (WordPress en `caaguazu.net`).
**Versión de la API:** `caaguazu-app-api` **0.4.0**, namespace `/wp-json/czu-app/v1/`.

Este documento es el contrato de las **tres entidades de contenido**: el sitio
turístico (la ficha), el artículo y el recorrido. Autenticación, mapa y
sincronización están en [`BRIEF-AGENTE-APP-TURISMO.md`](../BRIEF-AGENTE-APP-TURISMO.md)
y no se repiten acá. Los eventos sí están acá desde 0.4.0, porque dejaron de ser
una entidad aparte: un evento es una ficha con fechas (§1.5).

La lista completa de campos, con el nombre que tiene cada uno adentro de
WordPress y cuáles son obligatorios, está en
[`datos-para-la-app.md`](datos-para-la-app.md) — ese archivo se genera desde el
código, así que no puede quedar viejo.

Todo lo que sigue es lectura. Nada de esto se escribe desde la app todavía
(salvo los recorridos propios de cada persona, §3.6).

---

## 0.0. Lo que cambió en 0.4.0

Un solo cambio, y es de modelo: **un evento es ahora una ficha con fechas.**

| Entidad | Se suma | Se va | Se renombra |
| --- | --- | --- | --- |
| Sitio | `tipo_item`, `fechas`, filtro `?tipo_item=` | — | — |
| Evento (`/eventos`) | `origen`, `ficha_id` | — | — |
| Marker | `tipo_item` | — | — |
| Parada | — | — | — |

Nada dejó de venir, así que un cliente 0.3.0 sigue funcionando: `tipo_item` vale
`"sitio"` en todo lo que ya tenías. Lo que cambia es que **`/inventario` ahora
puede devolver eventos**, y si tu pantalla de inventario los muestra mezclados
con los lugares, filtralos con `?tipo_item=sitio`.

Todo el detalle está en §1.5.

---

## 0. Lo que cambió en 0.3.0, en una pantalla

Si ya tenías un cliente contra 0.2.0, esto es lo que hay que tocar:

| Entidad | Se suma | Se va | Se renombra |
| --- | --- | --- | --- |
| Sitio | `google_maps` | `practicos.duracion`, `practicos.servicios`, `practicos.temporada`, `acceso.como_llegar`, `acceso.referencia` | — |
| Artículo | `antetitulo`, `subtitulo`, `autores`, `fuentes`, `etiquetas` | `autor` (singular) | `bajada` → `entradilla` |
| Recorrido | `medios`, `articulos`, `google_maps` | — | — |
| Parada | `medio`, `google_maps` | — | `nota` → `texto` |

**Ninguna clave que se fue sigue viniendo vacía.** Desapareció del objeto. Si tu
modelo las tenía como opcionales, no se rompe nada; si las tenía obligatorias,
sí.

Y dos cambios de fondo que no se ven en el JSON pero explican el resto:

1. **Artículo y Recorrido se editan ahora en el panel de promotores**, con el
   mismo flujo de revisión que las fichas: borrador → enviado → en revisión →
   publicado. Antes se cargaban desde la administración de WordPress y no
   pasaban por ninguna aprobación. Para vos esto significa una sola cosa, pero
   importante: **lo que la API devuelve ya fue aprobado por una persona.**
2. **La ubicación de un sitio se carga como enlace de Google Maps**, no como dos
   números. Por eso aparece `google_maps`.

---

## 1. Sitio turístico (la ficha)

El inventario de atractivos del departamento. Es la entidad más vieja de las
tres y la que menos cambió.

```
GET /inventario            lista paginada
GET /inventario/{id}       la ficha completa
GET /mapa/markers          sólo pines, para el mapa
```

Filtros de `/inventario`: `categoria`, `bbox`, `buscar`, `tipo_item`,
`pagina`, `por_pagina`.

### 1.1 La ubicación: enlace primero, coordenadas después

Este es el cambio importante de la ficha, y conviene entender de dónde sale.

Quien carga una ficha es un promotor con un teléfono en la mano, parado en el
lugar o acordándose de él. Pedirle dos números con seis decimales es pedirle una
transcripción que sale mal seguido y falla en silencio: un pin corrido no tira
ningún error, manda a alguien al lugar equivocado. Pegar el enlace que Google
Maps le da al tocar «Compartir» sale bien siempre.

Así que **el enlace de Google Maps es el modo por defecto** de cargar la
ubicación, y las coordenadas quedaron como alternativa para lo que el enlace no
cubre.

Del lado del servidor:

- Cuando el enlace trae el punto, **se extraen las coordenadas y se guardan**.
  Esto no es un detalle de implementación: el filtro `bbox` del mapa es una
  consulta de rango sobre latitud y longitud, y eso no se puede hacer sobre un
  valor que se calcula al vuelo.
- Los cuatro formatos que Google escribe están cubiertos: `!3d…!4d…` (el punto
  exacto del enlace de «Compartir»), `@lat,lng,zoom` (el centro del mapa),
  `?query=` / `?q=` / `ll=` / `center=`, y dos números pegados a mano.
  `!3d!4d` gana sobre `@` a propósito: en un enlace de lugar conviven los dos, y
  el `@` es dónde estaba mirando la cámara, no dónde está el lugar.
- **Los enlaces cortos (`maps.app.goo.gl`) no traen el punto.** Resolverlos
  exigiría seguir la redirección desde el servidor en medio de un guardado, y
  eso no se hace. En ese caso el promotor carga las coordenadas a mano, y el
  panel se lo pide con todas las letras.

Lo que te llega a vos:

| Campo | Cuándo viene | Qué hacer con él |
| --- | --- | --- |
| `google_maps` | Siempre que la ficha tenga ubicación | Abrilo para llevar a la persona hasta el lugar |
| `coordenadas` | Cuando hay pin (del enlace o cargado a mano) | Dibujá el marcador |

Si el promotor cargó **sólo** coordenadas, `google_maps` viene igual: el panel lo
arma con el pin. **Tenés un solo camino para «llevame ahí», no dos.**

Al revés no: `coordenadas` puede ser `null` con `google_maps` presente (enlace
corto). Una ficha así no tiene pin en el mapa, pero sí botón de «cómo llego».
Prevé ese caso.

### 1.2 `GET /inventario/{id}`

```json
{
  "id": 41,
  "tipo": "destino",
  "tipo_item": "sitio",
  "fechas": null,
  "titulo": "…",
  "categoria": { "id": 12, "nombre": "Paisaje Natural", "color": "#2E7D32" },
  "etiquetas": [ { "id": 51, "slug": "con-ninos", "nombre": "Con niños" } ],
  "coordenadas": { "lat": -25.4669, "lng": -56.0175 },
  "google_maps": "https://www.google.com/maps/…",
  "portada": { "url": "…", "w": 1200, "h": 800, "credito": "…", "alt": "…" },
  "galeria": [ { "url": "…", "w": 1200, "h": 800, "credito": "", "alt": "" } ],
  "video": null,
  "practicos": {
    "horario": "…",
    "costo": "…",
    "rango_precio": 2,
    "contacto": "…"
  },
  "acceso": {
    "estado_camino": "asfalto"
  },
  "descripcion": "…",
  "articulos_relacionados": [ { "id": 55, "titulo": "…", "portada": { … } } ],
  "fuentes": "…",
  "autor": { "id": 41, "nombre": "…" },
  "actualizado": "2026-08-20T14:00:00Z"
}
```

### 1.3 Los cinco campos que se fueron, y por qué

`practicos.duracion`, `practicos.servicios`, `practicos.temporada`,
`acceso.como_llegar` y `acceso.referencia` ya no existen.

No es una poda de la API sino del modelo: en la práctica esos cinco campos se
llenaban con frases genéricas —«todo el año», «2 horas», «se llega por el camino
de tierra»— que no ayudaban a decidir nada y que la app iba a mostrar como si
fueran información. Cada campo de relleno es además un renglón más entre quien
carga la ficha y los datos que sí se usan.

Cómo llegar lo resuelve `google_maps` mejor que cualquier párrafo.

**Los datos ya cargados no se borraron**: siguen en la base, sólo que la ficha
dejó de pedirlos, de mostrarlos y de publicarlos. Si alguna vez hacen falta, el
valor está.

### 1.4 `rango_precio`

Entero `0`–`4` (0 = gratis), o `null` si nadie lo cargó. Existe **además** de
`practicos.costo`, no en su lugar: el número sirve para filtrar y pintar el
indicador de la tarjeta, y el texto dice lo que un número no («entrada libre,
estacionamiento 5.000 Gs»). No lo deduzcas del texto.

---

### 1.5 Un evento es una ficha con fechas

Hasta 0.3.0 los eventos eran otra cosa: un tipo de contenido aparte, cargado
desde la administración de WordPress, con la mitad de los campos de una ficha
—sin gancho, sin galería, sin fuentes— y sin pasar por revisión. Se juntaron
porque un evento **es** un lugar: tiene ubicación, foto, costo, horario y
contacto exactamente igual. Lo único que agrega es cuándo pasa.

Así que la ficha suma dos claves:

```json
"tipo_item": "evento",
"fechas": {
  "inicio":    "2026-03-14T19:00:00+00:00",
  "fin":       "2026-03-16T23:59:59+00:00",
  "en_curso":  false,
  "terminado": false
}
```

- **`tipo_item`** es `"sitio"` o `"evento"`, y viene siempre — también en la
  lista y en los markers. En todo lo cargado antes de 0.4.0 vale `"sitio"`.
- **`fechas`** es `null` en los sitios. En los eventos, `fin` puede ser `null`:
  significa que dura ese día, y `en_curso` / `terminado` ya vienen resueltos con
  esa regla para que no la implementes vos.
- **`tipo` sigue diciendo `"destino"`** en las dos. Dice de qué colección salió
  el objeto, no qué es; si filtrabas por `tipo`, no cambia nada.

**Dos formas de pedir los eventos, y cuál conviene.**

`GET /eventos` es la agenda: ordenada por fecha de inicio, y por defecto sólo lo
que no terminó. Acepta `desde`, `hasta` y `categoria`. Mezcla los eventos que
salen de una ficha con los del modelo viejo, y lo dice en `origen`:

| `origen` | Qué es | El detalle completo está en |
| --- | --- | --- |
| `ficha` | Una ficha con `tipo_item: "evento"`. Es como se cargan hoy. | `/inventario/{id}` |
| `evento_legado` | El modelo viejo. No se carga más; lo que ya existe se sigue sirviendo para no hacerlo desaparecer de los teléfonos que lo tienen. | `/eventos/{id}` |

`GET /eventos/{id}` funciona con los dos: si el id es una ficha, devuelve la
ficha completa (§1.2), no una versión recortada.

**Para la caché local conviene el otro camino:** clonar `/inventario` entero y
armar la agenda en el teléfono filtrando `tipo_item == "evento"` y ordenando por
`fechas.inicio`. El motivo es `/sync`: **un evento de `origen: ficha` sincroniza
en la colección `inventario`, no en `eventos`** —porque es una ficha—, y además
así la agenda anda sin conexión, que es lo que un endpoint ordenado por fecha no
te puede dar.

En `/mapa/markers` los pines de ficha vienen con `tipo: "destino"` y su
`tipo_item`; los del modelo viejo, con `tipo: "evento"`.

---

## 2. Artículo

Notas, historias y datos curiosos. Es contenido periodístico, y su forma es la
de una nota: tiene ante título, firma y fuentes.

```
GET /articulos             lista paginada
GET /articulos/{id}        la nota completa
```

Filtros de `/articulos`: `categoria`, `etiqueta`, `buscar`, `pagina`, `por_pagina`.

### 2.0 `buscar` es texto libre, no por tag

`buscar` (en `/articulos` desde 0.5.1, ya existía en `/inventario`) busca en
título y cuerpo, como el buscador nativo de WordPress — **no filtra por
etiqueta.** No es una limitación arbitraria: ni la lista de `/inventario` ni
la de `/articulos` traen las etiquetas del post —sólo el detalle las tiene—,
así que resolver "buscame las notas etiquetadas «con niños»" en la lista
pagaría el precio de cargar cada post entero sólo para mirarle las
etiquetas. Si en algún momento hace falta buscar de verdad por tag, el
camino es sumar `etiquetas` al payload de lista de los dos endpoints y
filtrar ahí — no está hecho todavía.

**Si vas a conectar esto a un campo de texto que dispara en cada tecla:**
esperá a que la persona deje de tipear antes de pedir — 300–400ms desde la
última letra alcanza y sobra — o vas a mandar un pedido por carácter. Esto
es enteramente del lado del cliente; la API no hace nada especial para
soportarlo (no hay throttling ni rate-limit pensado para eso), así que si no
lo hacés en el cliente, no lo hace nadie.

### 2.1 Las ocho piezas

| Campo | Qué es | Obligatorio para publicar |
| --- | --- | --- |
| `antetitulo` | La línea corta de arriba del título, que lo ubica | no |
| `titulo` | El titular | **sí** |
| `subtitulo` | La segunda línea | no |
| `portada` | La foto, con su pie y crédito en `credito` | **sí** |
| `autores` | Quién firma | **sí** |
| `entradilla` | El párrafo de arranque | **sí** |
| `cuerpo_html` | El texto | **sí** |
| `fuentes` | De dónde salió cada dato | **sí** |

Las cinco marcadas son parte del checklist que el panel exige antes de dejar
enviar la nota a revisión. **No vas a recibir un artículo publicado sin ellas** —
pero programá defensivo igual con `antetitulo` y `subtitulo`, que sí pueden
venir vacíos.

### 2.2 `autores` es una lista

```json
"autores": [
  { "nombre": "Ana Giménez", "cuenta": null },
  { "nombre": "Luis Rojas",  "cuenta": null }
]
```

Una nota puede tener dos firmas, y quien firma no siempre es quien la cargó (un
corresponsal, alguien del CEAD). Por eso el autor se escribe a mano en un campo
propio, en vez de deducirse de la cuenta.

`cuenta` trae un ID sólo cuando la nota **no** tiene firma escrita y el servidor
cayó a la cuenta que la cargó. En la práctica: mostrá `nombre`, e ignorá
`cuenta` salvo que quieras enlazar al perfil.

> Recordá lo de siempre: el autor **nunca** sale de `post_author`. La gente del
> panel no es usuaria de WordPress, así que el autor técnico de todo el contenido
> es un usuario de servicio. Si algún día ves «Contenido de paneles» firmando
> notas, algo se rompió del lado del servidor.

### 2.3 `fuentes` viene partido

```json
"fuentes": [
  "Entrevista a Doña Ramona Villalba, mayo de 2026",
  "Archivo de la Junta Municipal de Caaguazú"
]
```

Se escribe una por línea y se parte del lado del servidor. Si dejaras que cada
cliente lo interpretara, se terminan viendo dos fuentes pegadas en un renglón.
Puede venir vacío sólo en artículos cargados antes de que el campo existiera.

### 2.4 Las etiquetas son las mismas que las de las fichas

No hay un juego de etiquetas para artículos y otro para sitios: es el mismo. Es
una decisión, no una casualidad. Si fueran dos, «Con niños» sería dos cosas
distintas según de dónde la mires, y no se podría cruzar una nota con el lugar
del que habla.

Aprovechalo: `GET /articulos?etiqueta=51` y `GET /inventario` con la misma
etiqueta te dan las dos caras del mismo tema.

### 2.5 `GET /articulos/{id}`

```json
{
  "id": 55,
  "antetitulo": "Ruta de la madera",
  "titulo": "…",
  "subtitulo": "…",
  "entradilla": "…",
  "portada": { "url": "…", "credito": "Pie de foto y crédito", "alt": "…" },
  "autores": [ { "nombre": "…", "cuenta": null } ],
  "publicado": "2026-08-14T12:00:00Z",
  "cuerpo_html": "…",
  "fuentes": [ "…" ],
  "categoria": { "id": 12, "nombre": "…", "color": "#…" },
  "etiquetas": [ { "id": 51, "slug": "…", "nombre": "…" } ],
  "relacionados": [ { "id": 56, "titulo": "…", "portada": { … } } ],
  "actualizado": "2026-08-20T14:00:00Z"
}
```

`GET /articulos` devuelve el sobre paginado de siempre
(`{ items, total, pagina, por_pagina }`) con los campos de la cabeza —de
`antetitulo` a `publicado`—. **La lista y el detalle comparten esa forma a
propósito:** podés abrir el detalle pintando ya lo que tenías en la lista, sin
esperar la respuesta.

---

## 3. Recorrido

Una ruta armada con sitios del inventario, en un orden pensado, con la historia
de cada parada al lado.

```
GET /recorridos            lista paginada de los prehechos publicados
GET /recorridos/{id}       uno completo
```

Filtros de `/recorridos`: `pagina`, `por_pagina`. Hasta la 0.5.0 devolvía un
array sin paginar, con tope fijo de 50 — el recorrido 51 no existía para nadie.
Ahora es una lista paginada como `/inventario` y `/articulos`: ver §3.3.

### 3.1 Qué es y qué no

Un recorrido **no es una lista de lugares: es una secuencia.** Cambiar el tercero
por el quinto cambia el paseo. Por eso `orden` viene explícito (1..n) en cada
parada y no se confía en el orden del array. Respetalo.

Cada parada apunta a un sitio que **ya existe en el inventario** — no es un lugar
escrito de nuevo. Si el sitio cambia, cambia en el recorrido; si se despublica,
la parada te avisa (§3.4).

### 3.2 Nueve paradas, y ni una más

El tope es duro y está impuesto en tres lugares (el editor, el guardado del
panel y la API). El motivo: la app manda el recorrido a Google Maps como una
ruta con waypoints, y ahí hay un límite. Un recorrido de doce paradas se
cortaría solo, en silencio, en el teléfono de alguien que ya salió de casa.

Se corta donde se puede avisar. **No vas a recibir un recorrido de más de nueve
paradas.**

### 3.3 `GET /recorridos/{id}`

```json
{
  "id": 88,
  "tipo": "prehecho",
  "titulo": "…",
  "resumen": "…",
  "portada": { "url": "…", … },
  "duracion_estimada": "media jornada",
  "cantidad_paradas": 4,

  "paradas": [
    {
      "orden": 1,
      "ref_tipo": "destino",
      "ref_id": 41,
      "disponible": true,
      "titulo": "Salto Suizo",
      "portada": { "url": "…", … },
      "categoria": { "id": 12, "nombre": "…", "color": "#…" },
      "coordenadas": { "lat": -25.4669, "lng": -56.0175 },
      "google_maps": "https://www.google.com/maps/…",
      "costo": "Gratis",
      "horario": "8 a 17",
      "inicio": null,
      "fin": null,
      "texto": "Se llega por el camino viejo; la caída se escucha antes de verse.",
      "medio": { "tipo": "audio", "url": "https://…" }
    }
  ],

  "costo_total": { "hay_pago": true, "detalle": [ { "titulo": "…", "costo": "…" } ] },
  "fechas": { "compatible": true, "conflictos": [] },
  "google_maps": "https://www.google.com/maps/dir/?api=1&origin=…&destination=…&waypoints=…",

  "historia": { "introduccion": "…", "correlacion": "…", "personas": [], "curiosidades": [], "articulos_ref": [ 55 ] },
  "medios": [ { "tipo": "audio", "url": "https://…", "titulo": "Presentación" } ],
  "articulos": [ { "id": 55, "titulo": "…", "portada": { … } } ],
  "articulo_html": "…"
}
```

`GET /recorridos` devuelve el sobre paginado (`{ items, total, pagina,
por_pagina }`) con los campos de arriba de `paradas` (hasta
`cantidad_paradas`) en cada `item`: lo justo para la tarjeta.

### 3.4 `texto` y `disponible`, parada por parada

**`texto`** es lo que el equipo escribió *para esa parada dentro de ese
recorrido*: la historia, el dato curioso, por qué el lugar está ahí y qué contar
cuando se llega. **No es la descripción de la ficha** — esa la tenés en
`/inventario/{id}`. Un mismo sitio puede aparecer en dos recorridos con textos
distintos, y así debe ser.

Reemplaza al viejo `nota`, que era una sola línea. Puede tener saltos de línea.

**`disponible: false`** significa que la ficha de esa parada dejó de estar
publicada. La parada viene igual, con `orden`, `ref_id` y `texto`, y **sin** los
campos del sitio (`titulo`, `coordenadas`, `portada`…). Se informa en vez de
omitirla en silencio: alguien que guardó ese recorrido tiene que enterarse de que
perdió un lugar. Mostralo apagado, no lo escondas.

### 3.5 Medios, artículos y la ruta

- **`medios`** — audios y videos del recorrido entero: la presentación, el video
  de apertura. Son URLs y no archivos subidos: un audio guiado de veinte minutos
  no tiene por qué vivir en la mediateca de WordPress.
- **`paradas[].medio`** — el audio o video de *esa* parada, o `null`. Es lo que
  hace posible el recorrido guiado: se reproduce al llegar.
- **`articulos`** — las notas ya escritas que el equipo vinculó a mano. Sólo las
  publicadas. Ofrecelas al final del recorrido.
- **`google_maps`** — la ruta completa, ya armada, con las paradas como
  waypoints en orden. Las paradas sin coordenadas se saltean (no se puede rutear
  hacia un lugar sin pin; cortar la ruta ahí sería peor). Viene `null` si quedan
  menos de dos puntos ruteables.

  Se arma del lado del servidor a propósito: acá están todas las coordenadas ya
  resueltas, y el orden de los waypoints **es** el recorrido. Dejar que cada
  cliente lo componga es dejar que cada cliente lo componga distinto.

- **`historia`** es el bloque libre heredado del contrato anterior. Sigue
  viniendo; `articulos_ref` de adentro repite los IDs de `articulos` cuando hay
  vinculados desde el panel. Si estás empezando, usá `articulos` y `medios`.

### 3.6 Los recorridos de usuario no cambiaron

`GET·POST·PUT·DELETE /mis-recorridos` sigue igual, con dos ajustes:

- En `POST`/`PUT`, cada parada acepta `texto` además de `nota`. Se guardan hasta
  nueve, sin repetir el mismo sitio, y se renumeran del lado del servidor.
- En la respuesta, un recorrido de usuario trae `historia: null`, `medios: []` y
  `articulos: []`. Vienen las claves aunque estén vacías, para que no tengas dos
  formas distintas de leer la misma pantalla.

Costo total y compatibilidad de fechas se siguen **calculando**, no se mandan ni
se guardan.

---

## 4. Cómo se relacionan las tres

```
                    etiquetas y categorías (compartidas)
                    ─────────────┬───────────────
                                 │
   ┌─────────────────┐           │           ┌──────────────────┐
   │  SITIO (ficha)  │◄──────────┴──────────►│    ARTÍCULO      │
   │  /inventario    │  articulos_relacionados│    /articulos    │
   └────────┬────────┘                       └─────────▲────────┘
            │                                          │
            │ es una parada de                         │ vinculado a
            │                                          │
            │            ┌──────────────────┐          │
            └───────────►│    RECORRIDO     │──────────┘
                         │   /recorridos    │
                         └──────────────────┘
```

### 4.1 Caché: ETag en todo, desde la 0.5.0

Los ocho endpoints de contenido —lista y detalle de inventario, artículos,
recorridos y eventos— mandan `ETag` y `Cache-Control: public, max-age=…`
(60s en las listas, 180s en los detalles; markers, categorías, strings y
media-manifest ya lo hacían). Mandá `If-None-Match` con el `ETag` que
recibiste: si no cambió, la respuesta es un `304` sin cuerpo, y eso es todo
lo que hace falta para no volver a bajar una ficha que no se tocó.

La única excepción a propósito es el detalle de un **recorrido de usuario**
(`/recorridos/{id}` cuando es tuyo, y todo `/mis-recorridos`): no lleva
`ETag` ni `Cache-Control`, porque es de una sola cuenta y `public` no le
corresponde.

Esto es acumulativo con `/sync` (§4.2), no un reemplazo: `/sync` te dice
*qué* cambió; el ETag evita bajar de nuevo lo que ya tenías cuando lo pedís
igual, por ejemplo al reabrir el detalle de algo que ya estaba en la lista.

### 4.2 `/sync`: el delta, para no bajar todo de nuevo

```
GET /sync?since=2026-08-21T00:00:00Z
```

Sin `since`, o con uno más viejo que lo que se conserva (90 días): recarga
completa. Si tenías algo cacheado y hace más de 90 días que no sincronizás,
asumí que está desactualizado y volvé a pedir todo por los endpoints
normales.

```json
{
  "desde": "2026-08-21T00:00:00+00:00",
  "hasta": "2026-08-28T14:03:00+00:00",
  "cambiados": {
    "inventario": [12, 45, 88],
    "eventos":    [],
    "articulos":  [201],
    "recorridos": []
  },
  "eliminados": {
    "inventario": [7],
    "eventos":    [],
    "articulos":  [],
    "recorridos": []
  },
  "completo": false
}
```

- **`cambiados`** son IDs, no objetos: con la lista en la mano, pedís el
  detalle de cada uno por su endpoint normal (que ya viene con ETag, así que
  si además no cambió nada raro, ni siquiera baja el cuerpo).
- **`eliminados` es la razón de que este endpoint exista.** Algo sale de acá
  cuando deja de estar publicado —despublicado, archivado, borrado o
  mandado a la papelera—, no sólo cuando se borra de la base. Si tu caché
  local sólo mira altas y cambios, una ficha despublicada se queda
  mostrándose para siempre. Sacá esos IDs de tu caché.
- **`completo: true`** es la señal de "tirá todo y empezá de nuevo": pasa
  cuando el `since` es más viejo que la lápida más antigua que el servidor
  todavía tiene, porque en ese caso no se puede garantizar que la lista de
  bajas esté completa. Cuando viene así, `cambiados` y `eliminados` llegan
  vacíos — no los uses, andá a los endpoints normales.
- Guardá `hasta` como tu próximo `since`. No uses la hora del teléfono: el
  servidor puede estar en otro huso o con el reloj corrido.

Tres reglas que valen para las tres entidades:

1. **Sólo se publica lo aprobado.** Las tres pasan por el mismo flujo editorial
   del panel, y `post_status = publish` sólo lo alcanza lo que una persona
   aprobó. La API no reimplementa esa regla: la lee de donde ya está.
2. **`/sync` cubre las tres**, con altas, cambios y bajas
   (`inventario`, `articulos`, `recorridos`, `eventos`). Los eventos que salen
   de una ficha van en `inventario`, no en `eventos` (§1.5).
3. **El autor visible nunca sale de `post_author`.**

---

## 5. Lo que sigue abierto

- **`POST /contenido`** — dar de alta o editar una ficha desde el teléfono.
  Sigue sin implementarse; la lectura está completa.
- **Escribir artículos y recorridos desde la app.** No está pensado todavía: los
  dos exigen edición larga, y el panel ya funciona bien en un teléfono.
- **Los audios y videos son URLs sueltas.** Ni se validan ni se transcodifican
  del lado del servidor: si el equipo pega un enlace que tu reproductor no
  soporta, se va a ver ahí. Si necesitás una lista cerrada de formatos o
  proveedores, pedila y se restringe en el panel.

Como siempre: **si algo de esto no te sirve, decilo antes de que lo
implementemos del todo.** Cambiar el contrato ahora es barato.
