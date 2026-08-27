# Textos del panel

Todo lo que un usuario lee en el panel, sacado de las fuentes el 2026-08-27. **593 textos.**

Se regenera con `php tools/textos-del-panel.php > docs/textos-del-panel.md`.

- Escribí el reemplazo en la columna **Nuevo texto**; lo que quede en blanco se deja como está.
- Los marcados con ⚠️ llevan un hueco (`%s`, `%d`, `%1$s`) que el código rellena: hay que conservarlo tal cual y en el mismo orden.
- Los `[FALTA: …]` son huecos a propósito: textos que el diseño pide y que todavía no escribió nadie.
- Los marcados con 🔡 arrancan en minúscula.

## Sin escribir

Lo único que el panel muestra hoy y que nadie escribió todavía.

| # | Hueco | Dónde |
| --- | --- | --- |
| 334 | `[FALTA: aviso de que todavía no hay categorías]` | App (control de la app móvil) |

## Empiezan en minúscula

Casi todas son fragmentos escritos para leerse **después de un número** ("4 esperan revisión") o para ir dentro de una frase. Si se quieren usar como título, hay que reescribirlas enteras, no sólo poner la mayúscula.

| # | Texto | Dónde |
| --- | --- | --- |
| 48 | `atrás` | Notificaciones |
| 70 | `esperan revisión` | Inicio |
| 71 | `publicados` | Inicio |
| 72 | `esperan tu corrección` | Inicio |
| 73 | `en proceso` | Inicio |
| 74 | `reseñas por moderar` | Inicio |
| 75 | `consultas sin responder` | Inicio |
| 174 | `revisa %s` | Cola de revisión |
| 190 | `vence %s` | Tareas |
| 283 | `fichas publicadas sin portada` | Reportes |
| 284 | `fichas sin verificar hace +6 meses` | Reportes |
| 307 | `fichas publicadas` | Mi perfil |
| 308 | `vistas en total` | Mi perfil |
| 546 | `nunca` | Pantallas de wp-admin |

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
| 11 | `Curaduría` | 321 | |
| 12 | `Moderación` | 322 | |
| 13 | `Equipo` | 323 | |
| 14 | `Reportes` | 324 | |
| 15 | `Biblioteca` | 325 | |
| 16 | `Estructura` | 326 | |
| 17 | `App` | 334 | |
| 18 | `Mi perfil` | 354 | |
| 19 | `Ayuda` | 355 | |

### Menú lateral (pie)

`templates/partials/sidebar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 20 | `Abrir menú` | 47 | |
| 21 | `Buscar…` | 77 | |
| 22 | `Buscar` | 78 | |
| 23 | `Navegación del panel` | 82 | |
| 24 | `Instalar app` | 111 | |
| 25 | `Cerrar sesión` | 115 | |

### Barra superior

`templates/partials/topbar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 26 | `Abrir menú` | 21 | |
| 27 | `Navegación del panel` | 25 | |
| 28 | `Inicio` | 30 | |
| 29 | `Buscar…` | 39 | |
| 30 | `Buscar` | 39 | |
| 31 | `Cambiar tema` | 44 | |
| 32 | `Notificaciones` | 50 | |
| 33 | `Marcar todo como leído` | 63 | |
| 34 | `No hay novedades por ahora. ✨` | 69 | |

### Barra inferior (teléfono)

`templates/partials/bottomnav.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 35 | `Inicio` | 10 | |
| 36 | `Contenidos` | 11 | |
| 37 | `Campo` | 12 | |
| 38 | `Revisar` | 13 | |
| 39 | `Perfil` | 14 | |
| 40 | `Navegación rápida` | 20 | |

### Mensajes del JavaScript

`includes/class-assets.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 41 | `Instalar app` | 70 | |
| 42 | `Enviando…` | 71 | |
| 43 | `Algo salió mal. Probá de nuevo.` | 72 | |
| 44 | `Guardado` | 73 | |
| 45 | `¿Querés confirmar esta acción?` | 74 | |
| 46 | `Faltan algunos datos obligatorios.` | 75 | |

### Notificaciones

`includes/class-notifications.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 47 | `«%s» está esperando revisión` ⚠️ | 48 | |
| 48 | `atrás` 🔡 | 50 | |
| 49 | `«%s» necesita algunos cambios` ⚠️ | 72 | |
| 50 | `No tenés autorización para hacer esto.` | 129 | |
| 51 | `Notificaciones marcadas como leídas.` | 137 | |

### Estados del flujo editorial

`includes/class-editorial.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 52 | `Borrador` | 30 | |
| 53 | `Enviado` | 31 | |
| 54 | `En revisión` | 32 | |
| 55 | `Necesita cambios` | 33 | |
| 56 | `Aprobado` | 34 | |
| 57 | `Publicado` | 35 | |
| 58 | `Despublicado` | 36 | |
| 59 | `Archivado` | 37 | |
| 60 | `Nombre del destino` | 86 | |
| 61 | `Descripción` | 93 | |
| 62 | `Faltan fuentes o referencias.` | 123 | |
| 63 | `Mejorá las fotos: cuidá la luz, el encuadre y la portada.` | 124 | |
| 64 | `Verificá los horarios y los costos.` | 125 | |
| 65 | `Revisá la ortografía y la redacción.` | 126 | |
| 66 | `Precisá cómo llegar.` | 127 | |

