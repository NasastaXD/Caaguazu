# Textos del panel

Todo lo que un usuario lee en el panel, sacado de las fuentes el 2026-08-26. **569 textos.**

Se regenera con `php tools/textos-del-panel.php > docs/textos-del-panel.md`.

- Escribí el reemplazo en la columna **Nuevo texto**; lo que quede en blanco se deja como está.
- Los marcados con ⚠️ llevan un hueco (`%s`, `%d`, `%1$s`) que el código rellena: hay que conservarlo tal cual y en el mismo orden.
- Los `[FALTA: …]` son huecos a propósito: textos que el diseño pide y que todavía no escribió nadie.
- Los marcados con 🔡 arrancan en minúscula.

## Empiezan en minúscula

Casi todas son fragmentos escritos para leerse **después de un número** ("4 esperan revisión") o para ir dentro de una frase. Si se quieren usar como título, hay que reescribirlas enteras, no sólo poner la mayúscula.

| # | Texto | Dónde |
| --- | --- | --- |
| 47 | `atrás` | Notificaciones |
| 69 | `esperan revisión` | Inicio |
| 70 | `publicados` | Inicio |
| 71 | `esperan tu corrección` | Inicio |
| 72 | `en proceso` | Inicio |
| 73 | `reseñas por moderar` | Inicio |
| 74 | `consultas sin responder` | Inicio |
| 173 | `revisa %s` | Cola de revisión |
| 189 | `vence %s` | Tareas |
| 282 | `fichas publicadas sin portada` | Reportes |
| 283 | `fichas sin verificar hace +6 meses` | Reportes |
| 306 | `fichas publicadas` | Mi perfil |
| 307 | `vistas en total` | Mi perfil |
| 522 | `nunca` | Pantallas de wp-admin |

## El armazón

Lo que se ve en todas las pantallas.

### Menú lateral (rótulos y secciones)

`includes/helpers.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 1 | `Administrador` | 175 | |
| 2 | `Invitado` | 177 | |
| 3 | `GESTIÓN` | 286 | |
| 4 | `Inicio` | 288 | |
| 5 | `Mis contenidos` | 291 | |
| 6 | `Nueva ficha` | 295 | |
| 7 | `Salida de campo` | 296 | |
| 8 | `Cola de revisión` | 299 | |
| 9 | `Tareas` | 300 | |
| 10 | `PORTAL` | 304 | |
| 11 | `Curaduría` | 306 | |
| 12 | `Moderación` | 307 | |
| 13 | `Equipo` | 308 | |
| 14 | `Reportes` | 309 | |
| 15 | `Biblioteca` | 310 | |
| 16 | `Estructura` | 311 | |
| 17 | `Mi perfil` | 330 | |
| 18 | `Ayuda` | 331 | |

### Menú lateral (pie)

`templates/partials/sidebar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 19 | `Abrir menú` | 47 | |
| 20 | `Buscar…` | 77 | |
| 21 | `Buscar` | 78 | |
| 22 | `Navegación del panel` | 82 | |
| 23 | `Instalar app` | 111 | |
| 24 | `Cerrar sesión` | 115 | |

### Barra superior

`templates/partials/topbar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 25 | `Abrir menú` | 21 | |
| 26 | `Navegación del panel` | 25 | |
| 27 | `Inicio` | 30 | |
| 28 | `Buscar…` | 39 | |
| 29 | `Buscar` | 39 | |
| 30 | `Cambiar tema` | 44 | |
| 31 | `Notificaciones` | 50 | |
| 32 | `Marcar todo como leído` | 63 | |
| 33 | `No hay novedades por ahora. ✨` | 69 | |

### Barra inferior (teléfono)

`templates/partials/bottomnav.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 34 | `Inicio` | 10 | |
| 35 | `Contenidos` | 11 | |
| 36 | `Campo` | 12 | |
| 37 | `Revisar` | 13 | |
| 38 | `Perfil` | 14 | |
| 39 | `Navegación rápida` | 20 | |

### Mensajes del JavaScript

`includes/class-assets.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 40 | `Instalar app` | 70 | |
| 41 | `Enviando…` | 71 | |
| 42 | `Algo salió mal. Probá de nuevo.` | 72 | |
| 43 | `Guardado` | 73 | |
| 44 | `¿Querés confirmar esta acción?` | 74 | |
| 45 | `Faltan algunos datos obligatorios.` | 75 | |

### Notificaciones

`includes/class-notifications.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 46 | `«%s» está esperando revisión` ⚠️ | 48 | |
| 47 | `atrás` 🔡 | 50 | |
| 48 | `«%s» necesita algunos cambios` ⚠️ | 72 | |
| 49 | `No tenés autorización para hacer esto.` | 129 | |
| 50 | `Notificaciones marcadas como leídas.` | 137 | |

### Estados del flujo editorial

