# Textos del panel

Todo lo que un usuario lee en el panel, sacado de las fuentes el 2026-08-27. **470 textos.**

Se regenera con `php tools/textos-del-panel.php > docs/textos-del-panel.md`.

- Escribí el reemplazo en la columna **Nuevo texto**; lo que quede en blanco se deja como está.
- Los marcados con ⚠️ llevan un hueco (`%s`, `%d`, `%1$s`) que el código rellena: hay que conservarlo tal cual y en el mismo orden.
- Los `[FALTA: …]` son huecos a propósito: textos que el diseño pide y que todavía no escribió nadie.
- Los marcados con 🔡 arrancan en minúscula.

## Empiezan en minúscula

Casi todas son fragmentos escritos para leerse **después de un número** ("4 esperan revisión") o para ir dentro de una frase. Si se quieren usar como título, hay que reescribirlas enteras, no sólo poner la mayúscula.

| # | Texto | Dónde |
| --- | --- | --- |
| 46 | `atrás` | Notificaciones |
| 68 | `esperan revisión` | Inicio |
| 69 | `publicados` | Inicio |
| 70 | `esperan tu corrección` | Inicio |
| 71 | `en proceso` | Inicio |
| 170 | `revisa %s` | Cola de revisión |
| 186 | `vence %s` | Tareas |
| 212 | `fichas publicadas sin portada` | Reportes |
| 213 | `fichas sin verificar hace +6 meses` | Reportes |
| 236 | `fichas publicadas` | Mi perfil |
| 423 | `nunca` | Pantallas de wp-admin |

## El armazón

Lo que se ve en todas las pantallas.

### Menú lateral (rótulos y secciones)

`includes/helpers.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 1 | `Administrador` | 175 | |
| 2 | `Invitado` | 177 | |
| 3 | `GESTIÓN` | 301 | |
| 4 | `Inicio` | 303 | |
| 5 | `Mis contenidos` | 306 | |
| 6 | `Nueva ficha` | 310 | |
| 7 | `Salida de campo` | 311 | |
| 8 | `Cola de revisión` | 314 | |
| 9 | `Tareas` | 315 | |
| 10 | `PORTAL` | 319 | |
| 11 | `Equipo` | 321 | |
| 12 | `Reportes` | 322 | |
| 13 | `Biblioteca` | 323 | |
| 14 | `Estructura` | 324 | |
| 15 | `App` | 332 | |
| 16 | `Mi perfil` | 352 | |
| 17 | `Ayuda` | 353 | |

### Menú lateral (pie)

`templates/partials/sidebar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 18 | `Abrir menú` | 47 | |
| 19 | `Buscar…` | 77 | |
| 20 | `Buscar` | 78 | |
| 21 | `Navegación del panel` | 82 | |
| 22 | `Instalar app` | 111 | |
| 23 | `Cerrar sesión` | 115 | |

### Barra superior

`templates/partials/topbar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 24 | `Abrir menú` | 21 | |
| 25 | `Navegación del panel` | 25 | |
| 26 | `Inicio` | 30 | |
| 27 | `Buscar…` | 39 | |
| 28 | `Buscar` | 39 | |
| 29 | `Cambiar tema` | 44 | |
| 30 | `Notificaciones` | 50 | |
| 31 | `Marcar todo como leído` | 63 | |
| 32 | `No hay novedades por ahora. ✨` | 69 | |

### Barra inferior (teléfono)

`templates/partials/bottomnav.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 33 | `Inicio` | 10 | |
| 34 | `Contenidos` | 11 | |
| 35 | `Campo` | 12 | |
| 36 | `Revisar` | 13 | |
| 37 | `Perfil` | 14 | |
| 38 | `Navegación rápida` | 20 | |

### Mensajes del JavaScript

`includes/class-assets.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 39 | `Instalar app` | 70 | |
| 40 | `Enviando…` | 71 | |
| 41 | `Algo salió mal. Probá de nuevo.` | 72 | |
| 42 | `Guardado` | 73 | |
| 43 | `¿Querés confirmar esta acción?` | 74 | |
| 44 | `Faltan algunos datos obligatorios.` | 75 | |

### Notificaciones

`includes/class-notifications.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 45 | `«%s» está esperando revisión` ⚠️ | 48 | |
| 46 | `atrás` 🔡 | 50 | |
| 47 | `«%s» necesita algunos cambios` ⚠️ | 72 | |
| 48 | `No tenés autorización para hacer esto.` | 129 | |
| 49 | `Notificaciones marcadas como leídas.` | 137 | |

