# Panel de Promotores Turísticos

Todo lo que hace el panel, dónde vive cada cosa y qué queda pendiente.
Versión `3.2.0` del plugin `caaguazu-portal`.

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
| `/turismo-panel/accion/<nombre>` | Donde caen los formularios del panel (POST) |
| `/turismo-panel/datos/<nombre>` | Donde caen los pedidos del JavaScript (POST, responde JSON) |

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
| `promotur_manage_team` | ● | | |
| `promotur_manage_users` | ● | | |
| `promotur_view_reports` | ● | | |
| `promotur_manage_media` | ● | | |
| `promotur_manage_structure` | ● | | |
| `promotur_manage_app` | ● | | |

Las capabilities de contenido no llevan el nombre del tipo: `promotur_create_draft`, `promotur_edit_destino`, `promotur_review_content` y `promotur_publish_destino` gobiernan los tres —fichas, artículos y recorridos—. El nombre quedó de cuando la ficha era lo único que había; renombrarlas obligaría a tocar los permisos ya otorgados de cada cuenta a cambio de nada.

Un item de menú cuyo capability no tiene la cuenta **no se dibuja**, y un grupo que se queda sin items visibles tampoco pinta su rótulo: nadie ve un link que después le va a dar 403. El guard del router repite el chequeo del lado del servidor, así que ocultar el link no es la seguridad, es la cortesía.

**Acceso desde el CEAD**: el plugin `caaguazu-sso-cead` canjea el código del colegio y manda a `promotur_url( 'panel' )`. Como esa función ya apunta a `/turismo-panel`, el SSO siguió funcionando sin tocarle una línea.

Lo que sí hubo que arreglar (v1.1.0 de ese plugin) fue **la traducción de roles**. El CEAD es un WordPress y manda roles de WordPress; acá la identidad no es de WordPress, y son roles `promotur_*`. El puente entre los dos vocabularios era una constante de dos entradas (`alumno_turismo`, `docente_turismo`), y cualquier otra forma —`alumno`, `cead_alumno`, `subscriber`, `Docente`— rebotaba con «Tu rol no está habilitado». Peor: el registro de intentos guardaba la columna `rol_cead` y los claims traían la clave `rol`, así que **el rol quedaba en NULL en todas las filas** y la pantalla de admin mostraba «—» justo donde había que mirar. Ahora los nombres se comparan normalizados, el mapa se edita desde wp-admin, el rol crudo se registra, y hay una pantalla que valida las cinco piezas de la integración y prueba el endpoint del colegio.

### Nada de lo que hace el equipo pasa por WordPress

WordPress corre abajo y guarda lo que hay que guardar, pero **nadie del equipo abre una pantalla suya, y nada del panel depende de que exista un usuario de WordPress.** Esto no era así y costó arreglarlo; queda escrito para que no se vuelva atrás sin darse cuenta.

Lo que estaba mal, y dónde quedó:

| Estaba así | Por qué era un problema | Ahora |
| --- | --- | --- |
| Guardar, aprobar y subir colgaban de `admin-ajax.php` | `wp_ajax_*` —sin `nopriv`— **sólo corre para usuarios de WordPress**: un promotor con cuenta y sin usuario de WP recibía `0` en cada guardado | `/turismo-panel/datos/<nombre>`, autenticado con la cuenta |
| Invitar y marcar leído colgaban de `admin-post.php` | Igual, y además sacaba a la persona del panel | `/turismo-panel/accion/<nombre>` |
| Los nonces eran `wp_create_nonce()` | Se firman con el usuario de WordPress, no con la cuenta | Token HMAC firmado con la cuenta, ventana de 12 h y se acepta la anterior |
| Mi perfil enlazaba a `wp-admin/profile.php` | Edita **otra cosa** —un usuario de WordPress que un promotor no tiene— | Se edita en Mi perfil: nombre, correo, teléfono, foto y contraseña, contra `caaguazu-cuentas` |
| El avatar venía de Gravatar | `get_avatar_url()` devuelve URL siempre, así que la rama de iniciales no corría nunca: cada pantalla mandaba el correo de un promotor a un tercero | Foto de la cuenta, o iniciales |
| Biblioteca era un enlace a `wp-admin/upload.php` | Ese enlace *era* la galería | La galería está en el panel: grilla, subida, descripción, crédito y borrado |
| Estructura eran tres botones a `edit-tags.php` | Lo mismo | Se edita en el panel |
| «Usuarios» en wp-admin listaba usuarios de WordPress | Cambiaba roles de WordPress y suspendía con una usermeta: desde el cutover de identidad editaba a otra gente, o a nadie | Equipo, en el panel, sobre la cuenta y su permiso |