`includes/class-editorial.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 51 | `Borrador` | 30 | |
| 52 | `Enviado` | 31 | |
| 53 | `En revisión` | 32 | |
| 54 | `Necesita cambios` | 33 | |
| 55 | `Aprobado` | 34 | |
| 56 | `Publicado` | 35 | |
| 57 | `Despublicado` | 36 | |
| 58 | `Archivado` | 37 | |
| 59 | `Nombre del destino` | 86 | |
| 60 | `Descripción` | 93 | |
| 61 | `Faltan fuentes o referencias.` | 123 | |
| 62 | `Mejorá las fotos: cuidá la luz, el encuadre y la portada.` | 124 | |
| 63 | `Verificá los horarios y los costos.` | 125 | |
| 64 | `Revisá la ortografía y la redacción.` | 126 | |
| 65 | `Precisá cómo llegar.` | 127 | |

### Nombres de los roles

`includes/class-roles.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 66 | `Promotor` | 64 | |
| 67 | `Mini Promotor` | 65 | |
| 68 | `Visitante` | 66 | |

## Las secciones

Una tabla por pantalla del panel.

### Inicio

`templates/sections/home.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 69 | `esperan revisión` 🔡 | 34 | |
| 70 | `publicados` 🔡 | 35 | |
| 71 | `esperan tu corrección` 🔡 | 38 | |
| 72 | `en proceso` 🔡 | 39 | |
| 73 | `reseñas por moderar` 🔡 | 42 | |
| 74 | `consultas sin responder` 🔡 | 43 | |
| 75 | `Inicio` | 55 | |
| 76 | `Tu actividad de hoy` | 60 | |
| 77 | `Hola, %s 👋` ⚠️ | 63 | |
| 78 | `Actividad reciente` | 85 | |
| 79 | `Actividad de los últimos %d día` ⚠️ | 90 | |
| 80 | `Actividad de los últimos %d días` ⚠️ | 90 | |
| 81 | `Accesos rápidos` | 105 | |
| 82 | `Crear una ficha` | 110 | |
| 83 | `Mis contenidos` | 113 | |
| 84 | `Cola de revisión` | 116 | |
| 85 | `Equipo` | 119 | |
| 86 | `Mi perfil` | 121 | |

### Mis contenidos

`templates/sections/mis-contenidos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 87 | `Mis contenidos` | 17 | |
| 88 | `Tu producción` | 22 | |
| 89 | `+ Nueva ficha` | 25 | |
| 90 | `Todavía no creaste ninguna ficha. Empezá con una nueva.` | 30 | |
| 91 | `Crear mi primera ficha` | 31 | |
| 92 | `(sin título)` | 40 | |

### Editor de ficha

`templates/sections/editor.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 93 | `No podés editar esta ficha.` | 19 | |
| 94 | `Editar ficha` | 31 | |
| 95 | `Nueva ficha` | 31 | |
| 96 | `Ficha del destino` | 39 | |
| 97 | `Comentarios del revisor` | 47 | |
| 98 | `Nombre del destino` | 63 | |
| 99 | `Descripción` | 68 | |
| 100 | `—` | 90 | |
| 101 | `Subir foto` | 107 | |
| 102 | `📍 Usar mi ubicación actual` | 117 | |
| 103 | `Guardar borrador` | 124 | |
| 104 | `Enviar a revisión` | 125 | |
| 105 | `Checklist de mínimos` | 132 | |
| 106 | `Completá estos puntos antes de enviar la ficha a revisión.` | 133 | |

### Campos de la ficha

`includes/class-destinos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 107 | `Destinos` | 64 | |
| 108 | `Destino` | 65 | |
| 109 | `Nuevo destino` | 66 | |
| 110 | `Editar destino` | 67 | |
| 111 | `Buscar destinos` | 68 | |
| 112 | `Categorías` | 88 | |
| 113 | `Categoría` | 88 | |
| 114 | `Zonas` | 92 | |
| 115 | `Zona` | 92 | |
| 116 | `Etiquetas` | 97 | |
| 117 | `Etiqueta` | 97 | |
| 118 | `Identidad` | 111 | |
| 119 | `Gancho (una línea)` | 113 | |
| 120 | `Foto de portada` | 114 | |
| 121 | `Crédito de las fotos` | 115 | |
| 122 | `Video (URL, opcional)` | 116 | |
| 123 | `Ubicación y acceso` | 120 | |
| 124 | `Latitud (pin)` | 122 | |
| 125 | `Longitud (pin)` | 123 | |
| 126 | `Referencia («a 3 km de…»)` | 124 | |
| 127 | `Cómo llegar (auto / colectivo / a pie)` | 125 | |
| 128 | `Estado del camino` | 126 | |
| 129 | `Accesibilidad` | 127 | |
| 130 | `Datos prácticos` | 131 | |
| 131 | `Horario y mejor momento para visitar` | 133 | |
| 132 | `Temporada ideal / cuándo evitar` | 134 | |
| 133 | `Costo / entrada` | 135 | |
| 134 | `Rango de precio` | 143 | |
| 135 | `Sin especificar` | 147 | |
| 136 | `Gratis` | 148 | |
| 137 | `$ — Muy barato` | 149 | |
| 138 | `$$ — Barato` | 150 | |
| 139 | `$$$ — Intermedio` | 151 | |
| 140 | `$$$$ — Caro` | 152 | |
| 141 | `Servicios (baños, comida, sombra…)` | 155 | |
| 142 | `Duración sugerida` | 156 | |
| 143 | `Contacto del lugar` | 157 | |
| 144 | `Fuentes y referencias` | 161 | |
| 145 | `Fuentes / referencias` | 163 | |