### Nombres de los roles

`includes/class-roles.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 67 | `Promotor` | 65 | |
| 68 | `Mini Promotor` | 66 | |
| 69 | `Visitante` | 67 | |

## Las secciones

Una tabla por pantalla del panel.

### Inicio

`templates/sections/home.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 70 | `esperan revisión` 🔡 | 34 | |
| 71 | `publicados` 🔡 | 35 | |
| 72 | `esperan tu corrección` 🔡 | 38 | |
| 73 | `en proceso` 🔡 | 39 | |
| 74 | `reseñas por moderar` 🔡 | 42 | |
| 75 | `consultas sin responder` 🔡 | 43 | |
| 76 | `Inicio` | 55 | |
| 77 | `Tu actividad de hoy` | 60 | |
| 78 | `Hola, %s 👋` ⚠️ | 63 | |
| 79 | `Actividad reciente` | 85 | |
| 80 | `Actividad de los últimos %d día` ⚠️ | 90 | |
| 81 | `Actividad de los últimos %d días` ⚠️ | 90 | |
| 82 | `Accesos rápidos` | 105 | |
| 83 | `Crear una ficha` | 110 | |
| 84 | `Mis contenidos` | 113 | |
| 85 | `Cola de revisión` | 116 | |
| 86 | `Equipo` | 119 | |
| 87 | `Mi perfil` | 121 | |

### Mis contenidos

`templates/sections/mis-contenidos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 88 | `Mis contenidos` | 17 | |
| 89 | `Tu producción` | 22 | |
| 90 | `+ Nueva ficha` | 25 | |
| 91 | `Todavía no creaste ninguna ficha. Empezá con una nueva.` | 30 | |
| 92 | `Crear mi primera ficha` | 31 | |
| 93 | `(sin título)` | 40 | |

### Editor de ficha

`templates/sections/editor.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 94 | `No podés editar esta ficha.` | 19 | |
| 95 | `Editar ficha` | 31 | |
| 96 | `Nueva ficha` | 31 | |
| 97 | `Ficha del destino` | 39 | |
| 98 | `Comentarios del revisor` | 47 | |
| 99 | `Nombre del destino` | 63 | |
| 100 | `Descripción` | 68 | |
| 101 | `—` | 90 | |
| 102 | `Subir foto` | 107 | |
| 103 | `📍 Usar mi ubicación actual` | 117 | |
| 104 | `Guardar borrador` | 124 | |
| 105 | `Enviar a revisión` | 125 | |
| 106 | `Checklist de mínimos` | 132 | |
| 107 | `Completá estos puntos antes de enviar la ficha a revisión.` | 133 | |

### Campos de la ficha

`includes/class-destinos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 108 | `Destinos` | 64 | |
| 109 | `Destino` | 65 | |
| 110 | `Nuevo destino` | 66 | |
| 111 | `Editar destino` | 67 | |
| 112 | `Buscar destinos` | 68 | |
| 113 | `Categorías` | 88 | |
| 114 | `Categoría` | 88 | |
| 115 | `Zonas` | 92 | |
| 116 | `Zona` | 92 | |
| 117 | `Etiquetas` | 97 | |
| 118 | `Etiqueta` | 97 | |
| 119 | `Identidad` | 111 | |
| 120 | `Gancho (una línea)` | 113 | |
| 121 | `Foto de portada` | 114 | |
| 122 | `Crédito de las fotos` | 115 | |
| 123 | `Video (URL, opcional)` | 116 | |
| 124 | `Ubicación y acceso` | 120 | |
| 125 | `Latitud (pin)` | 122 | |
| 126 | `Longitud (pin)` | 123 | |
| 127 | `Referencia («a 3 km de…»)` | 124 | |
| 128 | `Cómo llegar (auto / colectivo / a pie)` | 125 | |
| 129 | `Estado del camino` | 126 | |
| 130 | `Accesibilidad` | 127 | |
| 131 | `Datos prácticos` | 131 | |
| 132 | `Horario y mejor momento para visitar` | 133 | |
| 133 | `Temporada ideal / cuándo evitar` | 134 | |
| 134 | `Costo / entrada` | 135 | |
| 135 | `Rango de precio` | 143 | |
| 136 | `Sin especificar` | 147 | |
| 137 | `Gratis` | 148 | |
| 138 | `$ — Muy barato` | 149 | |
| 139 | `$$ — Barato` | 150 | |
| 140 | `$$$ — Intermedio` | 151 | |
| 141 | `$$$$ — Caro` | 152 | |
| 142 | `Servicios (baños, comida, sombra…)` | 155 | |
| 143 | `Duración sugerida` | 156 | |
| 144 | `Contacto del lugar` | 157 | |
| 145 | `Fuentes y referencias` | 161 | |
| 146 | `Fuentes / referencias` | 163 | |

### Salida de campo

`templates/sections/captura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 147 | `Salida de campo` | 8 | |
| 148 | `Captura en el lugar` | 11 | |
| 149 | `Sacá una foto, anotá lo importante y guardá la ubicación, incluso si no tenés señal. Todo queda guardado en tu dispositivo y podés sincronizarlo como borrador cuando vuelva la conexión.` | 13 | |
| 150 | `Nombre del lugar` | 17 | |
| 151 | `Nota rápida` | 18 | |
| 152 | `Foto` | 21 | |
| 153 | `Ubicación (GPS)` | 25 | |
| 154 | `Tomar ubicación` | 27 | |
| 155 | `Guardar captura` | 33 | |
| 156 | `Capturas pendientes` | 39 | |
| 157 | `Sincronizar` | 40 | |

