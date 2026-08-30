=== Caaguazú App API ===
Contributors: municipalidadcaaguazu
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 0.7.0
License: GPLv2 or later

Capa REST que consume la app Android de turismo (Turismo App Czu).

== Description ==

Expone el contenido turístico y la identidad del ecosistema bajo
`/wp-json/czu-app/v1/`, para que la app Android los consuma.

**Es un plugin aparte a propósito.** El theme y el sitio público de
caaguazu.net van a rehacerse, y ese trabajo no debe poder romper la API de una
app ya publicada en la tienda. Por eso esta capa no usa el theme ni sus
helpers, no renderiza HTML, y lee el contenido de donde ya vive en vez de
duplicarlo.

**No reimplementa nada de lo que ya existe:**

* Identidad y cuentas → `caaguazu-cuentas`
* Permisos (rol + nivel de confianza) → `caaguazu_account_can()`
* Flujo editorial y visibilidad → `caaguazu-portal`
* Fichas turísticas → CPT `promotur_destino`

**Sí aporta lo que faltaba:** el CPT Evento, el rango de precio numérico, los
artículos relacionados, y el icono y color por categoría.

**Artículo y Recorrido se mudaron al panel.** Nacieron acá, pero los dos son
contenido humano con flujo editorial —se escriben, se revisan, los aprueba el
staff—, y eso lo hace `caaguazu-portal`. Mientras vivían acá había que
cargarlos desde wp-admin y la cola de revisión no los alcanzaba. El `post_type`
no cambió, así que no se perdió nada: esta capa los lee y los sirve, y conserva
un registro de respaldo del CPT por si corriera sin el panel.

== Endpoints ==

Namespace: `/wp-json/czu-app/v1/`

= Autenticación =
* `POST /auth/login` — email + contraseña → token bearer
* `POST /auth/logout`
* `GET  /auth/me`

= Contenido =
* `GET /categorias` — con descripción, imagen, icono, color y PNG de marcador
* `GET /etiquetas` — catálogo de tags, para armar chips de filtro
* `GET /inventario` — filtros: `categoria`, `etiqueta`, `bbox`, `buscar`, `tipo_item`, `pagina`, `por_pagina`
* `GET /inventario/{id}`
* `GET /eventos` — filtros: `desde`, `hasta`, `categoria`, `pagina`, `por_pagina`
* `GET /eventos/{id}`
* `GET /mapa/markers`
* `GET /recorridos` — filtros: `pagina`, `por_pagina` · `GET /recorridos/{id}`
* `GET·POST·PUT·DELETE /mis-recorridos` — requiere token
* `GET /articulos`, `GET /articulos/{id}` — filtros: `categoria`, `etiqueta`, `buscar`, `pagina`, `por_pagina`

= Interfaz =
* `GET /strings/{locale}` — `es`, `en`, `gn`
* `GET /media-manifest`

= Sincronización =
* `GET /sync?since={iso8601}`

== Decisiones que conviene conocer ==

**El autor no es `post_author`.** WordPress exige un autor válido en cada
entrada, pero la gente del panel no es usuaria de WordPress, así que el autor
técnico de todo el contenido es el usuario de servicio. El autor que publica la
API sale del meta del dueño real. Si algún día se cambia eso, todos los
artículos van a aparecer firmados por `caaguazu-servicio`.

**`/sync` informa bajas, no solo altas.** Una lista de lo que cambió no
alcanza: si una ficha se despublica y el delta solo trae altas y cambios, el
teléfono la sigue mostrando para siempre. El plugin registra lápidas cada vez
que algo sale de publicado, y las devuelve en `eliminados`. Se conservan 90
días; con un `since` más viejo se responde `completo: true`, que le pide a la
app recargar todo desde cero.

**Costo total y compatibilidad de fechas se calculan, no se guardan.**
Guardarlos duplicaría un dato que cambia cuando cambia una ficha, y quedaría
desactualizado en silencio.

**Los markers van separados del mapa base.** Es lo que mantiene el mapa
retroactivo: se registra un lugar y el pin aparece sin regenerar nada. El mapa
base lo resuelve la app con tiles vectoriales embebidos; este plugin no genera
ni sirve tiles.

**Tabla de tokens propia, no la de sesiones de `caaguazu-cuentas`.** Una sesión
de navegador y un token de teléfono tienen ciclos de vida distintos — cerrar
sesión en la web no debe desloguear el celular. Misma disciplina: se guarda
solo el hash SHA-256, nunca el token.

**`/mapa/markers` precarga meta y términos a propósito.** La consulta pide
sólo IDs (`fields => 'ids'`), y eso es justo lo que la hace liviana — pero
WP_Query se salta el precargado automático de metas y términos cuando pide
sólo IDs. Sin `update_meta_cache()`/`update_object_term_cache()` antes del
loop, cada pin dispara sus propias consultas; con cien sitios no se nota, con
varios miles sí. Mismo motivo detrás del `update_meta_cache()` en los markers
de evento.

