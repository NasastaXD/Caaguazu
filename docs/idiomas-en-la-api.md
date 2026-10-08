# Multi-idioma en la API de contenido

**Para:** quien construye la app Android.
**De:** el lado del panel (WordPress en `caaguazu.net`).
**Versión:** `caaguazu-app-api` **0.8.0**, namespace `/wp-json/czu-app/v1/`.

Desde 0.8.0 el contenido —fichas, artículos y recorridos— se puede pedir en
**inglés** y **portugués** además del castellano.

**Nada de lo que ya funcionaba cambia.** Sin `?idioma`, cada endpoint responde
exactamente lo que respondía en 0.7.1. Un cliente publicado sigue andando sin
tocar una línea. Lo único que aparece en todas las respuestas de contenido son
dos claves nuevas, `idioma` y `traducido`, que un cliente viejo ignora.

---

## 1. Lo que hay que saber antes de leer el resto

Tres cosas, y las tres cambian cómo se diseña la pantalla:

**1. El castellano es el original.** No es «un idioma más»: es en el que se
escribe todo. Las traducciones son una capa encima. Si falta, se sirve el
castellano — nunca un texto vacío, nunca un error.

**2. La caída al original es campo por campo, no por pieza.** Una ficha puede
venir con el título en inglés y la descripción en castellano, porque alguien
tradujo uno y todavía no el otro. Esto es a propósito: la alternativa —caer al
castellano entera si le falta un campo— tira a la basura las traducciones que sí
están.

**3. Por eso existe `traducido`.** Sin ese dato, la app muestra una mezcla de
dos idiomas como si fuera una sola cosa y no puede avisar de nada. Con él, puede
poner «parcialmente traducido» y que la persona entienda lo que está viendo.

---

## 2. Cómo se pide

Dos formas, y el parámetro gana sobre la cabecera:

```http
GET /wp-json/czu-app/v1/inventario?idioma=en
GET /wp-json/czu-app/v1/inventario
Accept-Language: pt-BR,pt;q=0.9,es;q=0.8
```

El parámetro gana **a propósito**: la app deja elegir idioma adentro, y esa
elección tiene que poder ser distinta a la del sistema operativo del teléfono.

De `Accept-Language` se mira sólo el código de idioma: `pt-BR` y `pt-PT` son el
mismo portugués para lo que se sirve acá.

**Un idioma desconocido no es un error.** `?idioma=fr` devuelve 200 con el
contenido en castellano y `idioma: "es"` en la respuesta. Un 400 dejaría la app
sin contenido por haber pedido un idioma que no tenemos, cuando lo correcto —y
lo que la persona espera— es ver el contenido igual.

### Dónde vale

| Endpoint | `?idioma` |
| --- | --- |
| `GET /inventario` | sí |
| `GET /inventario/{id}` | sí |
| `GET /articulos` | sí |
| `GET /articulos/{id}` | sí |
| `GET /recorridos` | sí |
| `GET /recorridos/{id}` | sí |
| `GET /mis-recorridos` · `POST` · `PUT` | sí *(ver §6)* |
| `GET /eventos` · `GET /eventos/{id}` | sí |
| `GET /categorias` · `/etiquetas` | sí — ver §7 |
| `GET /mapa/markers` | **no** — no lleva ningún texto |
| `GET /sync` | **no** — devuelve IDs, no textos |

---

## 3. Qué idiomas hay

```http
GET /wp-json/czu-app/v1/idiomas
```

```json
{
  "original": "es",
  "idiomas": [
    { "codigo": "es", "nombre": "Español",    "original": true  },
    { "codigo": "en", "nombre": "English",    "original": false },
    { "codigo": "pt", "nombre": "Português",  "original": false }
  ],
  "como_pedir": "…",
  "sin_traducir": "…"
}
```

`nombre` viene **en su propio idioma**, que es como se escribe un selector de
idioma: nadie busca «Inglés» en una lista que está mirando justamente porque no
entiende el castellano.

Conviene pedirlo al arranque y cachearlo (`ETag`, `max-age` 1 h) en vez de
tener la lista compilada: el guaraní está previsto y va a aparecer acá antes de
que salga un APK nuevo.

---

## 4. Las dos claves nuevas

Todo objeto de contenido —en lista y en detalle— trae ahora:

```json
{
  "id": 412,
  "titulo": "Ykua La Patria Waterfall",
  "idioma": "en",
  "traducido": false,
  "…": "…"
}
```

| Clave | Qué dice |
| --- | --- |
| `idioma` | En qué idioma están los textos **de este objeto**. Es el que se pidió, resuelto: si pediste `fr`, acá dice `es`. |
| `traducido` | `true` sólo si **todos** los campos traducibles de esta pieza estaban traducidos. `false` significa que alguno cayó al castellano. |

Con `idioma: "es"`, `traducido` viene siempre `false`: no hay nada traducido
porque el castellano es el original.

### Qué hacer con `traducido: false`

Es una decisión de diseño de la app, pero lo que el dato permite es:

- Mostrar un aviso discreto («Parts of this page are in Spanish»), o
- ofrecer un botón para ver la pieza en castellano, o
- nada — mostrar la mezcla, que es lo que pasaría igual sin el dato.