### Salida de campo

`templates/sections/captura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 146 | `Salida de campo` | 8 | |
| 147 | `Captura en el lugar` | 11 | |
| 148 | `Sacá una foto, anotá lo importante y guardá la ubicación, incluso si no tenés señal. Todo queda guardado en tu dispositivo y podés sincronizarlo como borrador cuando vuelva la conexión.` | 13 | |
| 149 | `Nombre del lugar` | 17 | |
| 150 | `Nota rápida` | 18 | |
| 151 | `Foto` | 21 | |
| 152 | `Ubicación (GPS)` | 25 | |
| 153 | `Tomar ubicación` | 27 | |
| 154 | `Guardar captura` | 33 | |
| 155 | `Capturas pendientes` | 39 | |
| 156 | `Sincronizar` | 40 | |

### Cola de revisión

`templates/sections/revision.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 157 | `No encontramos esta ficha.` | 13 | |
| 158 | `Revisión` | 20 | |
| 159 | `Volver a la cola` | 25 | |
| 160 | `Por %s` ⚠️ | 30 | |
| 161 | `Descripción` | 43 | |
| 162 | `Acciones` | 61 | |
| 163 | `Asignarme la revisión` | 64 | |
| 164 | `Comentarios para el autor` | 68 | |
| 165 | `Qué corregir o mejorar…` | 69 | |
| 166 | `Devolver con cambios` | 78 | |
| 167 | `Aprobar y publicar` | 80 | |
| 168 | `Historial` | 88 | |
| 169 | `Cola de revisión` | 116 | |
| 170 | `Taller editorial` | 119 | |
| 171 | `No hay fichas esperando revisión. 🎉` | 123 | |
| 172 | `%1$s · esperó %2$s` ⚠️ | 138 | |
| 173 | `revisa %s` ⚠️ 🔡 | 143 | |

### Tareas

`templates/sections/tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 174 | `Tareas` | 11 | |
| 175 | `Asignaciones` | 14 | |
| 176 | `Tareas y pendientes por cubrir` | 15 | |
| 177 | `+ Nueva tarea o hueco` | 19 | |
| 178 | `Título` | 21 | |
| 179 | `Detalle` | 22 | |
| 180 | `Tipo` | 24 | |
| 181 | `Tarea asignada` | 26 | |
| 182 | `Hueco disponible` | 27 | |
| 183 | `Vence` | 30 | |
| 184 | `Destino (opcional)` | 31 | |
| 185 | `Asignar a (Mini Promotores)` | 37 | |
| 186 | `Crear` | 42 | |
| 187 | `No hay tareas por ahora.` | 49 | |
| 188 | `Hueco` | 61 | |
| 189 | `vence %s` ⚠️ 🔡 | 63 | |
| 190 | `Reclamar` | 69 | |
| 191 | `Marcar como completada` | 72 | |

### Tareas (estados y avisos)

`includes/class-tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 192 | `Tareas` | 27 | |
| 193 | `Tarea` | 27 | |
| 194 | `Pendiente` | 38 | |
| 195 | `En curso` | 39 | |
| 196 | `Completada` | 40 | |
| 197 | `La tarea necesita un título.` | 58 | |

### Curaduría

`templates/sections/curaduria.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 198 | `Curaduría` | 10 | |
| 199 | `Portada` | 13 | |
| 200 | `Curaduría de la portada` | 14 | |
| 201 | `Elegí los destinos destacados y el banner de temporada. La portada pública se actualiza sola, sin tocar el código.` | 15 | |
| 202 | `Banner de temporada` | 22 | |
| 203 | `Título (dejalo vacío para no mostrar el banner)` | 23 | |
| 204 | `Texto` | 24 | |
| 205 | `Enlace (URL)` | 26 | |
| 206 | `Desde` | 27 | |
| 207 | `Hasta` | 28 | |
| 208 | `Destinos destacados` | 32 | |
| 209 | `Todavía no hay destinos publicados para destacar.` | 34 | |
| 210 | `Orden` | 46 | |
| 211 | `Guardar cambios` | 54 | |

### Moderación

`templates/sections/moderacion.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 212 | `Moderación` | 10 | |
| 213 | `Comunidad` | 13 | |
| 214 | `Reseñas pendientes` | 16 | |
| 215 | `No hay reseñas para moderar. 🎉` | 18 | |
| 216 | `Aprobar` | 30 | |
| 217 | `Descartar` | 31 | |
| 218 | `Bandeja de consultas` | 38 | |
| 219 | `No hay consultas pendientes.` | 40 | |
| 220 | `Derivar a un Mini…` | 56 | |
| 221 | `Derivar` | 61 | |
| 222 | `Marcar como resuelta` | 64 | |
| 223 | `Reportes de información desactualizada` | 72 | |
| 224 | `No hay reportes abiertos.` | 74 | |
| 225 | `Marcar como resuelto` | 83 | |