### Cola de revisión

`templates/sections/revision.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 158 | `No encontramos esta ficha.` | 13 | |
| 159 | `Revisión` | 20 | |
| 160 | `Volver a la cola` | 25 | |
| 161 | `Por %s` ⚠️ | 30 | |
| 162 | `Descripción` | 43 | |
| 163 | `Acciones` | 61 | |
| 164 | `Asignarme la revisión` | 64 | |
| 165 | `Comentarios para el autor` | 68 | |
| 166 | `Qué corregir o mejorar…` | 69 | |
| 167 | `Devolver con cambios` | 78 | |
| 168 | `Aprobar y publicar` | 80 | |
| 169 | `Historial` | 88 | |
| 170 | `Cola de revisión` | 116 | |
| 171 | `Taller editorial` | 119 | |
| 172 | `No hay fichas esperando revisión. 🎉` | 123 | |
| 173 | `%1$s · esperó %2$s` ⚠️ | 138 | |
| 174 | `revisa %s` ⚠️ 🔡 | 143 | |

### Tareas

`templates/sections/tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 175 | `Tareas` | 11 | |
| 176 | `Asignaciones` | 14 | |
| 177 | `Tareas y pendientes por cubrir` | 15 | |
| 178 | `+ Nueva tarea o hueco` | 19 | |
| 179 | `Título` | 21 | |
| 180 | `Detalle` | 22 | |
| 181 | `Tipo` | 24 | |
| 182 | `Tarea asignada` | 26 | |
| 183 | `Hueco disponible` | 27 | |
| 184 | `Vence` | 30 | |
| 185 | `Destino (opcional)` | 31 | |
| 186 | `Asignar a (Mini Promotores)` | 37 | |
| 187 | `Crear` | 42 | |
| 188 | `No hay tareas por ahora.` | 49 | |
| 189 | `Hueco` | 61 | |
| 190 | `vence %s` ⚠️ 🔡 | 63 | |
| 191 | `Reclamar` | 69 | |
| 192 | `Marcar como completada` | 72 | |

### Tareas (estados y avisos)

`includes/class-tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 193 | `Tareas` | 27 | |
| 194 | `Tarea` | 27 | |
| 195 | `Pendiente` | 38 | |
| 196 | `En curso` | 39 | |
| 197 | `Completada` | 40 | |
| 198 | `La tarea necesita un título.` | 58 | |

### Curaduría

`templates/sections/curaduria.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 199 | `Curaduría` | 10 | |
| 200 | `Portada` | 13 | |
| 201 | `Curaduría de la portada` | 14 | |
| 202 | `Elegí los destinos destacados y el banner de temporada. La portada pública se actualiza sola, sin tocar el código.` | 15 | |
| 203 | `Banner de temporada` | 22 | |
| 204 | `Título (dejalo vacío para no mostrar el banner)` | 23 | |
| 205 | `Texto` | 24 | |
| 206 | `Enlace (URL)` | 26 | |
| 207 | `Desde` | 27 | |
| 208 | `Hasta` | 28 | |
| 209 | `Destinos destacados` | 32 | |
| 210 | `Todavía no hay destinos publicados para destacar.` | 34 | |
| 211 | `Orden` | 46 | |
| 212 | `Guardar cambios` | 54 | |

### Moderación

`templates/sections/moderacion.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 213 | `Moderación` | 10 | |
| 214 | `Comunidad` | 13 | |
| 215 | `Reseñas pendientes` | 16 | |
| 216 | `No hay reseñas para moderar. 🎉` | 18 | |
| 217 | `Aprobar` | 30 | |
| 218 | `Descartar` | 31 | |
| 219 | `Bandeja de consultas` | 38 | |
| 220 | `No hay consultas pendientes.` | 40 | |
| 221 | `Derivar a un Mini…` | 56 | |
| 222 | `Derivar` | 61 | |
| 223 | `Marcar como resuelta` | 64 | |
| 224 | `Reportes de información desactualizada` | 72 | |
| 225 | `No hay reportes abiertos.` | 74 | |
| 226 | `Marcar como resuelto` | 83 | |

### Consultas (estados y avisos)

`includes/class-consultas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 227 | `Consultas` | 28 | |
| 228 | `Consulta` | 28 | |
| 229 | `Nueva` | 42 | |
| 230 | `Asignada` | 43 | |
| 231 | `Resuelta` | 44 | |
| 232 | `Completá tu nombre, email y mensaje.` | 61 | |
| 233 | `Consulta sobre %s` ⚠️ | 64 | |
| 234 | `Consulta general` | 64 | |
| 235 | `[Portal] %s` ⚠️ | 82 | |
| 236 | `De: %1$s <%2$s>` ⚠️ | 83 | |
| 237 | `El destino no es válido.` | 139 | |
| 238 | `Contanos qué información está desactualizada.` | 142 | |
| 239 | `No pudimos enviar la consulta.` | 158 | |