En wp-admin quedan dos pantallas —el registro de auditoría y las actualizaciones del plugin— y las dos son de administrador del sitio. La única persona que entra ahí es quien administra el servidor.

---

## 3. El armazón

```
/turismo-panel/…
├── Menú lateral      marca · buscador (⌘K) · grupos · submenú · pie (perfil, ayuda, instalar, salir)
├── Barra superior    migas de pan · buscador · tema · notificaciones · chip de usuario
├── Contenido         una sección (contrato $page_title + $body)
└── Barra inferior    5 accesos, sólo en teléfono
```

- **Menú agrupado con submenú.** Tres grupos —Gestión, Contenido y Portal— y un pie. "Mis contenidos" abre en árbol a "Nueva ficha" y "Salida de campo"; el submenú viene abierto si estás parado en el padre o en un hijo.
- **Migas de pan** en vez de repetir el título: el `<h1>` de la página ya dice dónde estás. En teléfono desaparecen, porque la barra no da para más.
- **Buscador con ⌘K / Ctrl+K.** La tecla dibujada al lado del campo funciona de verdad: un atajo anunciado y no implementado es peor que no anunciarlo.
- **Modo claro y oscuro**, elegido por el usuario y recordado, con anti-parpadeo antes del primer dibujado.
- **El panel es en español, y nada más.** No hay selector de idioma: el que había cambiaba el locale del panel por cookie y arrastraba una capa de traducción que nadie usaba. Los idiomas que sí existen son los de la **app** (ES / EN / GN).
- **Splash de marca** una vez por sesión, y apagado si el sistema pide movimiento reducido.
- **Barra inferior en teléfono**: una cápsula flotante centrada, de vidrio —papel translúcido con desenfoque—, que deja ver el contenido correr por debajo. Lleva los cinco accesos que corresponden al rol, y la etiqueta se abre **sólo en el activo**: los otros cuatro son ícono, que es lo que hace que entren sin apretujarse. El texto de los inactivos sigue en el DOM para quien usa lector de pantalla; se le cierra el ancho, no se lo esconde. Donde no hay soporte de desenfoque, cae a papel opaco.

---

## 4. Las secciones

Dieciséis, cada una con su capability. El shell resuelve la sección, verifica el permiso y ejecuta el contrato de página; una sección desconocida cae en el 404 del panel, no en el del sitio.

Tres de ellas —Inventario, Artículos y Recorridos— hacen de lista y de detalle en la misma ruta, como la cola de revisión: `/articulos` lista, `/articulos/nuevo` abre uno en blanco y `/articulos/<id>` abre ese.

