# Panel de Promotores Turísticos

Todo lo que hace el panel, dónde vive cada cosa y qué queda pendiente.
Versión `3.0.0` del plugin `caaguazu-portal`.

---

## 1. Dónde vive

**Todo el panel cuelga de `/turismo-panel`.** No hay una sola ruta del panel fuera de ahí.

| URL | Qué es |
| --- | --- |
| `/turismo-panel` | Inicio del panel |
| `/turismo-panel/<sección>` | Cada sección (ver §4) |
| `/turismo-panel/<sección>/<id>` | Vista de detalle (una ficha en el editor, una en revisión) |
| `/turismo-panel/entrar` | Acceso |
| `/turismo-panel/registro` | Alta (sólo con invitación) |
| `/turismo-panel/i/<token>` | Link corto de invitación |
| `/turismo-panel/recuperar` | Pedir recuperación de contraseña |
| `/turismo-panel/recuperar/nueva` | Elegir contraseña nueva |
| `/turismo-panel/salir` | Cerrar sesión |
| `/turismo-panel/manifest.webmanifest` | Manifiesto PWA |
| `/turismo-panel/sw.js` | Service worker (alcance `/turismo-panel/`) |
| `/turismo-panel/icon-<n>.png` | Ícono PWA en cualquier tamaño |
| `/turismo-panel/offline` | Pantalla sin conexión |

El slug base es **una constante**, `PROMOTUR_BASE` en `caaguazu-portal.php`. El router arma las reglas de reescritura con ella y `promotur_url()` arma las URLs con la misma: mover el panel de lugar es cambiar esa línea. Hay una comprobación automática que falla si alguien vuelve a escribir una URL del panel a mano (§6).

### Las rutas viejas siguen funcionando

Cada ruta anterior responde **301** a su equivalente nueva, conservando la query string (`?next=`, `?reset=1`). Es la parte que no se podía romper: hay invitaciones ya enviadas y promotores con la PWA instalada.

| Antes | Ahora |
| --- | --- |
| `/turismo/panel/…` | `/turismo-panel/…` |
| `/czu-login` | `/turismo-panel/entrar` |
| `/registro` | `/turismo-panel/registro` |
| `/i/<token>` | `/turismo-panel/i/<token>` |
| `/recuperar` · `/recuperar/restablecer` | `/turismo-panel/recuperar` · `/recuperar/nueva` |
| `/salir` | `/turismo-panel/salir` |
| `/promotur-manifest.webmanifest` · `/promotur-sw.js` · `/promotur-icon-<n>.png` · `/promotur-offline` | sus equivalentes bajo `/turismo-panel/` |

> El service worker viejo (`/promotur-sw.js`, alcance `/`) recibe un 301, y un redirect en el script de un SW hace que el navegador lo dé de baja solo. Es el comportamiento que queremos: el nuevo se registra con alcance `/turismo-panel/`, que es lo único que tiene que controlar.

---

## 2. Quién entra y con qué permisos

La identidad **no es de WordPress**: corre entera sobre `caaguazu-cuentas` (tabla propia, sesión propia firmada, contraseñas bcrypt). Ningún promotor tiene usuario de WordPress. Los administradores de WP entran por su login de siempre gracias al bypass de ese plugin.

Tres roles, y la UI se gatea **por capability, nunca por rol**:

| Capability | Promotor | Mini Promotor | Visitante |
| --- | :---: | :---: | :---: |
| `promotur_view_panel` | ● | ● | ● |
| `promotur_edit_profile` | ● | ● | ● |
| `promotur_create_draft` | ● | ● | |
| `promotur_edit_destino` | ● | ● | |
| `promotur_view_own_tasks` | | ● | |
| `promotur_review_content` | ● | | |
| `promotur_publish_destino` | ● | | |
| `promotur_assign_tasks` | ● | | |
| `promotur_curate_featured` | ● | | |
| `promotur_moderate` | ● | | |
| `promotur_manage_team` | ● | | |
| `promotur_manage_users` | ● | | |
| `promotur_view_reports` | ● | | |
| `promotur_manage_media` | ● | | |
| `promotur_manage_structure` | ● | | |