### Reseñas

`includes/class-resenas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 240 | `El destino no es válido.` | 44 | |
| 241 | `Escribí tu reseña.` | 47 | |
| 242 | `Usuario` | 52 | |
| 243 | `Decinos tu nombre.` | 58 | |
| 244 | `No pudimos guardar la reseña.` | 74 | |
| 245 | `%s de 5` ⚠️ | 137 | |
| 246 | `Reseñas` | 168 | |
| 247 | `%d reseña` ⚠️ | 175 | |
| 248 | `%d reseñas` ⚠️ | 175 | |
| 249 | `Sé el primero en dejar una reseña.` | 178 | |
| 250 | `Dejá tu reseña` | 196 | |
| 251 | `%d estrellas` ⚠️ | 199 | |
| 252 | `Tu nombre` | 205 | |
| 253 | `Email (no se publica)` | 206 | |
| 254 | `Tu experiencia` | 209 | |
| 255 | `Enviar reseña` | 210 | |
| 256 | `Tu reseña se publicará después de una breve moderación.` | 212 | |

### Equipo

`templates/sections/equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 257 | `Equipo` | 8 | |
| 258 | `Tu equipo` | 11 | |
| 259 | `Invitar a alguien` | 15 | |
| 260 | `Generá un enlace de invitación con el rol que quieras. El enlace es válido durante 14 días.` | 16 | |
| 261 | `Mini Promotor` | 21 | |
| 262 | `Promotor` | 22 | |
| 263 | `Visitante` | 23 | |
| 264 | `Crear enlace` | 25 | |
| 265 | `%1$d publicadas · %2$d en total` ⚠️ | 46 | |
| 266 | `Nivel de confianza:` | 56 | |
| 267 | `Guardar` | 63 | |

### Reportes

`templates/sections/reportes.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 268 | `Reportes` | 10 | |
| 269 | `Métricas` | 13 | |
| 270 | `Actividad del portal` | 14 | |
| 271 | `Producción por autor` | 16 | |
| 272 | `%1$d publicadas / %2$d` ⚠️ | 25 | |
| 273 | `Lo más visto` | 31 | |
| 274 | `Todavía no hay vistas registradas.` | 33 | |
| 275 | `%d vista` ⚠️ | 41 | |
| 276 | `%d vistas` ⚠️ | 41 | |
| 277 | `Búsquedas sin resultado` | 48 | |
| 278 | `(huecos de contenido)` | 48 | |
| 279 | `Todavía no hay búsquedas sin resultado.` | 50 | |
| 280 | `%d vez` ⚠️ | 56 | |
| 281 | `%d veces` ⚠️ | 56 | |
| 282 | `Estado del contenido` | 62 | |
| 283 | `fichas publicadas sin portada` 🔡 | 65 | |
| 284 | `fichas sin verificar hace +6 meses` 🔡 | 75 | |

### Biblioteca

`templates/sections/biblioteca.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 285 | `Biblioteca` | 4 | |
| 286 | `Medios` | 7 | |
| 287 | `Biblioteca de medios` | 8 | |
| 288 | `Tu galería de fotos, con créditos y atribución. Por ahora, las imágenes se suben directamente desde el editor de cada ficha.` | 10 | |
| 289 | `Abrir biblioteca de WordPress` | 11 | |

### Estructura

`templates/sections/estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 290 | `Estructura` | 4 | |
| 291 | `Organización` | 8 | |
| 292 | `Estructura del sitio` | 9 | |
| 293 | `Acá se organizan las categorías, zonas y etiquetas de los destinos. La edición completa llegará en la próxima fase; por ahora, gestioná estos elementos desde WordPress.` | 11 | |
| 294 | `Categorías` | 13 | |
| 295 | `Zonas` | 14 | |
| 296 | `Etiquetas` | 15 | |

### Buscar

`templates/sections/buscar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 297 | `Buscar` | 18 | |
| 298 | `Escribí algo en el buscador de arriba para encontrar fichas.` | 23 | |
| 299 | `%1$d resultado para «%2$s»` ⚠️ | 28 | |
| 300 | `%1$d resultados para «%2$s»` ⚠️ | 28 | |
| 301 | `No encontramos resultados. Probá con otras palabras.` | 32 | |

### Mi perfil

