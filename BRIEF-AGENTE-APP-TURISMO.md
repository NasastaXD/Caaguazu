# Brief — Turismo App Czu

**Para:** el agente que construye el APK.
**De:** el lado del panel (WordPress en `caaguazu.net`).

Las referencias visuales las pasa el dueño del proyecto en un zip aparte. Acá no hay UI: esto es qué existe, cómo funciona, y qué necesito de vos.

---

# PARTE 1 — Qué tenemos hoy

## 1.1 El contexto

`caaguazu.net` es el portal oficial del departamento de Caaguazú, Paraguay. Tiene varios "ecosistemas" (Turismo, Educación, Noticias, Agenda). **Turismo es uno de ellos, y ya está construido y funcionando en web.**

El sistema de turismo son cinco plugins de WordPress repartidos en dos repositorios:

| Plugin | Qué es |
|---|---|
| `caaguazu-portal` | **El panel.** Panel de promotores turísticos. Es el que te importa. |
| `caaguazu-cuentas` | Sistema de identidad propio (no usa usuarios de WordPress) |
| `caaguazu-locales` | Directorio de comercios: reservas por WhatsApp, reseñas, mapa |
| `caaguazu-sso-cead` | Entrada de un clic desde el colegio CEAD |
| `caaguazu-turismo` | 21 páginas estáticas de contenido turístico (historia, gastronomía, cultura) |

La app es **un cliente nuevo sobre ese sistema que ya existe**, no un reemplazo.

## 1.2 El panel no es una pantalla de wp-admin

Esto es lo primero que hay que entender: `caaguazu-portal` **no es un plugin que agrega pantallas al escritorio de WordPress**. Es una aplicación completa montada sobre rutas propias:

```
/czu-login              login propio
/registro               alta, solo por invitación
/recuperar              recuperación de contraseña
/salir                  cerrar sesión
/i/{token}              link de invitación
/turismo/panel/...      el panel
/promotur-manifest.webmanifest, /promotur-sw.js, /promotur-offline   PWA
```

Tiene **su propio shell visual** —sidebar + topbar + contenido, modo claro/oscuro— y deliberadamente **no usa el theme del sitio**. Es instalable como PWA. Un promotor nunca ve wp-admin: entra a `/czu-login`, cae en `/turismo/panel` y trabaja ahí adentro.

O sea: **ya existe una "app" de gestión, en web.** Lo que vas a construir es la app de consumo (y parcialmente de carga) en Android.

## 1.3 Cómo se usa hoy, paso a paso

Este es el flujo real, no una idealización:

1. **Un promotor entra** a `/czu-login` con email y contraseña. No es un usuario de WordPress (§1.4).
2. **Cae en el panel**, sección Inicio: su pulso — sus fichas por estado, tareas asignadas, qué falta revisar.
3. **Crea una ficha** en "Nueva ficha". El editor es guiado: campos estructurados por grupo (Identidad, Ubicación, Datos prácticos, Editorial), no un campo de texto libre.
4. **Un checklist en vivo** le muestra qué falta. Los campos obligatorios son: gancho, foto de portada, crédito de fotos, latitud, longitud, cómo llegar, horario y costo. **El checklist bloquea el envío** hasta que estén todos.
5. **Pone el pin en el mapa** haciendo clic, y lo arrastra para ajustar. No hay geocodificación automática — se decidió así porque falla con direcciones paraguayas.
6. **Envía a revisión.** La ficha pasa de `borrador` a `enviado`.
7. **Aparece en la "Cola de revisión"** de quien tenga permiso de revisar. Ese revisor se la asigna ("asignarme"), la abre en vista lado a lado, y la aprueba o la devuelve con feedback escrito.
8. **Devuelta** → estado `necesita_cambios`, vuelve al promotor con el comentario. **Aprobada** → `aprobado` → `publicado`, y recién ahí es visible al público.

Hay además un **modo "Salida de campo"**: captura rápida de foto + nota + GPS que **se guarda offline y se sincroniza como borrador cuando vuelve la conexión**. Ya está resuelto ese problema del lado del panel — tenelo en cuenta porque la app probablemente deba hacer lo mismo, mejor.

## 1.4 La identidad es propia, no de WordPress

