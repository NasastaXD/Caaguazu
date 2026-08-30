=== Caaguazú Portal — Promotores Turísticos ===
Contributors: municipalidadcaaguazu
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 3.6.3
License: GPLv2 or later

Panel autenticado tipo app (PWA) bajo /turismo-panel, con enrutador propio, login propio, roles y flujo editorial para las tres cosas que la app muestra: fichas del inventario turístico, artículos y recorridos.

== Description ==

Plugin del Portal de Promotores Turísticos de Caaguazú. Panel autenticado tipo
app bajo `/turismo-panel`, con enrutador, login y sesión propios —no depende
de usuarios de WordPress, corre sobre el sistema de cuentas universal
`caaguazu-cuentas`—, instalable como PWA, con modo claro/oscuro y un sistema
de diseño propio (tokens, tipografía autohospedada): no hereda nada del theme
activo, y desencola su CSS en las rutas del panel.

El equipo escribe ahí las tres cosas que la app muestra —fichas del
inventario turístico, artículos y recorridos— con un mismo flujo editorial:
borrador → enviar → cola de revisión (asignarme) → aprobar/devolver con
feedback → publicado. wp-admin no interviene: ni la edición de contenido, ni
la cuenta, ni la galería, ni la estructura (categorías/etiquetas), ni el
equipo (roles, suspensión, invitaciones) tienen pantalla ahí. Lo único que
queda en wp-admin es el registro de auditoría y las actualizaciones del
plugin, y ninguna de las dos cosas la necesita nadie del equipo.

Roles: Promotor, Mini Promotor, Visitante (capabilities `promotur_*`).

== Instalación ==

1. Subir `caaguazu-portal` a `/wp-content/plugins/` y activar (necesita el
   plugin `caaguazu-cuentas` activo).
2. Se crean los roles y se vacían (flush) las rewrite rules automáticamente.
3. Entrar a `/turismo-panel/entrar`, con una cuenta creada por invitación.

Convive con el plugin `caaguazu-locales` sin colisiones (prefijos distintos).

== Auto-actualización ==

El plugin se actualiza desde wp-admin sin pasar por WordPress.org, usando
plugin-update-checker (vendoreado en `vendor/`) contra los GitHub Releases de
`NasastaXD/Caaguazu`. Al mergear a `main`, el job `portal` de
`.github/workflows/release.yml` lee la versión del header, empaqueta
`caaguazu-portal.zip` y publica el release `v{version}`; el checker lo detecta
(~cada 12 h) y ofrece la actualización.

En ese repositorio también se publica el theme del sitio, con su propia versión
y su propio zip. Para que no se confundan, el updater sólo considera un release
que traiga adjunto `caaguazu-portal.zip` — la regla no depende de cómo se
llamen los tags.

* Versión en un solo lugar: header `Version:` + constante `PROMOTUR_VERSION` (semver).
* Migraciones de BD: incrementar `PROMOTUR_DB_VERSION`; corren solas en `admin_init`
  vía `promotur_run_migrations()`.
* Repo privado: definir `PROMOTUR_GITHUB_TOKEN` (PAT de solo lectura) en `wp-config.php`.

== Changelog ==

= 3.6.3 =
* Se saca la ayuda de «Descripción» en Categorías («una o dos líneas; encabeza
  la categoría en la app»): no hacía falta, y encima salía en rojo —el color
  de campo obligatorio— porque estaba metida en el mismo `<em>` que el
  asterisco, no en una ayuda de verdad.

= 3.6.2 =
* **Categorías: la descripción y la imagen se pisaban con el nombre y el
  badge de fichas.** El `<form>` de cada categoría —nombre, descripción,
  imagen, botón Guardar— es un solo flex sin salto de línea, y desde que la
  3.5.2 le agregó descripción e imagen quedaba todo apretado en una fila:
  campos amontonados y el badge de «N fichas» flotando centrado en el medio
  del formulario. Ahora cada campo va en su propio renglón.

= 3.6.1 =
* **«Pegar datos» en los tres editores.** Un cuadro plegable arriba del
  formulario donde se pega un JSON y los valores se reparten solos en las
  casillas. Es para cuando el contenido ya está escrito en otro lado —un
  documento, una planilla, un archivo de la Municipalidad— y cargarlo era
  copiar quince veces entre dos ventanas.