Lo que **no** conviene es esconder la pieza: una ficha a medio traducir sigue
teniendo la foto, el mapa, el horario y el precio, que es la mayor parte de para
qué se abre.

---

## 5. Qué se traduce de cada cosa

### Ficha (sitio o evento)

| Clave de la respuesta | Lista | Detalle |
| --- | --- | --- |
| `titulo` | ✔ | ✔ |
| `descripcion` | — | ✔ |
| `horario_resumen` | ✔ | — |
| `practicos.horario` | — | ✔ |
| `practicos.costo` | — | ✔ |
| `categoria.nombre` | ✔ | ✔ |
| `etiquetas[].nombre` | ✔ | ✔ |
| `articulos_relacionados[].titulo` | — | ✔ |

### Artículo

`antetitulo`, `titulo`, `subtitulo`, `entradilla`, `cuerpo_html`, `categoria.nombre`,
`etiquetas[].nombre`, y el `titulo` de cada `relacionados[]`.

`cuerpo_html` viene con los mismos párrafos en los dos idiomas: el texto se
elige en plano y el HTML se arma después, así que un idioma no sale con
párrafos y el otro sin ellos.

### Recorrido

`titulo`, `resumen`, `articulo_html`, `duracion_estimada`, el `titulo` de cada
`articulos[]`, y de cada parada:

- **`paradas[].texto`** — lo que el equipo escribió *para esa parada dentro de
  este recorrido*. Se traduce con el recorrido.
- **`paradas[].titulo`** — sale de la **ficha a la que la parada apunta**, no de
  una copia guardada en el recorrido. O sea: traducir una ficha una sola vez la
  traduce en todos los recorridos que la incluyen.
- **`paradas[].categoria.nombre`** — mismo origen que el título: la categoría de
  la ficha a la que apunta la parada.

### Categoría y etiqueta (dondequiera que aparezcan)

Sólo `nombre`. `descripcion`, `color`, `icono` e `imagen` de una categoría no se
traducen — quedan en castellano en cualquier idioma que pidas. Es la extensión
más obvia si en algún momento hace falta: un campo más en `campos()` del lado
del panel, el mismo mecanismo.

### Lo que NO se traduce, a propósito

- Coordenadas, enlaces de Google Maps, fechas, `rango_precio`, `cantidad_paradas`.
- `autores` de un artículo — son nombres propios.
- `fuentes` — son citas de dónde salió cada dato; traducir el nombre de un libro
  o de un archivo lo vuelve imposible de encontrar.
- Los montos en guaraníes dentro de `costo`: el texto se traduce, el número no.
- La `descripcion`, el `color`, el `icono` y la `imagen` de una categoría — sólo
  su `nombre` se traduce, ver arriba.

---

## 6. Los casos raros, que son los que rompen

**Recorridos de usuario (`/mis-recorridos`).** El texto lo escribió una persona
en la app, en su idioma, y no pasa por el panel: no hay dónde traducirlo, así
que `traducido` viene `false` siempre. Pero los **nombres de sus paradas** salen
de fichas que sí pueden estar traducidas, así que pasarle `?idioma` igual tiene
efecto y conviene hacerlo.

**Eventos del CPT viejo.** Los eventos cargados antes de que la ficha tuviera
tipo de item viven en otro modelo que no pasa por el panel. Traen
`idioma: "es"` y `traducido: false` siempre. Los eventos que salen de una ficha
(`origen: "ficha"`) sí se traducen normalmente. Las dos formas traen las mismas
claves para que no haya dos maneras de leer la misma tarjeta.

**Caché.** El `ETag` se calcula sobre el cuerpo de la respuesta, así que ya
varía con el idioma solo. Y el idioma viaja en el query string, así que una
caché intermedia no puede mezclarlos. **Pero ojo del lado de la app:** si
cacheás por `id` a secas, la ficha 412 en inglés te pisa la 412 en castellano.
La clave de caché tiene que incluir el idioma.

**`/sync`.** Devuelve IDs de lo que cambió, no textos, así que no lleva
`?idioma`. Lo importante es que **guardar una traducción cuenta como un cambio**:
mueve el `post_modified_gmt` de la pieza, así que aparece en el delta como
cualquier otra edición. Cuando `/sync` te dice que la ficha 412 cambió, hay que
volver a bajarla **en todos los idiomas que tengas cacheados** — puede haber
cambiado sólo el portugués.

---

## 7. Categorías y etiquetas

> **Corrección sobre la primera versión de este documento:** acá decía que
> categorías y etiquetas se traducían por `/strings`, buscando por
> `categoria.slug`. Eso describía cómo *debería* funcionar antes de
> construirlo, y quedó escrito como si ya estuviera — pero `/strings` nunca
> tuvo ese mecanismo: es una lista de claves sueltas (`nav.inventario`,
> `nav.mapa`…) sin ninguna noción de categoría o etiqueta. Si programaron
> contra esa versión, no hay ningún `categoria.<slug>` que vaya a aparecer
> ahí. Perdón por el lío — quedó corregido abajo, y es lo que ya está en la
> API.