### Estados del flujo editorial

`includes/class-editorial.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 50 | `Borrador` | 30 | |
| 51 | `Enviado` | 31 | |
| 52 | `En revisión` | 32 | |
| 53 | `Necesita cambios` | 33 | |
| 54 | `Aprobado` | 34 | |
| 55 | `Publicado` | 35 | |
| 56 | `Despublicado` | 36 | |
| 57 | `Archivado` | 37 | |
| 58 | `Nombre del destino` | 86 | |
| 59 | `Descripción` | 93 | |
| 60 | `Faltan fuentes o referencias.` | 123 | |
| 61 | `Mejorá las fotos: cuidá la luz, el encuadre y la portada.` | 124 | |
| 62 | `Verificá los horarios y los costos.` | 125 | |
| 63 | `Revisá la ortografía y la redacción.` | 126 | |
| 64 | `Precisá cómo llegar.` | 127 | |

### Nombres de los roles

`includes/class-roles.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 65 | `Promotor` | 63 | |
| 66 | `Mini Promotor` | 64 | |
| 67 | `Visitante` | 65 | |

## Las secciones

Una tabla por pantalla del panel.

### Inicio

`templates/sections/home.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 68 | `esperan revisión` 🔡 | 34 | |
| 69 | `publicados` 🔡 | 35 | |
| 70 | `esperan tu corrección` 🔡 | 38 | |
| 71 | `en proceso` 🔡 | 39 | |
| 72 | `Inicio` | 51 | |
| 73 | `Tu actividad de hoy` | 56 | |
| 74 | `Hola, %s 👋` ⚠️ | 59 | |
| 75 | `Actividad reciente` | 81 | |
| 76 | `Actividad de los últimos %d día` ⚠️ | 86 | |
| 77 | `Actividad de los últimos %d días` ⚠️ | 86 | |
| 78 | `Accesos rápidos` | 101 | |
| 79 | `Crear una ficha` | 106 | |
| 80 | `Mis contenidos` | 109 | |
| 81 | `Cola de revisión` | 112 | |
| 82 | `Equipo` | 115 | |
| 83 | `Mi perfil` | 117 | |

### Mis contenidos

`templates/sections/mis-contenidos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 84 | `Mis contenidos` | 17 | |
| 85 | `Tu producción` | 22 | |
| 86 | `+ Nueva ficha` | 25 | |
| 87 | `Todavía no creaste ninguna ficha. Empezá con una nueva.` | 30 | |
| 88 | `Crear mi primera ficha` | 31 | |
| 89 | `(sin título)` | 40 | |

### Editor de ficha