* No guarda ni envía nada: deja el formulario lleno y editable, y quien carga
  revisa y aprieta Guardar como siempre. El checklist y la validación del
  servidor siguen siendo los mismos, que es lo que sostiene la calidad de lo
  que se publica.
* Las claves del JSON son los nombres de los campos, con el prefijo largo
  (`_promotur_horario`) o sin él (`horario`), sin acentos ni mayúsculas que
  importen. Los desplegables aceptan tanto el valor como el texto que se ve
  («Sitio Natural», «Asfalto»). Al terminar dice cuántos campos llenó y cuáles
  no reconoció, en vez de fallar en silencio.
* La foto sigue subiéndose con su botón: un id de adjunto pegado a mano
  apuntaría a cualquier cosa que tenga ese id en la biblioteca.
* **No instalar la 3.6.0.** Se publicó por error desde una rama que todavía no
  tenía la 3.5.1 ni la 3.5.2, así que su zip no las trae: instalarla haría
  retroceder el rendimiento del panel y devolvería el menú de Etiquetas a
  wp-admin. Esta 3.6.1 es la misma función, ya sobre las dos.

= 3.5.2 =
* **El panel deja de hacer ~124 consultas de más en cada pantalla.** La barra
  superior pedía la lista de notificaciones y después el contador de no
  leídas, y el contador volvía a armar la lista entera: `get_items()` corría
  dos veces por carga. Encima las dos consultas usaban `fields => 'ids'`, que
  hace que WP_Query se saltee el precargado de posts, así que cada título y
  cada fecha del listado disparaba su propia consulta. Como la barra vive en
  el shell, eso lo pagaban **todas** las pantallas. Medido con las mismas
  llamadas que hace el topbar: de 126 consultas a 3.
* El contador de la cola de revisión se calcula una vez por carga y no dos
  (lo piden el menú lateral e Inicio).
* **Las categorías suman descripción e imagen**, que se editan en Estructura.
  Una categoría tiene pantalla propia en la app y necesitaba algo más que un
  nombre; una etiqueta no lleva ninguna de las dos, que es un chip de filtro.
  La descripción usa el campo nativo del término —no un meta nuevo— para no
  tener el mismo texto guardado en dos lugares.
* El widget de subir fotos dejó de estar atado al editor de contenido: ahora
  sirve en cualquier pantalla, que es lo que hizo falta para la imagen de la
  categoría.

= 3.5.1 =
* Se saca «Etiquetas» del menú nativo de Entradas en wp-admin. Es la
  taxonomía `post_tag` de fábrica de WordPress, sin relación con
  `promotur_etiqueta` —la que arma y usa el panel—: nadie del equipo escribe
  entradas nativas, así que ese menú era puro estorbo para quien entra a
  wp-admin. La taxonomía sigue registrada, sólo se sacó el enlace del menú.

= 3.5.0 =
* **Se sacaron Gancho y Accesibilidad de la ficha.** No le hacían falta a la
  app: el gancho no decía nada que el título y la portada no dijeran ya, y
  la accesibilidad se llenaba con frases sueltas sin ningún criterio común.
  El valor cargado sigue en su meta —no se borra un dato sin que alguien lo
  decida—, sólo se dejó de pedir y de mostrar.
* **Se sacó el concepto de Zona.** El departamento es chico y el enlace de
  Google Maps de cada ficha ya dice dónde queda. La taxonomía sigue
  registrada por las mismas razones que arriba, pero ya no se edita ni se
  muestra en ningún lado del panel.
* **La descripción de la ficha ahora coincide con lo que la app busca.**
  Era un bug del lado de la API, no del panel: viajaba con el nombre
  `articulo_html`, copiado sin pensar del formato de Artículos. Ver el
  changelog de `caaguazu-app-api` 0.5.0.
* Se sacó la línea de "acceso prestado" de Mi perfil: no cumplía ninguna
  función, sólo generaba dudas.
* Se sacaron las dos ayudas redundantes de Estructura (qué es una categoría,
  qué es una etiqueta): repetían lo que el nombre del campo ya decía.
