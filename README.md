# Caaguazú

Portal del departamento de Caaguazú, Paraguay. **El sitio se está rehaciendo desde cero.**

- [`caaguazu-theme/`](caaguazu-theme/) — el sitio público: un theme de una sola plantilla que muestra *"caaguazu.net está siendo construida"* en cualquier URL. Sin JavaScript, sin pedidos a terceros.
- [`caaguazu-portal/`](caaguazu-portal/) — **el panel de promotores turísticos**, bajo `/turismo-panel`. Es lo que sigue vivo y en desarrollo, y donde el equipo escribe las tres cosas que la app muestra: fichas del inventario turístico, artículos y recorridos. Ver [`docs/panel-turismo.md`](docs/panel-turismo.md).
- [`caaguazu-app-api/`](caaguazu-app-api/) — la API REST que consume la app Android. Lee el contenido de donde ya vive y lo sirve; no lo duplica.
- [`caaguazu-sso-cead/`](caaguazu-sso-cead/) — el acceso de un clic desde el CEAD. Estaba en el repo sólo como `.zip` subido a mano; se sacó el código para poder arreglarle el mapeo de roles y revisarlo como cualquier otra cosa.
- [`caaguazu-web-ios/`](caaguazu-web-ios/) — espejo web (sin build) de la app de turismo, servido en `caaguazu.net/ios/`, para quien usa iPhone mientras no exista una app nativa. Temporal: se retira cuando esa app exista.
- [`docs/contrato-app-contenido.md`](docs/contrato-app-contenido.md) — **el contrato de contenido para quien construye la app**: cómo interpretar un sitio turístico, un artículo y un recorrido, con payloads y con el porqué de cada decisión.
- [`docs/textos-del-panel.md`](docs/textos-del-panel.md) — todos los textos que se ven en el panel, por pantalla, con una columna para reescribirlos.
- [`tools/`](tools/) — `verificar-diseno.php` (comprueba las reglas del sistema de diseño), `verificar-logica.php` (las dos funciones que transforman un dato: el parseo del enlace de Google Maps y la normalización de roles del CEAD), `vista-previa-panel.php` (dibuja una pantalla del panel sin levantar WordPress) y `textos-del-panel.php` (regenera el inventario de textos). Los dos primeros salen con código 1 si algo falla; `npm run verificar` los corre.

Los dos `.zip` restantes son los plugins del ecosistema de los que depende el panel —cuentas y locales—, tal como se subieron.

## Qué se borró

Se sacó todo el sitio anterior: el theme completo (templates, Customizer, formularios, mapas, PWA, SEO, i18n), el plugin `caaguazu-modulos` (Noticias, Agenda, Ecosistema, Educación, Instituciones, Lugares, Proyectos, Servicios), el plugin `caaguazu-turismo` (25 páginas de contenido sembrado) y el plugin `caaguazu-editor-ux`. Nada de eso se reutiliza: la reconstrucción arranca en limpio.

Lo único que se conservó del theme viejo son dos cosas que no son "sitio": `inc/updater.php` (el auto-updater contra GitHub Releases, que es cómo el sitio en producción recibe esta misma página) y `assets/icons/` (el isotipo del portal).

## El ecosistema alrededor del panel

El panel no autentica ni guarda identidad por su cuenta: todo eso corre sobre `caaguazu-cuentas`. **Nadie del equipo abre una pantalla de WordPress y nada del panel depende de que exista un usuario de WordPress**: la cuenta, la galería, la estructura y el equipo se administran adentro del panel. Ver [«Nada de lo que hace el equipo pasa por WordPress»](docs/panel-turismo.md#nada-de-lo-que-hace-el-equipo-pasa-por-wordpress).

| Pieza | Plugin | Estado |
| --- | --- | --- |
| Panel de promotor | `caaguazu-portal` | acá, reworkeado (v3.2.0) |
| Identidad, sesión y permisos | `caaguazu-cuentas` | dependencia dura |
| Acceso de un clic desde el CEAD | `caaguazu-sso-cead` | acá, con el mapeo de roles arreglado (v1.1.0) |
| Negocios, reservas y reseñas | `caaguazu-locales` | independiente |
| API REST de la app Android | `caaguazu-app-api` | acá (v0.8.2) |
| Espejo web para iOS | `caaguazu-web-ios` | acá, temporal |

## Publicar

El theme, el panel y la API de la app se actualizan solos en producción desde los GitHub Releases de este repo. Cada componente tiene su versión, su tag y su zip; **cada release lleva un solo zip, y cada updater se queda con el release que trae el suyo.**

1. Subir el `Version:` del componente que cambió — y en los cuatro plugins, también su constante (`PROMOTUR_VERSION`, `CZUAPI_VERSION`, `CEADSSO_VERSION`, `CZUWIOS_VERSION`), que llevan el número dos veces.
2. [`.github/workflows/release.yml`](.github/workflows/release.yml) arma el zip y publica el Release. Si la versión no subió, el tag ya existe y no hace nada.
3. El sitio lo ve dentro de las 12 h, o al toque desde el botón de comprobar (barra de admin para el theme, *Portal Turismo → Actualizaciones* para el panel, *Caaguazú API → Actualizaciones* para la API).

**Los cinco componentes publican**: theme, panel, API de la app, SSO CEAD y el espejo web para iOS. Los dos últimos no se auto-actualizan —su release es para descargar y subir a mano—, pero tener el zip publicado es lo que hace que se puedan probar sin armarlo.

### Sacar un release sin mergear

El workflow se dispara de dos formas: **al mergear a `main`**, y **a mano** desde Actions → *Publicar releases* → *Run workflow*, eligiendo la rama y qué componente publicar. Lo segundo es lo que permite probar una rama antes de aprobarla: hasta que existió, testear un cambio en revisión obligaba a armar el zip a mano o a esperar el merge.

Para armar los zips a mano: `bash bin/build-zip.sh` (o `bash bin/build-zip.sh portal`, `app-api`, `sso`, `web-ios`).

`caaguazu-sso-cead` y `caaguazu-web-ios` **no** se auto-actualizan: sus zips se arman bajo demanda y se instalan a mano.

### Un tag por componente

Los tags son **`theme-X.Y.Z`**, **`portal-X.Y.Z`**, **`app-api-X.Y.Z`**, **`sso-X.Y.Z`** y **`web-ios-X.Y.Z`**. No es cosmético: hasta acá theme y panel usaban los dos `vX.Y.Z`, y el repo ya traía los tags `v1.x` a `v5.0.1` del sistema viejo. Cuando al theme le tocó `5.0.1` y al panel `3.1.0`, los dos tags ya existían y **ninguno de los dos releases llegó a publicarse**. Con un prefijo por componente, cada serie es suya y no se cruza ni con la otra ni con la historia vieja.

Los tres updaters sacan la versión del final del tag, y siguen aceptando los `vX.Y.Z` de antes para no perder de vista lo ya publicado.

> El updater del panel necesita un detalle extra: la librería que usa (plugin-update-checker) saca la versión del tag con `ltrim( $tag, 'v' )`, y su plan B —leer el header `Version:` del archivo principal— no le sirve acá porque el plugin vive en una subcarpeta y ella lo busca en la raíz. Por eso `caaguazu-portal.php` corrige la versión con `addResultFilter()`.

> **Una sola vez:** lo que hay instalado hoy en producción trae los updaters viejos, que no reconocen los tags nuevos. Este primer par de releases hay que instalarlo a mano (subir los dos zips desde WordPress). De ahí en adelante las actualizaciones vuelven a ser automáticas.