Un item de menú cuyo capability no tiene la cuenta **no se dibuja**, y un grupo que se queda sin items visibles tampoco pinta su rótulo: nadie ve un link que después le va a dar 403. El guard del router repite el chequeo del lado del servidor, así que ocultar el link no es la seguridad, es la cortesía.

**Acceso desde el CEAD**: el plugin `caaguazu-sso-cead` canjea el código del colegio y manda a `promotur_url( 'panel' )`. Como esa función ya apunta a `/turismo-panel`, el SSO siguió funcionando sin tocarle una línea.

---

## 3. El armazón

```
/turismo-panel/…
├── Menú lateral      marca · buscador (⌘K) · grupos · submenú · pie (perfil, ayuda, instalar, salir)
├── Barra superior    migas de pan · buscador · idioma (ES/EN/GN) · tema · notificaciones · chip de usuario
├── Contenido         una sección (contrato $page_title + $body)
└── Barra inferior    5 accesos, sólo en teléfono
```

- **Menú agrupado con submenú.** Dos grupos y un pie. "Mis contenidos" abre en árbol a "Nueva ficha" y "Salida de campo"; el submenú viene abierto si estás parado en el padre o en un hijo.
- **Migas de pan** en vez de repetir el título: el `<h1>` de la página ya dice dónde estás. En teléfono desaparecen, porque la barra no da para más.
- **Buscador con ⌘K / Ctrl+K.** La tecla dibujada al lado del campo funciona de verdad: un atajo anunciado y no implementado es peor que no anunciarlo.
- **Modo claro y oscuro**, elegido por el usuario y recordado, con anti-parpadeo antes del primer dibujado.
- **Splash de marca** una vez por sesión, y apagado si el sistema pide movimiento reducido.
- **Barra inferior en teléfono** con los cinco accesos que corresponden al rol, objetivos táctiles de 52 px.

---

## 4. Las secciones

Quince, cada una con su capability. El shell resuelve la sección, verifica el permiso y ejecuta el contrato de página; una sección desconocida cae en el 404 del panel, no en el del sitio.

| Sección | Ruta | Capability | Qué hace |
| --- | --- | --- | --- |
| **Inicio** | `/turismo-panel` | `promotur_view_panel` | Pulso del día: cuántas fichas esperan revisión, publicadas, esperando tu corrección, en proceso, reseñas por moderar y consultas sin responder — cada una es un link a donde se resuelve. Más la actividad editorial de los últimos 7 días y los accesos rápidos. |
| **Mis contenidos** | `/mis-contenidos` | `promotur_create_draft` | Tus fichas ordenadas por última modificación, con su estado editorial. |
| **Nueva ficha / Editor** | `/editor[/<id>]` | `promotur_edit_destino` | Ficha guiada por grupos de campos, con checklist de mínimos en vivo que bloquea el envío si falta algo, subida de fotos y geolocalización. Muestra el feedback de quien revisó. |
| **Salida de campo** | `/captura` | `promotur_create_draft` | Captura offline: título, nota, foto y GPS quedan en el teléfono y se sincronizan cuando hay señal. |
| **Cola de revisión** | `/revision[/<id>]` | `promotur_review_content` | Lo que espera revisión, con badge en el menú. Asignarse una ficha, aprobar, publicar o devolver con feedback (hay motivos de un clic). |
| **Tareas** | `/tareas` | `promotur_view_own_tasks` | Encargos del equipo: reclamar y completar. Badge con las pendientes. |
| **Curaduría** | `/curaduria` | `promotur_curate_featured` | Qué se destaca en la vitrina pública y el banner de temporada. |
| **Moderación** | `/moderacion` | `promotur_moderate` | Reseñas por aprobar, consultas de visitantes y reportes. |
| **Equipo** | `/equipo` | `promotur_manage_team` | Quién es quién, su rol y su nivel de confianza. |
| **Reportes** | `/reportes` | `promotur_view_reports` | Producción por autor, lo más visto, búsquedas sin resultado y salud del contenido (fichas sin foto, fichas viejas). |
| **Biblioteca** | `/biblioteca` | `promotur_manage_media` | Medios del panel. |
| **Estructura** | `/estructura` | `promotur_manage_structure` | Categorías, zonas y etiquetas de las fichas. |
| **Buscar** | `/buscar?q=` | `promotur_view_panel` | Búsqueda del panel; las búsquedas sin resultado se registran y salen en Reportes. |
| **Mi perfil** | `/perfil` | `promotur_edit_profile` | Datos de la cuenta y nivel de confianza. |
| **Ayuda** | `/ayuda` | `promotur_view_panel` | Cómo se usa el panel. |