`templates/sections/editor.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 90 | `No podés editar esta ficha.` | 19 | |
| 91 | `Editar ficha` | 31 | |
| 92 | `Nueva ficha` | 31 | |
| 93 | `Ficha del destino` | 39 | |
| 94 | `Comentarios del revisor` | 47 | |
| 95 | `Nombre del destino` | 63 | |
| 96 | `Descripción` | 68 | |
| 97 | `—` | 90 | |
| 98 | `Subir foto` | 107 | |
| 99 | `📍 Usar mi ubicación actual` | 117 | |
| 100 | `Guardar borrador` | 124 | |
| 101 | `Enviar a revisión` | 125 | |
| 102 | `Checklist de mínimos` | 132 | |
| 103 | `Completá estos puntos antes de enviar la ficha a revisión.` | 133 | |

### Campos de la ficha

`includes/class-destinos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 104 | `Destinos` | 63 | |
| 105 | `Destino` | 64 | |
| 106 | `Nuevo destino` | 65 | |
| 107 | `Editar destino` | 66 | |
| 108 | `Buscar destinos` | 67 | |
| 109 | `Categorías` | 112 | |
| 110 | `Categoría` | 112 | |
| 111 | `Zonas` | 115 | |
| 112 | `Zona` | 115 | |
| 113 | `Etiquetas` | 119 | |
| 114 | `Etiqueta` | 119 | |
| 115 | `Identidad` | 132 | |
| 116 | `Gancho (una línea)` | 134 | |
| 117 | `Foto de portada` | 135 | |
| 118 | `Crédito de las fotos` | 136 | |
| 119 | `Video (URL, opcional)` | 137 | |
| 120 | `Ubicación y acceso` | 141 | |
| 121 | `Latitud (pin)` | 143 | |
| 122 | `Longitud (pin)` | 144 | |
| 123 | `Referencia («a 3 km de…»)` | 145 | |
| 124 | `Cómo llegar (auto / colectivo / a pie)` | 146 | |
| 125 | `Estado del camino` | 147 | |
| 126 | `Accesibilidad` | 148 | |
| 127 | `Datos prácticos` | 152 | |
| 128 | `Horario y mejor momento para visitar` | 154 | |
| 129 | `Temporada ideal / cuándo evitar` | 155 | |
| 130 | `Costo / entrada` | 156 | |
| 131 | `Rango de precio` | 164 | |
| 132 | `Sin especificar` | 168 | |
| 133 | `Gratis` | 169 | |
| 134 | `$ — Muy barato` | 170 | |
| 135 | `$$ — Barato` | 171 | |
| 136 | `$$$ — Intermedio` | 172 | |
| 137 | `$$$$ — Caro` | 173 | |
| 138 | `Servicios (baños, comida, sombra…)` | 176 | |
| 139 | `Duración sugerida` | 177 | |
| 140 | `Contacto del lugar` | 178 | |
| 141 | `Fuentes y referencias` | 182 | |
| 142 | `Fuentes / referencias` | 184 | |

### Salida de campo

`templates/sections/captura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 143 | `Salida de campo` | 8 | |
| 144 | `Captura en el lugar` | 11 | |
| 145 | `Sacá una foto, anotá lo importante y guardá la ubicación, incluso si no tenés señal. Todo queda guardado en tu dispositivo y podés sincronizarlo como borrador cuando vuelva la conexión.` | 13 | |
| 146 | `Nombre del lugar` | 17 | |
| 147 | `Nota rápida` | 18 | |
| 148 | `Foto` | 21 | |
| 149 | `Ubicación (GPS)` | 25 | |
| 150 | `Tomar ubicación` | 27 | |
| 151 | `Guardar captura` | 33 | |
| 152 | `Capturas pendientes` | 39 | |
| 153 | `Sincronizar` | 40 | |

### Cola de revisión

`templates/sections/revision.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 154 | `No encontramos esta ficha.` | 13 | |
| 155 | `Revisión` | 20 | |
| 156 | `Volver a la cola` | 25 | |
| 157 | `Por %s` ⚠️ | 30 | |
| 158 | `Descripción` | 43 | |
| 159 | `Acciones` | 61 | |
| 160 | `Asignarme la revisión` | 64 | |
| 161 | `Comentarios para el autor` | 68 | |
| 162 | `Qué corregir o mejorar…` | 69 | |
| 163 | `Devolver con cambios` | 78 | |
| 164 | `Aprobar y publicar` | 80 | |
| 165 | `Historial` | 88 | |
| 166 | `Cola de revisión` | 116 | |
| 167 | `Taller editorial` | 119 | |
| 168 | `No hay fichas esperando revisión. 🎉` | 123 | |
| 169 | `%1$s · esperó %2$s` ⚠️ | 138 | |
| 170 | `revisa %s` ⚠️ 🔡 | 143 | |

### Tareas

`templates/sections/tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 171 | `Tareas` | 11 | |
| 172 | `Asignaciones` | 14 | |
| 173 | `Tareas y pendientes por cubrir` | 15 | |
| 174 | `+ Nueva tarea o hueco` | 19 | |
| 175 | `Título` | 21 | |
| 176 | `Detalle` | 22 | |
| 177 | `Tipo` | 24 | |
| 178 | `Tarea asignada` | 26 | |
| 179 | `Hueco disponible` | 27 | |
| 180 | `Vence` | 30 | |
| 181 | `Destino (opcional)` | 31 | |
| 182 | `Asignar a (Mini Promotores)` | 37 | |
| 183 | `Crear` | 42 | |
| 184 | `No hay tareas por ahora.` | 49 | |
| 185 | `Hueco` | 61 | |
| 186 | `vence %s` ⚠️ 🔡 | 63 | |
| 187 | `Reclamar` | 69 | |
| 188 | `Marcar como completada` | 72 | |

### Tareas (estados y avisos)

`includes/class-tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 189 | `Tareas` | 27 | |
| 190 | `Tarea` | 27 | |
| 191 | `Pendiente` | 38 | |
| 192 | `En curso` | 39 | |
| 193 | `Completada` | 40 | |
| 194 | `La tarea necesita un título.` | 58 | |

