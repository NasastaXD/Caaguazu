# Turismo App Czu — integración con el panel existente

**Fecha:** 25/08/2026 · **Alcance:** arquitectura, datos, API y sincronización. No cubre UI.

> **Faltan las referencias visuales.** `Style.PDF`, `Inventario-UI.pdf`, `Recorrido-UI.pdf`, `Main-bar.PDF`, `Articulos.PDF`, `Mapa.PDF`, `AI.PDF` no llegaron adjuntos. Todo lo de UI queda fuera de este documento.

---

## 1. La decisión central

**Un solo backend. La app es un cliente, no un sistema aparte.**

No sincronizar dos paneles: tener uno.

| Opción | Costo | Veredicto |
|---|---|---|
| **A. Extender el panel actual, la app consume su API** | API nueva + 3 entidades nuevas | ✅ **Recomendada** |
| B. Backend nuevo + sync con WordPress | Doble fuente de verdad, resolución de conflictos, doble identidad | ❌ |
| C. Backend nuevo como fuente, WP consume | Reescribir cuentas, roles, revisión, SSO CEAD | ❌ |

Razón: el panel actual **ya tiene** lo caro de construir — identidad propia, roles por panel, flujo de revisión, SSO del CEAD, coordenadas por ficha. Lo que falta es API y tres entidades.

```
┌──────────────┐     ┌──────────────┐
│  App (APK)   │     │ Panel (web)  │   ← staff + promotores
└──────┬───────┘     └──────┬───────┘
       │ REST + bearer      │ sesión cookie (ya existe)
       └─────────┬──────────┘
        ┌────────▼─────────┐
        │  WordPress       │   ← única fuente de verdad
        │  caaguazu-portal │
        │  caaguazu-cuentas│
        └──────────────────┘
```

---

## 2. Qué ya existe y sirve

| Pieza | Estado | Uso en la app |
|---|---|---|
| CPT `promotur_destino` + lat/lng | ✅ Listo | Base del Inventario |
| Taxonomías `promotur_categoria` / `zona` / `etiqueta` | ✅ Listo | Categorías (faltan icono y color) |
| Flujo editorial 8 estados | ✅ Listo | "Promotor publica bajo revisión" ya funciona |
| Checklist de mínimos (bloquea envío) | ✅ Listo | Control de calidad de fichas |
| `caaguazu_accounts` / `sessions` / `grants` | ✅ Listo | Los 3 tipos de usuario |
| Roles Promotor / Mini / Visitante | ✅ Listo | Mapean a Staff / Promotor / Usuario |
| SSO CEAD | ⚠️ Código listo, endpoint del colegio no existe | Entrada de alumnos |
| Auditoría `promotur_audit_log` | ✅ Listo | Trazabilidad |
| Selector ES / EN / GN | ✅ Listo | Base de i18n |
| CPT `cgz_local` + lat/lng (Locales) | ⚠️ Duplica el concepto "lugar" | Ver §3 |

### Campos que ya tiene una ficha `promotur_destino`

`_promotur_gancho`✱ · `_promotur_portada`✱ · `_promotur_credito_fotos`✱ · `_promotur_video` · `_promotur_lat`✱ · `_promotur_lng`✱ · `_promotur_referencia` · `_promotur_como_llegar`✱ · `_promotur_estado_camino` · `_promotur_accesibilidad` · `_promotur_horario`✱ · `_promotur_temporada` · `_promotur_costo`✱ · `_promotur_servicios` · `_promotur_duracion` · `_promotur_contacto` · `_promotur_fuentes`

✱ = obligatorio (checklist)

Cubre casi todo lo que pedís para "registro de lugar turístico". Falta: rango de horarios estructurado (hoy es texto libre).

---

## 3. El problema a resolver antes de programar

**Hay dos entidades "lugar" con coordenadas: `promotur_destino` y `cgz_local`.**

