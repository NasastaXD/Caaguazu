# Textos del panel

Todo lo que un usuario lee en el panel, sacado de las fuentes el 2026-08-27. **565 textos.**

Se regenera con `php tools/textos-del-panel.php > docs/textos-del-panel.md`.

- Escribí el reemplazo en la columna **Nuevo texto**; lo que quede en blanco se deja como está.
- Los marcados con ⚠️ llevan un hueco (`%s`, `%d`, `%1$s`) que el código rellena: hay que conservarlo tal cual y en el mismo orden.
- Los `[FALTA: …]` son huecos a propósito: textos que el diseño pide y que todavía no escribió nadie.
- Los marcados con 🔡 arrancan en minúscula.

## Empiezan en minúscula

Casi todas son fragmentos escritos para leerse **después de un número** ("4 esperan revisión") o para ir dentro de una frase. Si se quieren usar como título, hay que reescribirlas enteras, no sólo poner la mayúscula.

| # | Texto | Dónde |
| --- | --- | --- |
| 52 | `atrás` | Notificaciones |
| 182 | `sin texto` | Cola de revisión |
| 194 | `revisa %s` | Cola de revisión |
| 210 | `vence %s` | Tareas |
| 280 | `opcional` | Biblioteca |
| 349 | `opcional` | Mi perfil |
| 518 | `nunca` | Pantallas de wp-admin |

## El armazón

Lo que se ve en todas las pantallas.

### Menú lateral (rótulos y secciones)

`includes/helpers.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 1 | `Administrador` | 162 | |
| 2 | `Invitado` | 164 | |
| 3 | `—` | 258 | |
| 4 | `Subir foto` | 275 | |
| 5 | `GESTIÓN` | 521 | |
| 6 | `Inicio` | 523 | |
| 7 | `Mis contenidos` | 526 | |
| 8 | `Nueva ficha` | 530 | |
| 9 | `Salida de campo` | 531 | |
| 10 | `Cola de revisión` | 534 | |
| 11 | `Tareas` | 535 | |
| 12 | `CONTENIDO` | 539 | |
| 13 | `Inventario turístico` | 541 | |
| 14 | `Artículos` | 542 | |
| 15 | `Recorridos` | 543 | |
| 16 | `PORTAL` | 547 | |
| 17 | `Equipo` | 549 | |
| 18 | `Reportes` | 550 | |
| 19 | `Biblioteca` | 551 | |
| 20 | `Estructura` | 552 | |
| 21 | `App` | 560 | |
| 22 | `Mi perfil` | 580 | |
| 23 | `Ayuda` | 581 | |

### Menú lateral (pie)

`templates/partials/sidebar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 24 | `Abrir menú` | 47 | |
| 25 | `Buscar…` | 77 | |
| 26 | `Buscar` | 78 | |
| 27 | `Navegación del panel` | 82 | |
| 28 | `Instalar app` | 111 | |
| 29 | `Cerrar sesión` | 115 | |

### Barra superior

`templates/partials/topbar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 30 | `Abrir menú` | 21 | |
| 31 | `Navegación del panel` | 25 | |
| 32 | `Inicio` | 30 | |
| 33 | `Buscar…` | 39 | |
| 34 | `Buscar` | 39 | |
| 35 | `Cambiar tema` | 42 | |
| 36 | `Notificaciones` | 48 | |
| 37 | `Marcar todo como leído` | 60 | |
| 38 | `No hay novedades por ahora. ✨` | 66 | |

### Barra inferior (teléfono)

`templates/partials/bottomnav.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 39 | `Inicio` | 10 | |
| 40 | `Contenidos` | 11 | |
| 41 | `Campo` | 12 | |
| 42 | `Revisar` | 13 | |
| 43 | `Perfil` | 14 | |
| 44 | `Navegación rápida` | 20 | |

### Mensajes del JavaScript

`includes/class-assets.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 45 | `Instalar app` | 73 | |
| 46 | `Enviando…` | 74 | |
| 47 | `Algo salió mal. Probá de nuevo.` | 75 | |
| 48 | `Guardado` | 76 | |
| 49 | `¿Querés confirmar esta acción?` | 77 | |
| 50 | `Faltan algunos datos obligatorios.` | 78 | |

### Notificaciones

`includes/class-notifications.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 51 | `«%s» está esperando revisión` ⚠️ | 48 | |
| 52 | `atrás` 🔡 | 50 | |
| 53 | `«%s» necesita algunos cambios` ⚠️ | 72 | |
| 54 | `Notificaciones marcadas como leídas.` | 135 | |

### Estados del flujo editorial

