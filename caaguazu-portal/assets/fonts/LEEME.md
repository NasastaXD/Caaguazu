# Tipografía del panel

**Inter**, subconjunto latino, tres variantes (400 / 600 / 700), 76 KB en total.

- **Servida desde el plugin**, no desde Google Fonts ni un CDN. Un panel de trabajo no puede depender de un tercero en el camino crítico del primer dibujado, y la conexión de un promotor en el campo suele ser mala.
- Licencia **SIL Open Font License 1.1** (permite uso, incrustación y redistribución). Los archivos vienen del paquete de OneUI, pero la fuente en sí no es de pixelcave: es de The Inter Project Authors — <https://github.com/rsms/inter>.
- El `@font-face` vive en `assets/css/caaguazu-portal.css` con `font-display: swap`: si la fuente tarda, el texto se lee igual con la del sistema.
- `PROMOTUR_Assets::preload_fonts()` precarga sólo 400 y 600 (lo que se ve arriba de todo); la 700 carga cuando aparece.
- Las tres entran al precache del service worker, así que la pantalla offline se dibuja con la misma letra.

Si alguna vez hay que agregar una variante, va acá con el mismo nombre (`inter-<peso>.woff2`) y se declara en el bloque `@font-face` del CSS.