| | `promotur_destino` (Portal) | `cgz_local` (Locales) |
|---|---|---|
| Qué es | Ficha turística curada | Negocio (restaurante, hotel, comercio, atracción) |
| Coordenadas | Sí | Sí |
| Quién carga | Promotor, con revisión | Dueño del local / admin |
| Extras | Checklist, flujo editorial | Reservas WhatsApp, reseñas |

Ambos son "atractivos con un pin en el mapa". Si quedan separados, **cada funcionalidad de la app tiene que manejar dos tipos para siempre**: el mapa, el inventario, los recorridos, la IA.

Tres salidas:

| | Qué implica |
|---|---|
| **Unificar** en `promotur_destino`, migrar los locales | Migración de datos; se pierde/reubica reservas y reseñas |
| **Coexistir** con capa de abstracción | Un endpoint que devuelve ambos normalizados; complejidad permanente pero acotada a un punto |
| **Separar por rol**: destino = atractivo turístico, local = comercio | Limpio conceptualmente; el mapa muestra ambos con iconografía distinta |

Recomendación: **la tercera**. Encaja con tu propio planteo ("Atractivo" y "Evento" como categorías generales separadas) — sumaría "Comercio" como tercera. Sin migración, sin ambigüedad.

**Necesito tu decisión acá antes de definir el esquema.**

---

## 4. Entidades nuevas

### 4.1 Categoría — extender la taxonomía existente

`promotur_categoria` existe pero no tiene icono ni color. Agregar term meta:

```json
{
  "term_id": 12,
  "slug": "paisaje-natural",
  "name": "Paisaje Natural",
  "icon": "nature",
  "color": "#2E7D32",
  "marker": "https://…/markers/paisaje-natural.png"
}
```

- `icon` — clave del sistema de íconos del theme (23 SVG ya disponibles) o subida propia
- `color` — hex, para el pin y el chip de la ficha
- `marker` — PNG pre-renderizado del pin, generado al guardar (evita componer el pin en el cliente)

Creación: **staff only**.

### 4.2 Evento — CPT nuevo `promotur_evento`

| Campo | Tipo | Obligatorio |
|---|---|---|
| `titulo` | texto | ✱ |
| `_evento_inicio` | datetime | ✱ |
| `_evento_fin` | datetime | |
| `_evento_lugar_ref` | ID de `promotur_destino` **o** lat/lng propias | ✱ |
| `_evento_costo` | texto | |
| `_evento_categoria` | término | ✱ |
| cuerpo | artículo | ✱ |

Reusa el mismo flujo editorial y el mismo checklist.

### 4.3 Recorrido — CPT nuevo `promotur_recorrido`

Dos variantes con el mismo esquema base:

```json
{
  "id": 88,
  "tipo": "prehecho",
  "titulo": "…",
  "paradas": [
    { "orden": 1, "ref_tipo": "destino", "ref_id": 41, "nota": "" },
    { "orden": 2, "ref_tipo": "evento",  "ref_id": 12, "nota": "" }
  ],
  "duracion_estimada": "4h",
  "costo_total": null,
  "historia": {
    "introduccion": "",
    "correlacion": "",
    "personas": [],
    "curiosidades": [],
    "articulos_ref": []
  }
}
```

- `tipo: "usuario"` — solo `paradas`; `historia` vacío. Creado desde la app.
- `tipo: "prehecho"` — creado en el panel, con `historia` completa.
- `costo_total` y compatibilidad de fechas se **calculan** de las paradas, no se guardan.
- Exportar a Google Maps: `https://www.google.com/maps/dir/?api=1&waypoints=lat,lng|lat,lng…` armado en el cliente.

**Decisión pendiente:** ¿los recorridos del usuario se guardan en el servidor (sincronizan entre dispositivos, requieren cuenta) o quedan locales en el teléfono (sin cuenta, se pierden al reinstalar)?

### 4.4 Artículos

Ya existe `Noticias` como categoría de posts en `caaguazu-modulos` (repo `Caaguazu`). Dos caminos:

| | |
|---|---|
| Reusar posts + categoría | Sin código nuevo; los artículos viven con las noticias del portal |
| CPT `promotur_articulo` | Separado del portal institucional; el panel de turismo los gestiona solo |

Recomendación: **CPT propio**. El panel de turismo no debería depender de un plugin de otro repo.

---

## 5. Usuarios y permisos

El sistema de grants existente (`cuenta` × `panel` × `rol`) mapea directo:

| App | Panel actual | Acceso |
|---|---|---|
| **Staff** | `promotur_promotor` | Todo: crear, editar, revisar, publicar, categorías, recorridos prehechos |
| **Promotor** | `promotur_mini` | Crea y edita; **no publica** — pasa por revisión |
| **Usuario** | cuenta sin grant | Solo lectura + sus propios recorridos y favoritos |

Sin roles nuevos. Un usuario de la app es una fila en `caaguazu_accounts` sin grant en el panel.

**Nota sobre CEAD:** los alumnos entran hoy como `promotur_mini` = **Promotor** de la app. Sus fichas irían a la cola de revisión del staff. Confirmá que es lo que querés.

---

## 6. Lo que falta construir: la API

**Hoy no hay ninguna API.** Cero `register_rest_route`. El panel corre sobre `admin-ajax` y templates server-side. Los CPTs tienen `show_in_rest: true`, pero **todos los metas propios están en `show_in_rest: false`** — o sea lat, lng, horario, costo y el estado editorial no salen por REST.

Esto es el grueso del trabajo de integración.

### 6.1 Autenticación

Las sesiones actuales son **cookie firmada con HMAC, HttpOnly, SameSite=Lax** — sirve para navegador, no para una app nativa.

Buena noticia: `caaguazu_sessions` **ya guarda tokens hasheados** (SHA-256), no cookies. Agregar un modo bearer es una extensión chica:

| Hoy | Agregar |
|---|---|
| `start()` → setea cookie | `start()` → además devuelve el token en la respuesta |
| `resolve()` → lee `$_COOKIE` | `resolve()` → si no hay cookie, lee `Authorization: Bearer` |

Mismo almacenamiento, misma expiración, mismo `hash_equals`. Sin sistema de auth paralelo.

### 6.2 Endpoints

Namespace sugerido: `/wp-json/czu-app/v1/`

| Método | Ruta | Auth | Devuelve |
|---|---|---|---|
| `POST` | `/auth/login` | — | token + perfil |
| `POST` | `/auth/logout` | bearer | — |
| `GET` | `/categorias` | — | términos + icono + color + marker |
| `GET` | `/inventario` | — | fichas publicadas (filtros: categoría, zona, bbox) |
| `GET` | `/inventario/{id}` | — | ficha completa |
| `GET` | `/eventos` | — | eventos (filtro: rango de fechas) |
| `GET` | `/mapa/markers` | — | payload liviano: `id, lat, lng, categoria, tipo` |
| `GET` | `/recorridos` | — | prehechos publicados |
| `GET` | `/recorridos/{id}` | — | con `historia` |
| `GET/POST/PUT/DELETE` | `/mis-recorridos` | bearer | recorridos del usuario |
| `GET` | `/articulos` | — | listado |
| `GET` | `/strings/{locale}` | — | textos de UI (§8) |
| `GET` | `/media-manifest` | — | imágenes de UI (§8) |
| `POST` | `/contenido` | bearer + rol | crear ficha desde la app (promotor) |
| `GET` | `/sync?since={ts}` | — | delta para caché offline |

`/mapa/markers` va separado de `/inventario` a propósito: el mapa necesita miles de pines livianos, no fichas completas.

### 6.3 Offline

Público mayor, conectividad irregular. El `/sync?since=` permite caché incremental. Los tiles se cachean aparte (§7).

---

## 7. Mapa

Decidido: **tiles, no mapa live.**

| Pieza | Definición |
|---|---|
| Tiles | Pirámide raster del área de Caaguazú, zoom ~10–17 |
| Origen | Pre-renderizados y servidos estáticos, o empaquetados en el APK |
| Markers | Overlay desde `/mapa/markers`, **no** quemados en los tiles |
| Icono | Por categoría (§4.1), único, viene del term meta |
| Tap | Abre la ficha del inventario |