### Consultas (estados y avisos)

`includes/class-consultas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 226 | `Consultas` | 28 | |
| 227 | `Consulta` | 28 | |
| 228 | `Nueva` | 42 | |
| 229 | `Asignada` | 43 | |
| 230 | `Resuelta` | 44 | |
| 231 | `Completá tu nombre, email y mensaje.` | 61 | |
| 232 | `Consulta sobre %s` ⚠️ | 64 | |
| 233 | `Consulta general` | 64 | |
| 234 | `[Portal] %s` ⚠️ | 82 | |
| 235 | `De: %1$s <%2$s>` ⚠️ | 83 | |
| 236 | `El destino no es válido.` | 139 | |
| 237 | `Contanos qué información está desactualizada.` | 142 | |
| 238 | `No pudimos enviar la consulta.` | 158 | |

### Reseñas

`includes/class-resenas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 239 | `El destino no es válido.` | 44 | |
| 240 | `Escribí tu reseña.` | 47 | |
| 241 | `Usuario` | 52 | |
| 242 | `Decinos tu nombre.` | 58 | |
| 243 | `No pudimos guardar la reseña.` | 74 | |
| 244 | `%s de 5` ⚠️ | 137 | |
| 245 | `Reseñas` | 168 | |
| 246 | `%d reseña` ⚠️ | 175 | |
| 247 | `%d reseñas` ⚠️ | 175 | |
| 248 | `Sé el primero en dejar una reseña.` | 178 | |
| 249 | `Dejá tu reseña` | 196 | |
| 250 | `%d estrellas` ⚠️ | 199 | |
| 251 | `Tu nombre` | 205 | |
| 252 | `Email (no se publica)` | 206 | |
| 253 | `Tu experiencia` | 209 | |
| 254 | `Enviar reseña` | 210 | |
| 255 | `Tu reseña se publicará después de una breve moderación.` | 212 | |

### Equipo

`templates/sections/equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 256 | `Equipo` | 8 | |
| 257 | `Tu equipo` | 11 | |
| 258 | `Invitar a alguien` | 15 | |
| 259 | `Generá un enlace de invitación con el rol que quieras. El enlace es válido durante 14 días.` | 16 | |
| 260 | `Mini Promotor` | 21 | |
| 261 | `Promotor` | 22 | |
| 262 | `Visitante` | 23 | |
| 263 | `Crear enlace` | 25 | |
| 264 | `%1$d publicadas · %2$d en total` ⚠️ | 46 | |
| 265 | `Nivel de confianza:` | 56 | |
| 266 | `Guardar` | 63 | |

### Reportes

`templates/sections/reportes.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 267 | `Reportes` | 10 | |
| 268 | `Métricas` | 13 | |
| 269 | `Actividad del portal` | 14 | |
| 270 | `Producción por autor` | 16 | |
| 271 | `%1$d publicadas / %2$d` ⚠️ | 25 | |
| 272 | `Lo más visto` | 31 | |
| 273 | `Todavía no hay vistas registradas.` | 33 | |
| 274 | `%d vista` ⚠️ | 41 | |
| 275 | `%d vistas` ⚠️ | 41 | |
| 276 | `Búsquedas sin resultado` | 48 | |
| 277 | `(huecos de contenido)` | 48 | |
| 278 | `Todavía no hay búsquedas sin resultado.` | 50 | |
| 279 | `%d vez` ⚠️ | 56 | |
| 280 | `%d veces` ⚠️ | 56 | |
| 281 | `Estado del contenido` | 62 | |
| 282 | `fichas publicadas sin portada` 🔡 | 65 | |
| 283 | `fichas sin verificar hace +6 meses` 🔡 | 75 | |

### Biblioteca

`templates/sections/biblioteca.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 284 | `Biblioteca` | 4 | |
| 285 | `Medios` | 7 | |
| 286 | `Biblioteca de medios` | 8 | |
| 287 | `Tu galería de fotos, con créditos y atribución. Por ahora, las imágenes se suben directamente desde el editor de cada ficha.` | 10 | |
| 288 | `Abrir biblioteca de WordPress` | 11 | |

### Estructura

`templates/sections/estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 289 | `Estructura` | 4 | |
| 290 | `Organización` | 8 | |
| 291 | `Estructura del sitio` | 9 | |
| 292 | `Acá se organizan las categorías, zonas y etiquetas de los destinos. La edición completa llegará en la próxima fase; por ahora, gestioná estos elementos desde WordPress.` | 11 | |
| 293 | `Categorías` | 13 | |
| 294 | `Zonas` | 14 | |
| 295 | `Etiquetas` | 15 | |

### Buscar

