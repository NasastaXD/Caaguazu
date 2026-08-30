# Sistema de Turismo de Caaguazú — relevamiento completo

**Fecha del relevamiento:** 25 de agosto de 2026
**Alcance:** todo el código que compone el sistema de turismo, repartido en dos repositorios de GitHub.

---

## 1. Resumen ejecutivo

El "sistema de turismo" no es un solo plugin: son **cinco plugins activos repartidos en dos repositorios**, más los puntos de integración del theme del portal. Nació como un sitio aparte (`turismo.caaguazu.net`) y fue absorbido por el portal principal (`caaguazu.net`), pero **la absorción quedó a mitad de camino**: el repo viejo sigue existiendo, sigue publicando releases, y todavía contiene el theme del sitio original.

| Componente | Repo | Versión | Qué hace |
|---|---|---|---|
| `caaguazu-turismo` | `NasastaXD/Caaguazu` | 1.11.1 | Las 21 páginas de contenido turístico + integración con el nav del theme |
| `caaguazu-locales` | `NasastaXD/Turismo` | 1.1.0 | Directorio de negocios, reservas por WhatsApp, mapa, reseñas |
| `caaguazu-portal` | `NasastaXD/Turismo` | 2.1.0 | Panel de promotores turísticos (app tipo PWA) con flujo editorial |
| `caaguazu-cuentas` | `NasastaXD/Turismo` | 0.2.0 | Sistema de identidad propio, separado de los usuarios de WordPress |
| `caaguazu-sso-cead` | `NasastaXD/Turismo` | 1.0.0 | Acceso de un clic desde el colegio CEAD al panel de promotores |
| `caaguazu-app-api` | `NasastaXD/Turismo` | 0.1.0 | Capa REST que consume la app Android (`/wp-json/czu-app/v1/`) |
| `caaguazu-theme` (legado) | `NasastaXD/Turismo` | 1.0.0 | **Theme muerto del sitio viejo — ver §7.1** |

Todo corre sobre el theme `Caaguazú` v4.5.1 del repo `NasastaXD/Caaguazu`.

**Hallazgos que requieren atención** (detalle en §7): un theme obsoleto que se sigue publicando en cada release y que colisionaría con el contenido actual si alguien lo instala; una discrepancia de versión que rompe el auto-update de `caaguazu-turismo`; un enlace roto en la navegación del ecosistema; y un manifiesto de actualización desactualizado que deja a `caaguazu-locales` sin auto-update.

---

## 2. Arquitectura general

### 2.1 El principio de diseño

El theme **no sabe nada** de turismo en particular. Expone filtros genéricos y cada plugin se registra solo:

- `caaguazu_nav_items` — un link en el menú principal (con mega-menú opcional)
- `caaguazu_quick_access_items` — una tarjeta en los accesos rápidos del home
- `caaguazu_tourism_shell_items` — items del header/tabbar/footer propios del ecosistema Turismo

Esto significa que **si desactivás cualquier plugin, el sitio no se rompe** — simplemente deja de aparecer su sección. Cada punto donde el theme depende de un plugin está detrás de un `function_exists()` o `post_type_exists()`.

### 2.2 Cómo se relacionan

```
                    ┌─────────────────────────────────┐
                    │   caaguazu-theme  (v4.5.1)      │
                    │   Portal caaguazu.net            │
                    │   inc/ecosystem-shell.php        │
                    └───────────────┬─────────────────┘
                                    │  filtros de integración
             ┌──────────────────────┼──────────────────────┐
             │                      │                      │
    ┌────────▼────────┐   ┌─────────▼────────┐   ┌─────────▼────────┐
    │ caaguazu-turismo│   │ caaguazu-locales │   │ caaguazu-portal  │
    │ 21 páginas de   │   │ Negocios,        │   │ Panel de         │
    │ contenido       │   │ reservas,        │   │ promotores       │
    │                 │   │ reseñas, mapa    │   │ (PWA)            │
    └─────────────────┘   └──────────────────┘   └────────┬─────────┘
        embebe los shortcodes de los otros dos             │ depende de
                                                  ┌────────▼─────────┐
                                                  │ caaguazu-cuentas │
                                                  │ Identidad propia │
                                                  └────────┬─────────┘
                                                           │ depende de
                                                  ┌────────▼─────────┐
                                                  │ caaguazu-sso-cead│
                                                  │ Entrada del CEAD │
                                                  └──────────────────┘
```