* "Actividad reciente" ahora explica qué mide la métrica, con un subtítulo
  arriba del gráfico.
* El ícono de documento de la barra inferior se veía estirado hacia arriba
  cuando no estaba seleccionado; la barra es un poco más ancha de los
  costados.
* Mi perfil: la foto de cuenta ahora tiene tope de 5 MB, y se borra sola a
  los 1095 días de subida —excepto la de un Promotor, que no vence—.
* Ayuda se reescribió entera: describía un sitio público, reseñas y una
  curaduría que dejaron de existir hace versiones. Ahora lista las secciones
  reales, tomadas del mismo registro de permisos que decide qué puede abrir
  cada quien —una sección que no ves en el menú tampoco aparece acá—.

= 3.4.0 =
* **La ficha ahora es de sitio o de evento.** Es el primer campo del formulario,
  porque cambia el resto: un evento pide día y hora de inicio, y de cierre si lo
  tiene; un sitio no ve esos campos. Todo lo demás se carga igual, porque un
  evento **es** un lugar con fechas: tiene gancho, foto, ubicación, costo,
  horario, fuentes y flujo editorial exactamente como cualquier ficha.
* **Los eventos dejan de vivir en wp-admin.** Eran un tipo de contenido aparte
  en `caaguazu-app-api`, con la mitad de los campos —sin gancho, sin galería,
  sin fuentes— y sin pasar por revisión. Lo ya cargado ahí se sigue sirviendo a
  la app; lo nuevo se carga acá.
* **El checklist de mínimos entiende el tipo.** `aplica_campo()` decide si un
  campo corresponde, y lo usan las dos partes: el editor para mostrarlo o
  esconderlo, y el checklist para no exigirle a un sitio la fecha de un evento.
  Si sólo lo supiera el formulario, un sitio no se podría publicar nunca.
* **Un documento de datos que no puede quedar viejo.**
  `tools/inventario-de-datos.php` genera `docs/datos-para-la-app.md` leyendo el
  modelo real, y `npm run verificar` falla si el archivo quedó atrasado o si se
  agregó un campo sin decidir si sale a la app.

= 3.3.0 =
* **Se puede deshacer.** Hasta acá el panel sabía llevar algo de borrador a
  publicado y no sabía traerlo de vuelta: una vez publicada una ficha, lo único
  que se podía hacer con ella era editarla. No había forma de sacarla de la app,
  ni de archivarla, ni de borrarla. Ahora hay cinco operaciones nuevas —retirar
  de revisión, despublicar, volver a publicar, archivar y borrar— más recuperar
  lo borrado.
* **Tres estados dejan de ser decorativos.** De los ocho que el flujo declara,
  `aprobado`, `despublicado` y `archivado` eran inalcanzables: tenían su pastilla
  de color y nada podía ponerlos. Ahora se llega a los tres.
* **Borrar es la papelera, no la nada.** `wp_trash_post()`, recuperable desde el
  propio panel — nadie tiene que abrir wp-admin para deshacer un borrado, que es
  la regla que el plugin sostiene desde el cutover de identidad. Y es además lo
  que la app necesita: `CZUAPI_Sync` engancha ese hook y deja su lápida, así que
  el teléfono se entera de que eso dejó de existir.
* **Lo publicado no se borra de un clic.** Primero se despublica. Son dos pasos
  en vez de uno, y esa fricción es a propósito: lo publicado lo está leyendo
  gente en la app. El mensaje del rechazo dice exactamente qué hacer.
* **Una sola fuente para los botones y para el permiso.**
  `PROMOTUR_Editorial::transiciones()` declara qué puede hacer esta cuenta con
  esta pieza en este estado; la UI dibuja lo que devuelve y el servidor rechaza
  lo que no esté ahí. Si la lista la arma la plantilla y el permiso lo comprueba
  el handler, los dos se separan y aparece un botón que da 403 — o, peor, un
  handler que acepta algo que ningún botón ofrecía.
* **Mis contenidos gana un filtro por estado**, con papelera y archivados. Lo
  archivado sale de la lista de «en curso» a propósito: si no, lo que se dejó de
  lado sigue ocupando la pantalla que se abre para ver qué hay a medias.

