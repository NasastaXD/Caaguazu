# Caaguazú

Portal del departamento de Caaguazú, Paraguay. **El sitio se está rehaciendo desde cero.**

Este repo quedó reducido a una sola cosa: [`caaguazu-theme/`](caaguazu-theme/), un theme de WordPress de una sola plantilla que muestra *"caaguazu.net está siendo construida"* en cualquier URL del sitio. Sin plugins, sin módulos, sin JavaScript, sin pedidos a terceros.

## Qué se borró

Se sacó todo el sitio anterior: el theme completo (templates, Customizer, formularios, mapas, PWA, SEO, i18n), el plugin `caaguazu-modulos` (Noticias, Agenda, Ecosistema, Educación, Instituciones, Lugares, Proyectos, Servicios), el plugin `caaguazu-turismo` (25 páginas de contenido sembrado) y el plugin `caaguazu-editor-ux`. Nada de eso se reutiliza: la reconstrucción arranca en limpio.

Lo único que se conservó del theme viejo son dos cosas que no son "sitio": `inc/updater.php` (el auto-updater contra GitHub Releases, que es cómo el sitio en producción recibe esta misma página) y `assets/icons/` (el isotipo del portal).

## Qué sigue

La prioridad es el **panel de promotores turísticos**. Ese código no vive acá — está en el repo `nasastaxd/turismo`, junto con el sistema de cuentas y el SSO:

| Pieza | Plugin | Estado |
| --- | --- | --- |
| Panel de promotor | Caaguazú Portal (`caaguazu-portal`) | se mantiene; se reworkea casi todo el aspecto visual |
| Cuentas / reseñas / panel de dueños | Caaguazú Locales (`caaguazu-locales`) | se mantiene |
| SSO CEAD (acceso de un clic al panel) | Caaguazú SSO CEAD | se mantiene |

Este repo pasa a ser, por ahora, sólo la cáscara del sitio público.

## Publicar

El theme se actualiza solo en producción vía GitHub Releases:

1. Subir el `Version:` de `caaguazu-theme/style.css` en el PR.
2. Al mergear a `main`, [`.github/workflows/release.yml`](.github/workflows/release.yml) arma `caaguazu-theme.zip` y publica el Release `vX.Y.Z`.
3. El sitio lo ve dentro de las 12 h (o al toque con el botón *⟳ Buscar actualización* de la barra de admin).

Para armar el zip a mano: `bash bin/build-zip.sh`.