`templates/sections/perfil.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 302 | `Mi perfil` | 19 | |
| 303 | `Tu progreso de confianza` | 35 | |
| 304 | `Nivel máximo: publicás directamente y después se hace una auditoría. Gracias por tu compromiso.` | 49 | |
| 305 | `Promotor Jr: podés editar fichas publicadas sin pasar por una nueva revisión. Seguí sumando aprobaciones para llegar a «De confianza».` | 51 | |
| 306 | `Aprendiz: todo tu contenido pasa por revisión. A medida que sumás aprobaciones, vas ganando autonomía.` | 53 | |
| 307 | `fichas publicadas` 🔡 | 63 | |
| 308 | `vistas en total` 🔡 | 67 | |
| 309 | `Mi portafolio` | 71 | |
| 310 | `Todavía no tenés fichas publicadas.` | 73 | |
| 311 | `%d vista` ⚠️ | 82 | |
| 312 | `%d vistas` ⚠️ | 82 | |
| 313 | `Publicado` | 85 | |
| 314 | `Editar mi perfil en WordPress →` | 93 | |

### Niveles de confianza

`includes/class-stats.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 315 | `Aprendiz` | 88 | |
| 316 | `Promotor Jr` | 89 | |
| 317 | `De confianza` | 90 | |

### App (control de la app móvil)

`templates/sections/app.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 318 | `App` | 21 | |
| 319 | `Aplicación` | 27 | |
| 320 | `Textos` | 36 | |
| 321 | `Idioma` | 37 | |
| 322 | `Clave` | 64 | |
| 323 | `Texto` | 68 | |
| 324 | `Guardar cambios` | 75 | |
| 325 | `Medios` | 84 | |
| 326 | `Abrir biblioteca de WordPress` | 87 | |
| 327 | `Tipo` | 115 | |
| 328 | `Imagen` | 117 | |
| 329 | `Animación` | 118 | |
| 330 | `URL o ID` | 122 | |
| 331 | `Texto alternativo` | 126 | |
| 332 | `Formato` | 130 | |
| 333 | `Categorías` | 147 | |
| 334 | `[FALTA: aviso de que todavía no hay categorías]` | 151 | |
| 335 | `Estructura` | 152 | |
| 336 | `Nombre` | 163 | |
| 337 | `Color` | 167 | |
| 338 | `Icono` | 172 | |

### App (avisos)

`includes/class-app-control.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 339 | `No tenés autorización para hacer esto.` | 134 | |
| 340 | `Guardado` | 195 | |

### Ayuda

`templates/sections/ayuda.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 341 | `Ayuda` | 5 | |
| 342 | `Inicio` | 9 | |
| 343 | `Tu resumen del día: fichas que esperan revisión, contenido que necesita correcciones y accesos rápidos según tu rol.` | 9 | |
| 344 | `Nueva ficha` | 10 | |
| 345 | `El editor guiado para crear destinos. Completá los campos y el checklist; el sistema te avisa si falta algo antes de enviar la ficha a revisión.` | 10 | |
| 346 | `Salida de campo` | 11 | |
| 347 | `Sacá fotos, anotá información y guardá la ubicación GPS mientras estás en el lugar, incluso sin señal. Después podés sincronizar todo como borrador cuando vuelva la conexión.` | 11 | |
| 348 | `Mis contenidos` | 12 | |
| 349 | `Todas tus fichas, ordenadas por estado: borrador, enviada, en revisión, necesita cambios o publicada.` | 12 | |
| 350 | `Cola de revisión` | 13 | |
| 351 | `Para Promotores: revisá las fichas enviadas, asignate una, aprobala y publicala o devolvela con comentarios para que el autor haga los cambios necesarios.` | 13 | |
| 352 | `Tareas` | 14 | |
| 353 | `Asignaciones con fecha límite y una lista de lo que todavía falta cubrir. Los Mini Promotores pueden reclamar los huecos disponibles.` | 14 | |
| 354 | `Curaduría` | 15 | |
| 355 | `Elegí qué destinos aparecen destacados en la portada y configurá un banner de temporada. Los cambios se reflejan en la web pública sin tocar el código.` | 15 | |
| 356 | `Moderación` | 16 | |
| 357 | `Aprobá o descartá reseñas, respondé o derivá consultas de visitantes y atendé los reportes de información desactualizada.` | 16 | |
| 358 | `Equipo` | 17 | |
| 359 | `Gestioná a los Mini Promotores: revisá su producción, nivel de confianza y enlaces de invitación.` | 17 | |
| 360 | `Reportes` | 18 | |
| 361 | `Consultá la producción por autor, los destinos más vistos, las búsquedas sin resultado y el estado general del contenido.` | 18 | |
| 362 | `Mi perfil` | 19 | |
| 363 | `Consultá tu portafolio público, las vistas de tus fichas y tu progreso de nivel de confianza.` | 19 | |
| 364 | `Cómo funciona` | 22 | |
| 365 | `¿Qué hace cada sección?` | 23 | |
| 366 | `Este es el portal de los Promotores Turísticos: una web turística pública con un espacio de trabajo editorial detrás. Los Mini Promotores crean las fichas de destino y los Promotores las revisan y publican.` | 25 | |
| 367 | `El flujo editorial` | 28 | |
| 368 | `Borrador` | 31 | |
| 369 | `Enviado` | 32 | |
| 370 | `En revisión` | 33 | |
| 371 | `Necesita cambios` | 34 | |
| 372 | `Publicado` | 35 | |
| 373 | `Solo las fichas aprobadas por un Promotor llegan al público. La confianza se construye con cada aprobación: pasás de Aprendiz a Promotor Jr y luego a De confianza. Cada nivel te da más autonomía, como editar fichas publicadas sin una nueva revisión y, finalmente, publicar directamente.` | 38 | |
| 374 | `Las secciones` | 42 | |
| 375 | `Extras` | 56 | |
| 376 | `Podés instalar el portal como app (PWA) y consultar parte del contenido sin conexión desde el menú lateral.` | 58 | |
| 377 | `Cada ficha pública puede tener reseñas, indicaciones para llegar, un código QR para imprimir y un botón para agregarla a «Mi viaje».` | 59 | |
| 378 | `Podés cambiar entre modo claro y oscuro y elegir el idioma (ES/EN/GN) desde la barra superior.` | 60 | |
| 379 | `El acceso es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 61 | |