### Equipo

`templates/sections/equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 195 | `Equipo` | 8 | |
| 196 | `Tu equipo` | 11 | |
| 197 | `Invitar a alguien` | 15 | |
| 198 | `Generá un enlace de invitación con el rol que quieras. El enlace es válido durante 14 días.` | 16 | |
| 199 | `Mini Promotor` | 21 | |
| 200 | `Promotor` | 22 | |
| 201 | `Visitante` | 23 | |
| 202 | `Crear enlace` | 25 | |
| 203 | `%1$d publicadas · %2$d en total` ⚠️ | 46 | |
| 204 | `Nivel de confianza:` | 56 | |
| 205 | `Guardar` | 63 | |

### Reportes

`templates/sections/reportes.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 206 | `Reportes` | 12 | |
| 207 | `Métricas` | 15 | |
| 208 | `Actividad del portal` | 16 | |
| 209 | `Producción por autor` | 18 | |
| 210 | `%1$d publicadas / %2$d` ⚠️ | 27 | |
| 211 | `Estado del contenido` | 33 | |
| 212 | `fichas publicadas sin portada` 🔡 | 36 | |
| 213 | `fichas sin verificar hace +6 meses` 🔡 | 46 | |

### Biblioteca

`templates/sections/biblioteca.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 214 | `Biblioteca` | 4 | |
| 215 | `Medios` | 7 | |
| 216 | `Biblioteca de medios` | 8 | |
| 217 | `Tu galería de fotos, con créditos y atribución. Por ahora, las imágenes se suben directamente desde el editor de cada ficha.` | 10 | |
| 218 | `Abrir biblioteca de WordPress` | 11 | |

### Estructura

`templates/sections/estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 219 | `Estructura` | 4 | |
| 220 | `Organización` | 8 | |
| 221 | `Estructura del sitio` | 9 | |
| 222 | `Acá se organizan las categorías, zonas y etiquetas de los destinos. La edición completa llegará en la próxima fase; por ahora, gestioná estos elementos desde WordPress.` | 11 | |
| 223 | `Categorías` | 13 | |
| 224 | `Zonas` | 14 | |
| 225 | `Etiquetas` | 15 | |

### Buscar

`templates/sections/buscar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 226 | `Buscar` | 18 | |
| 227 | `Escribí algo en el buscador de arriba para encontrar fichas.` | 23 | |
| 228 | `%1$d resultado para «%2$s»` ⚠️ | 28 | |
| 229 | `%1$d resultados para «%2$s»` ⚠️ | 28 | |
| 230 | `No encontramos resultados. Probá con otras palabras.` | 32 | |

### Mi perfil

`templates/sections/perfil.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 231 | `Mi perfil` | 20 | |
| 232 | `Tu progreso de confianza` | 36 | |
| 233 | `Nivel máximo: publicás directamente y después se hace una auditoría. Gracias por tu compromiso.` | 50 | |
| 234 | `Promotor Jr: podés editar fichas publicadas sin pasar por una nueva revisión. Seguí sumando aprobaciones para llegar a «De confianza».` | 52 | |
| 235 | `Aprendiz: todo tu contenido pasa por revisión. A medida que sumás aprobaciones, vas ganando autonomía.` | 54 | |
| 236 | `fichas publicadas` 🔡 | 64 | |
| 237 | `Mi portafolio` | 68 | |
| 238 | `Todavía no tenés fichas publicadas.` | 70 | |
| 239 | `Publicado` | 78 | |
| 240 | `Editar mi perfil en WordPress →` | 86 | |

### Niveles de confianza