= 3.2.3 =
* **La foto de portada se daba por cargada sin foto.** Un campo de imagen vacío
  se guardaba como el entero `0`, y el checklist de mínimos —que sólo mira si el
  valor está vacío— lo tomaba por completo: un artículo sin portada pasaba el
  mínimo y se podía enviar a revisión. Afectaba igual a la ficha y al recorrido.
  Ahora un `0` se trata como lo que es —no hay foto— y el meta se borra.
* **El error del JavaScript dice qué pasó.** Cuando la respuesta del servidor no
  es JSON, en vez de «Algo salió mal. Probá de nuevo.» se muestra el código HTTP
  y el principio de lo que vino. Ese mensaje no distinguía «tu sesión venció» de
  «esta URL no existe», que son dos problemas con dos arreglos distintos.
* **Las reglas de reescritura se comprueban todas, no una.**
  `promotur_asegurar_rewrite_rules()` miraba sólo la del inicio del panel, y con
  esa presente no volvía a regenerar el juego. Ese atajo deja pasar el estado más
  difícil de diagnosticar que tiene el plugin: si falta la regla de
  `/turismo-panel/datos/`, las pantallas se dibujan perfecto y nada de lo que se
  guarda funciona. Las reglas viven ahora en un mapa (`PROMOTUR_Router::reglas()`)
  que la guarda recorre entero, y el despacho tiene una red: si `/datos/` o
  `/accion/` llegan igual al renderizador de secciones, se despachan como lo que
  son en vez de devolver un 404 en HTML con status 200.
* **El panel desencola el CSS del theme activo en sus rutas.** El theme 5.0.4 ya
  dejó de encolarlo ahí, que arregló el síntoma; pero esa guarda vive en el
  theme, y el theme es lo que se va a rehacer. La promesa del panel desde la
  3.0.0 —«el sitio público se puede rehacer entero sin poder cambiarle la cara al
  panel»— sólo se sostiene con la defensa de este lado. Se busca por origen y no
  por nombre de handle. Con el theme actual no desencola nada: es una red, no un
  parche.

= 3.2.2 =
* **Guardar borrador, enviar a revisión, aprobar/devolver, tareas y la
  captura offline no funcionaban: ningún botón de esas pantallas respondía
  al clic.** La causa era una sola: `initSubnav()` e `initAtajos()` (el
  submenú del lateral y el atajo ⌘K/Ctrl+K) se llamaban al cargar la página
  pero no estaban definidas en ningún lado —una poda de código de la v3.0.0
  les borró el cuerpo y se olvidó de sacar el llamado—, así que el
  navegador tiraba `initSubnav is not defined` a mitad de la secuencia de
  arranque y todo lo que venía después en esa lista —el editor, la
  revisión, la gestión de tareas, la captura de campo— nunca llegaba a
  engancharse. Verificado con un navegador real: antes del arreglo, un
  click en "Guardar borrador" no disparaba ningún pedido; después, sí.
* **Subir una foto no avisaba si había salido bien.** El caso de éxito
  borraba el mensaje en vez de escribir uno; la única señal era la miniatura
  cambiando en un recuadro de 64px, fácil de no ver. Ahora dice "Foto
  subida."

= 3.2.1 =
* **Se apaga la pantalla nativa de wp-admin para Destinos, Artículos y
  Recorridos** (lista + editor de bloques) y para sus tres taxonomías —el
  panel es la única pantalla de edición desde la v3.0.0, pero la de
  wp-admin seguía viva por detrás sin que nadie la usara: cualquier usuario
  de WordPress con permiso de Autor podía editar contenido del panel sin
  pasar por el flujo editorial ni quedar en la auditoría.
* **`PROMOTUR_Audit::post_actions()` cubre los tres tipos.** Desde que
  Artículos y Recorridos entraron al flujo editorial (v3.2.0), sus eventos
  —creado, enviado, aprobado, publicado— quedaban afuera de la pestaña
  "Contenido" del registro de wp-admin: la lista de acciones sólo tenía las
  cinco de `destino_*`. Ahora se arma sola a partir de los tipos que declara
  `PROMOTUR_Editorial`.