| Sección | Ruta | Capability | Qué hace |
| --- | --- | --- | --- |
| **Inicio** | `/turismo-panel` | `promotur_view_panel` | Pulso del día: cuántas fichas esperan revisión, publicadas, esperando tu corrección, en proceso, reseñas por moderar y consultas sin responder — cada una es un link a donde se resuelve. Más la actividad editorial de los últimos 7 días y los accesos rápidos. |
| **Mis contenidos** | `/mis-contenidos` | `promotur_create_draft` | Todo lo tuyo —fichas, artículos y recorridos— ordenado por última modificación, con su tipo y su estado editorial. |
| **Nueva ficha / Editor** | `/editor[/<id>]` | `promotur_edit_destino` | Ficha guiada por grupos de campos, con checklist de mínimos en vivo que bloquea el envío si falta algo, subida de fotos y geolocalización. Muestra el feedback de quien revisó. |
| **Inventario turístico** | `/inventario[/<id>]` | `promotur_view_panel` | El catálogo de fichas publicadas del departamento, con sus datos. Es de donde los recorridos toman sus paradas. |
| **Artículos** | `/articulos[/nuevo\|<id>]` | `promotur_create_draft` | Las notas que la app muestra: ante título, título, foto con su pie, autores, subtítulo, entradilla, cuerpo y fuentes, más categoría y etiquetas. |
| **Recorridos** | `/recorridos[/nuevo\|<id>]` | `promotur_create_draft` | Rutas armadas con hasta nueve sitios del inventario, cada uno con su texto y su audio o video, reordenables. Más los medios del recorrido entero y los artículos vinculados. |
| **Salida de campo** | `/captura` | `promotur_create_draft` | Captura offline: título, nota, foto y GPS quedan en el teléfono y se sincronizan cuando hay señal. |
| **Cola de revisión** | `/revision[/<id>]` | `promotur_review_content` | Lo que espera revisión, con badge en el menú. Asignarse una ficha, aprobar, publicar o devolver con feedback (hay motivos de un clic). |
| **Tareas** | `/tareas` | `promotur_view_own_tasks` | Encargos del equipo: reclamar y completar. Badge con las pendientes. |
| **Equipo** | `/equipo` | `promotur_manage_team` | Quién es quién, su rol y su nivel de confianza. Cambiar el rol, suspender, sacar del panel, e invitar: crear enlaces de invitación, ver los que están abiertos y revocarlos. |
| **Reportes** | `/reportes` | `promotur_view_reports` | Producción por autor y salud del contenido: fichas publicadas sin portada y fichas sin verificar hace más de seis meses. |
| **Biblioteca** | `/biblioteca` | `upload_files` | La galería: grilla de fotos, subida de a tandas, nombre, descripción y crédito de cada una, y borrado —bloqueado si la foto es la portada de una ficha. Filtro por nombre y por «sólo las mías». |
| **Estructura** | `/estructura` | `promotur_view_panel` (editar: `promotur_manage_structure`) | Categorías, zonas y etiquetas: cuántas fichas usa cada una, crear, renombrar en su lugar y borrar lo que no esté en uso. |
| **Buscar** | `/buscar?q=` | `promotur_view_panel` | Búsqueda de fichas dentro del panel. |
| **Mi perfil** | `/perfil` | `promotur_edit_profile` | La cuenta: nombre, correo, teléfono, foto y contraseña. Más el nivel de confianza y el portafolio de fichas publicadas. |
| **Ayuda** | `/ayuda` | `promotur_view_panel` | Cómo se usa el panel. |

### El flujo editorial

```
borrador ─enviar→ enviado ─asignarse→ en revisión ─┬─aprobar→ aprobado ─publicar→ publicado
                                                   └─devolver→ necesita cambios ─→ borrador
```

**Vale para los tres tipos de contenido**, no sólo para la ficha. Y tiene que ser así: el flujo es el acuerdo de cómo trabaja el equipo, no un detalle de la ficha. Un artículo y un recorrido se escriben, se envían, los revisa alguien y los aprueba el staff, exactamente igual.

Lo único que cambia entre tipos es **qué mínimos hay que cumplir**, y eso lo declara cada clase de contenido con dos métodos: `fields()` —de donde salen los campos marcados obligatorios— y `checklist_extra()` —lo que no es un campo suelto: que haya cuerpo, que haya dos paradas, que haya ubicación—. En `PROMOTUR_Editorial` no hay un solo `if` por tipo.

| | Ficha (`promotur_destino`) | Artículo (`promotur_articulo`) | Recorrido (`promotur_recorrido`) |
| --- | --- | --- | --- |
| Mínimos propios | gancho, portada, crédito, horario, costo, descripción y ubicación | autores, portada, pie de foto, fuentes, entradilla y cuerpo | portada, duración, introducción y dos paradas |
| Se edita en | `/editor` | `/articulos` | `/recorridos` |

Cada paso queda en el log de auditoría con quién, qué y cuándo, y la acción lleva el tipo adelante (`articulo_publicado`, `recorrido_enviado`) para que el registro siga diciendo qué se movió. Los estados tienen su pastilla de color, y el color viene del sistema de tokens (§5), no de un hex suelto.