`templates/sections/buscar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 296 | `Buscar` | 18 | |
| 297 | `Escribí algo en el buscador de arriba para encontrar fichas.` | 23 | |
| 298 | `%1$d resultado para «%2$s»` ⚠️ | 28 | |
| 299 | `%1$d resultados para «%2$s»` ⚠️ | 28 | |
| 300 | `No encontramos resultados. Probá con otras palabras.` | 32 | |

### Mi perfil

`templates/sections/perfil.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 301 | `Mi perfil` | 19 | |
| 302 | `Tu progreso de confianza` | 35 | |
| 303 | `Nivel máximo: publicás directamente y después se hace una auditoría. Gracias por tu compromiso.` | 49 | |
| 304 | `Promotor Jr: podés editar fichas publicadas sin pasar por una nueva revisión. Seguí sumando aprobaciones para llegar a «De confianza».` | 51 | |
| 305 | `Aprendiz: todo tu contenido pasa por revisión. A medida que sumás aprobaciones, vas ganando autonomía.` | 53 | |
| 306 | `fichas publicadas` 🔡 | 63 | |
| 307 | `vistas en total` 🔡 | 67 | |
| 308 | `Mi portafolio` | 71 | |
| 309 | `Todavía no tenés fichas publicadas.` | 73 | |
| 310 | `%d vista` ⚠️ | 82 | |
| 311 | `%d vistas` ⚠️ | 82 | |
| 312 | `Publicado` | 85 | |
| 313 | `Editar mi perfil en WordPress →` | 93 | |

### Niveles de confianza

`includes/class-stats.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 314 | `Aprendiz` | 88 | |
| 315 | `Promotor Jr` | 89 | |
| 316 | `De confianza` | 90 | |

### Ayuda

`templates/sections/ayuda.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 317 | `Ayuda` | 5 | |
| 318 | `Inicio` | 9 | |
| 319 | `Tu resumen del día: fichas que esperan revisión, contenido que necesita correcciones y accesos rápidos según tu rol.` | 9 | |
| 320 | `Nueva ficha` | 10 | |
| 321 | `El editor guiado para crear destinos. Completá los campos y el checklist; el sistema te avisa si falta algo antes de enviar la ficha a revisión.` | 10 | |
| 322 | `Salida de campo` | 11 | |
| 323 | `Sacá fotos, anotá información y guardá la ubicación GPS mientras estás en el lugar, incluso sin señal. Después podés sincronizar todo como borrador cuando vuelva la conexión.` | 11 | |
| 324 | `Mis contenidos` | 12 | |
| 325 | `Todas tus fichas, ordenadas por estado: borrador, enviada, en revisión, necesita cambios o publicada.` | 12 | |
| 326 | `Cola de revisión` | 13 | |
| 327 | `Para Promotores: revisá las fichas enviadas, asignate una, aprobala y publicala o devolvela con comentarios para que el autor haga los cambios necesarios.` | 13 | |
| 328 | `Tareas` | 14 | |
| 329 | `Asignaciones con fecha límite y una lista de lo que todavía falta cubrir. Los Mini Promotores pueden reclamar los huecos disponibles.` | 14 | |
| 330 | `Curaduría` | 15 | |
| 331 | `Elegí qué destinos aparecen destacados en la portada y configurá un banner de temporada. Los cambios se reflejan en la web pública sin tocar el código.` | 15 | |
| 332 | `Moderación` | 16 | |
| 333 | `Aprobá o descartá reseñas, respondé o derivá consultas de visitantes y atendé los reportes de información desactualizada.` | 16 | |
| 334 | `Equipo` | 17 | |
| 335 | `Gestioná a los Mini Promotores: revisá su producción, nivel de confianza y enlaces de invitación.` | 17 | |
| 336 | `Reportes` | 18 | |
| 337 | `Consultá la producción por autor, los destinos más vistos, las búsquedas sin resultado y el estado general del contenido.` | 18 | |
| 338 | `Mi perfil` | 19 | |
| 339 | `Consultá tu portafolio público, las vistas de tus fichas y tu progreso de nivel de confianza.` | 19 | |
| 340 | `Cómo funciona` | 22 | |
| 341 | `¿Qué hace cada sección?` | 23 | |
| 342 | `Este es el portal de los Promotores Turísticos: una web turística pública con un espacio de trabajo editorial detrás. Los Mini Promotores crean las fichas de destino y los Promotores las revisan y publican.` | 25 | |
| 343 | `El flujo editorial` | 28 | |
| 344 | `Borrador` | 31 | |
| 345 | `Enviado` | 32 | |
| 346 | `En revisión` | 33 | |
| 347 | `Necesita cambios` | 34 | |
| 348 | `Publicado` | 35 | |
| 349 | `Solo las fichas aprobadas por un Promotor llegan al público. La confianza se construye con cada aprobación: pasás de Aprendiz a Promotor Jr y luego a De confianza. Cada nivel te da más autonomía, como editar fichas publicadas sin una nueva revisión y, finalmente, publicar directamente.` | 38 | |
| 350 | `Las secciones` | 42 | |
| 351 | `Extras` | 56 | |
| 352 | `Podés instalar el portal como app (PWA) y consultar parte del contenido sin conexión desde el menú lateral.` | 58 | |
| 353 | `Cada ficha pública puede tener reseñas, indicaciones para llegar, un código QR para imprimir y un botón para agregarla a «Mi viaje».` | 59 | |
| 354 | `Podés cambiar entre modo claro y oscuro y elegir el idioma (ES/EN/GN) desde la barra superior.` | 60 | |
| 355 | `El acceso es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 61 | |

### Sección inexistente

`templates/sections/404.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 356 | `No encontramos esa sección` | 7 | |
| 357 | `Error 404` | 11 | |
| 358 | `Esta sección no existe` | 12 | |
| 359 | `Puede que el enlace esté roto o que no tengas permiso para acceder.` | 13 | |
| 360 | `Volver al inicio del panel` | 14 | |