### Sección inexistente

`templates/sections/404.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 380 | `No encontramos esa sección` | 7 | |
| 381 | `Error 404` | 11 | |
| 382 | `Esta sección no existe` | 12 | |
| 383 | `Puede que el enlace esté roto o que no tengas permiso para acceder.` | 13 | |
| 384 | `Volver al inicio del panel` | 14 | |

## Entrar y salir

Acceso, registro, recuperación, invitaciones y errores de permiso.

### Iniciar sesión

`templates/auth/login.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 385 | `Iniciar sesión` | 12 | |
| 386 | `Entrá al panel de Promotores Turísticos.` | 16 | |
| 387 | `Tu contraseña se actualizó. Ya podés iniciar sesión.` | 19 | |
| 388 | `Email` | 34 | |
| 389 | `Contraseña` | 38 | |
| 390 | `Mantener la sesión iniciada` | 42 | |
| 391 | `Entrar` | 45 | |
| 392 | `¿Olvidaste tu contraseña?` | 49 | |
| 393 | `Acceso solo por invitación` | 50 | |

### Registro

`templates/auth/registro.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 394 | `Crear cuenta` | 14 | |
| 395 | `Esta invitación ya fue usada.` | 27 | |
| 396 | `Esta invitación venció. Pedí una nueva al equipo.` | 28 | |
| 397 | `Esta invitación fue revocada.` | 29 | |
| 398 | `El registro es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 30 | |
| 399 | `Ya tengo una cuenta` | 35 | |
| 400 | `Invitación válida: te unirás como %s.` ⚠️ | 44 | |
| 401 | `Nombre de usuario` | 55 | |
| 402 | `Email` | 59 | |
| 403 | `Teléfono` | 63 | |
| 404 | `Ej.: 0981 123 456` | 64 | |
| 405 | `Contraseña (6 o más caracteres)` | 67 | |

### Recuperar contraseña

`templates/auth/recuperar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 406 | `Recuperar contraseña` | 10 | |
| 407 | `Te enviamos un enlace para restablecer tu contraseña.` | 14 | |
| 408 | `Email` | 27 | |
| 409 | `Enviar enlace` | 30 | |
| 410 | `Volver a iniciar sesión` | 34 | |

### Contraseña nueva

`templates/auth/restablecer.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 411 | `Nueva contraseña` | 12 | |
| 412 | `Nueva contraseña (6 o más caracteres)` | 28 | |
| 413 | `Guardar contraseña` | 31 | |
| 414 | `El enlace no es válido o ya venció. Pedí uno nuevo.` | 34 | |
| 415 | `Pedir un nuevo enlace` | 36 | |

### Marco de acceso

`templates/auth-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 416 | `Acceso` | 8 | |

### Errores y avisos de acceso

`includes/class-auth.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 417 | `No tenés autorización para hacer esto.` | 43 | |
| 418 | `Enlace de invitación creado. Es válido durante 14 días: %s` ⚠️ | 49 | |
| 419 | `Tu sesión venció. Recargá la página.` | 136 | |
| 420 | `Necesitás una invitación válida para registrarte.` | 175 | |
| 421 | `Completá usuario, email, teléfono y una contraseña de al menos 6 caracteres.` | 185 | |
| 422 | `Ese email ya está registrado.` | 189 | |
| 423 | `Si la cuenta existe, te enviamos un email con las instrucciones.` | 234 | |
| 424 | `El enlace para restablecer la contraseña venció o no es válido.` | 252 | |

### Invitaciones

`includes/class-invitations.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 425 | `Válida` | 99 | |
| 426 | `Usada` | 100 | |
| 427 | `Expirada` | 101 | |
| 428 | `Revocada` | 102 | |
| 429 | `Inválida` | 103 | |

### Guardas de acceso

`includes/class-router.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 430 | `No tenés acceso a este panel.` | 188 | |
| 431 | `Acceso denegado` | 189 | |

### Guardas de sección

`includes/class-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 432 | `No tenés permiso para ver esta sección.` | 42 | |
| 433 | `Acceso denegado` | 43 | |

### Sin conexión (PWA)

`includes/class-pwa.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 434 | `Sin conexión` | 169 | |
| 435 | `Promotores Turísticos` | 176 | |
| 436 | `Estás sin conexión` | 177 | |
| 437 | `No pudimos cargar esta pantalla. Revisá tu conexión e intentá de nuevo.` | 178 | |
| 438 | `Reintentar` | 179 | |

## Lo que ve el visitante