Decisión de seguridad, tomada a propósito: **las personas del panel no son usuarios de WordPress.** El objetivo fue eliminar la superficie de ataque de wp-admin y XML-RPC para las cuentas de gente real.

Tres tablas propias:

| Tabla | Qué guarda |
|---|---|
| `caaguazu_accounts` | email (único), hash de contraseña, nombre, teléfono, estado |
| `caaguazu_sessions` | **solo el hash SHA-256 del token** — nunca el token en claro |
| `caaguazu_grants` | permisos: `(cuenta × panel × rol)`, con override de capabilities por cuenta |

Detalles que importan:

- **Sesión:** cookie firmada con HMAC usando las sales de WordPress, `HttpOnly` + `Secure` + `SameSite=Lax`. Una cookie manipulada se descarta antes de tocar la base.
- **Contraseñas:** bcrypt. Verifica también hashes phpass heredados y los regraba solo, para no obligar a nadie a resetear.
- **Usuario de servicio:** WordPress exige un autor válido en cada entrada, pero nadie del panel es usuario de WP. La solución fue un único usuario bloqueado (`caaguazu-servicio`) que figura como autor técnico; el dueño real va en un meta.
- **Modelo de paneles:** `caaguazu_grants` está diseñado para que una cuenta tenga rol en **varios paneles**. Hoy existe el panel `promotor`; el sistema se pensó desde el principio para admitir otros. Por eso la app encaja sin inventar nada.

## 1.5 Permisos: dos ejes, no uno

Acá está la parte que se suele pasar por alto. Los permisos **no dependen solo del rol**.

**Eje 1 — Rol en el panel:**

| Rol | Qué puede |
|---|---|
| **Promotor** | Todo: crear, editar, revisar, publicar, asignar tareas, curar destacados, moderar, gestionar equipo, ver reportes, biblioteca, estructura |
| **Mini Promotor** | Crear borradores, editar sus fichas, ver sus tareas, editar su perfil |
| **Visitante** | Ver el panel y editar su perfil |

**Eje 2 — Nivel de confianza** (meta de cuenta, sube con el trabajo hecho):

| Nivel | Desbloquea |
|---|---|
| **Aprendiz** | — |
| **Promotor Jr** | Editar fichas ya publicadas |
| **De confianza** | **Publicar directo, sin pasar por revisión** |

O sea: un Mini Promotor que llega a "De confianza" puede publicar sin revisión. **La app tiene que gatear por lo que le diga el servidor, no por el rol solo.**

Toda la UI del panel se gatea por capability, nunca por rol hardcodeado. Seguí ese criterio.

## 1.6 Qué más hay en el ecosistema

**`caaguazu-locales`** — directorio de comercios. CPT `cgz_local` con cuatro tipos (restaurante, hotel, comercio, atracción), **con coordenadas propias**, reservas por WhatsApp (arma el mensaje y abre la app, sin pasarela de pago), y un sistema de reseñas con estrellas, fotos, votos y respuestas del dueño.

> **Esto te importa:** hay **dos entidades distintas con coordenadas** — `promotur_destino` (ficha turística) y `cgz_local` (comercio). Ambas son pines en el mapa. Ver §5.1.

**`caaguazu-turismo`** — 21 páginas estáticas ya sembradas (historia, ruta de la madera, artesanos, platos típicos, festividades, glosario guaraní, cómo llegar…). Contenido humano, escrito. Hoy son páginas web; está sin decidir si entran a la app.

**`caaguazu-sso-cead`** — el colegio CEAD tiene un curso de Servicios Turísticos. Sus alumnos y docentes entran al panel con un clic desde el panel del colegio, sin registrarse de nuevo, vía un código opaco de un solo uso canjeado servidor a servidor. Los alumnos entran como Mini Promotor. *(El código está listo; el endpoint del colegio todavía no existe.)*

**Auditoría** — cada cambio de estado editorial y cada login queda registrado en `promotur_audit_log`.

**Idiomas** — el panel ya tiene selector ES / EN / GN.

## 1.7 Por qué está construido así

Un principio que conviene respetar: **el theme no sabe nada de turismo.** Expone filtros genéricos (`caaguazu_nav_items`, `caaguazu_tourism_shell_items`) y cada plugin se registra solo. Si desactivás un plugin, el sitio no se rompe: desaparece su sección y nada más.