### La ubicación de una ficha

El **enlace de Google Maps es el modo por defecto**, y las coordenadas quedaron de alternativa. No es una preferencia: la app se apoya en Google Maps para llevar a la gente hasta el lugar, así que el enlace es el dato que de verdad se usa. Y del lado de quien carga, pegar un enlace que ya tiene en el teléfono sale bien siempre; transcribir dos números con seis decimales, no — y un pin corrido no tira ningún error, manda a alguien al lugar equivocado.

El pin se sigue necesitando (el mapa de la app filtra por rango de latitud y longitud, y eso no se puede hacer sobre un valor calculado al vuelo), así que **se saca del enlace al guardar** cuando el enlace lo trae. Están cubiertos los cuatro formatos que escribe Google, y `!3d!4d` gana sobre `@` a propósito: en un enlace de lugar conviven los dos, y el `@` es dónde estaba mirando la cámara, no dónde está el lugar.

Los enlaces cortos (`maps.app.goo.gl`) no traen el punto: resolverlos exigiría un pedido de red desde el servidor en medio de un guardado. Ahí el panel lo dice y quedan los campos de latitud y longitud.

Se fueron cinco campos de la ficha —cómo llegar, referencia, temporada ideal, servicios y duración sugerida—: se llenaban con frases genéricas que no ayudaban a decidir nada. **Los datos ya cargados no se borran**, sólo dejan de pedirse, de mostrarse y de publicarse (ver `PROMOTUR_Destinos::campos_retirados()`).

### Los recorridos

Un recorrido no es un conjunto de lugares: es una **secuencia**. Cambiar el tercero por el quinto cambia el paseo, así que el orden se guarda explícito y el editor tiene botones para subir y bajar cada parada — no un menú escondido ni algo que aparezca al pasar el mouse, porque en un teléfono no hay mouse y reordenar es la operación principal.

El tope de **nueve paradas** no es arbitrario: la app manda el recorrido a Google Maps como una ruta con waypoints, y ahí hay un límite duro. Un recorrido de doce se cortaría solo, en silencio, en el teléfono de alguien que ya salió de casa. Se corta acá, donde se puede avisar, y en tres lugares: el editor, el guardado del panel y la API.

### PWA

Instalable desde el propio panel ("Instalar app" aparece sola cuando el navegador lo permite). El service worker es *network-first* con caída a caché y, si la caída es una navegación, a la pantalla offline. En el precache entran la pantalla offline, el CSS, el JS, el ícono **y las tres variantes de la tipografía**: sin ellas, la pantalla de "no hay señal" se dibujaría con otra letra justo cuando el usuario ya está desconfiando.

### El panel manda sobre la app

La aplicación Android es otro cliente del mismo backend, y **el panel es su cabina de mando**: la app no se vuelve a publicar en la tienda para cambiar una palabra o una imagen. Lee del servidor lo que puede cambiar, y eso se edita acá.

| Qué controla el panel | Dónde vive | Endpoint que lo sirve |
| --- | --- | --- |
| Textos de interfaz, por idioma (ES / EN / GN) | opción `czuapi_strings_<locale>` | `GET /wp-json/czu-app/v1/strings/<locale>` |
| Manifiesto de medios (qué imagen o animación va en cada clave) | opción `czuapi_media_manifest` | `GET /wp-json/czu-app/v1/media-manifest` |
| Icono y color de cada categoría | term meta `czuapi_icono` · `czuapi_color` | `GET /wp-json/czu-app/v1/categorias` |

Ese mecanismo ya existía en `caaguazu-app-api` —endpoints, ETag, fusión sobre el respaldo local, y la regla de que un valor vacío no pisa— pero **no tenía editor**: la promesa de "se cambia sin publicar un APK" estaba a medias. La sección App fue la otra mitad, y **hoy está desconectada**: llamaba a `CZUAPI_UI_Content::get_strings()`, `get_manifest()` y `set_manifest()`, que existen desde `caaguazu-app-api` 0.2.0, contra la 0.1.0 que hay instalada — y `class_exists()` no distingue una versión de otra, así que la pantalla moría con un error fatal apenas se abría. Es exactamente la clase de dependencia que este ecosistema evita: el panel dando por sentado el interior de otro plugin. El código queda en el repo (`includes/class-app-control.php` y `templates/sections/app.php`) con una guarda, y volver a enchufarla es una línea en `promotur_app_api_activa()`: comprobar los métodos que se usan, no las clases. Mientras tanto los textos y los medios de la app se editan desde wp-admin.

