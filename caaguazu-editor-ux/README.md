# Caaguazú Editor UX

Plugin de WordPress que **no reemplaza Gutenberg**: lo mantiene como motor de
edición y reordena la experiencia alrededor para que editar una Entrada en
caaguazu.net se sienta como un editor cívico/editorial simple, no como un
constructor de landings.

No es Classic Editor. No es Elementor. No es un page builder. No es un CMS
aparte.

## Qué hace

1. **Restringe los bloques disponibles** en Entradas (`post`) a un set
   editorial (ver lista abajo) vía `allowed_block_types_all` — sin tocar
   otros post types del sitio (p. ej. `caaguazu_artisan`).
2. **Ordena el editor**: saca el metabox nativo "Campos personalizados",
   apaga los patrones de bloque (propios y del directorio remoto de
   WordPress.org), oculta los paneles de Comentarios, Etiquetas y Formato de
   entrada (no se usan en el flujo editorial de una nota), y desactiva la
   guía de bienvenida.
3. **Agrega el panel "caaguazu.net"** en la barra lateral del documento con
   un checklist editorial, dos campos de meta opcionales, y el acceso a la
   vista previa.
4. **Vista previa mejorada**: un botón "Vista previa caaguazu.net" (arriba,
   junto al resumen del documento, y dentro del panel propio) abre un modal
   con vista Escritorio/Celular y "Abrir en pestaña nueva", usando el mismo
   mecanismo de autosave que el botón nativo de WordPress (nunca muestra
   contenido desactualizado).
5. **Estilos de editor** que aproximan el canvas a la tipografía/paleta del
   frontend (`caaguazu-theme/assets/css/main.css`): blanco cálido, verde
   cívico, tinta, bordes suaves, Playfair Display en títulos y Lato en
   cuerpo.
6. **Template inicial** para Entradas nuevas (bajada + cuerpo, sin
   `template_lock`): guía, no jaula.

Todo vía hooks/filtros públicos de WordPress — no se modifica ningún archivo
del core, del theme ni de `caaguazu-modulos`.

## Instalación

1. Copiar la carpeta `caaguazu-editor-ux/` a `wp-content/plugins/`.
2. Activar **Caaguazú Editor UX** en Plugins.
3. No requiere configuración: aplica automáticamente a las Entradas
   (`post`) — incluye Noticias, Agenda y Educación, que en este sitio ya
   son Entradas nativas diferenciadas por Categoría (ver
   `caaguazu-modulos/includes/modules/`).

## Bloques permitidos en Entradas

- `core/paragraph`
- `core/heading`
- `core/image`
- `core/list` / `core/list-item`
- `core/quote`
- `core/separator`
- `core/embed`
- `core/video`
- `core/gallery`

Deliberadamente **no** están: `core/columns`, `core/group`, `core/cover`,
`core/buttons`, `core/spacer`, ni bloques de layout/experimentales.

## Vista previa: cómo funciona

Al abrir el modal, se dispara `wp.data.dispatch('core/editor').autosave()`
si hay cambios sin guardar (igual que el botón "Vista previa" nativo), y
luego se lee `getEditedPostPreviewLink()` — el mismo selector que usa el
core de WordPress. El iframe carga esa URL; "Abrir en pestaña nueva" usa el
mismo enlace. Si el post todavía no se pudo guardar (p. ej. nunca se
autosaveó), se muestra un mensaje en vez de un iframe roto — no hay riesgo
de mostrar contenido viejo.

## Meta fields agregados

Ambos con `show_in_rest`, `sanitize_callback: sanitize_text_field` y
`auth_callback` limitado a `current_user_can('edit_posts')`:

| Meta key                       | Campo en el panel          |
|---------------------------------|-----------------------------|
| `_czu_fuente_referencia`        | "Fuente / referencia"       |
| `_czu_responsable_contenido`    | "Responsable del contenido" |

Ambos opcionales, sin impacto en el frontend (el theme no los muestra
todavía — quedan disponibles para uso editorial interno o para una futura
plantilla que los consuma).

El checklist editorial del panel (título claro, resumen agregado, imagen de
portada, categoría, fuente verificada, contenido humano) es un recordatorio
visual y **no se guarda** como dato — se reinicia al recargar el editor a
propósito, para no sobre-construir el MVP con otro campo de post meta.

## Limitaciones conocidas

- Sólo cubre Entradas (`post`). Otros post types del sitio (p. ej.
  Artesanos) siguen con el editor de bloques estándar de WordPress.
- El checklist no persiste ni bloquea la publicación (es guía, no
  validación).
- La aproximación visual del canvas no es pixel-perfect al frontend (evita
  fragilidad ante cambios de WordPress/tema).
- Sin build step: los archivos JS son planos contra los globals `wp.*`
  (mismo enfoque que el resto del repo, que tampoco usa webpack).

## Próximos pasos posibles

- Sumar un checklist con estado persistido (post meta) si el equipo
  editorial lo pide explícitamente.
- Extender el panel/plantilla a otros post types cuando existan (eventos,
  instituciones, lugares) siguiendo el mismo patrón de esta clase.
- Vista previa con selector de ancho custom (tablet) si Escritorio/Celular
  no alcanza.