`includes/class-editorial.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 55 | `Ficha` | 97 | |
| 56 | `Borrador` | 137 | |
| 57 | `Enviado` | 138 | |
| 58 | `En revisión` | 139 | |
| 59 | `Necesita cambios` | 140 | |
| 60 | `Aprobado` | 141 | |
| 61 | `Publicado` | 142 | |
| 62 | `Despublicado` | 143 | |
| 63 | `Archivado` | 144 | |
| 64 | `Título` | 247 | |
| 65 | `Nombre del recorrido` | 249 | |
| 66 | `Nombre del destino` | 251 | |
| 67 | `Faltan fuentes o referencias.` | 269 | |
| 68 | `Mejorá las fotos: cuidá la luz, el encuadre y la portada.` | 270 | |
| 69 | `Verificá los horarios y los costos.` | 271 | |
| 70 | `Revisá la ortografía y la redacción.` | 272 | |
| 71 | `Comprobá que el enlace de Google Maps caiga en el lugar correcto.` | 273 | |
| 72 | `Revisá el orden de las paradas: no cuenta lo mismo al revés.` | 274 | |

### Guardas de las acciones

`includes/class-acciones.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 73 | `Esa acción no existe.` | 147 | |
| 74 | `Esa acción sólo acepta envíos.` | 151 | |
| 75 | `Tu sesión venció. Volvé a entrar.` | 158 | |
| 76 | `Tu sesión venció. Recargá la página.` | 165 | |
| 77 | `No tenés autorización para hacer esto.` | 170 | |

### Nombres de los roles

`includes/class-roles.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 78 | `Promotor` | 63 | |
| 79 | `Mini Promotor` | 64 | |
| 80 | `Visitante` | 65 | |

## Las secciones

Una tabla por pantalla del panel.

### Inicio

`templates/sections/home.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 81 | `Esperan revisión` | 35 | |
| 82 | `Publicados` | 36 | |
| 83 | `Esperan tu corrección` | 39 | |
| 84 | `En proceso` | 40 | |
| 85 | `Inicio` | 52 | |
| 86 | `Tu actividad de hoy` | 57 | |
| 87 | `Hola, %s 👋` ⚠️ | 60 | |
| 88 | `Actividad reciente` | 85 | |
| 89 | `Actividad de los últimos %d día` ⚠️ | 108 | |
| 90 | `Actividad de los últimos %d días` ⚠️ | 108 | |
| 91 | `Accesos rápidos` | 124 | |
| 92 | `Crear una ficha` | 129 | |
| 93 | `Mis contenidos` | 132 | |
| 94 | `Cola de revisión` | 135 | |
| 95 | `Equipo` | 138 | |
| 96 | `Mi perfil` | 140 | |

### Mis contenidos

`templates/sections/mis-contenidos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 97 | `Mis contenidos` | 22 | |
| 98 | `Tu producción` | 27 | |
| 99 | `+ Nueva ficha` | 30 | |
| 100 | `Todavía no creaste nada. Podés empezar por una ficha, un artículo o un recorrido.` | 35 | |
| 101 | `Nueva ficha` | 37 | |
| 102 | `Nuevo artículo` | 38 | |
| 103 | `Nuevo recorrido` | 39 | |
| 104 | `(sin título)` | 52 | |

### Editor de ficha

`templates/sections/editor.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 105 | `No podés editar esta ficha.` | 16 | |
| 106 | `Editar ficha` | 28 | |
| 107 | `Nueva ficha` | 28 | |
| 108 | `Ficha del destino` | 36 | |
| 109 | `Comentarios del revisor` | 44 | |
| 110 | `Nombre del destino` | 61 | |
| 111 | `Descripción` | 66 | |
| 112 | `Clasificación` | 71 | |
| 113 | `Categoría` | 74 | |
| 114 | `Zona` | 75 | |
| 115 | `Etiquetas` | 78 | |
| 116 | `Separadas por comas: «con niños», «gratis», «llega colectivo».` | 80 | |
| 117 | `📍 Usar mi ubicación actual` | 96 | |
| 118 | `Guardar borrador` | 103 | |
| 119 | `Enviar a revisión` | 104 | |
| 120 | `Checklist de mínimos` | 111 | |
| 121 | `Completá estos puntos antes de enviar la ficha a revisión.` | 112 | |

### Campos de la ficha