`includes/class-stats.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 241 | `Aprendiz` | 34 | |
| 242 | `Promotor Jr` | 35 | |
| 243 | `De confianza` | 36 | |

### App (control de la app móvil)

`templates/sections/app.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 244 | `App` | 21 | |
| 245 | `Aplicación` | 27 | |
| 246 | `Textos` | 36 | |
| 247 | `Idioma` | 37 | |
| 248 | `Clave` | 64 | |
| 249 | `Texto` | 68 | |
| 250 | `Guardar cambios` | 75 | |
| 251 | `Medios` | 84 | |
| 252 | `Abrir biblioteca de WordPress` | 87 | |
| 253 | `Tipo` | 115 | |
| 254 | `Imagen` | 117 | |
| 255 | `Animación` | 118 | |
| 256 | `URL o ID` | 122 | |
| 257 | `Texto alternativo` | 126 | |
| 258 | `Formato` | 130 | |
| 259 | `Categorías` | 147 | |
| 260 | `Todavía no hay categorías cargadas. Se crean en Estructura y después se les elige acá el icono y el color.` | 151 | |
| 261 | `Estructura` | 152 | |
| 262 | `Nombre` | 163 | |
| 263 | `Color` | 167 | |
| 264 | `Icono` | 172 | |

### App (avisos)

`includes/class-app-control.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 265 | `No tenés autorización para hacer esto.` | 134 | |
| 266 | `Guardado` | 195 | |

### Ayuda

`templates/sections/ayuda.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 267 | `Ayuda` | 5 | |
| 268 | `Inicio` | 9 | |
| 269 | `Tu resumen del día: fichas que esperan revisión, contenido que necesita correcciones y accesos rápidos según tu rol.` | 9 | |
| 270 | `Nueva ficha` | 10 | |
| 271 | `El editor guiado para crear destinos. Completá los campos y el checklist; el sistema te avisa si falta algo antes de enviar la ficha a revisión.` | 10 | |
| 272 | `Salida de campo` | 11 | |
| 273 | `Sacá fotos, anotá información y guardá la ubicación GPS mientras estás en el lugar, incluso sin señal. Después podés sincronizar todo como borrador cuando vuelva la conexión.` | 11 | |
| 274 | `Mis contenidos` | 12 | |
| 275 | `Todas tus fichas, ordenadas por estado: borrador, enviada, en revisión, necesita cambios o publicada.` | 12 | |
| 276 | `Cola de revisión` | 13 | |
| 277 | `Para Promotores: revisá las fichas enviadas, asignate una, aprobala y publicala o devolvela con comentarios para que el autor haga los cambios necesarios.` | 13 | |
| 278 | `Tareas` | 14 | |
| 279 | `Asignaciones con fecha límite y una lista de lo que todavía falta cubrir. Los Mini Promotores pueden reclamar los huecos disponibles.` | 14 | |
| 280 | `Curaduría` | 15 | |
| 281 | `Elegí qué destinos aparecen destacados en la portada y configurá un banner de temporada. Los cambios se reflejan en la web pública sin tocar el código.` | 15 | |
| 282 | `Moderación` | 16 | |
| 283 | `Aprobá o descartá reseñas, respondé o derivá consultas de visitantes y atendé los reportes de información desactualizada.` | 16 | |
| 284 | `Equipo` | 17 | |
| 285 | `Gestioná a los Mini Promotores: revisá su producción, nivel de confianza y enlaces de invitación.` | 17 | |
| 286 | `Reportes` | 18 | |
| 287 | `Consultá la producción por autor, los destinos más vistos, las búsquedas sin resultado y el estado general del contenido.` | 18 | |
| 288 | `Mi perfil` | 19 | |
| 289 | `Consultá tu portafolio público, las vistas de tus fichas y tu progreso de nivel de confianza.` | 19 | |
| 290 | `Cómo funciona` | 22 | |
| 291 | `¿Qué hace cada sección?` | 23 | |
| 292 | `Este es el portal de los Promotores Turísticos: una web turística pública con un espacio de trabajo editorial detrás. Los Mini Promotores crean las fichas de destino y los Promotores las revisan y publican.` | 25 | |
| 293 | `El flujo editorial` | 28 | |
| 294 | `Borrador` | 31 | |
| 295 | `Enviado` | 32 | |
| 296 | `En revisión` | 33 | |
| 297 | `Necesita cambios` | 34 | |
| 298 | `Publicado` | 35 | |
| 299 | `Solo las fichas aprobadas por un Promotor llegan al público. La confianza se construye con cada aprobación: pasás de Aprendiz a Promotor Jr y luego a De confianza. Cada nivel te da más autonomía, como editar fichas publicadas sin una nueva revisión y, finalmente, publicar directamente.` | 38 | |
| 300 | `Las secciones` | 42 | |
| 301 | `Extras` | 56 | |
| 302 | `Podés instalar el portal como app (PWA) y consultar parte del contenido sin conexión desde el menú lateral.` | 58 | |
| 303 | `Cada ficha pública puede tener reseñas, indicaciones para llegar, un código QR para imprimir y un botón para agregarla a «Mi viaje».` | 59 | |
| 304 | `Podés cambiar entre modo claro y oscuro y elegir el idioma (ES/EN/GN) desde la barra superior.` | 60 | |
| 305 | `El acceso es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 61 | |

### Sección inexistente

`templates/sections/404.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 306 | `No encontramos esa sección` | 7 | |
| 307 | `Error 404` | 11 | |
| 308 | `Esta sección no existe` | 12 | |
| 309 | `Puede que el enlace esté roto o que no tengas permiso para acceder.` | 13 | |
| 310 | `Volver al inicio del panel` | 14 | |