La vitrina pública que publica el panel — no es parte del panel, pero el texto sale del mismo plugin.

### Ficha pública

`templates/public/single-destino.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 439 | `Horario` | 25 | |
| 440 | `Costo / entrada` | 26 | |
| 441 | `Cómo llegar` | 27 | |
| 442 | `Destino` | 34 | |
| 443 | `Agregar a mi viaje` | 59 | |
| 444 | `QR` | 60 | |
| 445 | `Cerrar` | 64 | |
| 446 | `Escaneá este código para abrir la ficha en tu celular.` | 66 | |
| 447 | `Verificado por un Promotor el %s` ⚠️ | 101 | |
| 448 | `Reportar información desactualizada` | 112 | |
| 449 | `¿Qué información está desactualizada?` | 114 | |
| 450 | `Enviar reporte` | 115 | |
| 451 | `Ficha producida por %s — Promotores Turísticos del Bachiller Técnico de Servicios.` ⚠️ | 131 | |

### Vitrina pública y shortcodes

`includes/class-public.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 452 | `Enviando…` | 42 | |
| 453 | `Algo salió mal. Probá de nuevo.` | 43 | |
| 454 | `Agregado a tu viaje` | 44 | |
| 455 | `Enlace copiado` | 45 | |
| 456 | `Tu viaje está vacío. Agregá destinos desde sus fichas.` | 46 | |
| 457 | `Todavía no hay destinos publicados.` | 88 | |
| 458 | `Buscar destinos…` | 195 | |
| 459 | `Categoría` | 197 | |
| 460 | `Zona` | 198 | |
| 461 | `Etiqueta` | 199 | |
| 462 | `Filtrar` | 201 | |
| 463 | `Limpiar` | 202 | |
| 464 | `No encontramos destinos con esos filtros.` | 206 | |
| 465 | `Mi viaje` | 271 | |
| 466 | `Compartir` | 273 | |
| 467 | `Imprimir / PDF` | 274 | |
| 468 | `Vaciar` | 275 | |
| 469 | `¿Tenés una consulta?` | 302 | |
| 470 | `Nombre` | 304 | |
| 471 | `Email` | 305 | |
| 472 | `Mensaje` | 307 | |
| 473 | `Enviar consulta` | 308 | |
| 474 | `Destacado` | 382 | |
| 475 | `Lo que no te podés perder` | 391 | |
| 476 | `Recién publicado` | 400 | |
| 477 | `En el mapa` | 407 | |

### Formularios públicos

`includes/class-public-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 478 | `Tu sesión venció. Recargá la página.` | 34 | |
| 479 | `No tenés autorización para hacer esto.` | 40 | |
| 480 | `¡Gracias! Tu reseña se publicará después de una breve moderación.` | 59 | |
| 481 | `¡Recibimos tu consulta! Te responderemos pronto.` | 73 | |
| 482 | `¡Gracias por avisar! Un Promotor va a revisarlo.` | 88 | |
| 483 | `La acción no es válida.` | 102 | |
| 484 | `Faltan algunos datos.` | 112 | |
| 485 | `Consulta derivada.` | 115 | |

### Bloques curados

`includes/class-curaduria.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 486 | `No tenés autorización para hacer esto.` | 55 | |
| 487 | `Curaduría guardada. La portada ya refleja los cambios.` | 76 | |

## wp-admin y mensajes de sistema

Pantallas de administración y respuestas de las acciones.

### Pantallas de wp-admin