Separar tiles de markers es lo que hace el mapa "retroactivo": registrás un lugar y el pin aparece sin regenerar nada.

**A definir:** extensión geográfica exacta y zoom máximo — determina el peso del APK si van embebidos.

---

## 8. Textos e imágenes editables

Pediste que todo texto interno sea modificable sin tocar código.

```json
// strings/es.json
{
  "nav.inventario": "Inventario",
  "nav.mapa": "Mapa",
  "nav.recorridos": "Recorridos",
  "empty.recorridos": "…"
}
```

- Clave plana con prefijo de sección
- Un archivo por locale (`es`, `en`, `gn` — ya existen en el portal)
- Servido por `/strings/{locale}`, editable desde el panel, cacheado con ETag
- **No aplica** a artículos, fichas ni recorridos: eso es contenido humano en la base

```json
// media-manifest.json
{
  "splash.fondo": { "id": 412, "url": "…", "alt": "" },
  "home.hero":    { "id": 418, "url": "…", "alt": "" }
}
```

Clave lógica → adjunto de la biblioteca de medios. Cambiar una imagen = cambiar el adjunto, sin release del APK.

---

## 9. IA — qué dejar preparado ahora

No construir nada. Tres decisiones que, si se toman mal ahora, cuestan caro después:

1. **Esquema de contenido estable y legible.** Los endpoints de §6.2 ya sirven como fuente; que devuelvan texto limpio, no HTML del editor.
2. **Permiso explícito y granular.** La IA accede a datos del usuario (sus recorridos) solo con consentimiento. Modelarlo como un grant más en `caaguazu_grants`, no como un flag suelto.
3. **Frontera de lectura.** La IA lee por la misma API que la app, con su propio token y alcance acotado — nunca contra la base directamente.

Sin esto, "conectarle una IA después" implica reescribir la capa de datos.

---

## 10. Decisiones que necesito

| # | Decisión | Bloquea |
|---|---|---|
| 1 | `promotur_destino` vs `cgz_local` (§3) | El esquema entero |
| 2 | Recorridos de usuario: ¿servidor o local? (§4.3) | Si el usuario necesita cuenta |
| 3 | Artículos: ¿CPT propio o reusar posts? (§4.4) | Alcance del panel |
| 4 | ¿Los alumnos del CEAD son Promotores de la app? (§5) | Flujo de revisión |
| 5 | Extensión y zoom del mapa (§7) | Peso del APK |
| 6 | Las 21 páginas de `caaguazu-turismo`: ¿entran a la app o quedan web? | Alcance del contenido |

---

## 11. Orden de trabajo

| Fase | Qué | Depende de |
|---|---|---|
| 0 | Resolver §10.1 y §10.3 | — |
| 1 | Bearer auth sobre `caaguazu_sessions` | — |
| 2 | Term meta de categorías (icono, color, marker) | §10.1 |
| 3 | Endpoints de lectura: categorías, inventario, markers | 1, 2 |
| 4 | CPT Evento | §10.1 |
| 5 | CPT Recorrido + endpoints | §10.2 |
| 6 | Artículos | §10.3 |
| 7 | `strings` + `media-manifest` | — |
| 8 | Tiles | §10.5 |
| 9 | `/sync` + caché offline | 3–6 |
| 10 | Escritura desde la app (promotor) | 1, 3 |
| 11 | IA | Todo |

Las fases 1, 7 y 8 son independientes: pueden ir en paralelo con lo demás.

---

## 12. Riesgo abierto

`caaguazu-cuentas` y `caaguazu-sso-cead` **no tienen auto-updater** — se instalan y actualizan a mano. Hoy es incómodo; cuando sean el backend de una app publicada, un parche de seguridad urgente depende de que alguien suba un zip por FTP.

Conviene resolverlo antes de que la app esté en la calle.