Una categoría (`Sitios Naturales`) y una etiqueta (`con niños`) no son
contenido de una ficha: son del sistema, y las comparten cientos de fichas.
Pero eso es un argumento sobre **dónde se edita** la traducción —una vez por
categoría, no una vez por cada ficha de esa categoría—, no sobre por qué
endpoint se sirve. Y el mecanismo que ya existe para «una vez por categoría,
resuelto en cada lugar donde aparece» es el mismo que usa cada ficha: `?idioma`.

**Así que van por el mismo camino que todo lo demás.** `/categorias` y
`/etiquetas` aceptan `?idioma=es|en|pt`, y el `nombre` de cada objeto
`categoria`/`etiquetas` embebido en una ficha, un artículo o un recorrido
—también el de cada parada de un recorrido— sale ya traducido, con el mismo
`?idioma` que mandaste para el resto de la respuesta. Nada nuevo que aprender:
es exactamente el mismo parámetro, aplicado también acá.

```http
GET /wp-json/czu-app/v1/categorias?idioma=en

[
  { "id": 4, "slug": "sitios-naturales", "nombre": "Natural Sites", "…": "…" }
]
```

```json
// /inventario/412?idioma=en — la categoría embebida sale traducida sola
{
  "id": 412,
  "titulo": "Ykua La Patria Waterfall",
  "categoria": { "id": 4, "slug": "sitios-naturales", "nombre": "Natural Sites" },
  "…": "…"
}
```

Se resuelve contra la fila del término —una traducción por categoría, cargada
una vez en el panel (§8)— y no se duplica en cada ficha, que es lo que la
primera versión de este documento estaba tratando de evitar. Sólo que el
resultado se sirve donde ya se pedía todo lo demás, no en un canal aparte.

Un objeto `categoria`/`etiqueta` **no** trae `idioma` ni `traducido` propios
—esos dos campos siguen siendo del objeto de contenido que lo contiene—: si
una categoría no tiene traducción cargada, su `nombre` sale en castellano
dentro de una respuesta que puede estar `traducido: true` para el resto de sus
campos. Es el mismo principio de «cae al original campo por campo» de la
ficha, aplicado un nivel más adentro.

**`/strings/{locale}`** sigue siendo sólo para los textos fijos de la interfaz
—menús, botones, avisos— y ahora acepta también `pt` (antes era `es`/`en`/`gn`
nada más). No tiene ninguna clave de categoría ni de etiqueta, ni la va a
tener.

---

## 8. Cómo se cargan las traducciones (para que se entienda de dónde salen)

No hace falta para integrar, pero explica los tiempos y por qué una pieza puede
estar a medias.

En el panel, cada ficha, artículo y recorrido tiene al pie un bloque **Idiomas**
con el original al lado del cuadro donde se escribe la traducción. Es de
**Profesor**: una traducción sale publicada tal cual, sin pasar por revisión —el
flujo editorial revisa el castellano, que es el original— así que quien la
escribe está publicando.

Además se puede bajar un archivo `.json` con todos los textos de una pieza, con
**las instrucciones adentro** (qué es cada campo, qué no traducir, que los
párrafos se conservan), traducirlo entero afuera —a mano o con un modelo de
lenguaje— y volver a subirlo. Es el camino que se va a usar para el grueso.

El panel marca cuatro estados por idioma, y uno importa acá: **«el castellano
cambió después»**. Si alguien edita la ficha en castellano, su traducción queda
marcada como desactualizada, pero **la app la sigue sirviendo** — es mejor un
texto en inglés levemente viejo que un salto al castellano sin aviso. `/sync` va
a avisar del cambio igual, porque editar mueve la fecha de modificación.

**Categorías y etiquetas se traducen aparte, en Estructura** —no en el bloque
Idiomas de una ficha, porque no son de ninguna ficha en particular—: cada
término de la lista tiene un desplegable «Traducciones» con un campo por
idioma. También de Profesor. No hay archivo para bajar y subir acá: son pocos
términos y un solo campo cada uno, así que no vale la pena el mecanismo del
archivo.

---

## 9. Resumen para implementar

1. Pedir `GET /idiomas` al arranque y cachearlo. No compilar la lista.
2. Guardar el idioma elegido y mandarlo como `?idioma=` en **todos** los
   endpoints de contenido.
3. **Incluir el idioma en la clave de caché local.** Es el error que más caro
   sale.
4. Leer `traducido` y decidir qué mostrar cuando es `false`.
5. Pasar `?idioma=` también a `/categorias` y `/etiquetas` si tenés un catálogo
   propio de filtros — el `nombre` de cada uno sale traducido igual que en
   cualquier otro endpoint. **No hace falta leer nada de `/strings` para
   esto** — si programaron el lookup por `/strings` contra la versión anterior
   de este documento, se puede sacar entero.
6. Cuando `/sync` marque una pieza como cambiada, invalidarla **en todos los
   idiomas**.

Cualquier cosa que no esté acá o que no cierre, avisen y lo resolvemos del lado
del panel — el modelo está hecho para que sumar un idioma o un campo sea un
renglón.