`includes/class-admin.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 488 | `Portal Turismo` | 38 | |
| 489 | `Usuarios` | 41 | |
| 490 | `Invitaciones` | 42 | |
| 491 | `Registros` | 43 | |
| 492 | `Actualizaciones` | 45 | |
| 493 | `No tenés autorización para hacer esto.` | 60 | |
| 494 | `Usuarios del portal` | 73 | |
| 495 | `Editar: %s` ⚠️ | 77 | |
| 496 | `Nombre` | 84 | |
| 497 | `Email` | 85 | |
| 498 | `Teléfono` | 86 | |
| 499 | `Rol` | 87 | |
| 500 | `Restablecer contraseña` | 91 | |
| 501 | `Generar una nueva y mostrarla` | 91 | |
| 502 | `Guardar cambios` | 93 | |
| 503 | `Cancelar` | 94 | |
| 504 | `Usuario` | 101 | |
| 505 | `Estado` | 103 | |
| 506 | `Suspendido` | 116 | |
| 507 | `Activo` | 116 | |
| 508 | `Editar` | 118 | |
| 509 | `Reactivar` | 119 | |
| 510 | `Suspender` | 119 | |
| 511 | `Eliminar` | 120 | |
| 512 | `¿Seguro? Esta acción no se puede deshacer.` | 132 | |
| 513 | `El usuario no es válido.` | 151 | |
| 514 | `No podés modificar a un administrador ni tu propia cuenta desde acá.` | 155 | |
| 515 | `Usuario actualizado.` | 172 | |
| 516 | `Nueva contraseña: %s` ⚠️ | 176 | |
| 517 | `Usuario suspendido. Su sesión fue cerrada.` | 187 | |
| 518 | `Usuario reactivado.` | 193 | |
| 519 | `Usuario eliminado. Su contenido se reasignó a tu cuenta.` | 200 | |
| 520 | `Crear invitación` | 220 | |
| 521 | `Email (opcional)` | 229 | |
| 522 | `Expira (días)` | 230 | |
| 523 | `Cantidad` | 231 | |
| 524 | `Generar enlace(s)` | 233 | |
| 525 | `Invitaciones recientes` | 236 | |
| 526 | `Expira` | 240 | |
| 527 | `Enlace` | 241 | |
| 528 | `Revocar` | 262 | |
| 529 | `Invitación(es) creada(s):` | 287 | |
| 530 | `Invitación revocada.` | 290 | |
| 531 | `Entradas` | 313 | |
| 532 | `Fecha` | 317 | |
| 533 | `Acción` | 318 | |
| 534 | `Elemento` | 318 | |
| 535 | `IP` | 319 | |
| 536 | `Detalle` | 319 | |
| 537 | `No hay registros.` | 323 | |
| 538 | `Actualizaciones del portal` | 380 | |
| 539 | `No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.` | 384 | |
| 540 | `Atención: la versión del encabezado del plugin (%1$s) no coincide con PROMOTUR_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.` ⚠️ | 391 | |
| 541 | `Versión instalada` | 400 | |
| 542 | `Última disponible` | 401 | |
| 543 | `Actualizar ahora` | 410 | |
| 544 | `Estás al día.` | 412 | |
| 545 | `Última comprobación` | 415 | |
| 546 | `nunca` 🔡 | 416 | |
| 547 | `Repositorio` | 418 | |
| 548 | `Buscar actualizaciones ahora` | 427 | |
| 549 | `Limpiar caché del actualizador` | 433 | |
| 550 | `Token de GitHub` | 437 | |
| 551 | `Definido en wp-config.php mediante PROMOTUR_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.` | 439 | |
| 552 | `El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.` | 441 | |
| 553 | `Token` | 447 | |
| 554 | `•••• guardado (dejá vacío para conservarlo)` | 448 | |
| 555 | `Eliminar el token guardado` | 450 | |
| 556 | `Guardar token` | 454 | |
| 557 | `Hay una nueva versión disponible: %s.` ⚠️ | 473 | |
| 558 | `No hay actualizaciones: ya tenés la última versión.` | 475 | |
| 559 | `El verificador de actualizaciones no está disponible.` | 478 | |
| 560 | `Caché del actualizador limpiada.` | 487 | |
| 561 | `El token está definido en wp-config.php y no se puede cambiar desde acá.` | 492 | |
| 562 | `Token eliminado.` | 499 | |
| 563 | `Token guardado.` | 502 | |
| 564 | `No hubo cambios en el token.` | 504 | |

### Respuestas del editor

`includes/class-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 565 | `Tu sesión venció. Recargá la página.` | 38 | |
| 566 | `Necesitás iniciar sesión.` | 41 | |
| 567 | `No tenés permiso para hacer esto.` | 44 | |
| 568 | `No podés editar esta ficha.` | 83 | |
| 569 | `(sin título)` | 93 | |
| 570 | `Borrador guardado.` | 127 | |
| 571 | `Guardado. Como editaste una ficha publicada, tendrá que pasar por una nueva revisión.` | 135 | |
| 572 | `La ficha no es válida.` | 152 | |
| 573 | `Faltan datos obligatorios. Completá el checklist antes de enviarla.` | 156 | |
| 574 | `Publicación directa por nivel de confianza. Se hará una auditoría posterior.` | 163 | |
| 575 | `¡Publicado! Se aplicó tu nivel de confianza.` | 164 | |
| 576 | `¡Ficha enviada a revisión!` | 167 | |
| 577 | `Te asignaste la revisión.` | 178 | |
| 578 | `Ficha aprobada y publicada.` | 193 | |
| 579 | `Escribí los comentarios para el autor.` | 205 | |
| 580 | `Ficha devuelta al autor con comentarios.` | 209 | |
| 581 | `No recibimos ninguna imagen.` | 216 | |
| 582 | `Solo podés subir imágenes.` | 220 | |

### Respuestas de gestión

`includes/class-gestion-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 583 | `Tu sesión venció. Recargá la página.` | 28 | |
| 584 | `No tenés permiso para hacer esto.` | 31 | |
| 585 | `Tarea creada.` | 48 | |
| 586 | `La tarea no es válida.` | 55 | |
| 587 | `Reclamaste esta tarea. Ya podés trabajar en ella.` | 58 | |
| 588 | `Tarea completada. 🎉` | 72 | |
| 589 | `El usuario no es válido.` | 80 | |
| 590 | `Nivel actualizado.` | 83 | |

### Selector de idioma

`includes/class-i18n.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 591 | `Idioma` | 82 | |

### Avisos del plugin

`caaguazu-portal.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 592 | `Caaguazú Portal necesita tener activo el plugin «Caaguazú Cuentas» para funcionar. El inicio de sesión de los Promotores ya no usa los usuarios de WordPress. Activá el plugin desde Plugins para volver a usar el panel.` | 91 | |
| 593 | `Portal de Promotores` | 112 | |