`includes/class-destinos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 122 | `Destinos` | 63 | |
| 123 | `Destino` | 64 | |
| 124 | `Nuevo destino` | 65 | |
| 125 | `Editar destino` | 66 | |
| 126 | `Buscar destinos` | 67 | |
| 127 | `Categorías` | 112 | |
| 128 | `Categoría` | 112 | |
| 129 | `Zonas` | 115 | |
| 130 | `Zona` | 115 | |
| 131 | `Etiquetas` | 119 | |
| 132 | `Etiqueta` | 119 | |
| 133 | `Identidad` | 133 | |
| 134 | `Gancho (una línea)` | 135 | |
| 135 | `Foto de portada` | 136 | |
| 136 | `Crédito de las fotos` | 137 | |
| 137 | `Video (URL, opcional)` | 138 | |
| 138 | `Ubicación` | 158 | |
| 139 | `Enlace de Google Maps` | 161 | |
| 140 | `Buscá el lugar en Google Maps, tocá «Compartir» y pegá acá el enlace. De ahí sacamos el pin solos.` | 165 | |
| 141 | `Latitud (alternativa al enlace)` | 168 | |
| 142 | `Sólo si el enlace no alcanza: un enlace corto, o un lugar que Google no tiene.` | 171 | |
| 143 | `Longitud (alternativa al enlace)` | 174 | |
| 144 | `Estado del camino` | 178 | |
| 145 | `Accesibilidad` | 179 | |
| 146 | `Datos prácticos` | 191 | |
| 147 | `Horario y mejor momento para visitar` | 193 | |
| 148 | `Costo / entrada` | 194 | |
| 149 | `Rango de precio` | 202 | |
| 150 | `Sin especificar` | 206 | |
| 151 | `Gratis` | 207 | |
| 152 | `$ — Muy barato` | 208 | |
| 153 | `$$ — Barato` | 209 | |
| 154 | `$$$ — Intermedio` | 210 | |
| 155 | `$$$$ — Caro` | 211 | |
| 156 | `Contacto del lugar` | 214 | |
| 157 | `Fuentes y referencias` | 218 | |
| 158 | `Fuentes / referencias` | 220 | |
| 159 | `Descripción` | 362 | |
| 160 | `Ubicación (enlace de Google Maps o coordenadas)` | 367 | |

### Salida de campo

`templates/sections/captura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 161 | `Salida de campo` | 8 | |
| 162 | `Captura en el lugar` | 11 | |
| 163 | `Sacá una foto, anotá lo importante y guardá la ubicación, incluso si no tenés señal. Todo queda guardado en tu dispositivo y podés sincronizarlo como borrador cuando vuelva la conexión.` | 13 | |
| 164 | `Nombre del lugar` | 17 | |
| 165 | `Nota rápida` | 18 | |
| 166 | `Foto` | 21 | |
| 167 | `Ubicación (GPS)` | 25 | |
| 168 | `Tomar ubicación` | 27 | |
| 169 | `Guardar captura` | 33 | |
| 170 | `Capturas pendientes` | 39 | |
| 171 | `Sincronizar` | 40 | |

### Cola de revisión

`templates/sections/revision.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 172 | `No encontramos esto.` | 19 | |
| 173 | `Revisión` | 28 | |
| 174 | `Volver a la cola` | 33 | |
| 175 | `Por %s` ⚠️ | 39 | |
| 176 | `Entradilla` | 74 | |
| 177 | `Cuerpo` | 80 | |
| 178 | `Descripción` | 80 | |
| 179 | `Ubicación` | 87 | |
| 180 | `Abrir el pin en Google Maps` | 88 | |
| 181 | `Paradas, en orden` | 93 | |
| 182 | `sin texto` 🔡 | 99 | |
| 183 | `Acciones` | 130 | |
| 184 | `Asignarme la revisión` | 133 | |
| 185 | `Comentarios para el autor` | 137 | |
| 186 | `Qué corregir o mejorar…` | 138 | |
| 187 | `Devolver con cambios` | 147 | |
| 188 | `Aprobar y publicar` | 149 | |
| 189 | `Historial` | 157 | |
| 190 | `Cola de revisión` | 185 | |
| 191 | `Taller editorial` | 188 | |
| 192 | `No hay nada esperando revisión. 🎉` | 192 | |
| 193 | `%1$s · esperó %2$s` ⚠️ | 208 | |
| 194 | `revisa %s` ⚠️ 🔡 | 213 | |

### Tareas

`templates/sections/tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 195 | `Tareas` | 11 | |
| 196 | `Asignaciones` | 14 | |
| 197 | `Tareas y pendientes por cubrir` | 15 | |
| 198 | `+ Nueva tarea o hueco` | 19 | |
| 199 | `Título` | 21 | |
| 200 | `Detalle` | 22 | |
| 201 | `Tipo` | 24 | |
| 202 | `Tarea asignada` | 26 | |
| 203 | `Hueco disponible` | 27 | |
| 204 | `Vence` | 30 | |
| 205 | `Destino (opcional)` | 31 | |
| 206 | `Asignar a (Mini Promotores)` | 37 | |
| 207 | `Crear` | 42 | |
| 208 | `No hay tareas por ahora.` | 49 | |
| 209 | `Hueco` | 61 | |
| 210 | `vence %s` ⚠️ 🔡 | 63 | |
| 211 | `Reclamar` | 69 | |
| 212 | `Marcar como completada` | 72 | |

### Tareas (estados y avisos)

`includes/class-tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 213 | `Tareas` | 27 | |
| 214 | `Tarea` | 27 | |
| 215 | `Pendiente` | 38 | |
| 216 | `En curso` | 39 | |
| 217 | `Completada` | 40 | |
| 218 | `La tarea necesita un título.` | 58 | |

### Equipo

`templates/sections/equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 219 | `Equipo` | 17 | |
| 220 | `Tu equipo` | 20 | |
| 221 | `Invitar a alguien` | 24 | |
| 222 | `Generá un enlace de invitación con el rol que quieras. El enlace es válido durante 14 días.` | 25 | |
| 223 | `Mini Promotor` | 29 | |
| 224 | `Promotor` | 30 | |
| 225 | `Visitante` | 31 | |
| 226 | `Crear enlace` | 33 | |
| 227 | `%1$d publicadas · %2$d en total` ⚠️ | 54 | |
| 228 | `Nivel de confianza:` | 64 | |
| 229 | `Guardar` | 71 | |
| 230 | `Suspendida` | 80 | |
| 231 | `Rol` | 87 | |
| 232 | `Cambiar rol` | 92 | |
| 233 | `Reactivar` | 99 | |
| 234 | `Suspender` | 99 | |
| 235 | `Deja de tener acceso al panel. Su cuenta y lo que publicó quedan como están. ¿Seguimos?` | 104 | |
| 236 | `Sacar del panel` | 107 | |
| 237 | `Invitaciones abiertas` | 117 | |
| 238 | `No hay ninguna esperando. Los enlaces que crees acá arriba aparecen en esta lista hasta que alguien los use o se venzan.` | 120 | |
| 239 | `Vence el %s` ⚠️ | 133 | |
| 240 | `El enlace deja de servir. ¿Seguimos?` | 139 | |
| 241 | `Revocar` | 142 | |