== Instalación ==

1. Requiere `caaguazu-cuentas` y `caaguazu-portal` activos.
2. Subir a `/wp-content/plugins/` y activar. Crea sus dos tablas.
3. Cargar icono y color de cada categoría en **Destinos → Categorías**.

== Cambios del contrato en 0.7.0 ==

Sólo suma campos: nada dejó de venir, así que un cliente 0.6.0 sigue andando.

* **`GET /categorias` suma `descripcion`**, una o dos líneas para encabezar
  la pantalla de la categoría en la app. Sale del campo `description` nativo
  del término —no de un meta nuevo— y viene siempre: cadena vacía cuando
  nadie la escribió, nunca `null`.
* **`GET /categorias` suma `imagen`**, la foto que encabeza la categoría, con
  la misma forma de imagen que el resto de la API (`{url,w,h,credito,alt}`)
  o `null`. **No reemplaza a `marker`**: el marker es el PNG chico del pin
  del mapa y sigue viniendo igual. Son dos cosas distintas.
* Las dos se cargan desde el panel, en Estructura (`caaguazu-portal` 3.5.2).
* El objeto `categoria` que va embebido en fichas y artículos **no** cambió:
  sigue siendo el resumen (id, slug, nombre, color), sin descripción ni
  imagen, para no repetir la misma foto en cada ítem de una lista. Quien las
  necesite, cruza por `id` contra `/categorias`, que ya se cachea.

Ver `docs/contrato-app-contenido.md` §4.0.

== Cambios del contrato en 0.6.0 ==

Búsqueda por tag de verdad, la que 0.5.1 dejó pendiente.

* **`GET /etiquetas` es nuevo**: catálogo completo de etiquetas (id, slug,
  nombre, cantidad), como ya tenía `/categorias`. Sin esto no había forma de
  que el cliente supiera qué ids existen para armar un selector.
* **`etiqueta` es filtro nuevo en `GET /inventario`** (ya existía en
  `/articulos`): filtra por id exacto de etiqueta, con `tax_query`, igual de
  barato que el filtro `categoria` que ya tenía. Esto es lo que hace posible
  "buscar por tag" de verdad — `buscar` (texto libre) sigue sin matchear
  nombres de etiqueta, y no lo va a hacer: son dos mecanismos distintos.
* **La lista de `/inventario` y de `/articulos` ahora trae `etiquetas`** en
  cada ítem (antes sólo el detalle la tenía). Es gratis: `WP_Query` ya
  precarga los términos de toda la página en una sola consulta, así que
  mostrar el chip de tag en la tarjeta de lista no agrega ninguna consulta
  extra ni obliga a pedir el detalle primero.

Ver `docs/contrato-app-contenido.md` §2.0 para el detalle completo, con la
diferencia entre `etiqueta` (exacta, para un selector de chips) y `buscar`
(texto libre, con la recomendación de los 350ms de espera del lado del
cliente).

== Cambios del contrato en 0.5.1 ==

* **`GET /articulos` suma el filtro `buscar`**, con el mismo comportamiento
  que ya tenía `/inventario`: texto libre sobre título y cuerpo. Se pidió
  como búsqueda "por tags", pero ni la lista de `/inventario` ni la de
  `/articulos` traen las etiquetas del post —sólo el detalle las tiene—, así
  que filtrar la lista por tag ahí pagaría el precio de cargar cada post
  entero para revisárselas. Queda como texto libre; si en algún momento hace
  falta filtrar de verdad por etiqueta en la lista, hay que sumar
  `etiquetas` al payload de lista de los dos endpoints primero. Ver
  `docs/contrato-app-contenido.md` §2.0.
* Nota para quien conecte esto a un campo de texto: esperá ~350ms desde la
  última letra antes de pedir. Es enteramente del lado del cliente, la API
  no hace nada especial para eso.

== Cambios del contrato en 0.5.0 ==

Están detallados, con payloads, en `docs/contrato-app-contenido.md`. La lista
completa de campos, generada desde el código, está en
`docs/datos-para-la-app.md`.

* **Se retira Zona.** `GET /zonas` deja de existir, `/inventario` pierde el
  filtro `zona` y el objeto `zona` de la lista y el detalle, y `/eventos` deja
  de traer la taxonomía en sus términos. El departamento es chico y el enlace
  de Google Maps de cada ficha ya dice dónde queda; la taxonomía sigue
  registrada del lado del panel, sólo se dejó de exponer acá.
* **Se retiran `gancho` y `accesibilidad` de la ficha**, siguiendo a la poda
  del modelo en `caaguazu-portal` 3.5.0: no le aportaban nada a la app que el
  título, la portada y el enlace de Maps no dieran ya. En `/eventos`, la
  tarjeta de un evento cargado como ficha (`origen: "ficha"`) ahora arma
  `resumen` con la descripción en vez del gancho retirado.