## Entrar y salir

Acceso, registro, recuperación, invitaciones y errores de permiso.

### Iniciar sesión

`templates/auth/login.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 311 | `Iniciar sesión` | 12 | |
| 312 | `Entrá al panel de Promotores Turísticos.` | 16 | |
| 313 | `Tu contraseña se actualizó. Ya podés iniciar sesión.` | 19 | |
| 314 | `Email` | 34 | |
| 315 | `Contraseña` | 38 | |
| 316 | `Mantener la sesión iniciada` | 42 | |
| 317 | `Entrar` | 45 | |
| 318 | `¿Olvidaste tu contraseña?` | 49 | |
| 319 | `Acceso solo por invitación` | 50 | |

### Registro

`templates/auth/registro.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 320 | `Crear cuenta` | 14 | |
| 321 | `Esta invitación ya fue usada.` | 27 | |
| 322 | `Esta invitación venció. Pedí una nueva al equipo.` | 28 | |
| 323 | `Esta invitación fue revocada.` | 29 | |
| 324 | `El registro es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 30 | |
| 325 | `Ya tengo una cuenta` | 35 | |
| 326 | `Invitación válida: te unirás como %s.` ⚠️ | 44 | |
| 327 | `Nombre de usuario` | 55 | |
| 328 | `Email` | 59 | |
| 329 | `Teléfono` | 63 | |
| 330 | `Ej.: 0981 123 456` | 64 | |
| 331 | `Contraseña (6 o más caracteres)` | 67 | |

### Recuperar contraseña

`templates/auth/recuperar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 332 | `Recuperar contraseña` | 10 | |
| 333 | `Te enviamos un enlace para restablecer tu contraseña.` | 14 | |
| 334 | `Email` | 27 | |
| 335 | `Enviar enlace` | 30 | |
| 336 | `Volver a iniciar sesión` | 34 | |

### Contraseña nueva

`templates/auth/restablecer.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 337 | `Nueva contraseña` | 12 | |
| 338 | `Nueva contraseña (6 o más caracteres)` | 28 | |
| 339 | `Guardar contraseña` | 31 | |
| 340 | `El enlace no es válido o ya venció. Pedí uno nuevo.` | 34 | |
| 341 | `Pedir un nuevo enlace` | 36 | |

### Marco de acceso

`templates/auth-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 342 | `Acceso` | 8 | |

### Errores y avisos de acceso