### Equipo (avisos)

`includes/class-equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 242 | `Esa persona no existe.` | 51 | |
| 243 | `A vos mismo no te podés editar desde acá.` | 54 | |
| 244 | `Ese rol no existe.` | 72 | |
| 245 | `%1$s ahora es %2$s.` ⚠️ | 85 | |
| 246 | `Suspendimos a %s. No va a poder entrar hasta que la reactives.` ⚠️ | 114 | |
| 247 | `%s puede volver a entrar.` ⚠️ | 119 | |
| 248 | `%s ya no entra al panel. Su cuenta y lo que publicó quedan como están.` ⚠️ | 142 | |
| 249 | `Esa invitación ya no existe.` | 150 | |
| 250 | `Invitación revocada. Ese enlace ya no sirve.` | 153 | |

### Reportes

`templates/sections/reportes.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 251 | `Reportes` | 12 | |
| 252 | `Métricas` | 15 | |
| 253 | `Actividad del portal` | 16 | |
| 254 | `Producción por autor` | 18 | |
| 255 | `%1$d publicadas / %2$d` ⚠️ | 27 | |
| 256 | `Estado del contenido` | 33 | |
| 257 | `Fichas publicadas sin portada` | 36 | |
| 258 | `Fichas sin verificar hace +6 meses` | 46 | |

### Biblioteca

`templates/sections/biblioteca.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 259 | `Biblioteca` | 21 | |
| 260 | `Medios` | 26 | |
| 261 | `Biblioteca de medios` | 27 | |
| 262 | `Subir fotos` | 37 | |
| 263 | `Podés elegir varias de una vez. JPG, PNG, WEBP o GIF.` | 40 | |
| 264 | `Subir` | 42 | |
| 265 | `Buscar una foto` | 50 | |
| 266 | `Sólo las mías` | 54 | |
| 267 | `Filtrar` | 56 | |
| 268 | `No encontramos ninguna foto con ese nombre.` | 64 | |
| 269 | `Todavía no hay fotos. Subí las primeras acá arriba.` | 66 | |
| 270 | `%d foto` ⚠️ | 77 | |
| 271 | `%d fotos` ⚠️ | 77 | |
| 272 | `Sin descripción` | 101 | |
| 273 | `Es la portada de %d ficha.` ⚠️ | 112 | |
| 274 | `Es la portada de %d fichas.` ⚠️ | 112 | |
| 275 | `Esta foto la subió otra persona, así que sólo podés verla.` | 120 | |
| 276 | `Nombre` | 128 | |
| 277 | `Descripción` | 132 | |
| 278 | `Qué se ve en la foto` | 134 | |
| 279 | `Crédito` | 137 | |
| 280 | `opcional` 🔡 | 137 | |
| 281 | `Quién la sacó` | 139 | |
| 282 | `Guardar` | 143 | |
| 283 | `Se borra la foto y no se puede deshacer. ¿Seguimos?` | 149 | |
| 284 | `Borrar foto` | 152 | |
| 285 | `Anteriores` | 165 | |
| 286 | `Página %1$d de %2$d` ⚠️ | 171 | |
| 287 | `Siguientes` | 179 | |

### Biblioteca (avisos)

`includes/class-medios.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 288 | `No elegiste ninguna foto.` | 141 | |
| 289 | `No pudimos subir ninguna: revisá que sean JPG, PNG, WEBP o GIF.` | 182 | |
| 290 | `Subimos %1$d foto. %2$d quedó afuera por el formato.` ⚠️ | 187 | |
| 291 | `Subimos %1$d fotos. %2$d quedaron afuera por el formato.` ⚠️ | 187 | |
| 292 | `Subimos %d foto.` ⚠️ | 194 | |
| 293 | `Subimos %d fotos.` ⚠️ | 194 | |
| 294 | `Esa foto no existe.` | 218 | |
| 295 | `Esa foto la subió otra persona.` | 221 | |
| 296 | `Listo, guardamos la foto.` | 234 | |
| 297 | `No la borramos: es la portada de %d ficha. Cambiala ahí primero.` ⚠️ | 250 | |
| 298 | `No la borramos: es la portada de %d fichas. Cambiala ahí primero.` ⚠️ | 250 | |
| 299 | `Foto borrada.` | 259 | |

### Estructura