La misma lógica aplica a la app: **es un cliente más de un backend que ya existe**, no un sistema paralelo.

---

# PARTE 2 — Qué quiero

## 2.1 El objetivo

Una app Android para turistas y visitantes de Caaguazú, con cinco piezas:

| Pieza | Qué es |
|---|---|
| **Inventario de Atractivos** | Lista navegable de las atracciones, ordenada por categorías con icono y color |
| **Mapa** | Pines de todo el inventario. Pieza central. |
| **Recorridos** | El usuario arma su propia ruta (o toma una prehecha) y la manda a Google Maps |
| **Artículos** | Noticias, datos curiosos, historias |
| **IA** | Al final del desarrollo. Deja la arquitectura preparada. |

## 2.2 Y lo que quiero de vos, concretamente

**Que construyas la app entera, contra la API del panel, sin duplicar nada de lo que el panel ya resuelve.**

Eso significa:

1. **No construyas identidad.** Ni login propio, ni almacenamiento de contraseñas, ni tabla de usuarios. Consumís `/auth/login` y guardás un token.
2. **No construyas permisos.** El servidor te dice qué puede hacer la cuenta. Gateás la UI con eso.
3. **No construyas flujo de revisión.** Ya existe, con ocho estados y cola de revisión. Si un promotor carga desde la app, la ficha entra a ese mismo flujo.
4. **No construyas una base de contenido.** Caché local sí, descartable por definición. Fuente de verdad, no.
5. **Sí construí** todo lo demás: navegación, mapa con tiles, armador de recorridos, lector de artículos, caché offline, i18n, y la carga de contenido desde el teléfono.

Y una cosa más: **decime si el contrato no te sirve.** Está propuesto, no grabado en piedra. Cambiarlo antes de que yo lo implemente es barato.

## 2.3 División de trabajo

| | Panel (yo) | App (vos) |
|---|---|---|
| Identidad, sesiones, roles, niveles | ✅ | Consumís |
| Flujo de revisión editorial | ✅ | Consumís |
| Almacenamiento de contenido + CRUD del panel | ✅ | Consumís |
| API REST | ✅ | Consumís |
| Tiles del mapa | ✅ genero y sirvo | Renderizás |
| Textos e imágenes de UI | ✅ sirvo por endpoint | Consumís |
| **La app** | ❌ | ✅ |

Punto de contacto único: **la API.** Nada de acceso directo a la base.

---

# PARTE 3 — El contrato

## 3.1 Aviso importante

**Hoy esta API no existe.** El panel corre sobre `admin-ajax` y templates renderizados en servidor: cero `register_rest_route`. Y aunque los CPTs tienen `show_in_rest: true`, **todos los metadatos propios están en `show_in_rest: false`** — lat, lng, horario, costo y el estado editorial no salen por los endpoints nativos de WordPress.

Construirla es mi parte del trabajo. **Vos arrancá contra mocks** (§3.5).

Namespace: `/wp-json/czu-app/v1/`

## 3.2 Autenticación

Las sesiones de hoy son cookie firmada — sirve para navegador, no para app nativa. Voy a agregar modo **bearer** sobre la misma tabla (que ya guarda tokens hasheados, así que es una extensión chica, no un sistema paralelo).

```http
POST /auth/login
{ "email": "…", "password": "…" }

200 → {
  "token": "…",
  "expires_at": "2026-09-08T…Z",
  "cuenta": {
    "id": 41, "nombre": "…", "email": "…",
    "rol": "promotur_mini",
    "nivel": "jr",
    "permisos": [
      "promotur_view_panel",
      "promotur_create_draft",
      "promotur_edit_destino",
      "promotur_edit_published",
      "promotur_view_own_tasks",
      "promotur_edit_profile"
    ]
  }
}
```

Usá **`permisos`** para gatear la UI, no `rol` ni `nivel` — ese array ya resuelve la combinación de los dos ejes de §1.5. Son los nombres reales de capability que usa el panel, así los dos lados hablan el mismo vocabulario.

