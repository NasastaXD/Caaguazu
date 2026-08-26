# Caaguazú — theme de obra

Theme classic de WordPress con **una sola plantilla**. Mientras el portal se reconstruye, cualquier URL del frontend (home, entradas, páginas, archivos, búsqueda, 404) cae en `index.php` y muestra la página *"caaguazu.net está siendo construida"*.

```
style.css        cabecera del theme + todos los estilos (~4 KB)
index.php        la página entera
functions.php    bootstrap: encolado, limpieza de bloat, favicon, noindex, isotipo e ilustración SVG
inc/updater.php  auto-updater contra GitHub Releases
assets/icons/    isotipo del portal (favicon / apple-touch-icon)
```

Sin JavaScript, sin webfonts, sin imágenes de fondo, sin llamadas a terceros: la página son dos requests (HTML + CSS) más el favicon. La escena de obra (monitor vallado con cinta, grúa, cartel de precaución, conos) es un SVG plano dibujado a mano dentro de `functions.php`, no un vector de stock; los aros del fondo son gradientes CSS; y la única animación —el punto del chip "En construcción"— se apaga sola con `prefers-reduced-motion`.

## Instalación

Apariencia → Temas → Añadir → Subir tema → `caaguazu-theme.zip` → Activar. No necesita ningún plugin.

## Cosas a tener en cuenta

- **`noindex`**: `functions.php` fuerza `wp_robots_no_robots` para que los buscadores no indexen la página de obra como si fuera el sitio. Cuando vuelva el sitio real hay que sacar ese `add_filter`. Si la obra se estira meses y preocupa el SEO, la alternativa estándar es responder `503` con `Retry-After` en vez de `200 + noindex`.
- **Menús, widgets y Customizer**: el theme no registra ninguno a propósito. Lo que haya configurado el sitio queda guardado en la base y vuelve a aparecer cuando el theme nuevo los registre de nuevo.
- **Contenido**: el theme no borra ni toca nada de la base de datos. Las entradas, páginas y CPTs siguen ahí; simplemente no hay plantilla que los muestre.
- **Versión**: subir `Version:` en `style.css` es lo que dispara el Release y, con eso, la actualización automática en producción.