## Entrar y salir

Acceso, registro, recuperación, invitaciones y errores de permiso.

### Iniciar sesión

`templates/auth/login.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 361 | `Iniciar sesión` | 12 | |
| 362 | `Entrá al panel de Promotores Turísticos.` | 16 | |
| 363 | `Tu contraseña se actualizó. Ya podés iniciar sesión.` | 19 | |
| 364 | `Email` | 34 | |
| 365 | `Contraseña` | 38 | |
| 366 | `Mantener la sesión iniciada` | 42 | |
| 367 | `Entrar` | 45 | |
| 368 | `¿Olvidaste tu contraseña?` | 49 | |
| 369 | `Acceso solo por invitación` | 50 | |

### Registro

`templates/auth/registro.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 370 | `Crear cuenta` | 14 | |
| 371 | `Esta invitación ya fue usada.` | 27 | |
| 372 | `Esta invitación venció. Pedí una nueva al equipo.` | 28 | |
| 373 | `Esta invitación fue revocada.` | 29 | |
| 374 | `El registro es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 30 | |
| 375 | `Ya tengo una cuenta` | 35 | |
| 376 | `Invitación válida: te unirás como %s.` ⚠️ | 44 | |
| 377 | `Nombre de usuario` | 55 | |
| 378 | `Email` | 59 | |
| 379 | `Teléfono` | 63 | |
| 380 | `Ej.: 0981 123 456` | 64 | |
| 381 | `Contraseña (6 o más caracteres)` | 67 | |

### Recuperar contraseña

`templates/auth/recuperar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 382 | `Recuperar contraseña` | 10 | |
| 383 | `Te enviamos un enlace para restablecer tu contraseña.` | 14 | |
| 384 | `Email` | 27 | |
| 385 | `Enviar enlace` | 30 | |
| 386 | `Volver a iniciar sesión` | 34 | |

### Contraseña nueva

`templates/auth/restablecer.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 387 | `Nueva contraseña` | 12 | |
| 388 | `Nueva contraseña (6 o más caracteres)` | 28 | |
| 389 | `Guardar contraseña` | 31 | |
| 390 | `El enlace no es válido o ya venció. Pedí uno nuevo.` | 34 | |
| 391 | `Pedir un nuevo enlace` | 36 | |

### Marco de acceso

`templates/auth-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 392 | `Acceso` | 8 | |

### Errores y avisos de acceso

`includes/class-auth.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 393 | `No tenés autorización para hacer esto.` | 43 | |
| 394 | `Enlace de invitación creado. Es válido durante 14 días: %s` ⚠️ | 49 | |
| 395 | `Tu sesión venció. Recargá la página.` | 136 | |
| 396 | `Necesitás una invitación válida para registrarte.` | 175 | |
| 397 | `Completá usuario, email, teléfono y una contraseña de al menos 6 caracteres.` | 185 | |
| 398 | `Ese email ya está registrado.` | 189 | |
| 399 | `Si la cuenta existe, te enviamos un email con las instrucciones.` | 234 | |
| 400 | `El enlace para restablecer la contraseña venció o no es válido.` | 252 | |

### Invitaciones

`includes/class-invitations.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 401 | `Válida` | 99 | |
| 402 | `Usada` | 100 | |
| 403 | `Expirada` | 101 | |
| 404 | `Revocada` | 102 | |
| 405 | `Inválida` | 103 | |

### Guardas de acceso

`includes/class-router.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 406 | `No tenés acceso a este panel.` | 188 | |
| 407 | `Acceso denegado` | 189 | |

### Guardas de sección

`includes/class-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 408 | `No tenés permiso para ver esta sección.` | 42 | |
| 409 | `Acceso denegado` | 43 | |

### Sin conexión (PWA)