* **La descripción de la ficha se llama `descripcion`, no `articulo_html`.**
  Era un bug, no un cambio de diseño: el nombre se copió sin pensar de la
  respuesta de Artículos al armar el detalle de ficha, y la app la buscaba
  como `descripcion` — esa clave nunca existió y la descripción nunca
  llegaba. `articulo_html` sigue viajando igual en Recorrido y en el evento
  legado (`/eventos/{id}` con `origen: "evento_legado"`): ahí sí es un
  artículo, el nombre no está mal.
* **`GET /recorridos` ahora pagina**, con el mismo sobre que ya usaban
  inventario y artículos (`{ items, total, pagina, por_pagina }`) y los
  mismos parámetros `pagina`/`por_pagina`. Antes devolvía un array plano con
  tope fijo de 50 sin forma de pedir el resto.
* **ETag y `Cache-Control` en los ocho endpoints de contenido** (lista y
  detalle de inventario, artículos, recorridos y eventos), como ya tenían
  `/categorias`, `/mapa/markers`, `/strings` y `/media-manifest`. Mandá
  `If-None-Match`: si no cambió, la respuesta es `304` sin cuerpo. No aplica
  al detalle de un recorrido de usuario (es de una sola cuenta).

== Cambios del contrato en 0.4.0 ==

Están detallados, con payloads, en `docs/contrato-app-contenido.md` §1.5. La
lista completa de campos, generada desde el código, está en
`docs/datos-para-la-app.md`.

* **Un evento es una ficha con fechas.** La ficha suma `tipo_item` (`sitio` o
  `evento`) y `fechas` (`inicio`, `fin`, `en_curso`, `terminado`), en la lista,
  en el detalle y en los markers. `/inventario` acepta `?tipo_item=` para
  filtrar; `sitio` incluye todo lo cargado antes de que el tipo existiera.
* **`/eventos` mezcla dos fuentes y lo dice.** Los eventos que salen de una
  ficha vienen con `origen: "ficha"` y su `ficha_id`; los del CPT
  `promotur_evento`, con `origen: "evento_legado"`. `/eventos/{id}` responde a
  los dos: si el id es una ficha, devuelve la ficha entera y no una versión
  recortada.
* **Un evento de `origen: ficha` sincroniza en `inventario`**, no en `eventos`,
  porque es una ficha. Para la caché local conviene clonar `inventario` y armar
  la agenda en el teléfono: así funciona sin conexión.
* **Las paradas de un recorrido reconocen los eventos nuevos.** Una parada que
  referencia una ficha de tipo evento trae ahora `inicio` y `fin`, y sigue
  trayendo coordenadas, costo, horario y enlace de Maps como cualquier ficha.
* **Nada dejó de venir.** Un cliente 0.3.0 sigue funcionando: `tipo_item` vale
  `"sitio"` en todo lo que ya tenía. Lo que cambia es que `/inventario` puede
  devolver eventos — si la pantalla de inventario no los quiere mezclados,
  `?tipo_item=sitio`.

== Cambios del contrato en 0.3.0 ==

Están detallados, con payloads, en `docs/contrato-app-contenido.md`.

* **La ficha suma `google_maps`** y pierde `practicos.duracion`,
  `practicos.servicios`, `practicos.temporada`, `acceso.como_llegar` y
  `acceso.referencia`. El enlace de Google Maps pasó a ser el modo por defecto
  de cargar la ubicación en el panel; las coordenadas siguen viniendo, y se
  derivan del enlace cuando se pueden leer de él.
* **El artículo suma `antetitulo`, `subtitulo`, `autores`, `fuentes` y
  `etiquetas`**, y su `bajada` se llama ahora `entradilla`.
* **El recorrido suma `medios`, `articulos` y `google_maps`**, y cada parada
  suma `texto` (que reemplaza a `nota`), `medio` y `google_maps`.

== Pendiente ==

* Escritura desde la app (`POST /contenido`) para que un promotor cargue
  fichas desde el teléfono. La lectura ya está; falta el alta.
* El CPT `promotur_evento` es sólo de lectura en la práctica: no se carga más
  desde ningún lado. Se puede retirar cuando lo que quedó cargado ahí se haya
  vuelto a cargar como ficha, o cuando pase la retención de lápidas y ningún
  teléfono lo tenga en caché.
* Pantalla de panel para editar textos e imágenes de UI. Existió (sección
  «App» de `caaguazu-portal`) y está desconectada: llamaba a `get_strings()`,
  `get_manifest()` y `set_manifest()`, que existen desde 0.2.0, contra una
  instalación 0.1.0 — y moría con un error fatal. Se vuelve a enchufar cuando
  la versión instalada acá sea la que esa pantalla necesita.
* Este plugin todavía no tiene auto-updater, igual que `caaguazu-cuentas` y
  `caaguazu-sso-cead`.