`caaguazu-turismo` **orquesta sin fusionar**: siembra páginas que embeben los shortcodes de Locales y Portal (`[caaguazu_locales]`, `[caaguazu_mapa]`, `[promotur_destacados]`), pero no toca el código interno de ninguno de los dos.

---

## 3. `caaguazu-turismo` — el contenido

**Repo:** `NasastaXD/Caaguazu` · **Versión:** 1.11.1 · **852 líneas de PHP**

### 3.1 Archivos

| Archivo | Líneas | Qué hace |
|---|---|---|
| `caaguazu-turismo.php` | 30 | Bootstrap; registra el hook de activación |
| `includes/tourism-content.php` | 235 | Las 21 páginas (título, extracto, cuerpo HTML) en un array |
| `includes/tourism-seeder.php` | 230 | Siembra, migraciones de jerarquía, pantalla de admin |
| `includes/nav-integration.php` | 159 | Mega-menú, accesos rápidos, items del shell |
| `includes/updater.php` | 198 | Auto-actualización desde GitHub Releases |

### 3.2 Las 21 páginas

La jerarquía es **plana a propósito**: todo cuelga directo de `turismo`, salvo las subpáginas de las dos secciones que sí tienen profundidad real.

| # | Slug | Padre | Título |
|---|---|---|---|
| 1 | `turismo` | *(raíz)* | Turismo en Caaguazú |
| 2 | `la-capital-de-la-madera` | `turismo` | La Capital de la Madera |
| 3 | `historia` | `la-capital-de-la-madera` | Historia de Caaguazú — 1845 hasta hoy |
| 4 | `la-ruta-de-la-madera` | `la-capital-de-la-madera` | La Ruta de la Madera |
| 5 | `artesania-y-oficios` | `la-capital-de-la-madera` | Artesanía y oficios |
| 6 | `artesanos` | `la-capital-de-la-madera` | Artesanos de Caaguazú |
| 7 | `que-hacer` | `turismo` | Qué hacer en Caaguazú |
| 8 | `ykua-la-patria` | `que-hacer` | Ykua La Patria — Manantial fundacional |
| 9 | `patrimonio-religioso` | `que-hacer` | Patrimonio religioso — Iglesia Inmaculada Concepción |
| 10 | `mercado-municipal` | `que-hacer` | Mercado de Abasto |
| 11 | `parques-y-naturaleza` | `que-hacer` | Parques y naturaleza |
| 12 | `platos-tipicos` | `turismo` | Platos típicos de Caaguazú |
| 13 | `donde-comer` | `turismo` | Dónde comer en Caaguazú |
| 14 | `mate-y-terere` | `turismo` | Mate y tereré en Caaguazú |
| 15 | `festividades` | `turismo` | Festividades y calendario |
| 16 | `guarani-en-nuestra-ciudad` | `turismo` | Guaraní en Caaguazú — Glosario |
| 17 | `galeria` | `turismo` | Galería — Caaguazú en imágenes |
| 18 | `como-llegar` | `turismo` | Cómo llegar a Caaguazú |
| 19 | `donde-alojarte` | `turismo` | Dónde alojarte en Caaguazú |
| 20 | `mejor-epoca` | `turismo` | Mejor época para visitar |
| 21 | `mapa-interactivo` | `turismo` | Mapa de Caaguazú |

> **Nota sobre el conteo:** la documentación existente dice tres números distintos y ninguno coincide con el código. El header del plugin y su README dicen **22 páginas**; el README del theme dice **25**; el código tiene **21**. Ver §7.4.

### 3.3 Migraciones automáticas

El seeder incluye tres rutinas de catch-up que corren en `admin_init` (una sola vez cada una, con flag en opciones), para que sitios sembrados con versiones viejas se pongan al día sin reinstalar nada:

1. **`caaguazu_seed_tourism_on_activation()`** — siembra las páginas que falten. No pisa páginas ya editadas a mano.
2. **`caaguazu_tourism_flatten_hierarchy()`** — reubica las páginas que colgaban de las antiguas páginas puente (`sabores-de-caaguazu`, `vivir-caaguazu`, `planifica-tu-visita`) directo bajo `turismo`, y borra esas páginas puente. Desde la v1.9 busca en las **dos** ubicaciones posibles (anidada bajo `turismo/` o en la raíz del sitio), porque las siembras anteriores a la v1.8 dejaban páginas huérfanas que ningún reintento tocaba.
3. **`caaguazu_tourism_remove_contacto_page()`** — borra `turismo/contacto` (una Secretaría de Turismo que duplicaba la página institucional `/contacto/`). **Nunca toca la página raíz `/contacto/`.**