### El flujo editorial

```
borrador ─enviar→ enviado ─asignarse→ en revisión ─┬─aprobar→ aprobado ─publicar→ publicado
                                                   └─devolver→ necesita cambios ─→ borrador
```

Cada paso queda en el log de auditoría con quién, qué y cuándo. Los estados tienen su pastilla de color, y el color viene del sistema de tokens (§5), no de un hex suelto.

### PWA

Instalable desde el propio panel ("Instalar app" aparece sola cuando el navegador lo permite). El service worker es *network-first* con caída a caché y, si la caída es una navegación, a la pantalla offline. En el precache entran la pantalla offline, el CSS, el JS, el ícono **y las tres variantes de la tipografía**: sin ellas, la pantalla de "no hay señal" se dibujaría con otra letra justo cuando el usuario ya está desconfiando.

---

## 5. El sistema de diseño

Un solo archivo (`assets/css/caaguazu-portal.css`), sin framework, sin utilidades sueltas, sin dependencias. Las reglas están escritas en la cabecera del archivo **y comprobadas por un script** (§6).

| Regla | Cómo queda |
| --- | --- |
| **Color** | Conjunto cerrado de tokens nombrados por rol (`--tinta`, `--papel`, `--lienzo`, `--linea`, `--texto`…), nunca por color. Ningún literal fuera del bloque de tokens. |
| **Acento** | La tinta como fondo aparece en seis lugares y en ninguno más: item activo del menú, botón primario, pastilla activa de un segmentado, barra destacada del gráfico, item activo de la barra inferior, e isotipo/avatar. Nunca como fondo de una superficie grande ni de una tarjeta. |
| **Semántica** | Verde, rojo, ámbar y azul sólo dicen estado (aprobado, error, en espera, enviado). Nunca decoran. |
| **Neutros** | Grises con un sesgo cálido elegido a propósito: un gris medio puro se lee como no elegido. |
| **Radios** | Tres, nombrados: `--r-1` 8px, `--r-2` 12px, `--r-3` 16px, más `--r-full` para pastillas. Ningún número a mano. |
| **Sombra** | Una, `--sombra`, y sólo en lo que flota de verdad: menú activo, desplegables, modales. Las tarjetas se separan con una línea de 1px. |
| **Tipografía** | Una familia, Inter, servida desde el plugin (400/600/700, 76 KB). Sin Google Fonts ni CDN. |
| **Gradientes** | Ninguno. El único motivo decorativo es la trama de rayas diagonales (`--trama`), y vive en un token. |
| **Movimiento** | Sólo lo que explica de dónde sale algo. Todo se apaga con `prefers-reduced-motion`. |

**El panel ya no hereda nada del theme.** Antes tomaba colores y fuentes del sitio público vía `var(--flag-green, …)`; ahora trae lo suyo y **desencola el CSS del theme activo** en sus rutas. El sitio público se puede rehacer entero sin poder cambiarle la cara —ni romperle el layout— al panel.

---

## 6. Las reglas se verifican

```bash
php tools/verificar-diseno.php          # informe completo
php tools/verificar-diseno.php --breve  # sólo lo que falla
```

Sale con código 1 si algo rompe una regla, así se puede colgar de CI. Comprueba:

1. Ningún color literal fuera del bloque de tokens.
2. Ningún token nombrado por color en vez de por rol.
3. Ningún radio fuera de los cuatro declarados.
4. Una sola sombra en todo el panel.
5. Una sola familia tipográfica.
6. Sin gradientes decorativos.
7. Sin assets de terceros en el panel.
8. Ningún elemento cuyas clases no tengan **ninguna** regla (una pantalla sin estilo esperando su turno).
9. Ninguna clase declarada que nadie use.
10. Sin `alert()` del navegador.
11. Ninguna URL del panel armada a mano en vez de con `promotur_url()`.

Cada comprobación está escrita en las dos direcciones: se probó que detecta el caso malo (inyectando a propósito un `#ff0000`, un radio de 7px, una sombra suelta, otra tipografía y un gradiente: las cinco saltaron) y que no marca el caso bueno.

Y para mirar una pantalla sin levantar un WordPress:

```bash
php tools/vista-previa-panel.php sections/home > /tmp/home.html
php tools/vista-previa-panel.php auth/login    > /tmp/login.html
```

Con eso se revisaron las **21 plantillas** del panel, una por una, antes de dar nada por bueno. Los datos son de maqueta y están declarados como tales.

---

## 7. Qué se tocó y qué no

**Backend intacto.** No se cambió ni una línea de: el sistema de cuentas, los roles y capabilities, el CPT Destino, el flujo editorial, las tareas, la curaduría, la moderación, las reseñas, las consultas, las invitaciones, la auditoría, el SEO, las pantallas de wp-admin ni los shortcodes públicos.

Catorce archivos tocados, todos de presentación o de ruteo:

| Archivo | Qué cambió |
| --- | --- |
| `caaguazu-portal.php` | Constante `PROMOTUR_BASE`, versión 3.0.0 |
| `includes/class-router.php` | Rutas bajo `/turismo-panel` + redirecciones 301 de las viejas |
| `includes/helpers.php` | `promotur_url()` reescrita; menú en grupos con submenú; íconos nuevos |
| `includes/class-assets.php` | Precarga de tipografía; desencolado del CSS del theme |
| `includes/class-pwa.php` | URLs y alcance del SW; colores del cromo; fuentes al precache |
| `includes/class-stats.php` | **Añadido**: `serie_diaria()` |
| `assets/css/caaguazu-portal.css` | Reescrito entero (sistema de diseño) |
| `assets/js/caaguazu-portal.js` | Submenú, atajo ⌘K, alcance del SW, mensajes en pantalla en vez de `alert()` |
| `assets/fonts/` | **Nuevo**: Inter 400/600/700 + su licencia |
| `templates/partials/sidebar.php` · `topbar.php` · `head.php` · `splash.php` | Rediseñados |
| `templates/auth-shell.php` | Isotipo unificado |
| `templates/sections/home.php` | Rediseñado + gráfico de actividad |
| `readme.txt` | Changelog |

Las otras 20 plantillas **no se tocaron**: usan el mismo vocabulario de clases de siempre, así que se rediseñaron solas al cambiar el sistema debajo. Fue deliberado — mantiene el diff chico y el riesgo bajo.

### Sobre la referencia comprada

El `.zip` es **OneUI 5.12** (pixelcave): Bootstrap 5, jQuery y 40 dependencias, 534 KB de CSS minificado. De ahí se tomó **la tipografía Inter** (licencia SIL OFL, redistribuible; no es propiedad de pixelcave) y las ideas de estructura. **No se importó el framework**: el panel tiene hoy un CSS de ~30 KB y cero dependencias, y meterle Bootstrap + jQuery contradiría de frente las reglas de peso, tokens, radios y sombras de la filosofía del ecosistema. La referencia visual real fueron las dos capturas, que además están mucho más cerca de esas reglas que OneUI.

---

## 8. Pendiente, y por qué

Está acá para que sea una decisión y no una sorpresa.