`includes/class-pwa.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 410 | `Sin conexión` | 169 | |
| 411 | `Promotores Turísticos` | 176 | |
| 412 | `Estás sin conexión` | 177 | |
| 413 | `No pudimos cargar esta pantalla. Revisá tu conexión e intentá de nuevo.` | 178 | |
| 414 | `Reintentar` | 179 | |

## Lo que ve el visitante

La vitrina pública que publica el panel — no es parte del panel, pero el texto sale del mismo plugin.

### Ficha pública

`templates/public/single-destino.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 415 | `Horario` | 25 | |
| 416 | `Costo / entrada` | 26 | |
| 417 | `Cómo llegar` | 27 | |
| 418 | `Destino` | 34 | |
| 419 | `Agregar a mi viaje` | 59 | |
| 420 | `QR` | 60 | |
| 421 | `Cerrar` | 64 | |
| 422 | `Escaneá este código para abrir la ficha en tu celular.` | 66 | |
| 423 | `Verificado por un Promotor el %s` ⚠️ | 101 | |
| 424 | `Reportar información desactualizada` | 112 | |
| 425 | `¿Qué información está desactualizada?` | 114 | |
| 426 | `Enviar reporte` | 115 | |
| 427 | `Ficha producida por %s — Promotores Turísticos del Bachiller Técnico de Servicios.` ⚠️ | 131 | |

### Vitrina pública y shortcodes

`includes/class-public.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 428 | `Enviando…` | 42 | |
| 429 | `Algo salió mal. Probá de nuevo.` | 43 | |
| 430 | `Agregado a tu viaje` | 44 | |
| 431 | `Enlace copiado` | 45 | |
| 432 | `Tu viaje está vacío. Agregá destinos desde sus fichas.` | 46 | |
| 433 | `Todavía no hay destinos publicados.` | 88 | |
| 434 | `Buscar destinos…` | 195 | |
| 435 | `Categoría` | 197 | |
| 436 | `Zona` | 198 | |
| 437 | `Etiqueta` | 199 | |
| 438 | `Filtrar` | 201 | |
| 439 | `Limpiar` | 202 | |
| 440 | `No encontramos destinos con esos filtros.` | 206 | |
| 441 | `Mi viaje` | 271 | |
| 442 | `Compartir` | 273 | |
| 443 | `Imprimir / PDF` | 274 | |
| 444 | `Vaciar` | 275 | |
| 445 | `¿Tenés una consulta?` | 302 | |
| 446 | `Nombre` | 304 | |
| 447 | `Email` | 305 | |
| 448 | `Mensaje` | 307 | |
| 449 | `Enviar consulta` | 308 | |
| 450 | `Destacado` | 382 | |
| 451 | `Lo que no te podés perder` | 391 | |
| 452 | `Recién publicado` | 400 | |
| 453 | `En el mapa` | 407 | |

### Formularios públicos

`includes/class-public-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 454 | `Tu sesión venció. Recargá la página.` | 34 | |
| 455 | `No tenés autorización para hacer esto.` | 40 | |
| 456 | `¡Gracias! Tu reseña se publicará después de una breve moderación.` | 59 | |
| 457 | `¡Recibimos tu consulta! Te responderemos pronto.` | 73 | |
| 458 | `¡Gracias por avisar! Un Promotor va a revisarlo.` | 88 | |
| 459 | `La acción no es válida.` | 102 | |
| 460 | `Faltan algunos datos.` | 112 | |
| 461 | `Consulta derivada.` | 115 | |

### Bloques curados