### 3.4 Navegación

Hay **tres** menús distintos poblados desde este plugin, con contenidos que no coinciden entre sí:

**Mega-menú del nav principal** (`caaguazu_turismo_menu_groups`) — 4 grupos, 14 páginas:
- La Capital de la Madera: Introducción, Historia, La Ruta de la Madera, Artesanos
- Qué hacer: Ykua La Patria, Patrimonio religioso, Mercado de Abasto, Parque Techapyrã
- Sabores: Platos típicos, Dónde comer, Mate y tereré
- Planificá tu visita: Cómo llegar, Dónde alojarte, Mapa interactivo

**Shell del ecosistema** (`caaguazu_tourism_shell_items`) — header/tabbar/footer propios que aparecen mientras navegás dentro de Turismo: La Capital de la Madera, Qué hacer, Sabores, Vivir Caaguazú, Planificá tu visita, **Contacto** *(roto, ver §7.3)*, más "Directorio de locales" si Locales está activo, más "Mapa".

**Accesos rápidos del home** — una sola tarjeta, "Turismo" (🌲).

Cuatro páginas (`artesania-y-oficios`, `guarani-en-nuestra-ciudad`, `galeria`, `mejor-epoca`) **no aparecen en ningún menú** — solo se llega a ellas por enlaces dentro del cuerpo de otras páginas. No es necesariamente un error, pero conviene saberlo.

---

## 4. `caaguazu-locales` — el directorio de negocios

**Repo:** `NasastaXD/Turismo` · **Versión:** 1.1.0 · **14 archivos PHP**

### 4.1 Modelo de datos

**CPT `cgz_local`** con estos metadatos:

| Meta | Tipo | Para qué |
|---|---|---|
| `_cgz_tipo` | string | restaurante / hotel / comercio / atracción |
| `_cgz_whatsapp` | string | Número para el widget de reservas |
| `_cgz_telefono` | string | Teléfono de contacto |
| `_cgz_direccion` | string | Dirección física |
| `_cgz_horario` | string | Horario de atención |
| `_cgz_booking_template` | string | Plantilla del mensaje de WhatsApp |
| `_cgz_lat` / `_cgz_lng` | number | Coordenadas del pin (puestas a mano en el mapa) |
| `_cgz_owner` | integer | Usuario dueño del local |

**Cuatro tablas propias** para el sistema de reseñas: `cgz_reviews`, `cgz_review_replies`, `cgz_review_votes`, `cgz_review_photos`.

### 4.2 Funcionalidades

1. **Reservas por WhatsApp** — el visitante elige una opción de un menú, edita el mensaje, y se abre WhatsApp con el texto listo para el número del local. Sin pasarela de pago ni backend de reservas.
2. **Mapa con edición manual** — en vez de geocodificación automática (que suele fallar en direcciones paraguayas), el admin o el dueño hacen clic en el mapa para colocar el pin y lo arrastran para ajustarlo.
3. **Reseñas con cuentas** — estrellas, fotos, votos de utilidad, comentarios y respuestas del dueño.
4. **Panel de dueños** — cada dueño gestiona su propio local sin acceso al resto de wp-admin.

### 4.3 Shortcodes

| Shortcode | Qué renderiza |
|---|---|
| `[caaguazu_locales tipo="" ]` | Grilla de locales, filtrable por tipo |
| `[caaguazu_mapa]` | Mapa con todos los locales geolocalizados |
| `[caaguazu_booking id="123"]` | Widget de reserva por WhatsApp de un local |
| `[caaguazu_resenas]` | Reseñas de un local |
| `[caaguazu_cuenta]` | Registro/login de visitantes |
| `[caaguazu_dueno_panel]` | Panel de gestión del dueño |

### 4.4 Páginas propias

Al activarse crea `/cuenta/` y `/panel-de-mi-local/` automáticamente.

---

## 5. `caaguazu-portal` — el panel de promotores

**Repo:** `NasastaXD/Turismo` · **Versión:** 2.1.0 · **56 archivos PHP** — es, por lejos, el componente más grande del sistema.

