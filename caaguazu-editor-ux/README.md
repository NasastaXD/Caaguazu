# Caaguazú Editor UX

Plugin de WordPress que **no reemplaza Gutenberg**: lo mantiene como motor de
edición y reordena la experiencia alrededor para que editar contenido cívico
en caaguazu.net se sienta como un editor editorial simple, no como un
constructor de landings.

No es Classic Editor. No es Elementor. No es un page builder. No es un CMS
aparte.

Cubre las Entradas nativas (`post` — Noticias, Agenda y Educación, que ya
viven ahí como Categoría, ver `caaguazu-modulos/README.md`) y, desde la 1.1
(V5, civic CMS), los CPTs cívicos nuevos de `caaguazu-modulos`:
**Instituciones, Lugares, Servicios y Proyectos**. Mismo criterio en los
cinco: contenido escrito por una persona, no una landing de layout libre —
por eso comparten el mismo set de bloques permitidos y el mismo panel. La
lista completa vive en la constante `CZU_POST_TYPES`
(`caaguazu-editor-ux.php`); sumar un post type nuevo es agregarlo ahí.

## Qué hace

1. **Restringe los bloques disponibles** en el contenido cubierto (ver
   `CZU_POST_TYPES`) a un set editorial (ver lista abajo) vía
   `allowed_block_types_all` — sin tocar otros post types del sitio (p. ej.
   `caaguazu_artisan`).
2. **Ordena el editor**: saca el metabox nativo "Campos personalizados",
   apaga los patrones de bloque (propios y del directorio remoto de
   WordPress.org), oculta los paneles de Comentarios, Etiquetas y Formato de
   entrada (no se usan en el flujo editorial de este contenido), y desactiva
   la guía de bienvenida.
3. **Agrega el panel "caaguazu.net"** en la barra lateral del documento con
   un checklist editorial, tres campos de meta opcionales (Fuente,
   Responsable, Estado de verificación), y el acceso a la vista previa.
4. **Vista previa mejorada**: un botón "Vista previa caaguazu.net" (arriba,
   junto al resumen del documento, y dentro del panel propio) abre un modal
   con vista Escritorio/Celular y "Abrir en pestaña nueva", usando el mismo
   mecanismo de autosave que el botón nativo de WordPress (nunca muestra
   contenido desactualizado).
5. **Estilos de editor** que aproximan el canvas a la tipografía/paleta del
   frontend (`caaguazu-theme/assets/css/main.css`): blanco cálido, verde
   cívico, tinta, bordes suaves, Playfair Display en títulos y Lato en
   cuerpo.
6. **Template inicial** para contenido nuevo (resumen + descripción, sin
   `template_lock`): guía, no jaula.

Todo vía hooks/filtros públicos de WordPress — no se modifica ningún archivo
del core, del theme ni de `caaguazu-modulos`.

## Instalación

1. Copiar la carpeta `caaguazu-editor-ux/` a `wp-content/plugins/`.
2. Activar **Caaguazú Editor UX** en Plugins.
3. No requiere configuración: aplica automáticamente a todo lo listado en
   `CZU_POST_TYPES` (Entradas + los 4 CPTs cívicos de `caaguazu-modulos`, si
   ese plugin está activo). Si `caaguazu-modulos` no está activo, esos CPTs
   no existen y sus entradas en `CZU_POST_TYPES` simplemente no aplican a
   nada — no rompe nada tenerlas ahí igual.

## Bloques permitidos

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
de mostrar contenido viejo. Funciona igual para cualquier post type de
`CZU_POST_TYPES`: no depende de nada específico de Entradas.

## Meta fields agregados

Los cuatro registrados en cada post type de `CZU_POST_TYPES`, con
`show_in_rest`, `auth_callback` limitado a `current_user_can('edit_posts')`,
y sanitización propia:

| Meta key                      | Campo en el panel            | Sanitización |
|--------------------------------|-------------------------------|--------------|
| `_czu_fuente_referencia`       | "Fuente / referencia"         | `sanitize_text_field` |
| `_czu_responsable_contenido`   | "Responsable del contenido"   | `sanitize_text_field` |
| `_czu_estado_verificacion`     | "Estado de verificación" (select: Pendiente/Revisado/Verificado/Desactualizado) | whitelist propia — cualquier otro valor cae al vacío |
| `_czu_checklist_state`         | Checklist editorial (6 checkboxes) | sólo dígitos y comas (`^[0-9]+(,[0-9]+)*$`) — cualquier otra cosa cae al vacío |

Los tres primeros opcionales y editoriales (se muestran en el frontend). El
cuarto (`_czu_checklist_state`) es interno: guarda qué casillas del
checklist quedaron marcadas (índices posicionales separados por coma, p.
ej. `"0,2,4"`) para que no se reinicie cada vez que se recarga el editor —
no se muestra en el frontend, no bloquea publicar, es sólo memoria del
checklist.

"Actualizado el" **no** es un meta propio: se lee directo de
`post_modified` (WordPress ya lo mantiene solo) — ver
`caaguazu_render_trust_meta()` en `caaguazu-theme/inc/helpers.php`, que
combina Fuente/Responsable/Estado con esa fecha nativa para mostrarlos en
el frontend de Instituciones/Lugares/Servicios/Proyectos (y, si se suma
ahí, también en Noticias/Agenda/Educación).

## Limitaciones conocidas

- Sólo cubre lo listado en `CZU_POST_TYPES`. Otros post types del sitio
  (p. ej. Artesanos) siguen con el editor de bloques estándar de WordPress.
- El checklist persiste (ver tabla arriba) pero no bloquea la publicación —
  sigue siendo guía, no validación; se puede publicar con casillas sin
  marcar a propósito.
- La aproximación visual del canvas no es pixel-perfect al frontend (evita
  fragilidad ante cambios de WordPress/tema).
- Sin build step: los archivos JS son planos contra los globals `wp.*`
  (mismo enfoque que el resto del repo, que tampoco usa webpack).

## Próximos pasos posibles

- Vista previa con selector de ancho custom (tablet) si Escritorio/Celular
  no alcanza.