El set completo posible: `promotur_view_panel` · `promotur_create_draft` · `promotur_edit_destino` · `promotur_edit_published` · `promotur_publish_destino` · `promotur_review_content` · `promotur_assign_tasks` · `promotur_view_own_tasks` · `promotur_curate_featured` · `promotur_moderate` · `promotur_manage_team` · `promotur_manage_users` · `promotur_view_reports` · `promotur_manage_media` · `promotur_manage_structure` · `promotur_edit_profile`

Los dos que salen del nivel de confianza y no del rol son `promotur_edit_published` (nivel Jr) y `promotur_publish_destino` (nivel De confianza) — por eso no alcanza con mirar el rol.

- `401` → token vencido o inválido. Borrá el token y mandá al login.
- `403` → autenticado sin permiso. No reintentes.
- Guardá el token en almacenamiento seguro del sistema.
- **Navegar no requiere cuenta.** Solo escribir y sincronizar recorridos propios.

## 3.3 Endpoints

| Método | Ruta | Auth | Para qué |
|---|---|---|---|
| `POST` | `/auth/login` | — | Token |
| `POST` | `/auth/logout` | bearer | Cerrar sesión |
| `GET` | `/categorias` | — | Categorías con icono y color |
| `GET` | `/inventario` | — | Filtros: `categoria`, `zona`, `bbox`, `page` |
| `GET` | `/inventario/{id}` | — | Ficha completa |
| `GET` | `/eventos` | — | Filtros: `desde`, `hasta` |
| `GET` | `/mapa/markers` | — | Payload liviano para el mapa |
| `GET` | `/recorridos` | — | Prehechos publicados |
| `GET` | `/recorridos/{id}` | — | Con bloque de historia |
| `GET·POST·PUT·DELETE` | `/mis-recorridos` | bearer | Recorridos del usuario |
| `GET` | `/articulos`, `/articulos/{id}` | — | Artículos |
| `GET` | `/strings/{locale}` | — | Textos de UI |
| `GET` | `/media-manifest` | — | Imágenes de UI |
| `POST·PUT` | `/contenido` | bearer + permiso | Alta y edición de fichas desde la app |
| `GET` | `/sync?since={iso8601}` | — | Delta para caché offline |

## 3.4 Payloads

**`GET /categorias`**

```json
[
  { "id": 12, "slug": "paisaje-natural", "nombre": "Paisaje Natural",
    "icono": "nature", "color": "#2E7D32",
    "marker": "https://…/markers/paisaje-natural.png" }
]
```

`marker` es un PNG pre-renderizado. **No compongas el pin en el cliente.**

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
  "id": 41, "tipo": "destino", "titulo": "…", "gancho": "…",
  "categoria": { "id": 12, "nombre": "Paisaje Natural", "color": "#2E7D32" },
  "zona": { "id": 3, "nombre": "…" },
  "coordenadas": { "lat": -25.4669, "lng": -56.0175 },
  "portada": { "url": "…", "credito": "…" },
  "galeria": [ { "url": "…" } ],
  "video": null,
  "practicos": { "horario": "…", "costo": "…", "duracion": "…",
                 "servicios": "…", "temporada": "…", "contacto": "…" },
  "acceso": { "como_llegar": "…", "referencia": "…",
              "estado_camino": "asfalto", "accesibilidad": "…" },
  "articulo_html": "…",
  "actualizado": "2026-08-20T14:00:00Z"
}
```

Los campos salen tal cual del modelo que ya existe (§1.3, paso 3).

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
  "id": 88, "tipo": "prehecho", "titulo": "…", "duracion_estimada": "4h",
  "paradas": [
    { "orden": 1, "ref_tipo": "destino", "ref_id": 41, "nota": "" },
    { "orden": 2, "ref_tipo": "evento",  "ref_id": 12, "nota": "" }
  ],
  "historia": { "introduccion": "…", "correlacion": "…",
                "personas": [], "curiosidades": [], "articulos_ref": [ 55 ] }
}
```

`historia` viene vacío en los recorridos de usuario y completo en los prehechos.

En `POST /mis-recorridos` mandá solo `titulo` y `paradas`. Costo total y compatibilidad de fechas **se calculan** de las paradas — no se envían ni se guardan.

Export a Google Maps, del lado del cliente:
`https://www.google.com/maps/dir/?api=1&waypoints=lat,lng|lat,lng…`

## 3.5 Arrancá sin esperarme