`templates/sections/estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 300 | `Estructura` | 16 | |
| 301 | `Organización` | 21 | |
| 302 | `Estructura del sitio` | 22 | |
| 303 | `Esto lo organiza un Promotor. Podés ver cómo está armado, pero no cambiarlo.` | 28 | |
| 304 | `Todavía no hay ninguna.` | 40 | |
| 305 | `Guardar` | 53 | |
| 306 | `%d ficha` ⚠️ | 64 | |
| 307 | `%d fichas` ⚠️ | 64 | |
| 308 | `Se borra y no se puede deshacer. ¿Seguimos?` | 71 | |
| 309 | `Borrar` | 75 | |
| 310 | `Agregar` | 92 | |
| 311 | `El ícono y el color con que la app muestra cada categoría se eligen en App →` | 100 | |

### Estructura (nombres y avisos)

`includes/class-estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 312 | `Categorías` | 42 | |
| 313 | `Categoría` | 43 | |
| 314 | `De qué tipo es el lugar: salto, museo, feria. La app agrupa por acá y les pone ícono y color.` | 44 | |
| 315 | `Zonas` | 47 | |
| 316 | `Zona` | 48 | |
| 317 | `Dónde queda: el distrito o la región del departamento.` | 49 | |
| 318 | `Etiquetas` | 52 | |
| 319 | `Etiqueta` | 53 | |
| 320 | `Lo que no entra en las otras dos: «con niños», «gratis», «llega colectivo».` | 54 | |
| 321 | `Eso no se puede editar desde acá.` | 79 | |
| 322 | `Escribí un nombre.` | 88 | |
| 323 | `Ya existe una con ese nombre.` | 91 | |
| 324 | `Creamos «%s».` ⚠️ | 106 | |
| 325 | `Listo, cambiamos el nombre.` | 121 | |
| 326 | `Eso ya no existe.` | 129 | |
| 327 | `No la borramos: %d ficha la usa. Movelas primero.` ⚠️ | 135 | |
| 328 | `No la borramos: %d fichas la usan. Movelas primero.` ⚠️ | 135 | |
| 329 | `Borramos «%s».` ⚠️ | 149 | |

### Buscar

`templates/sections/buscar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 330 | `Buscar` | 18 | |
| 331 | `Escribí algo en el buscador de arriba para encontrar fichas, artículos o recorridos.` | 23 | |
| 332 | `%1$d resultado para «%2$s»` ⚠️ | 28 | |
| 333 | `%1$d resultados para «%2$s»` ⚠️ | 28 | |
| 334 | `No encontramos resultados. Probá con otras palabras.` | 32 | |

### Mi perfil

`templates/sections/perfil.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 335 | `Mi perfil` | 26 | |
| 336 | `Tu progreso de confianza` | 42 | |
| 337 | `Nivel máximo: publicás directamente y después se hace una auditoría. Gracias por tu compromiso.` | 56 | |
| 338 | `Promotor Jr: podés editar fichas publicadas sin pasar por una nueva revisión. Seguí sumando aprobaciones para llegar a «De confianza».` | 58 | |
| 339 | `Aprendiz: todo tu contenido pasa por revisión. A medida que sumás aprobaciones, vas ganando autonomía.` | 60 | |
| 340 | `Fichas publicadas` | 70 | |
| 341 | `Mi portafolio` | 79 | |
| 342 | `Todavía no tenés nada publicado.` | 81 | |
| 343 | `Publicado` | 89 | |
| 344 | `Estás entrando como administrador del sitio, con acceso prestado. Este acceso no tiene cuenta del panel, así que no hay datos que editar acá.` | 97 | |
| 345 | `Mis datos` | 101 | |
| 346 | `Nombre` | 108 | |
| 347 | `Correo` | 115 | |
| 348 | `Teléfono` | 120 | |
| 349 | `opcional` 🔡 | 120 | |
| 350 | `Foto` | 126 | |
| 351 | `Con el correo entrás al panel: si lo cambiás, la próxima vez iniciás sesión con el nuevo.` | 129 | |
| 352 | `Guardar cambios` | 132 | |
| 353 | `Contraseña` | 137 | |
| 354 | `Contraseña actual` | 144 | |
| 355 | `Contraseña nueva` | 150 | |
| 356 | `Repetila` | 154 | |
| 357 | `Cambiar contraseña` | 160 | |

### Mi perfil (avisos)

`includes/class-cuenta.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 358 | `Estás entrando como administrador de WordPress, que no tiene cuenta del panel que editar.` | 50 | |
| 359 | `Escribí tu nombre.` | 58 | |
| 360 | `Ese correo no parece válido.` | 61 | |
| 361 | `Ya hay una cuenta con ese correo.` | 67 | |
| 362 | `No pudimos guardar los cambios. Probá de nuevo.` | 76 | |
| 363 | `Listo, guardamos tus datos.` | 79 | |
| 364 | `La foto tiene que ser JPG, PNG o WEBP.` | 102 | |
| 365 | `La contraseña actual no coincide.` | 137 | |
| 366 | `Las dos contraseñas nuevas tienen que ser iguales.` | 140 | |
| 367 | `No pudimos cambiar la contraseña. Probá de nuevo.` | 148 | |
| 368 | `Listo, cambiaste tu contraseña.` | 154 | |

### Niveles de confianza

`includes/class-stats.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 369 | `Aprendiz` | 34 | |
| 370 | `Promotor Jr` | 35 | |
| 371 | `De confianza` | 36 | |