* Dos funciones sin ningún llamador (`promotur_nav_items()`,
  `promotur_user_phone()`) y la descripción del plugin en este archivo, que
  todavía hablaba de tokens heredados del theme y de rutas de antes de la
  v3.0.0.

= 3.2.0 =
* **Artículos.** El panel gana la sección donde se escriben las notas que la app
  muestra: ante título, título, foto de portada con su pie, autor o autores,
  subtítulo, entradilla, cuerpo y fuentes, más categoría y etiquetas. Pasan por
  el mismo flujo de aprobación del staff que una ficha. El CPT
  `promotur_articulo` se muda desde `caaguazu-app-api` sin cambiar de nombre, así
  que no se pierde nada de lo ya cargado.
* **Recorridos.** Se arman eligiendo sitios del inventario turístico —no
  escribiendo lugares de nuevo—, hasta nueve, cada uno con el texto que lo
  acompaña (la historia, el dato curioso), un audio o video propio, y botones
  para subirlo o bajarlo en el orden del paseo. El recorrido entero puede llevar
  además sus audios y videos, y vincularse con artículos ya publicados. Mismo
  flujo editorial. La app recibe la ruta ya armada como enlace de Google Maps.
* **Inventario turístico.** Sección nueva: el catálogo de fichas publicadas del
  departamento, con sus datos. Es de donde los recorridos toman sus paradas, y
  hacía falta poder verlo entero antes de armar uno.
* **El flujo editorial deja de ser sólo de fichas.** Estados, checklist, cola de
  revisión, notificaciones, búsqueda, «Mis contenidos», reportes y portafolio
  miran ahora los tres tipos. Cada tipo declara qué campos tiene y qué mínimos
  exige; no hay un solo `if` por tipo en el flujo.
* **La ubicación de una ficha se carga con un enlace de Google Maps**, y las
  coordenadas quedan de alternativa. El pin se saca del enlace cuando el enlace
  lo trae (los cuatro formatos que escribe Google están cubiertos); si es un
  enlace corto, el panel lo dice y pide las coordenadas a mano. La app se apoya
  en Google Maps para llevar a la gente hasta el lugar, así que el enlace es el
  dato que de verdad se usa.
* **Se van cinco campos de la ficha**: cómo llegar, referencia, temporada ideal,
  servicios y duración sugerida. Se llenaban con frases genéricas que no ayudaban
  a decidir nada, y cómo llegar lo resuelve el enlace de Google Maps mejor que un
  párrafo. Los datos ya cargados **no se borran**: la ficha deja de pedirlos, de
  mostrarlos y de publicarlos, y el valor queda en su meta.
* **La sección App queda fuera de circulación.** Llamaba a tres métodos de
  `caaguazu-app-api` que existen desde su versión 0.2.0, contra la 0.1.0 que hay
  instalada — y `class_exists()` no distingue una versión de otra, así que la
  pantalla moría con un error fatal apenas se abría. El código queda en el repo,
  con su plantilla y una guarda, para volver a enchufarlo de una línea.
* **`tools/verificar-logica.php`**: comprobaciones de las dos funciones que
  transforman un dato en vez de moverlo —el parseo del enlace de Google Maps y la
  normalización de roles del CEAD—, que son las dos que fallan en silencio si se
  equivocan. `npm run verificar` las corre.
* La vista previa sin WordPress (`tools/vista-previa-panel.php`) acepta ahora el
  segmento de detalle, así las secciones que hacen de lista y de editor a la vez
  se pueden mirar de las dos formas. La auditoría móvil pasó de 15 pantallas a
  22 con eso, y destapó dos objetivos táctiles por debajo de 44px que nunca se
  habían medido: el «← Volver» de las vistas de detalle (21px) y los motivos de
  un clic de la revisión (30px). Los dos arreglados.