No es "un CPT con un formulario": es una **aplicación completa** montada sobre rutas propias, con su propio enrutador, su propio shell visual (no usa el theme para el panel) e instalable como PWA.

### 5.1 Rutas propias

| Ruta | Qué es |
|---|---|
| `/czu-login` | Login del portal *(era `/login`; se renombró para no chocar con wp-admin)* |
| `/registro` | Alta con token de invitación (invite-only) |
| `/recuperar` · `/recuperar/restablecer` | Recuperación de contraseña |
| `/salir` | Cerrar sesión |
| `/i/{token}` | Link de invitación |
| `/turismo/panel/...` | El panel en sí |
| `/promotur-manifest.webmanifest`, `/promotur-sw.js`, `/promotur-icon-{n}.png`, `/promotur-offline` | PWA |

El plugin **redirige `wp-login.php` a `/czu-login`** para todo usuario que no sea administrador — esto afecta a todo el sitio, no solo a las rutas del portal. Los administradores conservan `wp-login.php` de siempre.

### 5.2 Modelo de contenido

**CPT `promotur_destino`** (ficha turística) con tres taxonomías: `promotur_categoria`, `promotur_zona`, `promotur_etiqueta`.

Campos de la ficha, agrupados (los marcados con ✱ son obligatorios y componen el checklist de mínimos que bloquea el envío a revisión):

| Grupo | Campos |
|---|---|
| **Identidad** | Gancho ✱, Foto de portada ✱, Crédito de fotos ✱, Video |
| **Ubicación y acceso** | Latitud ✱, Longitud ✱, Referencia, Cómo llegar ✱, Estado del camino (asfalto/ripio/tierra), Accesibilidad |
| **Datos prácticos** | Horario ✱, Temporada ideal, Costo ✱, Servicios, Duración sugerida, Contacto del lugar |
| **Editorial** | Fuentes / referencias |

### 5.3 Flujo editorial

```
borrador → enviado → en_revisión → ┬→ aprobado → publicado
                                    └→ necesita_cambios → (vuelve a borrador)

                        publicado → despublicado → archivado
```

**Solo el estado `publicado` hace la ficha visible al público** (`post_status = publish`); todos los demás la dejan en `draft`. Al publicar se sella `_promotur_verificado_en`. Las transiciones a `enviado`, `aprobado`, `necesita_cambios` y `publicado` quedan auditadas en `promotur_audit_log`.

### 5.4 Roles y permisos

Tres roles, definidos en `class-roles.php` como fuente única de verdad. **Toda la UI se gatea por capability, nunca por rol hardcodeado.**

| Rol | Puede |
|---|---|
| **Profesor** | Todo: crear, editar, revisar, publicar, asignar tareas, curar destacados, moderar, gestionar equipo, ver reportes, gestionar biblioteca y estructura |
| **Alumno** | Crear borradores, editar sus fichas, ver sus tareas, editar su perfil |
| **Visitante** | Solo ver el panel y editar su perfil |

Las 15 secciones del panel (`home`, `buscar`, `editor`, `captura`, `mis-contenidos`, `revision`, `tareas`, `curaduria`, `moderacion`, `equipo`, `reportes`, `biblioteca`, `estructura`, `perfil`, `ayuda`) están mapeadas cada una a la capability que requieren.

### 5.5 Otras piezas

- **Shortcodes públicos:** `[promotur_destinos]`, `[promotur_mapa]`, `[promotur_destacados]`, `[promotur_explorar]`, `[promotur_itinerario]`, `[promotur_home]`, `[promotur_contacto]`
- **Selector de idioma** ES / EN / GN, persistido en cookie
- **Invitaciones** con token hasheado y vencimiento, en tabla `promotur_invitations`
- **Auditoría** completa en `promotur_audit_log`
- **PWA** instalable con lectura offline
- **Override de templates desde el theme** en `/<theme>/promotur/<ruta>.php`

---

## 6. Identidad, SSO y la API de la app

### 6.1 `caaguazu-cuentas` (v0.2.0)

Sistema de identidad **deliberadamente separado de `wp_users`**. La razón: que el login de un panel no sea un login de WordPress, eliminando la superficie de ataque de wp-admin y XML-RPC para las cuentas de personas.

**Tres tablas:**