`includes/class-curaduria.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 462 | `No tenés autorización para hacer esto.` | 55 | |
| 463 | `Curaduría guardada. La portada ya refleja los cambios.` | 76 | |

## wp-admin y mensajes de sistema

Pantallas de administración y respuestas de las acciones.

### Pantallas de wp-admin

`includes/class-admin.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 464 | `Portal Turismo` | 38 | |
| 465 | `Usuarios` | 41 | |
| 466 | `Invitaciones` | 42 | |
| 467 | `Registros` | 43 | |
| 468 | `Actualizaciones` | 45 | |
| 469 | `No tenés autorización para hacer esto.` | 60 | |
| 470 | `Usuarios del portal` | 73 | |
| 471 | `Editar: %s` ⚠️ | 77 | |
| 472 | `Nombre` | 84 | |
| 473 | `Email` | 85 | |
| 474 | `Teléfono` | 86 | |
| 475 | `Rol` | 87 | |
| 476 | `Restablecer contraseña` | 91 | |
| 477 | `Generar una nueva y mostrarla` | 91 | |
| 478 | `Guardar cambios` | 93 | |
| 479 | `Cancelar` | 94 | |
| 480 | `Usuario` | 101 | |
| 481 | `Estado` | 103 | |
| 482 | `Suspendido` | 116 | |
| 483 | `Activo` | 116 | |
| 484 | `Editar` | 118 | |
| 485 | `Reactivar` | 119 | |
| 486 | `Suspender` | 119 | |
| 487 | `Eliminar` | 120 | |
| 488 | `¿Seguro? Esta acción no se puede deshacer.` | 132 | |
| 489 | `El usuario no es válido.` | 151 | |
| 490 | `No podés modificar a un administrador ni tu propia cuenta desde acá.` | 155 | |
| 491 | `Usuario actualizado.` | 172 | |
| 492 | `Nueva contraseña: %s` ⚠️ | 176 | |
| 493 | `Usuario suspendido. Su sesión fue cerrada.` | 187 | |
| 494 | `Usuario reactivado.` | 193 | |
| 495 | `Usuario eliminado. Su contenido se reasignó a tu cuenta.` | 200 | |
| 496 | `Crear invitación` | 220 | |
| 497 | `Email (opcional)` | 229 | |
| 498 | `Expira (días)` | 230 | |
| 499 | `Cantidad` | 231 | |
| 500 | `Generar enlace(s)` | 233 | |
| 501 | `Invitaciones recientes` | 236 | |
| 502 | `Expira` | 240 | |
| 503 | `Enlace` | 241 | |
| 504 | `Revocar` | 262 | |
| 505 | `Invitación(es) creada(s):` | 287 | |
| 506 | `Invitación revocada.` | 290 | |
| 507 | `Entradas` | 313 | |
| 508 | `Fecha` | 317 | |
| 509 | `Acción` | 318 | |
| 510 | `Elemento` | 318 | |
| 511 | `IP` | 319 | |
| 512 | `Detalle` | 319 | |
| 513 | `No hay registros.` | 323 | |
| 514 | `Actualizaciones del portal` | 380 | |
| 515 | `No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.` | 384 | |
| 516 | `Atención: la versión del encabezado del plugin (%1$s) no coincide con PROMOTUR_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.` ⚠️ | 391 | |
| 517 | `Versión instalada` | 400 | |
| 518 | `Última disponible` | 401 | |
| 519 | `Actualizar ahora` | 410 | |
| 520 | `Estás al día.` | 412 | |
| 521 | `Última comprobación` | 415 | |
| 522 | `nunca` 🔡 | 416 | |
| 523 | `Repositorio` | 418 | |
| 524 | `Buscar actualizaciones ahora` | 427 | |
| 525 | `Limpiar caché del actualizador` | 433 | |
| 526 | `Token de GitHub` | 437 | |
| 527 | `Definido en wp-config.php mediante PROMOTUR_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.` | 439 | |
| 528 | `El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.` | 441 | |
| 529 | `Token` | 447 | |
| 530 | `•••• guardado (dejá vacío para conservarlo)` | 448 | |
| 531 | `Eliminar el token guardado` | 450 | |
| 532 | `Guardar token` | 454 | |
| 533 | `Hay una nueva versión disponible: %s.` ⚠️ | 473 | |
| 534 | `No hay actualizaciones: ya tenés la última versión.` | 475 | |
| 535 | `El verificador de actualizaciones no está disponible.` | 478 | |
| 536 | `Caché del actualizador limpiada.` | 487 | |
| 537 | `El token está definido en wp-config.php y no se puede cambiar desde acá.` | 492 | |
| 538 | `Token eliminado.` | 499 | |
| 539 | `Token guardado.` | 502 | |
| 540 | `No hubo cambios en el token.` | 504 | |

### Respuestas del editor

`includes/class-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 541 | `Tu sesión venció. Recargá la página.` | 38 | |
| 542 | `Necesitás iniciar sesión.` | 41 | |
| 543 | `No tenés permiso para hacer esto.` | 44 | |
| 544 | `No podés editar esta ficha.` | 83 | |
| 545 | `(sin título)` | 93 | |
| 546 | `Borrador guardado.` | 127 | |
| 547 | `Guardado. Como editaste una ficha publicada, tendrá que pasar por una nueva revisión.` | 135 | |
| 548 | `La ficha no es válida.` | 152 | |
| 549 | `Faltan datos obligatorios. Completá el checklist antes de enviarla.` | 156 | |
| 550 | `Publicación directa por nivel de confianza. Se hará una auditoría posterior.` | 163 | |
| 551 | `¡Publicado! Se aplicó tu nivel de confianza.` | 164 | |
| 552 | `¡Ficha enviada a revisión!` | 167 | |
| 553 | `Te asignaste la revisión.` | 178 | |
| 554 | `Ficha aprobada y publicada.` | 193 | |
| 555 | `Escribí los comentarios para el autor.` | 205 | |
| 556 | `Ficha devuelta al autor con comentarios.` | 209 | |
| 557 | `No recibimos ninguna imagen.` | 216 | |
| 558 | `Solo podés subir imágenes.` | 220 | |

### Respuestas de gestión

`includes/class-gestion-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 559 | `Tu sesión venció. Recargá la página.` | 28 | |
| 560 | `No tenés permiso para hacer esto.` | 31 | |
| 561 | `Tarea creada.` | 48 | |
| 562 | `La tarea no es válida.` | 55 | |
| 563 | `Reclamaste esta tarea. Ya podés trabajar en ella.` | 58 | |
| 564 | `Tarea completada. 🎉` | 72 | |
| 565 | `El usuario no es válido.` | 80 | |
| 566 | `Nivel actualizado.` | 83 | |

### Selector de idioma

`includes/class-i18n.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 567 | `Idioma` | 82 | |

### Avisos del plugin

`caaguazu-portal.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 568 | `Caaguazú Portal necesita tener activo el plugin «Caaguazú Cuentas» para funcionar. El inicio de sesión de los Promotores ya no usa los usuarios de WordPress. Activá el plugin desde Plugins para volver a usar el panel.` | 90 | |
| 569 | `Portal de Promotores` | 111 | |
