# Brief — Turismo App Czu

**Para:** el agente que construye el APK.
**De:** el lado del panel (`caaguazu-portal` + `caaguazu-cuentas`, WordPress en `caaguazu.net`).
**Estado:** contrato propuesto. Del lado del panel todavía no hay API — ver §4.

Las referencias visuales (`Style`, `Inventario-UI`, `Recorrido-UI`, `Main-bar`, `Articulos`, `Mapa`, `AI`) te las pasa el dueño del proyecto en un zip aparte. Este documento no cubre UI.

---

## 1. División de trabajo

| | Lado panel (yo) | Lado app (vos) |
|---|---|---|
| Identidad, cuentas, sesiones | ✅ Construyo | ❌ No construyas |
| Roles y permisos | ✅ Construyo | Consumís |
| Flujo de revisión editorial | ✅ Construyo | Consumís |
| Contenido (fichas, eventos, recorridos, artículos) | ✅ Construyo el CRUD y el almacenamiento | Consumís |
| API REST | ✅ Construyo | Consumís |
| Tiles del mapa | ✅ Genero y sirvo | Renderizás |
| Textos e imágenes de UI | ✅ Sirvo por endpoint | Consumís |
| **La app entera** | ❌ | ✅ **Tuyo** |

**Punto de contacto único: la API de §4.** Nada de acceso directo a la base de datos ni de replicar lógica de permisos del lado de la app.

---

## 2. Qué es el panel (lo que ya existe y vas a consumir)

WordPress con plugins propios. Lo relevante para vos:

**Sistema de identidad propio, separado de los usuarios de WordPress.** Tres tablas: `caaguazu_accounts` (email + hash), `caaguazu_sessions` (guarda solo el hash SHA-256 del token), `caaguazu_grants` (permisos por panel).

**Contenido ya modelado.** El CPT `promotur_destino` es la ficha turística, y ya trae los campos que vas a mostrar:

`gancho` · `portada` · `credito_fotos` · `video` · `lat` · `lng` · `referencia` · `como_llegar` · `estado_camino` · `accesibilidad` · `horario` · `temporada` · `costo` · `servicios` · `duracion` · `contacto` · `fuentes`

**Flujo editorial de 8 estados** — `borrador → enviado → en_revision → necesita_cambios → aprobado → publicado → despublicado → archivado`. Solo `publicado` es visible al público. **La app nunca ve nada que no esté publicado**, salvo el contenido propio de un promotor autenticado.

**Taxonomías:** `promotur_categoria`, `promotur_zona`, `promotur_etiqueta`.

---

## 3. Los tres tipos de usuario

Ya existen como roles del panel. No inventes un sistema paralelo.

| Tipo | Qué puede en la app |
|---|---|
| **Usuario** | Leer todo lo publicado. Crear sus propios recorridos. Sin cuenta obligatoria para navegar. |
| **Promotor** | Lo anterior + crear y editar fichas, que van a la cola de revisión. **No publica.** |
| **Staff** | Todo, incluido publicar y crear categorías. |

El rol viene en la respuesta del login. **Gateá la UI por lo que devuelve el servidor, nunca por lógica local** — el servidor revalida cada escritura de todos modos.

---

## 4. El contrato: la API

> **Importante para tu planificación: hoy esta API no existe.** El panel corre sobre `admin-ajax` y templates renderizados en servidor, con cero `register_rest_route`. Además, los metadatos propios (lat, lng, horario, costo, estado) están marcados `show_in_rest: false`, así que tampoco salen por los endpoints nativos de WordPress.
>
> La voy a construir contra este contrato. **Vos arrancá contra mocks** (§5) — no esperes.

Namespace: `/wp-json/czu-app/v1/`

### 4.1 Autenticación

Las sesiones actuales son cookie firmada con HMAC (`HttpOnly`, `SameSite=Lax`) — sirve para navegador, no para app nativa. Voy a agregar modo **bearer** sobre la misma tabla de sesiones.

```http
POST /auth/login
{ "email": "…", "password": "…" }

200 → { "token": "…", "expires_at": "2026-09-08T…Z",
        "cuenta": { "id": 41, "nombre": "…", "email": "…", "rol": "promotor" } }
```

Después: `Authorization: Bearer <token>` en cada request autenticado.

- `401` → token inválido o vencido. Borrá el token local y mandá al login.
- `403` → autenticado pero sin permiso. No reintentes.
- Guardá el token en almacenamiento seguro del sistema, no en preferencias planas.
- `POST /auth/logout` invalida el token del lado servidor.

**Navegar no requiere cuenta.** Solo escribir y sincronizar recorridos propios.

### 4.2 Endpoints