| Tabla | Contenido |
|---|---|
| `caaguazu_accounts` | email (único), hash de contraseña, nombre, teléfono, estado, metadata JSON |
| `caaguazu_sessions` | Solo el **hash SHA-256** del token de sesión — nunca el token en claro |
| `caaguazu_grants` | Permisos por panel: una cuenta puede tener rol en varios paneles, con override de capabilities por cuenta |

**Mecánica de sesión:** cookie firmada con HMAC usando las sales de WordPress, HttpOnly + Secure + SameSite=Lax. Una cookie manipulada se descarta antes de tocar la base de datos.

**Contraseñas:** bcrypt para las nuevas; verifica también los hashes phpass heredados de `wp_users` (para no obligar a nadie a resetear su clave al migrar), y los regraba a bcrypt de forma transparente en el próximo login.

**Usuario de servicio:** WordPress exige un `post_author` válido en cada entrada, pero ninguna persona del panel es ya un usuario de WP. La solución es un único usuario de WordPress bloqueado (`caaguazu-servicio`, contraseña aleatoria, marcado como de sistema) que figura como autor técnico; el dueño real se guarda en el meta `_caaguazu_owner`.

**Bypass de administrador:** los administradores de WordPress no se migran a cuentas propias a propósito — siguen entrando por wp-admin y conservan acceso total a cualquier panel.

### 6.2 `caaguazu-sso-cead` (v1.1.0)

Acceso de un clic desde el panel del colegio CEAD (curso de Servicios Turísticos) al panel de promotores.

> **Este plugin se instala en `caaguazu.net`, no en el sitio del CEAD.** Es la mitad receptora. La mitad emisora es otro plugin (`cead-acad`) en `cead.caaguazu.net`, que no depende de ninguno de estos.

**El flujo** (patrón *authorization code* de OAuth, reducido al mínimo):

```
Alumno         Panel CEAD          caaguazu.net          CEAD (REST)
  │                 │                    │                    │
  │ "Ir al portal"  │                    │                    │
  ├────────────────►│ genera código      │                    │
  │                 │ opaco (2 min,      │                    │
  │                 │ un solo uso)       │                    │
  │◄────────────────┤ 302 → /acceso-cead?code=XXXX            │
  ├──────────────────────────────────────►│                   │
  │                 │                    │ POST /redeem ─────►│
  │                 │                    │◄──── claims ───────┤
  │                 │            busca/crea cuenta,           │
  │                 │            aplica rol, abre sesión      │
  │◄──────────────────────────────────────┤ 302 → /panel      │
```

Por la URL viaja **solo un código sin significado**. Los datos de la persona (email, nombre, teléfono, rol, curso) viajan servidor a servidor, firmados con HMAC-SHA256 y con ventana de 300 segundos contra desfase de reloj.

**Reglas de negocio decididas:**

- Un email que **ya tiene cuenta** en el portal sin vincular se **rechaza**, no se vincula solo. Vincular automáticamente por email sería la puerta de un robo de cuenta: quien controle esa dirección en el CEAD pasaría a manejar la cuenta existente con todos sus permisos. Un admin lo vincula a mano desde **Herramientas → Acceso desde el CEAD**.
- Mapeo de roles: quien cursa entra como Alumno, quien enseña como Profesor. Un rol que el CEAD mande y no caiga en el mapa se rechaza — no se inventa un permiso. **Desde v1.1.0 el mapa dejó de ser una constante de dos entradas**: el CEAD es un WordPress y manda roles de WordPress (`alumno`, `cead_alumno`, `subscriber`, `Docente`), ninguno de los cuales era `alumno_turismo`. Ahora los nombres se comparan normalizados (sin acentos, sin mayúsculas, sin el prefijo del colegio ni el sufijo del curso) y el mapa se edita desde **Herramientas → Acceso desde el CEAD**. Los roles administrativos del colegio siguen sin entrar, a propósito.
- Entran al panel `promotor` que ya existe, no a uno aparte.
- Las cuentas creadas por SSO llevan una contraseña aleatoria de 64 caracteres que nunca se muestra: la cuenta existe y entra por SSO, pero no hay ninguna contraseña real que adivinar.
- Sesión **sin "recordarme"** — el acceso vive del vínculo con el CEAD.

**Dos tablas:** `caaguazu_sso_cead_links` (mapa `account_id` ↔ `cead_uid`, indexado) y `caaguazu_sso_cead_log` (auditoría de cada intento, con el motivo cuando se rechazó).

**Configuración requerida** en `wp-config.php` de `caaguazu.net`:

```php
define( 'CEAD_TUR_SSO_SECRET', '…64 hex…' );  // idéntico en los dos sitios
define( 'CEAD_TUR_SSO_URL', 'https://cead.caaguazu.net/wp-json/cead-sso/v1/redeem' );
```

El secreto va igual en los dos sitios (uno firma, el otro verifica); las URLs son cruzadas (cada sitio guarda la dirección del otro).

> **Estado actual: inactivo.** El endpoint `/wp-json/cead-sso/v1/redeem` de `cead.caaguazu.net` **todavía no existe** — devuelve 404 `rest_no_route`. La mitad emisora no está construida, así que el SSO no puede funcionar aunque se configuren las constantes. El plugin activa igual y avisa en wp-admin; `/acceso-cead` responde 503.
>
> Desde v1.1.0 esto se comprueba sin adivinar: **Herramientas → Acceso desde el CEAD** revisa las cinco piezas de este lado (plugins, constantes, regla de reescritura, panel registrado y mapa de roles) y tiene un botón que prueba el endpoint del colegio con un código inválido, distinguiendo «no existe» de «existe y rechaza la firma» de «funciona».

> **El código de este plugin ahora está en el repo.** Hasta acá vivía sólo como un `.zip` subido a mano, sin fuente que revisar: arreglarle el mapeo de roles obligaba a editar un binario. Se sacó a `caaguazu-sso-cead/` y su zip lo arma `bin/build-zip.sh sso`, como los otros tres componentes.

### 6.3 `caaguazu-app-api` (v0.1.0)

Capa REST bajo `/wp-json/czu-app/v1/` que consume la app Android de turismo. Es el componente más nuevo del sistema.

**Es un plugin aparte a propósito:** el theme y las páginas de `caaguazu.net` van a rehacerse, y ese trabajo no debe poder romper la API de una app ya publicada en la tienda. No usa el theme ni sus helpers, no renderiza HTML, y lee el contenido de donde ya vive.

No reimplementa identidad, permisos ni flujo editorial — delega en `caaguazu-cuentas` y `caaguazu-portal`. Sí aporta las entidades que faltaban:

| Aporta | Qué es |
|---|---|
| CPT `promotur_evento` | Eventos con fecha, lugar (referenciado o propio) y costo |
| CPT `promotur_recorrido` | Recorridos prehechos y de usuario, con paradas ordenadas |
| CPT `promotur_articulo` | Artículos de la app, separados de las Noticias del portal |
| Term meta en `promotur_categoria` | Icono, color y PNG de marcador por categoría |
| `_promotur_rango_precio` | Rango 0–4 en la ficha, además del texto libre de costo |

**Dos tablas propias:** `caaguazu_app_tokens` (tokens bearer para la app — tabla separada de las sesiones de navegador a propósito, porque cerrar sesión en la web no debe desloguear el celular) y `caaguazu_app_tombstones` (registro de lo que dejó de estar publicado, para que `/sync` pueda informar bajas y no solo altas).

**Un detalle que importa:** el autor que devuelve la API **no sale de `post_author`**. Como la gente del panel no es usuaria de WordPress, el autor técnico de todo el contenido es el usuario de servicio; el autor real se resuelve desde el meta del dueño. Si eso se cambia, todos los artículos aparecen firmados por `caaguazu-servicio`.

El mapa base **no** lo sirve este plugin: la app usa tiles vectoriales embebidos (2 MB, contra los ~250 MB que pesaría una pirámide ráster). Lo que sí sirve son los markers, separados del mapa base — eso es lo que hace que registrar un lugar haga aparecer su pin sin regenerar nada.

**Pendiente:** `POST /contenido` (alta de fichas desde el teléfono) no está implementado; la lectura sí. Y no tiene auto-updater (§7.5).

---

## 7. Hallazgos

Los cuatro primeros son defectos reales verificados en el código. Ninguno rompe el sitio hoy, pero todos tienen consecuencias.

### 7.1 El repo `Turismo` publica un theme obsoleto en cada release

**Severidad: alta.**

El repo `NasastaXD/Turismo` contiene `caaguazu-theme/` v1.0.0 — el theme del sitio original `turismo.caaguazu.net`, anterior a la consolidación en el portal. Su último cambio real fue el 10 de julio de 2026.

El problema no es que exista, sino que **`.github/workflows/publish-releases.yml` lo empaqueta y publica en cada release**. El release v2.1.0 incluye `caaguazu-theme.zip` (126 KB).