### App (control de la app móvil)

`templates/sections/app.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 372 | `App` | 21 | |
| 373 | `Fuera de servicio` | 25 | |
| 374 | `La cabina de mando de la app está desconectada` | 26 | |
| 375 | `Los textos y los medios de la aplicación se editan por ahora desde la administración del sitio. Se vuelve a enchufar cuando la API de la app esté en la versión que esta pantalla necesita.` | 27 | |
| 376 | `Volver al inicio del panel` | 28 | |
| 377 | `Aplicación` | 52 | |
| 378 | `Textos` | 61 | |
| 379 | `Idioma` | 62 | |
| 380 | `Clave` | 88 | |
| 381 | `Texto` | 92 | |
| 382 | `Guardar cambios` | 99 | |
| 383 | `Medios` | 108 | |
| 384 | `Ir a la biblioteca` | 111 | |
| 385 | `Tipo` | 138 | |
| 386 | `Imagen` | 140 | |
| 387 | `Animación` | 141 | |
| 388 | `URL o ID` | 145 | |
| 389 | `Texto alternativo` | 149 | |
| 390 | `Formato` | 153 | |
| 391 | `Categorías` | 170 | |
| 392 | `Todavía no hay categorías cargadas. Se crean en Estructura y después se les elige acá el icono y el color.` | 174 | |
| 393 | `Estructura` | 175 | |
| 394 | `Nombre` | 185 | |
| 395 | `Color` | 189 | |
| 396 | `Icono` | 194 | |

### App (avisos)

`includes/class-app-control.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 397 | `No tenés autorización para hacer esto.` | 134 | |
| 398 | `Guardado` | 195 | |

### Ayuda

`templates/sections/ayuda.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 399 | `Ayuda` | 5 | |
| 400 | `Inicio` | 9 | |
| 401 | `Tu resumen del día: fichas que esperan revisión, contenido que necesita correcciones y accesos rápidos según tu rol.` | 9 | |
| 402 | `Nueva ficha` | 10 | |
| 403 | `El editor guiado para crear destinos. Completá los campos y el checklist; el sistema te avisa si falta algo antes de enviar la ficha a revisión.` | 10 | |
| 404 | `Salida de campo` | 11 | |
| 405 | `Sacá fotos, anotá información y guardá la ubicación GPS mientras estás en el lugar, incluso sin señal. Después podés sincronizar todo como borrador cuando vuelva la conexión.` | 11 | |
| 406 | `Mis contenidos` | 12 | |
| 407 | `Todas tus fichas, ordenadas por estado: borrador, enviada, en revisión, necesita cambios o publicada.` | 12 | |
| 408 | `Cola de revisión` | 13 | |
| 409 | `Para Promotores: revisá las fichas enviadas, asignate una, aprobala y publicala o devolvela con comentarios para que el autor haga los cambios necesarios.` | 13 | |
| 410 | `Tareas` | 14 | |
| 411 | `Asignaciones con fecha límite y una lista de lo que todavía falta cubrir. Los Mini Promotores pueden reclamar los huecos disponibles.` | 14 | |
| 412 | `Curaduría` | 15 | |
| 413 | `Elegí qué destinos aparecen destacados en la portada y configurá un banner de temporada. Los cambios se reflejan en la web pública sin tocar el código.` | 15 | |
| 414 | `Moderación` | 16 | |
| 415 | `Aprobá o descartá reseñas, respondé o derivá consultas de visitantes y atendé los reportes de información desactualizada.` | 16 | |
| 416 | `Equipo` | 17 | |
| 417 | `Gestioná a los Mini Promotores: revisá su producción, nivel de confianza y enlaces de invitación.` | 17 | |
| 418 | `Reportes` | 18 | |
| 419 | `Consultá la producción por autor, los destinos más vistos, las búsquedas sin resultado y el estado general del contenido.` | 18 | |
| 420 | `Mi perfil` | 19 | |
| 421 | `Consultá tu portafolio público, las vistas de tus fichas y tu progreso de nivel de confianza.` | 19 | |
| 422 | `Cómo funciona` | 22 | |
| 423 | `¿Qué hace cada sección?` | 23 | |
| 424 | `Este es el portal de los Promotores Turísticos: una web turística pública con un espacio de trabajo editorial detrás. Los Mini Promotores crean las fichas de destino y los Promotores las revisan y publican.` | 25 | |
| 425 | `El flujo editorial` | 28 | |
| 426 | `Borrador` | 31 | |
| 427 | `Enviado` | 32 | |
| 428 | `En revisión` | 33 | |
| 429 | `Necesita cambios` | 34 | |
| 430 | `Publicado` | 35 | |
| 431 | `Solo las fichas aprobadas por un Promotor llegan al público. La confianza se construye con cada aprobación: pasás de Aprendiz a Promotor Jr y luego a De confianza. Cada nivel te da más autonomía, como editar fichas publicadas sin una nueva revisión y, finalmente, publicar directamente.` | 38 | |
| 432 | `Las secciones` | 42 | |
| 433 | `Extras` | 56 | |
| 434 | `Podés instalar el portal como app (PWA) y consultar parte del contenido sin conexión desde el menú lateral.` | 58 | |
| 435 | `Cada ficha pública puede tener reseñas, indicaciones para llegar, un código QR para imprimir y un botón para agregarla a «Mi viaje».` | 59 | |
| 436 | `Podés cambiar entre modo claro y oscuro desde la barra superior.` | 60 | |
| 437 | `El acceso es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 61 | |