| Método | Ruta | Auth | Para qué |
|---|---|---|---|
| `POST` | `/auth/login` | — | Token |
| `POST` | `/auth/logout` | bearer | Cerrar sesión |
| `GET` | `/categorias` | — | Categorías con icono y color |
| `GET` | `/inventario` | — | Fichas publicadas. Filtros: `categoria`, `zona`, `bbox`, `page` |
| `GET` | `/inventario/{id}` | — | Ficha completa |
| `GET` | `/eventos` | — | Filtros: `desde`, `hasta` |
| `GET` | `/mapa/markers` | — | Payload liviano para el mapa |
| `GET` | `/recorridos` | — | Prehechos publicados |
| `GET` | `/recorridos/{id}` | — | Con bloque de historia |
| `GET·POST·PUT·DELETE` | `/mis-recorridos` | bearer | Recorridos del usuario |
| `GET` | `/articulos` | — | Listado paginado |
| `GET` | `/articulos/{id}` | — | Artículo completo |
| `GET` | `/strings/{locale}` | — | Textos de UI (§7) |
| `GET` | `/media-manifest` | — | Imágenes de UI (§7) |
| `POST·PUT` | `/contenido` | bearer + promotor | Alta y edición de fichas desde la app |
| `GET` | `/sync?since={iso8601}` | — | Delta para caché offline |

### 4.3 Payloads

**`GET /categorias`**

```json
[
  { "id": 12, "slug": "paisaje-natural", "nombre": "Paisaje Natural",
    "icono": "nature", "color": "#2E7D32",
    "marker": "https://…/markers/paisaje-natural.png" }
]
```

`marker` es un PNG pre-renderizado del pin. **No compongas el pin en el cliente** — el color y el icono se resuelven del lado servidor al guardar la categoría.

**`GET /mapa/markers`** — separado de `/inventario` a propósito: el mapa necesita muchos pines livianos, no fichas completas.

```json
[
  { "id": 41, "tipo": "destino", "lat": -25.4669, "lng": -56.0175, "categoria": 12 },
  { "id": 12, "tipo": "evento",  "lat": -25.4701, "lng": -56.0220, "categoria": 8 }
]
```

**`GET /inventario/{id}`**

```json
{
  "id": 41,
  "tipo": "destino",
  "titulo": "…",
  "gancho": "…",
  "categoria": { "id": 12, "nombre": "Paisaje Natural", "color": "#2E7D32" },
  "zona": { "id": 3, "nombre": "…" },
  "coordenadas": { "lat": -25.4669, "lng": -56.0175 },
  "portada": { "url": "…", "credito": "…" },
  "galeria": [ { "url": "…" } ],
  "video": null,
  "practicos": {
    "horario": "…", "costo": "…", "duracion": "…",
    "servicios": "…", "temporada": "…", "contacto": "…"
  },
  "acceso": {
    "como_llegar": "…", "referencia": "…",
    "estado_camino": "asfalto", "accesibilidad": "…"
  },
  "articulo_html": "…",
  "actualizado": "2026-08-20T14:00:00Z"
}
```

**`GET /eventos`**

```json
[
  { "id": 12, "titulo": "…",
    "inicio": "2026-09-14T19:00:00Z", "fin": "2026-09-14T23:00:00Z",
    "lugar": { "ref_tipo": "destino", "ref_id": 41,
               "lat": -25.4669, "lng": -56.0175 },
    "costo": "Gratis",
    "categoria": { "id": 8, "nombre": "…", "color": "#…" },
    "articulo_html": "…" }
]
```

**`GET /recorridos/{id}`**

```json
{
  "id": 88,
  "tipo": "prehecho",
  "titulo": "…",
  "duracion_estimada": "4h",
  "paradas": [
    { "orden": 1, "ref_tipo": "destino", "ref_id": 41, "nota": "" },
    { "orden": 2, "ref_tipo": "evento",  "ref_id": 12, "nota": "" }
  ],
  "historia": {
    "introduccion": "…",
    "correlacion": "…",
    "personas": [],
    "curiosidades": [],
    "articulos_ref": [ 55 ]
  }
}
```

`historia` viene vacío en los recorridos de usuario y completo en los prehechos.

**`POST /mis-recorridos`** — mandá solo `titulo` y `paradas`. `costo_total` y compatibilidad de fechas **se calculan**, no se envían ni se guardan.

---

## 5. Cómo arrancar sin esperar la API

No te bloquees. Los payloads de §4.3 son el contrato:

1. Levantá un mock server con esos JSON fijos.
2. Poné la URL base en configuración, no hardcodeada.
3. Aislá todo el acceso a red en una capa: cuando la API real esté, cambiás una implementación y nada más.
4. Cuando la tengas, avisame y validamos contra los payloads reales.