Ese theme, al activarse, **siembra automáticamente sus propias 27 páginas** con los mismos slugs que usa `caaguazu-turismo` (`/que-hacer`, `/la-capital-de-la-madera/historia`, etc.). Si alguien lo instala pensando que es el theme del portal, obtiene un theme que compite con el actual y duplica el contenido.

Se agrava porque hay **dos themes con nombre casi idéntico** en dos repos: `Caaguazú` v4.5.1 (el real) y `Caaguazú Turismo` v1.0.0 (el legado).

**Recomendación:** sacar `caaguazu-theme/` del workflow de releases. Borrarlo del repo o moverlo a una carpeta `legacy/` con un README que explique qué fue.

### 7.2 `caaguazu-turismo` ofrece una actualización que nunca termina

**Severidad: media.**

En `caaguazu-turismo.php` hay dos números de versión distintos:

```php
 * Version:           1.11.1      ← línea 6, el header del plugin
define( 'CAAGUAZU_TURISMO_VERSION', '1.11.0' );   ← línea 17, la constante
```

`bin/build-zip.sh` genera el `manifest.json` del release leyendo **el header** (1.11.1), mientras que el auto-updater se instancia con **la constante** (1.11.0):

```php
new Caaguazu_Component_Updater( __FILE__, CAAGUAZU_TURISMO_VERSION, ... );
```

El updater compara manifest (1.11.1) contra instalado (1.11.0), concluye que hay una versión nueva, y ofrece la actualización. Al actualizar, el archivo nuevo trae la misma discrepancia — así que **la ofrece otra vez, indefinidamente**.

**Recomendación:** igualar los dos valores. Conviene además que `build-zip.sh` verifique que header y constante coinciden, para que esto no vuelva a pasar.

### 7.3 El "Contacto" del shell de Turismo lleva al home

**Severidad: media.**

`nav-integration.php` registra `contacto` como una de las secciones del shell:

```php
'contacto' => array( 'mail', __( 'Contacto' ), __( 'Contacto' ) ),
```

Pero esa página **fue eliminada a propósito** — `tourism-seeder.php` la borra activamente (`caaguazu_tourism_remove_contacto_page()`), porque duplicaba la página institucional `/contacto/`.

Como `caaguazu_tourism_page_url()` devuelve `home_url('/')` cuando el slug no existe en el array de páginas, **el botón "Contacto" del header, el tabbar y el footer de todo el ecosistema Turismo apunta a la portada del sitio**.

**Recomendación:** o apuntar ese item a la página institucional `/contacto/`, o sacarlo del shell.

### 7.4 `caaguazu-locales` no se auto-actualiza

**Severidad: media.**

`caaguazu-locales` usa un mecanismo de actualización distinto al resto: consulta un manifiesto JSON en el propio repo:

```
https://raw.githubusercontent.com/nasastaxd/turismo/main/updates/caaguazu-locales.json
```

Ese archivo dice `"version": "1.0.0"`, pero el plugin va por **1.1.0**. El manifiesto quedó atrás porque **nadie lo actualiza al subir la versión** — el workflow de releases no toca la carpeta `updates/`.

Resultado: el updater compara 1.0.0 (remoto) contra 1.1.0 (instalado), no ve nada nuevo, y nunca ofrece una actualización. El auto-update de Locales está efectivamente muerto.

**Recomendación:** migrar Locales al mismo mecanismo que el Portal (plugin-update-checker contra los releases), o hacer que el workflow regenere `updates/*.json` en cada release.

### 7.5 Cuatro mecanismos de actualización distintos

No es un defecto, pero es la causa raíz de 7.2 y 7.4, y conviene tenerlo escrito:

| Componente | Mecanismo |
|---|---|
| Theme, `caaguazu-modulos`, `caaguazu-turismo`, `caaguazu-editor-ux` | Clase compartida `Caaguazu_Component_Updater`, compara contra `manifest.json` del release |
| `caaguazu-portal` | `plugin-update-checker` vendoreado, lee los GitHub Releases directamente |
| `caaguazu-locales` | Manifiesto JSON manual en `updates/` *(roto, ver 7.4)* |
| `caaguazu-cuentas`, `caaguazu-sso-cead`, `caaguazu-app-api` | **Ninguno** — instalación y actualización a mano |