### Sección inexistente

`templates/sections/404.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 438 | `No encontramos esa sección` | 7 | |
| 439 | `Error 404` | 11 | |
| 440 | `Esta sección no existe` | 12 | |
| 441 | `Puede que el enlace esté roto o que no tengas permiso para acceder.` | 13 | |
| 442 | `Volver al inicio del panel` | 14 | |

## Entrar y salir

Acceso, registro, recuperación, invitaciones y errores de permiso.

### Iniciar sesión

`templates/auth/login.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 443 | `Iniciar sesión` | 12 | |
| 444 | `Entrá al panel de Promotores Turísticos.` | 16 | |
| 445 | `Tu contraseña se actualizó. Ya podés iniciar sesión.` | 19 | |
| 446 | `Email` | 34 | |
| 447 | `Contraseña` | 38 | |
| 448 | `Mantener la sesión iniciada` | 42 | |
| 449 | `Entrar` | 45 | |
| 450 | `¿Olvidaste tu contraseña?` | 49 | |
| 451 | `Acceso solo por invitación` | 50 | |

### Registro

`templates/auth/registro.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 452 | `Crear cuenta` | 14 | |
| 453 | `Esta invitación ya fue usada.` | 27 | |
| 454 | `Esta invitación venció. Pedí una nueva al equipo.` | 28 | |
| 455 | `Esta invitación fue revocada.` | 29 | |
| 456 | `El registro es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 30 | |
| 457 | `Ya tengo una cuenta` | 35 | |
| 458 | `Invitación válida: te unirás como %s.` ⚠️ | 44 | |
| 459 | `Nombre de usuario` | 55 | |
| 460 | `Email` | 59 | |
| 461 | `Teléfono` | 63 | |
| 462 | `Ej.: 0981 123 456` | 64 | |
| 463 | `Contraseña (6 o más caracteres)` | 67 | |

### Recuperar contraseña

`templates/auth/recuperar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 464 | `Recuperar contraseña` | 10 | |
| 465 | `Te enviamos un enlace para restablecer tu contraseña.` | 14 | |
| 466 | `Email` | 27 | |
| 467 | `Enviar enlace` | 30 | |
| 468 | `Volver a iniciar sesión` | 34 | |

### Contraseña nueva

`templates/auth/restablecer.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 469 | `Nueva contraseña` | 12 | |
| 470 | `Nueva contraseña (6 o más caracteres)` | 28 | |
| 471 | `Guardar contraseña` | 31 | |
| 472 | `El enlace no es válido o ya venció. Pedí uno nuevo.` | 34 | |
| 473 | `Pedir un nuevo enlace` | 36 | |

### Marco de acceso

`templates/auth-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 474 | `Acceso` | 8 | |

### Errores y avisos de acceso

`includes/class-auth.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 475 | `No tenés autorización para hacer esto.` | 43 | |
| 476 | `Enlace de invitación creado. Es válido durante 14 días: %s` ⚠️ | 49 | |
| 477 | `Tu sesión venció. Recargá la página.` | 143 | |
| 478 | `Necesitás una invitación válida para registrarte.` | 182 | |
| 479 | `Completá usuario, email, teléfono y una contraseña de al menos 6 caracteres.` | 192 | |
| 480 | `Ese email ya está registrado.` | 196 | |
| 481 | `Si la cuenta existe, te enviamos un email con las instrucciones.` | 241 | |
| 482 | `El enlace para restablecer la contraseña venció o no es válido.` | 259 | |

### Invitaciones

`includes/class-invitations.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 483 | `Válida` | 99 | |
| 484 | `Usada` | 100 | |
| 485 | `Expirada` | 101 | |
| 486 | `Revocada` | 102 | |
| 487 | `Inválida` | 103 | |

### Guardas de acceso

`includes/class-router.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 488 | `No tenés acceso a este panel.` | 203 | |
| 489 | `Acceso denegado` | 204 | |

### Guardas de sección

`includes/class-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 490 | `No tenés permiso para ver esta sección.` | 42 | |
| 491 | `Acceso denegado` | 43 | |

### Sin conexión (PWA)

`includes/class-pwa.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 492 | `Sin conexión` | 169 | |
| 493 | `Promotores Turísticos` | 176 | |
| 494 | `Estás sin conexión` | 177 | |
| 495 | `No pudimos cargar esta pantalla. Revisá tu conexión e intentá de nuevo.` | 178 | |
| 496 | `Reintentar` | 179 | |