= 3.0.0 =
* **El panel entero se muda a `/turismo-panel`**: secciones, acceso (`/turismo-panel/entrar`), invitaciones (`/turismo-panel/i/<token>`) y PWA (`manifest.webmanifest`, `sw.js`, `icon-<n>.png`, `offline`) cuelgan de ahí. Las rutas viejas —`/turismo/panel`, `/czu-login`, `/registro`, `/recuperar`, `/salir`, `/i/<token>` y los archivos `promotur-*` de la raíz— responden 301 a su equivalente nueva, así no se rompen las invitaciones ya enviadas ni la PWA instalada.
* **Rediseño visual completo**: sistema propio de tokens (tinta única como acento, tres radios, una sombra, una tipografía), menú lateral agrupado con submenú, migas de pan, barra inferior en teléfono y modo oscuro. Sin framework y sin dependencias nuevas.
* **Tipografía servida desde el plugin** (Inter, 3 variantes, 76 KB): el panel deja de heredar las fuentes del theme y de depender de un CDN.
* **El panel deja de heredar el CSS del theme activo**: se desencola en las rutas del panel. El sitio público se rehace sin poder romper el panel.
* `PROMOTUR_Stats::serie_diaria()`: actividad editorial por día leída del log de auditoría (una sola consulta agrupada), para las barras del inicio.
* Atajo ⌘K / Ctrl+K para el buscador, y submenú plegable en el lateral.
* **Tarjetas con relieve**: las de cifra pasan a ser banda de título con trama + caja interior con el número y la flecha, y los rótulos arrancan en mayúscula. El pulso de actividad deja de ser un sparkline en un rincón y pasa a ser un trazo sobre reja punteada, con el día debajo de cada punto y el de hoy marcado.
* **Barra inferior nueva en teléfono**: cápsula flotante de vidrio, centrada y despegada del borde, con la etiqueta abriéndose sólo en el acceso activo.
* **El panel queda sólo en español**: se saca el selector de idioma de la barra superior y la capa que cambiaba el locale por cookie. Los idiomas que sí existen son los de la app (ES/EN/GN), y se editan en la sección App.
* **Repaso del panel en teléfono**: las 15 pantallas auditadas con `tools/auditar-movil.mjs` — sin desborde horizontal y con todo lo que se toca en 44px o más. En el editor, el checklist de mínimos sube antes del formulario cuando hay una sola columna.
* **Poda del sitio viejo**: se fue todo lo que existía para alimentar la web pública que este plugin publicaba — la vitrina y sus 7 shortcodes, la ficha pública, las reseñas y consultas de visitantes, la curaduría de portada, el SEO/Open Graph, la integración con el nav del theme viejo, y las secciones Moderación y Curaduría del panel. Con eso se van también Leaflet por CDN y qrcode.js. El destino deja de ser una página web (`public => false`): su consumidor es la app, que lo lee por `/wp-json/czu-app/v1/inventario`.
* **Sección App**: el panel pasa a ser la cabina de mando de la aplicación móvil. Desde ahí se editan los textos de la app por idioma (ES/EN/GN), el manifiesto de medios y el icono y color de cada categoría — todo lo que la app lee del servidor y antes tenía endpoint pero no editor. Requiere `caaguazu-app-api` 0.2.0; sin ese plugin la sección no se registra.
* Repaso completo de textos: los 569 mensajes del panel revisados y reescritos por una persona (ver `docs/textos-del-panel.md` en el repo).
* Se sacan los `alert()` del navegador: los errores se dicen en la pantalla, donde pasó la cosa.
* **El auto-updater pasa a leer los releases de `NasastaXD/Caaguazu`**, donde vive ahora el codigo. Como en ese repo tambien se publica el theme del sitio, el updater filtra por release que traiga adjunto `caaguazu-portal.zip`: nunca se come un release del theme creyendo que es suyo.

= 1.1.3 =
* Integración con el shell propio de Turismo del theme Caaguazú (`caaguazu_tourism_shell_items`): agrega "Destinos" (desplegable con las categorías reales de `promotur_categoria`) y, solo para usuarios logueados con el permiso `promotur_view_panel`, un link directo al panel de promotor.

= 1.1.0 =
* Registro INVITE-ONLY con teléfono obligatorio; invitaciones en tabla custom con link corto /i/<token>.
* Gestión en wp-admin: Usuarios (editar/eliminar/suspender), Invitaciones y Logs (usuarios y posts) sobre una tabla de auditoría.
* Suspensión reversible de usuarios. Sección "Ayuda" en el panel. Barra de navegación inferior en móvil y pulido del modo claro.

= 1.0.0 =
* Fase 0 (framework del panel) + Fase 1 (MVP editorial).