Los tres plugins sin updater son los de identidad y el que sirve la API de la app — o sea los que más conviene poder parchear rápido si aparece un problema de seguridad. Con una app publicada dependiendo de esa API, deja de ser incómodo y pasa a ser un riesgo.

### 7.6 El conteo de páginas está mal documentado en tres lugares

**Severidad: baja** (solo documentación).

El código tiene 21 páginas. El header y el README de `caaguazu-turismo` dicen 22. El README del theme dice 25. Ninguno coincide.

---

## 8. Dónde tocar cada cosa

| Si querés… | Andá a |
|---|---|
| Cambiar el texto de una página de turismo | `caaguazu-turismo/includes/tourism-content.php` — o editarla como Página normal en wp-admin (el seeder no pisa lo editado) |
| Agregar una página nueva | `tourism-content.php` + sumarla al menú en `nav-integration.php` |
| Cambiar el menú de Turismo | `caaguazu-turismo/includes/nav-integration.php` |
| Reimportar el contenido | wp-admin → **Apariencia → Caaguazú** |
| Agregar un tipo de local | Filtro `cgz_local_types` (no hace falta tocar el plugin) |
| Cambiar los campos de una ficha de destino | `caaguazu-portal/includes/class-destinos.php`, método `fields()` |
| Cambiar el flujo editorial | `caaguazu-portal/includes/class-editorial.php` |
| Cambiar roles o permisos del panel | `caaguazu-portal/includes/class-roles.php` (fuente única) |
| Cambiar cómo se ve el panel | `caaguazu-portal/templates/` — o sobrescribir desde el theme en `/<theme>/promotur/` |
| Cambiar el mapeo de roles del CEAD | `caaguazu-sso-cead/includes/class-link.php`, constante `ROLE_MAP` |
| Vincular a mano una cuenta del CEAD | wp-admin → **Herramientas → Acceso desde el CEAD** |

---

## 9. Publicación y despliegue

**Repo `NasastaXD/Caaguazu`** — el workflow se dispara solo cuando un push a `main` toca la línea `Version:` de `caaguazu-theme/style.css`. Empaqueta theme + los tres plugins + un `manifest.json` con la versión real de cada componente, y publica un GitHub Release. El tag lleva la versión del **theme**, por eso los plugins comparan contra el manifest y no contra el tag.

**Repo `NasastaXD/Turismo`** — el workflow se dispara con cualquier cambio en las carpetas de los componentes. Lee la versión de `caaguazu-portal/caaguazu-portal.php` para el tag, y **no publica nada si ese tag ya existe**. Esto significa que un cambio en Locales, Cuentas o SSO que no venga acompañado de un bump de versión del Portal **no genera release**.

Último release: **v2.1.0** (26/08/2026), con `caaguazu-portal.zip`, `caaguazu-locales.zip`, `caaguazu-cuentas.zip`, `caaguazu-sso-cead.zip`, `caaguazu-app-api.zip` y `caaguazu-theme.zip` *(este último, el legado de §7.1)*.

---

## 10. Orden de instalación

En un sitio limpio:

1. **Theme `Caaguazú`** (repo `Caaguazu`) — activar.
2. **`caaguazu-cuentas`** — primero, porque el Portal depende de él como dependencia dura (sin él el Portal no levanta el router y solo muestra un aviso).
3. **`caaguazu-portal`** — crea los roles y las rutas.
4. **`caaguazu-locales`** — crea `/cuenta/` y `/panel-de-mi-local/`.
5. **`caaguazu-turismo`** (repo `Caaguazu`) — siembra las 21 páginas, que ya embeben los shortcodes de los dos anteriores.
6. **`caaguazu-sso-cead`** — opcional, y hoy inactivo (§6.2).
7. **`caaguazu-app-api`** — solo si se va a usar la app Android (§6.3). Crea sus dos tablas.
8. **Ajustes → Enlaces permanentes → Guardar**, para refrescar las rewrite rules.

**No instalar** `caaguazu-theme.zip` del repo `Turismo` (§7.1).

### Slugs reservados

Evitar crear páginas con estos slugs, porque los toman los plugins por rewrite rule:

`/czu-login` · `/registro` · `/recuperar` · `/salir` · `/i/{token}` · `/turismo/panel/...` · `/acceso-cead` · `/promotur-*` · `/evento/*` · `/articulo/*` · `/recorrido/*`

`/cuenta/` y `/panel-de-mi-local/` las crea Locales solo.