Si algo del contrato no te cierra para la app, **decilo antes de que yo lo implemente** — es mucho más barato cambiar el contrato ahora.

---

## 6. Mapa

Decidido: **tiles, no mapa live.**

| Pieza | Quién | Qué |
|---|---|---|
| Tiles | Yo | Pirámide raster del área de Caaguazú, zoom ~10–17 |
| Renderizado | Vos | Librería de tiles offline-capable |
| Markers | Vos | Overlay desde `/mapa/markers` — **nunca quemados en los tiles** |
| Icono del pin | Yo | Viene en `/categorias` como PNG |
| Tap en pin | Vos | Abre la ficha (`/inventario/{id}` o `/eventos/{id}`) |

Que los markers vayan **separados** de los tiles es lo que hace el mapa "retroactivo": el staff registra un lugar y el pin aparece sin regenerar ni redistribuir nada.

Pendiente de definir con el dueño del proyecto: extensión geográfica exacta y zoom máximo — determina si los tiles van embebidos en el APK o se descargan.

---

## 7. Textos e imágenes: nada hardcodeado

Requisito del proyecto: **cualquier texto o imagen de la interfaz debe poder cambiarse sin publicar una versión nueva del APK.**

```json
GET /strings/es
{
  "nav.inventario": "Inventario",
  "nav.mapa": "Mapa",
  "nav.recorridos": "Recorridos",
  "empty.recorridos": "…"
}
```

```json
GET /media-manifest
{
  "splash.fondo": { "url": "…", "alt": "" },
  "home.hero":    { "url": "…", "alt": "" }
}
```

- Locales disponibles: `es`, `en`, `gn`.
- Cacheá con `ETag`; refrescá al abrir la app y seguí con la copia local si falla.
- Embebé una copia de `es` como fallback de primer arranque sin red.
- **No aplica** a fichas, eventos, recorridos ni artículos: eso es contenido humano y viene de la base.

---

## 8. Restricciones de producto

Vienen del dueño del proyecto y condicionan cómo construís:

1. **Público mayor.** Explicaciones animadas antes que párrafos. Todo claro y directo. Esto es un requisito, no una preferencia estética.
2. **No escribas contenido.** Ni artículos, ni descripciones, ni copy de relleno, ni textos de ejemplo que después queden en producción. Todo texto de producto lo escribe una persona. Si necesitás un placeholder para maquetar, marcalo inequívocamente como tal.
3. **No inventes bocetos visuales.** Las referencias vienen en el zip.

---

## 9. IA — no la construyas todavía

Va al final del desarrollo. Lo que sí tenés que respetar desde ahora, porque después sale caro:

1. **Toda lectura de datos pasa por la API.** Si la app arma estado propio que no existe del lado servidor, la IA no lo va a poder ver.
2. **El acceso de la IA a datos del usuario es con permiso explícito.** Modelalo como un permiso más, revocable, no como un flag global.
3. **La IA va a consumir la misma API** con su propio token y alcance acotado. No abras un canal aparte.

---

## 10. Lo que NO construís

- Sistema de cuentas, login propio o almacenamiento de contraseñas
- Lógica de roles o permisos que no venga del servidor
- Flujo de revisión editorial
- Panel de administración (existe en web)
- Base de datos propia de contenido — solo caché local, que es descartable por definición

---

## 11. Decisiones abiertas que te afectan

Las resuelve el dueño del proyecto. Te las paso porque cambian tu trabajo:

| # | Pregunta | Te afecta en |
|---|---|---|
| 1 | Hay dos entidades "lugar con coordenadas" en el sistema (`promotur_destino` y `cgz_local`, este último de un plugin de comercios con reservas y reseñas). ¿Se unifican, coexisten o se separan por rol? | Si `/inventario` devuelve uno o varios `tipo` |
| 2 | Recorridos de usuario: ¿se guardan en el servidor o solo en el teléfono? | Si el usuario necesita cuenta; si existe `/mis-recorridos` |
| 3 | Extensión y zoom del mapa | Peso del APK |
| 4 | ¿Los alumnos del CEAD (que entran por SSO) son Promotores de la app? | Volumen de la cola de revisión |

Mi recomendación en la 1 es separar por rol —Atractivo, Evento, Comercio— y que `/inventario` devuelva el campo `tipo`. **Programá asumiendo que `tipo` existe**; así ninguna de las tres salidas te rompe el cliente.

---

## 12. Coordinación

- El contrato de §4 es la fuente de verdad compartida. Cambios de un lado se avisan antes de implementar.
- Si un endpoint no te alcanza, pedí el cambio en vez de compensarlo con parches en el cliente.
- Cuando tengas los mocks andando, avisá y arrancamos la integración real endpoint por endpoint.
