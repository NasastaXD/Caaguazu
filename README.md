# Caaguazú

Portal del departamento de Caaguazú, Paraguay. **El sitio se está rehaciendo desde cero.**

- [`caaguazu-theme/`](caaguazu-theme/) — el sitio público: un theme de una sola plantilla que muestra *"caaguazu.net está siendo construida"* en cualquier URL. Sin JavaScript, sin pedidos a terceros.
- [`caaguazu-portal/`](caaguazu-portal/) — **el panel de promotores turísticos**, bajo `/turismo-panel`. Es lo que sigue vivo y en desarrollo. Ver [`docs/panel-turismo.md`](docs/panel-turismo.md).
- [`caaguazu-app-api/`](caaguazu-app-api/) — la API REST que consume la app Android. El panel es su cabina de mando: desde ahí se editan los textos, los medios y los colores que la app lee.
- [`docs/textos-del-panel.md`](docs/textos-del-panel.md) — todos los textos que se ven en el panel, por pantalla, con una columna para reescribirlos.
- [`tools/`](tools/) — `verificar-diseno.php` (comprueba las reglas del sistema de diseño; sale con código 1 si algo las rompe) y `vista-previa-panel.php` (dibuja una pantalla del panel sin levantar WordPress) y `textos-del-panel.php` (regenera el inventario de textos).

Los tres `.zip` restantes son los plugins del ecosistema de los que depende el panel —cuentas, locales y SSO CEAD—, tal como se subieron.

## Qué se borró

Se sacó todo el sitio anterior: el theme completo (templates, Customizer, formularios, mapas, PWA, SEO, i18n), el plugin `caaguazu-modulos` (Noticias, Agenda, Ecosistema, Educación, Instituciones, Lugares, Proyectos, Servicios), el plugin `caaguazu-turismo` (25 páginas de contenido sembrado) y el plugin `caaguazu-editor-ux`. Nada de eso se reutiliza: la reconstrucción arranca en limpio.

Lo único que se conservó del theme viejo son dos cosas que no son "sitio": `inc/updater.php` (el auto-updater contra GitHub Releases, que es cómo el sitio en producción recibe esta misma página) y `assets/icons/` (el isotipo del portal).

## El ecosistema alrededor del panel

El panel no autentica ni guarda identidad por su cuenta: todo eso corre sobre `caaguazu-cuentas`.

| Pieza | Plugin | Estado |
| --- | --- | --- |
| Panel de promotor | `caaguazu-portal` | acá, reworkeado (v3.0.0) |
| Identidad, sesión y permisos | `caaguazu-cuentas` | dependencia dura |
| Acceso de un clic desde el CEAD | `caaguazu-sso-cead` | funciona sin cambios |
| Negocios, reservas y reseñas | `caaguazu-locales` | independiente |
| API REST de la app Android | `caaguazu-app-api` | independiente |

## Publicar

El theme y el panel se actualizan solos en producción desde los GitHub Releases de este repo. Cada uno tiene su versión, su tag y su zip; **cada release lleva un solo zip, y cada updater se queda con el release que trae el suyo.**

1. Subir el `Version:` del componente que cambió (`caaguazu-theme/style.css` o `caaguazu-portal/caaguazu-portal.php`).
2. Al mergear a `main`, [`.github/workflows/release.yml`](.github/workflows/release.yml) arma su zip y publica el Release `vX.Y.Z`. Si la versión no subió, el tag ya existe y no hace nada.
3. El sitio lo ve dentro de las 12 h, o al toque desde el botón de comprobar (barra de admin para el theme, *Portal Turismo → Actualizaciones* para el panel).

Para armar los zips a mano: `bash bin/build-zip.sh` (o `bash bin/build-zip.sh portal`).

> Las dos series de versiones no deben cruzarse: el theme va por 5.x y el panel por 3.x. Si coincidieran, el job falla porque el tag ya existe.