`includes/class-auth.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 343 | `No tenés autorización para hacer esto.` | 43 | |
| 344 | `Enlace de invitación creado. Es válido durante 14 días: %s` ⚠️ | 49 | |
| 345 | `Tu sesión venció. Recargá la página.` | 136 | |
| 346 | `Necesitás una invitación válida para registrarte.` | 175 | |
| 347 | `Completá usuario, email, teléfono y una contraseña de al menos 6 caracteres.` | 185 | |
| 348 | `Ese email ya está registrado.` | 189 | |
| 349 | `Si la cuenta existe, te enviamos un email con las instrucciones.` | 234 | |
| 350 | `El enlace para restablecer la contraseña venció o no es válido.` | 252 | |

### Invitaciones

`includes/class-invitations.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 351 | `Válida` | 99 | |
| 352 | `Usada` | 100 | |
| 353 | `Expirada` | 101 | |
| 354 | `Revocada` | 102 | |
| 355 | `Inválida` | 103 | |

### Guardas de acceso

`includes/class-router.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 356 | `No tenés acceso a este panel.` | 188 | |
| 357 | `Acceso denegado` | 189 | |

### Guardas de sección

`includes/class-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 358 | `No tenés permiso para ver esta sección.` | 42 | |
| 359 | `Acceso denegado` | 43 | |

### Sin conexión (PWA)

`includes/class-pwa.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 360 | `Sin conexión` | 169 | |
| 361 | `Promotores Turísticos` | 176 | |
| 362 | `Estás sin conexión` | 177 | |
| 363 | `No pudimos cargar esta pantalla. Revisá tu conexión e intentá de nuevo.` | 178 | |
| 364 | `Reintentar` | 179 | |

## wp-admin y mensajes de sistema

Pantallas de administración y respuestas de las acciones.

### Pantallas de wp-admin

`includes/class-admin.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 365 | `Portal Turismo` | 38 | |
| 366 | `Usuarios` | 41 | |
| 367 | `Invitaciones` | 42 | |
| 368 | `Registros` | 43 | |
| 369 | `Actualizaciones` | 45 | |
| 370 | `No tenés autorización para hacer esto.` | 60 | |
| 371 | `Usuarios del portal` | 73 | |
| 372 | `Editar: %s` ⚠️ | 77 | |
| 373 | `Nombre` | 84 | |
| 374 | `Email` | 85 | |
| 375 | `Teléfono` | 86 | |
| 376 | `Rol` | 87 | |
| 377 | `Restablecer contraseña` | 91 | |
| 378 | `Generar una nueva y mostrarla` | 91 | |
| 379 | `Guardar cambios` | 93 | |
| 380 | `Cancelar` | 94 | |
| 381 | `Usuario` | 101 | |
| 382 | `Estado` | 103 | |
| 383 | `Suspendido` | 116 | |
| 384 | `Activo` | 116 | |
| 385 | `Editar` | 118 | |
| 386 | `Reactivar` | 119 | |
| 387 | `Suspender` | 119 | |
| 388 | `Eliminar` | 120 | |
| 389 | `¿Seguro? Esta acción no se puede deshacer.` | 132 | |
| 390 | `El usuario no es válido.` | 151 | |
| 391 | `No podés modificar a un administrador ni tu propia cuenta desde acá.` | 155 | |
| 392 | `Usuario actualizado.` | 172 | |
| 393 | `Nueva contraseña: %s` ⚠️ | 176 | |
| 394 | `Usuario suspendido. Su sesión fue cerrada.` | 187 | |
| 395 | `Usuario reactivado.` | 193 | |
| 396 | `Usuario eliminado. Su contenido se reasignó a tu cuenta.` | 200 | |
| 397 | `Crear invitación` | 220 | |
| 398 | `Email (opcional)` | 229 | |
| 399 | `Expira (días)` | 230 | |
| 400 | `Cantidad` | 231 | |
| 401 | `Generar enlace(s)` | 233 | |
| 402 | `Invitaciones recientes` | 236 | |
| 403 | `Expira` | 240 | |
| 404 | `Enlace` | 241 | |
| 405 | `Revocar` | 262 | |
| 406 | `Invitación(es) creada(s):` | 287 | |
| 407 | `Invitación revocada.` | 290 | |
| 408 | `Entradas` | 313 | |
| 409 | `Fecha` | 317 | |
| 410 | `Acción` | 318 | |
| 411 | `Elemento` | 318 | |
| 412 | `IP` | 319 | |
| 413 | `Detalle` | 319 | |
| 414 | `No hay registros.` | 323 | |
| 415 | `Actualizaciones del portal` | 380 | |
| 416 | `No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.` | 384 | |
| 417 | `Atención: la versión del encabezado del plugin (%1$s) no coincide con PROMOTUR_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.` ⚠️ | 391 | |
| 418 | `Versión instalada` | 400 | |
| 419 | `Última disponible` | 401 | |
| 420 | `Actualizar ahora` | 410 | |
| 421 | `Estás al día.` | 412 | |
| 422 | `Última comprobación` | 415 | |
| 423 | `nunca` 🔡 | 416 | |
| 424 | `Repositorio` | 418 | |
| 425 | `Buscar actualizaciones ahora` | 427 | |
| 426 | `Limpiar caché del actualizador` | 433 | |
| 427 | `Token de GitHub` | 437 | |
| 428 | `Definido en wp-config.php mediante PROMOTUR_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.` | 439 | |
| 429 | `El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.` | 441 | |
| 430 | `Token` | 447 | |
| 431 | `•••• guardado (dejá vacío para conservarlo)` | 448 | |
| 432 | `Eliminar el token guardado` | 450 | |
| 433 | `Guardar token` | 454 | |
| 434 | `Hay una nueva versión disponible: %s.` ⚠️ | 473 | |
| 435 | `No hay actualizaciones: ya tenés la última versión.` | 475 | |
| 436 | `El verificador de actualizaciones no está disponible.` | 478 | |
| 437 | `Caché del actualizador limpiada.` | 487 | |
| 438 | `El token está definido en wp-config.php y no se puede cambiar desde acá.` | 492 | |
| 439 | `Token eliminado.` | 499 | |
| 440 | `Token guardado.` | 502 | |
| 441 | `No hubo cambios en el token.` | 504 | |