1. Mock server con los JSON de §3.4.
2. URL base en configuración, no hardcodeada.
3. Todo el acceso a red aislado en una capa: cuando llegue la API real, cambiás una implementación.
4. Avisá cuando tengas los mocks andando y validamos endpoint por endpoint.

---

# PARTE 4 — Definiciones

## 4.1 Mapa

Decidido: **tiles, no mapa live** (para ahorrar recursos).

| Pieza | Quién |
|---|---|
| Tiles (pirámide raster, zoom ~10–17) | Yo genero y sirvo |
| Renderizado | Vos |
| Markers, desde `/mapa/markers` | Vos — **nunca quemados en los tiles** |
| Icono del pin (PNG por categoría) | Yo |
| Tap → abre la ficha | Vos |

Separar markers de tiles es lo que hace el mapa **retroactivo**: el staff registra un lugar y el pin aparece sin regenerar ni redistribuir nada.

Pendiente: extensión geográfica y zoom máximo — define si los tiles van embebidos en el APK o se descargan.

## 4.2 Textos e imágenes: nada hardcodeado

Requisito: **cualquier texto o imagen de interfaz debe cambiarse sin publicar un APK nuevo.**

```json
GET /strings/es
{ "nav.inventario": "Inventario", "nav.mapa": "Mapa",
  "nav.recorridos": "Recorridos", "empty.recorridos": "…" }
```

```json
GET /media-manifest
{ "splash.fondo": { "url": "…", "alt": "" },
  "home.hero":    { "url": "…", "alt": "" } }
```

- Locales: `es`, `en`, `gn`.
- Cacheá con `ETag`; si falla el refresco, seguí con la copia local.
- Embebé `es` como fallback de primer arranque sin red.
- **No aplica** a fichas, eventos, recorridos ni artículos: eso es contenido humano y viene de la base.

## 4.3 Restricciones de producto

Vienen del dueño del proyecto:

1. **El público es mayormente gente mayor que no lee párrafos largos.** Explicaciones animadas antes que texto. Claridad y concisión por encima de todo. Es un requisito, no una preferencia estética.
2. **No escribas contenido.** Ni artículos, ni descripciones, ni copy de relleno, ni textos de ejemplo que queden en producción. Todo texto de producto lo escribe una persona. Si necesitás placeholder para maquetar, marcalo inequívocamente.
3. **No inventes bocetos visuales.** Vienen en el zip.

## 4.4 IA — no la construyas todavía

Va al final. Tres cosas a respetar desde ahora, porque después salen caras:

1. **Toda lectura pasa por la API.** Si la app arma estado propio que el servidor no conoce, la IA no lo va a poder ver.
2. **El acceso a datos del usuario es con permiso explícito y revocable.** Modelalo como un permiso más, no como un flag global.
3. **La IA consume la misma API**, con su propio token y alcance acotado. No abras un canal aparte.

---

# PARTE 5 — Abierto

## 5.1 Decisiones que no son tuyas pero te afectan

| # | Pregunta | Te cambia |
|---|---|---|
| 1 | Hay dos entidades "lugar con coordenadas": `promotur_destino` (ficha turística) y `cgz_local` (comercio, con reservas y reseñas). ¿Se unifican, coexisten, o se separan por rol? | Si `/inventario` devuelve uno o varios `tipo` |
| 2 | Recorridos de usuario: ¿servidor o solo teléfono? | Si el usuario necesita cuenta |
| 3 | Extensión y zoom del mapa | Peso del APK |
| 4 | Las 21 páginas estáticas de turismo: ¿entran a la app? | Alcance del contenido |
| 5 | ¿Los alumnos del CEAD son promotores de la app? | Volumen de la cola de revisión |

**Sobre la 1:** mi recomendación es separar por rol (Atractivo / Evento / Comercio) y que `/inventario` devuelva `tipo`. **Programá asumiendo que `tipo` existe** — así ninguna de las tres salidas te rompe el cliente.

## 5.2 Coordinación

- El contrato de la Parte 3 es la fuente de verdad compartida. Cambios de un lado se avisan antes de implementar.
- Si un endpoint no te alcanza, pedí el cambio en vez de compensarlo con parches en el cliente.
- Cuando tengas mocks andando, avisá y arrancamos la integración real.