1. **Tres textos sin escribir.** Los dos rótulos de grupo del menú y el título del gráfico de actividad aparecen como `[FALTA: …]`. Son los únicos textos que el diseño nuevo pide y que no existían antes, y **los escribe una persona**. `verificar-diseno.php` los lista mientras sigan puestos.
2. **El texto sigue viviendo en el código.** Hay 311 cadenas `__()` en plantillas y clases: cambiar "Cola de revisión" hoy exige publicar el plugin. El mecanismo correcto ya existe en el ecosistema (`caaguazu-app-api` sirve textos por clave desde opciones, con fusión sobre el respaldo local y sin pisar con vacíos), pero no tiene editor y el panel no lo consume. Es un trabajo aparte, y grande.
3. **Leaflet por CDN** en la vitrina pública (`class-public.php`, `unpkg.com`). Está fuera del panel, pero es la misma regla. Se arregla vendorizándolo. El verificador lo reporta como aviso, no como falla, para no dejar la suite en rojo por deuda declarada.
4. **Los cuatro estados, a medias.** Hay vacío y éxito; el error es un mensaje genérico sin "reintentar" y no hay estado de carga salvo el "Enviando…". Sin conexión sólo sobrevive la cola de capturas.
5. **Sólo hay una serie temporal.** La actividad editorial sale del log de auditoría, que es lo único con timestamp. No hay serie de fichas publicadas por día, ni de visitas: por eso las tarjetas de cifras no tienen variación "vs. la semana pasada" como la referencia. Antes que inventar el número, no está.
6. **Colapsar el menú lateral** (el ícono de la referencia que reduce el panel a íconos) no está hecho.

---

## 9. Cómo se publica

El plugin y el theme viven en **este** repo y se actualizan solos desde sus GitHub Releases. `PROMOTUR_REPO` apunta acá; el repo `nasastaxd/turismo` ya no interviene.

Dos componentes en un mismo repo comparten el espacio de tags, y el updater saca la versión del tag del release. La regla que los mantiene separados no depende de cómo se llamen los tags:

> **Cada release lleva un solo zip, y cada updater se queda con el release que trae el suyo.**

- El plugin filtra por un release que tenga adjunto `caaguazu-portal.zip` (`setReleaseFilter` en `caaguazu-portal.php`).
- El theme hace lo mismo con `caaguazu-theme.zip`: dejó de mirar `/releases/latest` —que bien puede ser un release del plugin— y ahora recorre los últimos hasta encontrar el que trae su zip.

Para publicar una versión nueva alcanza con subir el número de versión en su header y mergear a `main`:

| Componente | Dónde va la versión | Tag | Asset |
| --- | --- | --- | --- |
| Panel | header `Version:` de `caaguazu-portal/caaguazu-portal.php` (y `PROMOTUR_VERSION`) | `v3.0.0` | `caaguazu-portal.zip` |
| Theme | header `Version:` de `caaguazu-theme/style.css` | `v5.0.1` | `caaguazu-theme.zip` |

[`.github/workflows/release.yml`](../.github/workflows/release.yml) tiene un job por componente: cada uno lee su versión, y si ese tag todavía no existe arma su zip y publica el release. Si la versión no subió, el tag ya existe y el job no hace nada.

Los sitios ven la actualización dentro de las 12 h, o al toque desde **wp-admin → Portal Turismo → Actualizaciones**, que tiene su botón de comprobar.

> Las dos series de números tienen que seguir sin cruzarse: el theme va por 5.x y el panel por 3.x. Si algún día coincidieran, el job falla porque el tag ya existe — falla ruidosa, no un release corrupto.

---

## 10. Los textos

Todos los textos que se ven en el panel están inventariados en [`textos-del-panel.md`](textos-del-panel.md): 569 en total, agrupados por pantalla, con su archivo y línea y una columna para escribir el reemplazo. Se regenera con:

```bash
php tools/textos-del-panel.php > docs/textos-del-panel.md
```

Ahí están marcados los tres huecos `[FALTA: …]` y los catorce textos que arrancan en minúscula (casi todos son fragmentos pensados para leerse después de un número: *"4 esperan revisión"*).