### Respuestas del editor

`includes/class-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 442 | `Tu sesión venció. Recargá la página.` | 38 | |
| 443 | `Necesitás iniciar sesión.` | 41 | |
| 444 | `No tenés permiso para hacer esto.` | 44 | |
| 445 | `No podés editar esta ficha.` | 83 | |
| 446 | `(sin título)` | 93 | |
| 447 | `Borrador guardado.` | 127 | |
| 448 | `Guardado. Como editaste una ficha publicada, tendrá que pasar por una nueva revisión.` | 135 | |
| 449 | `La ficha no es válida.` | 152 | |
| 450 | `Faltan datos obligatorios. Completá el checklist antes de enviarla.` | 156 | |
| 451 | `Publicación directa por nivel de confianza. Se hará una auditoría posterior.` | 163 | |
| 452 | `¡Publicado! Se aplicó tu nivel de confianza.` | 164 | |
| 453 | `¡Ficha enviada a revisión!` | 167 | |
| 454 | `Te asignaste la revisión.` | 178 | |
| 455 | `Ficha aprobada y publicada.` | 193 | |
| 456 | `Escribí los comentarios para el autor.` | 205 | |
| 457 | `Ficha devuelta al autor con comentarios.` | 209 | |
| 458 | `No recibimos ninguna imagen.` | 216 | |
| 459 | `Solo podés subir imágenes.` | 220 | |

### Respuestas de gestión

`includes/class-gestion-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 460 | `Tu sesión venció. Recargá la página.` | 28 | |
| 461 | `No tenés permiso para hacer esto.` | 31 | |
| 462 | `Tarea creada.` | 48 | |
| 463 | `La tarea no es válida.` | 55 | |
| 464 | `Reclamaste esta tarea. Ya podés trabajar en ella.` | 58 | |
| 465 | `Tarea completada. 🎉` | 72 | |
| 466 | `El usuario no es válido.` | 80 | |
| 467 | `Nivel actualizado.` | 83 | |

### Selector de idioma

`includes/class-i18n.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 468 | `Idioma` | 82 | |

### Avisos del plugin

`caaguazu-portal.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 469 | `Caaguazú Portal necesita tener activo el plugin «Caaguazú Cuentas» para funcionar. El inicio de sesión de los Promotores ya no usa los usuarios de WordPress. Activá el plugin desde Plugins para volver a usar el panel.` | 84 | |
| 470 | `Portal de Promotores` | 105 | |
