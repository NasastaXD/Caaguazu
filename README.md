# Caaguazú

Portal del departamento de Caaguazú, Paraguay. **El sitio se está rehaciendo desde cero.**

- [`caaguazu-theme/`](caaguazu-theme/) — el sitio público: un theme de una sola plantilla que muestra *"caaguazu.net está siendo construida"* en cualquier URL. Sin JavaScript, sin pedidos a terceros.
- [`caaguazu-portal/`](caaguazu-portal/) — **el panel de promotores turísticos**, bajo `/turismo-panel`. Es lo que sigue vivo y en desarrollo. Ver [`docs/panel-turismo.md`](docs/panel-turismo.md).
- [`tools/`](tools/) — `verificar-diseno.php` (comprueba las reglas del sistema de diseño; sale con código 1 si algo las rompe) y `vista-previa-panel.php` (dibuja una pantalla del panel sin levantar WordPress).

Los cuatro `.zip` restantes son los plugins del ecosistema de los que depende el panel —cuentas, locales, SSO CEAD y la API de la app—, tal como se subieron. Su código fuente vive en `nasastaxd/turismo`.

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

El theme se actualiza solo en producción vía GitHub Releases:

1. Subir el `Version:` de `caaguazu-theme/style.css` en el PR.
2. Al mergear a `main`, [`.github/workflows/release.yml`](.github/workflows/release.yml) arma `caaguazu-theme.zip` y publica el Release `vX.Y.Z`.
3. El sitio lo ve dentro de las 12 h (o al toque con el botón *⟳ Buscar actualización* de la barra de admin).

Para armar el zip a mano: `bash bin/build-zip.sh`.
