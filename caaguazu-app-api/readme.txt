=== Caaguazú App API ===
Contributors: municipalidadcaaguazu
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 0.4.0
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
* `GET /categorias` — con icono, color y PNG de marcador
* `GET /zonas`
* `GET /inventario` — filtros: `categoria`, `zona`, `bbox`, `buscar`, `pagina`, `por_pagina`
* `GET /inventario/{id}`
* `GET /eventos` — filtros: `desde`, `hasta`, `categoria`
* `GET /eventos/{id}`
* `GET /mapa/markers`
* `GET /recorridos`, `GET /recorridos/{id}`
* `GET·POST·PUT·DELETE /mis-recorridos` — requiere token
* `GET /articulos`, `GET /articulos/{id}` — filtros: `categoria`, `etiqueta`

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

== Instalación ==

1. Requiere `caaguazu-cuentas` y `caaguazu-portal` activos.
2. Subir a `/wp-content/plugins/` y activar. Crea sus dos tablas.
3. Cargar icono y color de cada categoría en **Destinos → Categorías**.

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