Dos detalles de cómo está hecho:

- **El panel no escribe las opciones del otro plugin a mano.** Pasa por su API pública (`CZUAPI_UI_Content::set_strings()`, `set_manifest()`) y por sus constantes de meta. El día que cambie el formato, cambia en un solo lado. Para eso hubo que agregarle a `caaguazu-app-api` los tres accesos que le faltaban (`get_strings()`, `get_manifest()`, `set_manifest()`): se pidió el cambio en el origen en vez de compensarlo con un parche del lado del panel.
- **Es una dependencia blanda.** Si la API de la app no está instalada, la sección no se registra, el item no aparece en el menú y su ruta cae en el 404 del panel. El panel funciona igual sin app; la app no funciona sin panel.

**Artículos y Recorridos ya están en el panel** (v3.2.0). Los dos nacieron como CPTs de `caaguazu-app-api` y estaban al revés: son contenido humano que se escribe, se revisa y aprueba el staff, o sea exactamente lo que hace el panel, y mientras vivían allá había que cargarlos desde wp-admin sin pasar por ninguna revisión. Se mudaron sin cambiar de `post_type`, así que no se perdió nada, y la API se corrió: si el panel está activo, no vuelve a registrarlos.

Lo que **no** controla el panel todavía: los Eventos, que se siguen cargando desde wp-admin.

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
| **Sombra** | Dos, cada una con su lugar escrito: `--sombra` es contacto (menú activo, desplegables) y `--sombra-flotante` es distancia, en una sola cosa — la cápsula de la barra inferior, que flota sobre el contenido. Las tarjetas no llevan ninguna: se separan con una línea de 1px. |
| **Tipografía** | Una familia, Inter, servida desde el plugin (400/600/700, 76 KB). Sin Google Fonts ni CDN. |
| **Gradientes** | Ninguno. El único motivo decorativo es la trama de rayas diagonales (`--trama`), y vive en un token. Va **sólo en la banda de título** de las tarjetas: se intuye, no se ve. |
| **Profundidad** | Sin sombras en las tarjetas: la dan las superficies anidadas. Una tarjeta de cifra es banda de título con trama + caja interior lisa con el número, como la referencia. |
| **Cifras** | `tabular-nums`: sin eso un 1 ocupa menos que un 8 y los números de dos tarjetas contiguas no alinean. |
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
4. Ninguna sombra fuera de `--sombra` / `--sombra-flotante`.
5. Una sola familia tipográfica.
6. Sin gradientes decorativos.
7. Sin assets de terceros en el panel.
8. Ningún elemento cuyas clases no tengan **ninguna** regla (una pantalla sin estilo esperando su turno).
9. Ninguna clase declarada que nadie use.
10. Sin `alert()` del navegador.
11. Ninguna URL del panel armada a mano en vez de con `promotur_url()`.

Cada comprobación está escrita en las dos direcciones: se probó que detecta el caso malo (inyectando a propósito un `#ff0000`, un radio de 7px, una sombra suelta, otra tipografía y un gradiente: las cinco saltaron) y que no marca el caso bueno.

Hoy pasa **sin un solo aviso**: no queda CSS sin usar, ni una clase sin estilo, ni un texto pendiente, ni un asset de terceros.

Y hay un segundo verificador, para lo que el de diseño no puede ver:

```bash
php tools/verificar-logica.php
```

Comprueba las **dos únicas funciones del ecosistema que transforman un dato en vez de moverlo de un lado a otro**, que son justo las dos que fallan en silencio si se equivocan:

- `PROMOTUR_Destinos::coords_desde_maps()` — decide dónde cae un pin. Un pin corrido no tira ningún error: manda a alguien al lugar equivocado.
- `CEADSSO_Roles::normalizar()` — decide si alguien del CEAD entra o no. Un rol que no normaliza bien rebota a una persona con un mensaje que no dice por qué.

Los casos son los reales: los cuatro formatos de enlace que escribe Google (incluido el corto, que no trae el punto, y uno con la latitud fuera del planeta), y las formas en que un WordPress escribe el nombre de un rol. Corre sin WordPress: probar esto no puede costar levantar un sitio.

`npm run verificar` corre los dos, más la auditoría móvil.

Y para mirar una pantalla sin levantar un WordPress:

```bash
php tools/vista-previa-panel.php sections/home             > /tmp/home.html
php tools/vista-previa-panel.php auth/login                > /tmp/login.html
php tools/vista-previa-panel.php sections/recorridos nuevo > /tmp/recorrido.html
```

El segundo argumento es el segmento que el router pasa como id: sin él, las secciones que hacen de lista y de detalle a la vez sólo se pueden mirar en su mitad de lista. Con esto se revisaron las **23 pantallas** del panel, una por una y en sus dos mitades, antes de dar nada por bueno. Los datos son de maqueta y están declarados como tales.

---

## 7. Qué se tocó y qué no

**El backend que quedó, quedó intacto.** No se cambió ni una línea de: el sistema de cuentas, los roles y capabilities, el flujo editorial, las tareas, las invitaciones, la auditoría ni las pantallas de wp-admin. Lo que sí se fue, entero y a propósito, está en §9.

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
| `includes/class-app-control.php` | **Nuevo**: lee y guarda lo que la app consume |
| `templates/sections/app.php` | **Nuevo**: la sección App |
| `readme.txt` | Changelog |

En `caaguazu-app-api` (v0.2.0) se agregaron **sólo tres métodos**, todos aditivos: `CZUAPI_UI_Content::get_strings()`, `get_manifest()` y `set_manifest()`. Ni un endpoint, ni un formato, ni un comportamiento cambió: es la contraparte de escritura de lo que ya se leía.

Las otras 20 plantillas **no se tocaron**: usan el mismo vocabulario de clases de siempre, así que se rediseñaron solas al cambiar el sistema debajo. Fue deliberado — mantiene el diff chico y el riesgo bajo.

### Sobre la referencia comprada

El `.zip` es **OneUI 5.12** (pixelcave): Bootstrap 5, jQuery y 40 dependencias, 534 KB de CSS minificado. De ahí se tomó **la tipografía Inter** (licencia SIL OFL, redistribuible; no es propiedad de pixelcave) y las ideas de estructura. **No se importó el framework**: el panel tiene hoy un CSS de ~30 KB y cero dependencias, y meterle Bootstrap + jQuery contradiría de frente las reglas de peso, tokens, radios y sombras de la filosofía del ecosistema. La referencia visual real fueron las dos capturas, que además están mucho más cerca de esas reglas que OneUI.

---

## 8. Pendiente, y por qué

Está acá para que sea una decisión y no una sorpresa.

1. **El texto sigue viviendo en el código.** Hay 537 cadenas `__()` en plantillas y clases: cambiar "Cola de revisión" hoy exige publicar el plugin. El mecanismo correcto ya existe en el ecosistema (`caaguazu-app-api` sirve textos por clave desde opciones, con fusión sobre el respaldo local y sin pisar con vacíos), pero no tiene editor y el panel no lo consume. Es un trabajo aparte, y grande.
2. **Los cuatro estados, a medias.** Hay vacío y éxito; el error es un mensaje genérico sin "reintentar" y no hay estado de carga salvo el "Enviando…". Sin conexión sólo sobrevive la cola de capturas.
3. **Sólo hay una serie temporal.** La actividad editorial sale del log de auditoría, que es lo único con timestamp. No hay serie de fichas publicadas por día, ni de visitas: por eso las tarjetas de cifras no tienen variación "vs. la semana pasada" como la referencia. Antes que inventar el número, no está.
4. **Colapsar el menú lateral** (el ícono de la referencia que reduce el panel a íconos) no está hecho.
5. **La galería lista todas las imágenes del sitio**, no sólo las que entraron por el panel. Hoy da igual —el sitio viejo se borró entero y no hay otras—, pero cada foto que entra queda marcada como del panel, así que el día que haga falta separarlas, el dato ya está guardado.
6. **La foto de la cuenta no se puede sacar**, sólo reemplazar por otra.