## wp-admin y mensajes de sistema

Pantallas de administración y respuestas de las acciones.

### Pantallas de wp-admin

`includes/class-admin.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 497 | `Portal Turismo` | 46 | |
| 498 | `Registros` | 49 | |
| 499 | `Actualizaciones` | 51 | |
| 500 | `No tenés autorización para hacer esto.` | 66 | |
| 501 | `Usuarios` | 95 | |
| 502 | `Entradas` | 96 | |
| 503 | `Fecha` | 100 | |
| 504 | `Usuario` | 100 | |
| 505 | `Acción` | 101 | |
| 506 | `Elemento` | 101 | |
| 507 | `IP` | 102 | |
| 508 | `Detalle` | 102 | |
| 509 | `No hay registros.` | 106 | |
| 510 | `Actualizaciones del portal` | 163 | |
| 511 | `No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.` | 167 | |
| 512 | `Atención: la versión del encabezado del plugin (%1$s) no coincide con PROMOTUR_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.` ⚠️ | 174 | |
| 513 | `Versión instalada` | 183 | |
| 514 | `Última disponible` | 184 | |
| 515 | `Actualizar ahora` | 193 | |
| 516 | `Estás al día.` | 195 | |
| 517 | `Última comprobación` | 198 | |
| 518 | `nunca` 🔡 | 199 | |
| 519 | `Repositorio` | 201 | |
| 520 | `Buscar actualizaciones ahora` | 210 | |
| 521 | `Limpiar caché del actualizador` | 216 | |
| 522 | `Token de GitHub` | 220 | |
| 523 | `Definido en wp-config.php mediante PROMOTUR_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.` | 222 | |
| 524 | `El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.` | 224 | |
| 525 | `Token` | 230 | |
| 526 | `•••• guardado (dejá vacío para conservarlo)` | 231 | |
| 527 | `Eliminar el token guardado` | 233 | |
| 528 | `Guardar token` | 237 | |
| 529 | `Hay una nueva versión disponible: %s.` ⚠️ | 256 | |
| 530 | `No hay actualizaciones: ya tenés la última versión.` | 258 | |
| 531 | `El verificador de actualizaciones no está disponible.` | 261 | |
| 532 | `Caché del actualizador limpiada.` | 270 | |
| 533 | `El token está definido en wp-config.php y no se puede cambiar desde acá.` | 275 | |
| 534 | `Token eliminado.` | 282 | |
| 535 | `Token guardado.` | 285 | |
| 536 | `No hubo cambios en el token.` | 287 | |

### Respuestas del editor

`includes/class-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 537 | `No tenés permiso para hacer esto.` | 61 | |
| 538 | `Ese tipo de contenido no existe.` | 74 | |
| 539 | `No podés editar esto.` | 112 | |
| 540 | `(sin título)` | 128 | |
| 541 | `Borrador guardado.` | 192 | |
| 542 | `Guardado. Como editaste algo ya publicado, tendrá que pasar por una nueva revisión.` | 199 | |
| 543 | `De ese enlace no pudimos sacar el pin (los enlaces cortos no lo traen). Cargá la latitud y la longitud a mano, o pegá el enlace largo.` | 239 | |
| 544 | `Un recorrido lleva hasta %d paradas, y sin repetir el mismo sitio. Guardamos las que entraron.` ⚠️ | 294 | |
| 545 | `Esto no se puede enviar.` | 319 | |
| 546 | `Faltan datos obligatorios. Completá el checklist antes de enviar.` | 323 | |
| 547 | `Publicación directa por nivel de confianza. Se hará una auditoría posterior.` | 333 | |
| 548 | `¡Publicado! Se aplicó tu nivel de confianza.` | 334 | |
| 549 | `¡Enviado a revisión!` | 338 | |
| 550 | `Eso no existe o no es contenido del panel.` | 351 | |
| 551 | `Te asignaste la revisión.` | 360 | |
| 552 | `Aprobado y publicado.` | 371 | |
| 553 | `Escribí los comentarios para el autor.` | 379 | |
| 554 | `Devuelto al autor con comentarios.` | 383 | |
| 555 | `No recibimos ninguna imagen.` | 390 | |
| 556 | `Solo podés subir imágenes.` | 394 | |

### Respuestas de gestión

`includes/class-gestion-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 557 | `No tenés permiso para hacer esto.` | 29 | |
| 558 | `Tarea creada.` | 46 | |
| 559 | `La tarea no es válida.` | 53 | |
| 560 | `Reclamaste esta tarea. Ya podés trabajar en ella.` | 56 | |
| 561 | `Tarea completada. 🎉` | 70 | |
| 562 | `El usuario no es válido.` | 78 | |
| 563 | `Nivel actualizado.` | 81 | |

### Avisos del plugin

`caaguazu-portal.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 564 | `Caaguazú Portal necesita tener activo el plugin «Caaguazú Cuentas» para funcionar. El inicio de sesión de los Promotores ya no usa los usuarios de WordPress. Activá el plugin desde Plugins para volver a usar el panel.` | 94 | |
| 565 | `Portal de Promotores` | 115 | |