---

## 9. Lo que se podó

El panel arrastraba media aplicación que existía para alimentar **la web pública que este mismo plugin publicaba**: una vitrina con shortcodes, la ficha de destino como página, reseñas y consultas de visitantes, la curaduría de esa portada. Ese sitio se rehace desde cero y el producto pasó a ser la app. Todo eso era residuo, y se fue.

El criterio fue uno solo, verificable: **¿la app lo consume?** La API de la app (`caaguazu-app-api`) usa exactamente cuatro cosas del panel — `PROMOTUR_Destinos::owner_account_id()` y tres métodos de niveles de confianza de `PROMOTUR_Stats` — y no toca reseñas, curaduría ni consultas. Lo que no alimentaba ni a la app ni al trabajo editorial que produce sus fichas, se borró.

| Se fue | Qué era |
| --- | --- |
| `class-public.php` | La vitrina web y sus 7 shortcodes (`[promotur_destinos]`, `[promotur_mapa]`, `[promotur_explorar]`, `[promotur_itinerario]`, `[promotur_contacto]`, `[promotur_home]`, `[promotur_destacados]`) |
| `class-public-ajax.php` | Los formularios de esa vitrina y los AJAX de moderación |
| `templates/public/single-destino.php` | La ficha de destino como página web |
| `class-resenas.php` · `class-consultas.php` | Reseñas, consultas y reportes de visitantes |
| `class-curaduria.php` + sección **Curaduría** | Destacados y banner de la portada web |
| Sección **Moderación** | Moderaba lo anterior; sin origen, no tenía qué mostrar |
| `class-seo.php` | Open Graph de las fichas públicas |
| `nav-integration.php` | Se enganchaba al nav del theme de Turismo, que ya no existe |
| `qrcode.js` (2.297 líneas) · `caaguazu-portal-public.js` | Sólo servían a la ficha pública |
| Vistas y búsquedas sin resultado (`PROMOTUR_Stats`) | Se contaban en esa web; sin ella, mostrarían cero para siempre |

Dos consecuencias que conviene tener presentes:

- **El destino dejó de ser una página web.** El CPT pasó a `public => false`: se sirve por `/wp-json/czu-app/v1/inventario`, no por `/destino/<slug>`. Si siguiera siendo público, esa URL la dibujaría el theme del sitio —hoy una página de obra— y Google indexaría fichas que no se ven. Sigue editándose en el panel y en wp-admin, y sigue teniendo estados. El día que el sitio nuevo quiera fichas públicas, es volver a `'public' => true` y escribirle su plantilla.
- **Leaflet por CDN desapareció solo**, porque vivía en la vitrina. El verificador de diseño quedó **sin un solo aviso**.

El panel bajó de 16 secciones a 14, y de 608 textos a 481.

> Desde v3.2.0 son **16 secciones y 565 textos** otra vez, pero por el otro motivo: no volvió nada de la web pública: entraron Inventario, Artículos y Recorridos, que es contenido de la app. Y se fue la sección App, que estaba rota.

---

## 10. Cómo se publica

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

## 11. Los textos

Todos los textos que se ven en el panel están inventariados en [`textos-del-panel.md`](textos-del-panel.md): 565 en total, agrupados por pantalla, con su archivo y línea y una columna para escribir el reemplazo. Se regenera con:

```bash
php tools/textos-del-panel.php > docs/textos-del-panel.md
```

Fueron revisados y reescritos por una persona: el panel no tiene hoy ningún texto pendiente, y `verificar-diseno.php` lo comprueba (falla si vuelve a aparecer un `[FALTA: …]`).

Quedan a propósito catorce textos que arrancan en minúscula: son fragmentos pensados para leerse dentro de una frase o después de un número — *"4 esperan revisión"*. El inventario los marca con 🔡 para que se vean de un vistazo.
