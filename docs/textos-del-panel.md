# Textos del panel

Todo lo que un usuario lee en el panel, sacado de las fuentes el 2026-10-08. **611 textos.**

Se regenera con `php tools/textos-del-panel.php > docs/textos-del-panel.md`.

- Escribí el reemplazo en la columna **Nuevo texto**; lo que quede en blanco se deja como está.
- Los marcados con ⚠️ llevan un hueco (`%s`, `%d`, `%1$s`) que el código rellena: hay que conservarlo tal cual y en el mismo orden.
- Los `[FALTA: …]` son huecos a propósito: textos que el diseño pide y que todavía no escribió nadie.
- Los marcados con 🔡 arrancan en minúscula.

## Empiezan en minúscula

Casi todas son fragmentos escritos para leerse **después de un número** ("4 esperan revisión") o para ir dentro de una frase. Si se quieren usar como título, hay que reescribirlas enteras, no sólo poner la mayúscula.

| # | Texto | Dónde |
| --- | --- | --- |
| 58 | `atrás` | Notificaciones |
| 219 | `sin texto` | Cola de revisión |
| 231 | `revisa %s` | Cola de revisión |
| 247 | `vence %s` | Tareas |
| 317 | `opcional` | Biblioteca |
| 343 | `una o dos líneas; encabeza la categoría en la app` | Estructura |
| 384 | `opcional` | Mi perfil |
| 386 | `opcional, JPG/PNG/WEBP, hasta 5 MB` | Mi perfil |
| 564 | `nunca` | Pantallas de wp-admin |

## El armazón

Lo que se ve en todas las pantallas.

### Menú lateral (rótulos y secciones)

`includes/helpers.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 1 | `Administrador` | 162 | |
| 2 | `Invitado` | 164 | |
| 3 | `Qué hacer con esto` | 265 | |
| 4 | `Está publicado: la app lo está mostrando. Lo que edites y guardes se ve ahí.` | 268 | |
| 5 | `¿Borrarlo? Va a la papelera y lo podés recuperar desde Mis contenidos.` | 283 | |
| 6 | `Borrar` | 284 | |
| 7 | `Para borrarlo, despublicalo primero.` | 287 | |
| 8 | `—` | 345 | |
| 9 | `Subir foto` | 365 | |
| 10 | `GESTIÓN` | 611 | |
| 11 | `Inicio` | 613 | |
| 12 | `Mis contenidos` | 616 | |
| 13 | `Nueva ficha` | 620 | |
| 14 | `Salida de campo` | 621 | |
| 15 | `Cola de revisión` | 624 | |
| 16 | `Tareas` | 625 | |
| 17 | `CONTENIDO` | 629 | |
| 18 | `Inventario turístico` | 631 | |
| 19 | `Artículos` | 632 | |
| 20 | `Recorridos` | 633 | |
| 21 | `PORTAL` | 637 | |
| 22 | `Equipo` | 639 | |
| 23 | `Reportes` | 640 | |
| 24 | `Biblioteca` | 641 | |
| 25 | `Estructura` | 642 | |
| 26 | `App` | 650 | |
| 27 | `Mi perfil` | 670 | |
| 28 | `Ayuda` | 671 | |

### Menú lateral (pie)

`templates/partials/sidebar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 29 | `Abrir menú` | 47 | |
| 30 | `Buscar…` | 77 | |
| 31 | `Buscar` | 78 | |
| 32 | `Navegación del panel` | 82 | |
| 33 | `Instalar app` | 111 | |
| 34 | `Cerrar sesión` | 115 | |

### Barra superior

`templates/partials/topbar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 35 | `Abrir menú` | 21 | |
| 36 | `Navegación del panel` | 25 | |
| 37 | `Inicio` | 30 | |
| 38 | `Buscar…` | 39 | |
| 39 | `Buscar` | 39 | |
| 40 | `Cambiar tema` | 42 | |
| 41 | `Notificaciones` | 48 | |
| 42 | `Marcar todo como leído` | 60 | |
| 43 | `No hay novedades por ahora. ✨` | 66 | |

### Barra inferior (teléfono)

`templates/partials/bottomnav.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 44 | `Inicio` | 10 | |
| 45 | `Contenidos` | 11 | |
| 46 | `Campo` | 12 | |
| 47 | `Revisar` | 13 | |
| 48 | `Perfil` | 14 | |
| 49 | `Navegación rápida` | 20 | |

### Mensajes del JavaScript

`includes/class-assets.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 50 | `Instalar app` | 135 | |
| 51 | `Enviando…` | 136 | |
| 52 | `Algo salió mal. Probá de nuevo.` | 137 | |
| 53 | `Guardado` | 138 | |
| 54 | `¿Querés confirmar esta acción?` | 139 | |
| 55 | `Faltan algunos datos obligatorios.` | 140 | |
| 56 | `Foto subida.` | 141 | |

### Notificaciones

`includes/class-notifications.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 57 | `«%s» está esperando revisión` ⚠️ | 81 | |
| 58 | `atrás` 🔡 | 83 | |
| 59 | `«%s» necesita algunos cambios` ⚠️ | 106 | |
| 60 | `Notificaciones marcadas como leídas.` | 181 | |

### Estados del flujo editorial

`includes/class-editorial.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 61 | `Ficha` | 97 | |
| 62 | `Borrador` | 137 | |
| 63 | `Enviado` | 138 | |
| 64 | `En revisión` | 139 | |
| 65 | `Necesita cambios` | 140 | |
| 66 | `Aprobado` | 141 | |
| 67 | `Publicado` | 142 | |
| 68 | `Despublicado` | 143 | |
| 69 | `Archivado` | 144 | |
| 70 | `Retirar de revisión` | 240 | |
| 71 | `¿Sacarlo de la cola de revisión y volverlo a borrador?` | 242 | |
| 72 | `Despublicar` | 249 | |
| 73 | `Esto está publicado y la app lo está mostrando. ¿Sacarlo de circulación? El contenido se conserva entero.` | 251 | |
| 74 | `Publicar de nuevo` | 258 | |
| 75 | `Archivar` | 267 | |
| 76 | `¿Archivarlo? Sale de circulación y se puede recuperar cuando quieras.` | 269 | |
| 77 | `Título` | 370 | |
| 78 | `Nombre del recorrido` | 372 | |
| 79 | `Nombre del destino` | 374 | |
| 80 | `Faltan fuentes o referencias.` | 392 | |
| 81 | `Mejorá las fotos: cuidá la luz, el encuadre y la portada.` | 393 | |
| 82 | `Verificá los horarios y los costos.` | 394 | |
| 83 | `Revisá la ortografía y la redacción.` | 395 | |
| 84 | `Comprobá que el enlace de Google Maps caiga en el lugar correcto.` | 396 | |
| 85 | `Revisá el orden de las paradas: no cuenta lo mismo al revés.` | 397 | |

### Guardas de las acciones

`includes/class-acciones.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 86 | `Esa acción no existe.` | 147 | |
| 87 | `Esa acción sólo acepta envíos.` | 151 | |
| 88 | `Tu sesión venció. Volvé a entrar.` | 158 | |
| 89 | `Tu sesión venció. Recargá la página.` | 165 | |
| 90 | `No tenés autorización para hacer esto.` | 170 | |

### Nombres de los roles

`includes/class-roles.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 91 | `Promotor` | 63 | |
| 92 | `Mini Promotor` | 64 | |
| 93 | `Visitante` | 65 | |

## Las secciones

Una tabla por pantalla del panel.

### Inicio

`templates/sections/home.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 94 | `Esperan revisión` | 35 | |
| 95 | `Publicados` | 36 | |
| 96 | `Esperan tu corrección` | 39 | |
| 97 | `En proceso` | 40 | |
| 98 | `Inicio` | 52 | |
| 99 | `Tu actividad de hoy` | 57 | |
| 100 | `Hola, %s 👋` ⚠️ | 60 | |
| 101 | `Actividad reciente` | 85 | |
| 102 | `Fichas, artículos y recorridos creados, enviados o publicados en los últimos 7 días.` | 87 | |
| 103 | `Actividad de los últimos %d día` ⚠️ | 109 | |
| 104 | `Actividad de los últimos %d días` ⚠️ | 109 | |
| 105 | `Accesos rápidos` | 125 | |
| 106 | `Crear una ficha` | 130 | |
| 107 | `Mis contenidos` | 133 | |
| 108 | `Cola de revisión` | 136 | |
| 109 | `Equipo` | 139 | |
| 110 | `Mi perfil` | 141 | |

### Mis contenidos

`templates/sections/mis-contenidos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 111 | `Contenidos del equipo` | 76 | |
| 112 | `Mis contenidos` | 76 | |
| 113 | `En curso` | 79 | |
| 114 | `Publicados` | 80 | |
| 115 | `Despublicados` | 81 | |
| 116 | `Archivados` | 82 | |
| 117 | `Papelera` | 83 | |
| 118 | `Lo que escribe todo el equipo` | 111 | |
| 119 | `Tu producción` | 111 | |
| 120 | `+ Nueva ficha` | 114 | |
| 121 | `De quién` | 119 | |
| 122 | `Míos` | 120 | |
| 123 | `Del equipo` | 120 | |
| 124 | `Filtrar por estado` | 131 | |
| 125 | `La papelera está vacía.` | 144 | |
| 126 | `Nadie del equipo tiene nada en ese estado.` | 146 | |
| 127 | `Nadie del equipo tiene nada en curso.` | 148 | |
| 128 | `No tenés nada en ese estado.` | 150 | |
| 129 | `Todavía no creaste nada. Podés empezar por una ficha, un artículo o un recorrido.` | 152 | |
| 130 | `Nueva ficha` | 154 | |
| 131 | `Nuevo artículo` | 155 | |
| 132 | `Nuevo recorrido` | 156 | |
| 133 | `Lo borrado se recupera acá, como borrador. Nada se pierde de verdad hasta que alguien lo vacíe.` | 161 | |
| 134 | `(sin título)` | 166 | |
| 135 | `Recuperar` | 170 | |

### Editor de ficha

`templates/sections/editor.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 136 | `No podés editar esta ficha.` | 16 | |
| 137 | `Editar ficha` | 28 | |
| 138 | `Nueva ficha` | 28 | |
| 139 | `Ficha del destino` | 36 | |
| 140 | `Comentarios del revisor` | 44 | |
| 141 | `Nombre del destino` | 61 | |
| 142 | `Descripción` | 66 | |
| 143 | `Clasificación` | 71 | |
| 144 | `Categoría` | 74 | |
| 145 | `Etiquetas` | 77 | |
| 146 | `Separadas por comas: «con niños», «gratis», «llega colectivo».` | 79 | |
| 147 | `📍 Usar mi ubicación actual` | 95 | |
| 148 | `Guardar borrador` | 102 | |
| 149 | `Enviar a revisión` | 103 | |
| 150 | `Checklist de mínimos` | 110 | |
| 151 | `Completá estos puntos antes de enviar la ficha a revisión.` | 111 | |

### Campos de la ficha

`includes/class-destinos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 152 | `Destinos` | 68 | |
| 153 | `Destino` | 69 | |
| 154 | `Nuevo destino` | 70 | |
| 155 | `Editar destino` | 71 | |
| 156 | `Buscar destinos` | 72 | |
| 157 | `Categorías` | 119 | |
| 158 | `Categoría` | 119 | |
| 159 | `Zonas` | 126 | |
| 160 | `Zona` | 126 | |
| 161 | `Etiquetas` | 130 | |
| 162 | `Etiqueta` | 130 | |
| 163 | `Qué es` | 160 | |
| 164 | `Tipo` | 163 | |
| 165 | `Sitio — está siempre` | 167 | |
| 166 | `Evento — pasa en una fecha` | 168 | |
| 167 | `Un evento es un lugar con fecha: la fiesta patronal, una feria, un festival. Todo lo demás se carga igual.` | 170 | |
| 168 | `Empieza` | 173 | |
| 169 | `Día y hora de inicio.` | 177 | |
| 170 | `Termina` | 180 | |
| 171 | `Si dura un solo día, alcanza con la hora de cierre. Si es de varios días, poné el último.` | 184 | |
| 172 | `Identidad` | 189 | |
| 173 | `Foto de portada` | 191 | |
| 174 | `Crédito de las fotos` | 192 | |
| 175 | `Video (URL, opcional)` | 193 | |
| 176 | `Ubicación` | 213 | |
| 177 | `Enlace de Google Maps` | 216 | |
| 178 | `Buscá el lugar en Google Maps, tocá «Compartir» y pegá acá el enlace. De ahí sacamos el pin solos.` | 220 | |
| 179 | `Latitud (alternativa al enlace)` | 223 | |
| 180 | `Sólo si el enlace no alcanza: un enlace corto, o un lugar que Google no tiene.` | 226 | |
| 181 | `Longitud (alternativa al enlace)` | 229 | |
| 182 | `Estado del camino` | 233 | |
| 183 | `Datos prácticos` | 245 | |
| 184 | `Horario` | 247 | |
| 185 | `Costo / entrada` | 248 | |
| 186 | `Rango de precio` | 256 | |
| 187 | `Sin especificar` | 260 | |
| 188 | `Gratis` | 261 | |
| 189 | `$ — Muy barato` | 262 | |
| 190 | `$$ — Barato` | 263 | |
| 191 | `$$$ — Intermedio` | 264 | |
| 192 | `$$$$ — Caro` | 265 | |
| 193 | `Contacto del lugar` | 268 | |
| 194 | `Fuentes y referencias` | 272 | |
| 195 | `Fuentes / referencias` | 274 | |
| 196 | `Descripción` | 456 | |
| 197 | `Ubicación (enlace de Google Maps o coordenadas)` | 461 | |

### Salida de campo

`templates/sections/captura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 198 | `Salida de campo` | 8 | |
| 199 | `Captura en el lugar` | 11 | |
| 200 | `Sacá una foto, anotá lo importante y guardá la ubicación, incluso si no tenés señal. Todo queda guardado en tu dispositivo y podés sincronizarlo como borrador cuando vuelva la conexión.` | 13 | |
| 201 | `Nombre del lugar` | 17 | |
| 202 | `Nota rápida` | 18 | |
| 203 | `Foto` | 21 | |
| 204 | `Ubicación (GPS)` | 25 | |
| 205 | `Tomar ubicación` | 27 | |
| 206 | `Guardar captura` | 33 | |
| 207 | `Capturas pendientes` | 39 | |
| 208 | `Sincronizar` | 40 | |

### Cola de revisión

`templates/sections/revision.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 209 | `No encontramos esto.` | 19 | |
| 210 | `Revisión` | 28 | |
| 211 | `Volver a la cola` | 33 | |
| 212 | `Por %s` ⚠️ | 39 | |
| 213 | `Entradilla` | 74 | |
| 214 | `Cuerpo` | 80 | |
| 215 | `Descripción` | 80 | |
| 216 | `Ubicación` | 87 | |
| 217 | `Abrir el pin en Google Maps` | 88 | |
| 218 | `Paradas, en orden` | 93 | |
| 219 | `sin texto` 🔡 | 99 | |
| 220 | `Acciones` | 130 | |
| 221 | `Asignarme la revisión` | 133 | |
| 222 | `Comentarios para el autor` | 137 | |
| 223 | `Qué corregir o mejorar…` | 138 | |
| 224 | `Devolver con cambios` | 147 | |
| 225 | `Aprobar y publicar` | 149 | |
| 226 | `Historial` | 157 | |
| 227 | `Cola de revisión` | 185 | |
| 228 | `Taller editorial` | 188 | |
| 229 | `No hay nada esperando revisión. 🎉` | 192 | |
| 230 | `%1$s · esperó %2$s` ⚠️ | 208 | |
| 231 | `revisa %s` ⚠️ 🔡 | 213 | |

### Tareas

`templates/sections/tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 232 | `Tareas` | 11 | |
| 233 | `Asignaciones` | 14 | |
| 234 | `Tareas y pendientes por cubrir` | 15 | |
| 235 | `+ Nueva tarea o hueco` | 19 | |
| 236 | `Título` | 21 | |
| 237 | `Detalle` | 22 | |
| 238 | `Tipo` | 24 | |
| 239 | `Tarea asignada` | 26 | |
| 240 | `Hueco disponible` | 27 | |
| 241 | `Vence` | 30 | |
| 242 | `Destino (opcional)` | 31 | |
| 243 | `Asignar a (Mini Promotores)` | 37 | |
| 244 | `Crear` | 42 | |
| 245 | `No hay tareas por ahora.` | 49 | |
| 246 | `Hueco` | 61 | |
| 247 | `vence %s` ⚠️ 🔡 | 63 | |
| 248 | `Reclamar` | 69 | |
| 249 | `Marcar como completada` | 72 | |

### Tareas (estados y avisos)

`includes/class-tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 250 | `Tareas` | 27 | |
| 251 | `Tarea` | 27 | |
| 252 | `Pendiente` | 38 | |
| 253 | `En curso` | 39 | |
| 254 | `Completada` | 40 | |
| 255 | `La tarea necesita un título.` | 58 | |

### Equipo

`templates/sections/equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 256 | `Equipo` | 17 | |
| 257 | `Tu equipo` | 20 | |
| 258 | `Invitar a alguien` | 24 | |
| 259 | `Generá un enlace de invitación con el rol que quieras. El enlace es válido durante 14 días.` | 25 | |
| 260 | `Mini Promotor` | 29 | |
| 261 | `Promotor` | 30 | |
| 262 | `Visitante` | 31 | |
| 263 | `Crear enlace` | 33 | |
| 264 | `%1$d publicadas · %2$d en total` ⚠️ | 54 | |
| 265 | `Nivel de confianza:` | 64 | |
| 266 | `Guardar` | 71 | |
| 267 | `Suspendida` | 80 | |
| 268 | `Rol` | 87 | |
| 269 | `Cambiar rol` | 92 | |
| 270 | `Reactivar` | 99 | |
| 271 | `Suspender` | 99 | |
| 272 | `Deja de tener acceso al panel. Su cuenta y lo que publicó quedan como están. ¿Seguimos?` | 104 | |
| 273 | `Sacar del panel` | 107 | |
| 274 | `Invitaciones abiertas` | 117 | |
| 275 | `No hay ninguna esperando. Los enlaces que crees acá arriba aparecen en esta lista hasta que alguien los use o se venzan.` | 120 | |
| 276 | `Vence el %s` ⚠️ | 133 | |
| 277 | `El enlace deja de servir. ¿Seguimos?` | 139 | |
| 278 | `Revocar` | 142 | |

### Equipo (avisos)

`includes/class-equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 279 | `Esa persona no existe.` | 51 | |
| 280 | `A vos mismo no te podés editar desde acá.` | 54 | |
| 281 | `Ese rol no existe.` | 72 | |
| 282 | `%1$s ahora es %2$s.` ⚠️ | 85 | |
| 283 | `Suspendimos a %s. No va a poder entrar hasta que la reactives.` ⚠️ | 114 | |
| 284 | `%s puede volver a entrar.` ⚠️ | 119 | |
| 285 | `%s ya no entra al panel. Su cuenta y lo que publicó quedan como están.` ⚠️ | 142 | |
| 286 | `Esa invitación ya no existe.` | 150 | |
| 287 | `Invitación revocada. Ese enlace ya no sirve.` | 153 | |

### Reportes

`templates/sections/reportes.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 288 | `Reportes` | 12 | |
| 289 | `Métricas` | 15 | |
| 290 | `Actividad del portal` | 16 | |
| 291 | `Producción por autor` | 18 | |
| 292 | `%1$d publicadas / %2$d` ⚠️ | 27 | |
| 293 | `Estado del contenido` | 33 | |
| 294 | `Fichas publicadas sin portada` | 36 | |
| 295 | `Fichas sin verificar hace +6 meses` | 46 | |

### Biblioteca

`templates/sections/biblioteca.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 296 | `Biblioteca` | 21 | |
| 297 | `Medios` | 26 | |
| 298 | `Biblioteca de medios` | 27 | |
| 299 | `Subir fotos` | 37 | |
| 300 | `Podés elegir varias de una vez. JPG, PNG, WEBP o GIF.` | 40 | |
| 301 | `Subir` | 42 | |
| 302 | `Buscar una foto` | 50 | |
| 303 | `Sólo las mías` | 54 | |
| 304 | `Filtrar` | 56 | |
| 305 | `No encontramos ninguna foto con ese nombre.` | 64 | |
| 306 | `Todavía no hay fotos. Subí las primeras acá arriba.` | 66 | |
| 307 | `%d foto` ⚠️ | 77 | |
| 308 | `%d fotos` ⚠️ | 77 | |
| 309 | `Sin descripción` | 101 | |
| 310 | `Es la portada de %d ficha.` ⚠️ | 112 | |
| 311 | `Es la portada de %d fichas.` ⚠️ | 112 | |
| 312 | `Esta foto la subió otra persona, así que sólo podés verla.` | 120 | |
| 313 | `Nombre` | 128 | |
| 314 | `Descripción` | 132 | |
| 315 | `Qué se ve en la foto` | 134 | |
| 316 | `Crédito` | 137 | |
| 317 | `opcional` 🔡 | 137 | |
| 318 | `Quién la sacó` | 139 | |
| 319 | `Guardar` | 143 | |
| 320 | `Se borra la foto y no se puede deshacer. ¿Seguimos?` | 149 | |
| 321 | `Borrar foto` | 152 | |
| 322 | `Anteriores` | 165 | |
| 323 | `Página %1$d de %2$d` ⚠️ | 171 | |
| 324 | `Siguientes` | 179 | |

### Biblioteca (avisos)

`includes/class-medios.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 325 | `No elegiste ninguna foto.` | 141 | |
| 326 | `No pudimos subir ninguna: revisá que sean JPG, PNG, WEBP o GIF.` | 182 | |
| 327 | `Subimos %1$d foto. %2$d quedó afuera por el formato.` ⚠️ | 187 | |
| 328 | `Subimos %1$d fotos. %2$d quedaron afuera por el formato.` ⚠️ | 187 | |
| 329 | `Subimos %d foto.` ⚠️ | 194 | |
| 330 | `Subimos %d fotos.` ⚠️ | 194 | |
| 331 | `Esa foto no existe.` | 218 | |
| 332 | `Esa foto la subió otra persona.` | 221 | |
| 333 | `Listo, guardamos la foto.` | 234 | |
| 334 | `No la borramos: es la portada de %d ficha. Cambiala ahí primero.` ⚠️ | 250 | |
| 335 | `No la borramos: es la portada de %d fichas. Cambiala ahí primero.` ⚠️ | 250 | |
| 336 | `Foto borrada.` | 259 | |

### Estructura

`templates/sections/estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 337 | `Estructura` | 16 | |
| 338 | `Organización` | 21 | |
| 339 | `Estructura del sitio` | 22 | |
| 340 | `Esto lo organiza un Promotor. Podés ver cómo está armado, pero no cambiarlo.` | 28 | |
| 341 | `Todavía no hay ninguna.` | 45 | |
| 342 | `Descripción` | 64 | |
| 343 | `una o dos líneas; encabeza la categoría en la app` 🔡 | 64 | |
| 344 | `Imagen` | 68 | |
| 345 | `Subir foto` | 74 | |
| 346 | `Guardar` | 80 | |
| 347 | `%d ficha` ⚠️ | 91 | |
| 348 | `%d fichas` ⚠️ | 91 | |
| 349 | `Se borra y no se puede deshacer. ¿Seguimos?` | 98 | |
| 350 | `Borrar` | 102 | |
| 351 | `Agregar` | 119 | |
| 352 | `El ícono y el color con que la app muestra cada categoría se eligen en App →` | 127 | |

### Estructura (nombres y avisos)

`includes/class-estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 353 | `Categorías` | 42 | |
| 354 | `Categoría` | 43 | |
| 355 | `Etiquetas` | 51 | |
| 356 | `Etiqueta` | 52 | |
| 357 | `Eso no se puede editar desde acá.` | 97 | |
| 358 | `Escribí un nombre.` | 106 | |
| 359 | `Ya existe una con ese nombre.` | 109 | |
| 360 | `Creamos «%s».` ⚠️ | 124 | |
| 361 | `Listo, guardamos los cambios.` | 160 | |
| 362 | `Eso ya no existe.` | 168 | |
| 363 | `No la borramos: %d ficha la usa. Movelas primero.` ⚠️ | 174 | |
| 364 | `No la borramos: %d fichas la usan. Movelas primero.` ⚠️ | 174 | |
| 365 | `Borramos «%s».` ⚠️ | 188 | |

### Buscar

`templates/sections/buscar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 366 | `Buscar` | 18 | |
| 367 | `Escribí algo en el buscador de arriba para encontrar fichas, artículos o recorridos.` | 23 | |
| 368 | `%1$d resultado para «%2$s»` ⚠️ | 28 | |
| 369 | `%1$d resultados para «%2$s»` ⚠️ | 28 | |
| 370 | `No encontramos resultados. Probá con otras palabras.` | 32 | |

### Mi perfil

`templates/sections/perfil.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 371 | `Mi perfil` | 26 | |
| 372 | `Tu progreso de confianza` | 42 | |
| 373 | `Nivel máximo: publicás directamente y después se hace una auditoría. Gracias por tu compromiso.` | 56 | |
| 374 | `Promotor Jr: podés editar fichas publicadas sin pasar por una nueva revisión. Seguí sumando aprobaciones para llegar a «De confianza».` | 58 | |
| 375 | `Aprendiz: todo tu contenido pasa por revisión. A medida que sumás aprobaciones, vas ganando autonomía.` | 60 | |
| 376 | `Fichas publicadas` | 70 | |
| 377 | `Mi portafolio` | 79 | |
| 378 | `Todavía no tenés nada publicado.` | 81 | |
| 379 | `Publicado` | 89 | |
| 380 | `Mis datos` | 97 | |
| 381 | `Nombre` | 104 | |
| 382 | `Correo` | 111 | |
| 383 | `Teléfono` | 116 | |
| 384 | `opcional` 🔡 | 116 | |
| 385 | `Foto` | 122 | |
| 386 | `opcional, JPG/PNG/WEBP, hasta 5 MB` 🔡 | 122 | |
| 387 | `Con el correo entrás al panel: si lo cambiás, la próxima vez iniciás sesión con el nuevo.` | 125 | |
| 388 | `Guardar cambios` | 128 | |
| 389 | `Contraseña` | 133 | |
| 390 | `Contraseña actual` | 140 | |
| 391 | `Contraseña nueva` | 146 | |
| 392 | `Repetila` | 150 | |
| 393 | `Cambiar contraseña` | 156 | |

### Mi perfil (avisos)

`includes/class-cuenta.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 394 | `Estás entrando como administrador de WordPress, que no tiene cuenta del panel que editar.` | 78 | |
| 395 | `Escribí tu nombre.` | 86 | |
| 396 | `Ese correo no parece válido.` | 89 | |
| 397 | `Ya hay una cuenta con ese correo.` | 95 | |
| 398 | `No pudimos guardar los cambios. Probá de nuevo.` | 104 | |
| 399 | `Listo, guardamos tus datos.` | 107 | |
| 400 | `La foto tiene que ser JPG, PNG o WEBP.` | 130 | |
| 401 | `La foto pesa más de 5 MB. Subí una más liviana.` | 133 | |
| 402 | `La contraseña actual no coincide.` | 229 | |
| 403 | `Las dos contraseñas nuevas tienen que ser iguales.` | 232 | |
| 404 | `No pudimos cambiar la contraseña. Probá de nuevo.` | 240 | |
| 405 | `Listo, cambiaste tu contraseña.` | 246 | |

### Niveles de confianza

`includes/class-stats.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 406 | `Aprendiz` | 34 | |
| 407 | `Promotor Jr` | 35 | |
| 408 | `De confianza` | 36 | |

### App (control de la app móvil)

`templates/sections/app.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 409 | `App` | 21 | |
| 410 | `Fuera de servicio` | 25 | |
| 411 | `La cabina de mando de la app está desconectada` | 26 | |
| 412 | `Los textos y los medios de la aplicación se editan por ahora desde la administración del sitio. Se vuelve a enchufar cuando la API de la app esté en la versión que esta pantalla necesita.` | 27 | |
| 413 | `Volver al inicio del panel` | 28 | |
| 414 | `Aplicación` | 52 | |
| 415 | `Textos` | 61 | |
| 416 | `Idioma` | 62 | |
| 417 | `Clave` | 88 | |
| 418 | `Texto` | 92 | |
| 419 | `Guardar cambios` | 99 | |
| 420 | `Medios` | 108 | |
| 421 | `Ir a la biblioteca` | 111 | |
| 422 | `Tipo` | 138 | |
| 423 | `Imagen` | 140 | |
| 424 | `Animación` | 141 | |
| 425 | `URL o ID` | 145 | |
| 426 | `Texto alternativo` | 149 | |
| 427 | `Formato` | 153 | |
| 428 | `Categorías` | 170 | |
| 429 | `Todavía no hay categorías cargadas. Se crean en Estructura y después se les elige acá el icono y el color.` | 174 | |
| 430 | `Estructura` | 175 | |
| 431 | `Nombre` | 185 | |
| 432 | `Color` | 189 | |
| 433 | `Icono` | 194 | |

### App (avisos)

`includes/class-app-control.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 434 | `No tenés autorización para hacer esto.` | 134 | |
| 435 | `Guardado` | 195 | |

### Ayuda

`templates/sections/ayuda.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 436 | `Ayuda` | 16 | |
| 437 | `Inicio` | 25 | |
| 438 | `Tu resumen del día: lo que espera revisión, lo que necesita correcciones tuyas, y accesos rápidos según tu rol.` | 25 | |
| 439 | `Buscar` | 26 | |
| 440 | `Buscar entre las fichas, artículos y recorridos del panel.` | 26 | |
| 441 | `Nueva ficha` | 27 | |
| 442 | `El editor guiado de una ficha —un sitio o un evento con fecha—, con checklist de mínimos que avisa si falta algo antes de enviar a revisión.` | 27 | |
| 443 | `Salida de campo` | 28 | |
| 444 | `Sacá una foto, anotá información y guardá la ubicación GPS en el lugar, incluso sin señal. Se sincroniza como borrador cuando vuelve la conexión.` | 28 | |
| 445 | `Mis contenidos` | 29 | |
| 446 | `Todo lo que cargaste —fichas, artículos, recorridos—, ordenado por estado, con filtro para ver lo archivado y lo borrado. Si revisás, también podés ver lo de todo el equipo, borradores incluidos.` | 29 | |
| 447 | `Inventario turístico` | 30 | |
| 448 | `El catálogo de fichas publicadas del departamento. De acá se eligen las paradas al armar un recorrido.` | 30 | |
| 449 | `Artículos` | 31 | |
| 450 | `Las notas que la app muestra: título, foto de portada, entradilla, cuerpo y fuentes. Pasan por el mismo flujo de revisión que una ficha.` | 31 | |
| 451 | `Recorridos` | 32 | |
| 452 | `Se arman eligiendo sitios del inventario —hasta nueve—, cada uno con su propio texto, audio o video, en el orden del paseo.` | 32 | |
| 453 | `Cola de revisión` | 33 | |
| 454 | `La cola de lo que espera revisión: asignate una pieza, aprobala y publicala, o devolvela al autor con comentarios.` | 33 | |
| 455 | `Tareas` | 34 | |
| 456 | `Encargos con fecha límite. Los Mini Promotores pueden reclamar los que están disponibles y marcarlos como hechos.` | 34 | |
| 457 | `Equipo` | 35 | |
| 458 | `Quién entra al panel, con qué rol y su nivel de confianza. Cambiar el rol, suspender, sacar del panel, e invitar gente nueva.` | 35 | |
| 459 | `Reportes` | 36 | |
| 460 | `Producción por autor y salud del contenido: lo publicado sin portada, y lo que no se verifica hace más de seis meses.` | 36 | |
| 461 | `Biblioteca` | 37 | |
| 462 | `La galería de fotos del panel: subir de a tandas, describir, dar crédito y borrar.` | 37 | |
| 463 | `Estructura` | 38 | |
| 464 | `Las categorías y etiquetas de las fichas: crear, renombrar en su lugar, y borrar lo que no esté en uso.` | 38 | |
| 465 | `Mi perfil` | 39 | |
| 466 | `Tu cuenta —nombre, correo, teléfono, foto y contraseña—, tu nivel de confianza y el portafolio de lo que publicaste.` | 39 | |
| 467 | `Cómo funciona` | 42 | |
| 468 | `¿Qué hace cada sección?` | 43 | |
| 469 | `Este es el panel de los Promotores Turísticos: acá se escribe, se revisa y se publica todo lo que la app de Caaguazú muestra —fichas de destinos y eventos, artículos y recorridos—. Los Mini Promotores crean; los Promotores revisan y publican.` | 45 | |
| 470 | `El flujo editorial` | 48 | |
| 471 | `Borrador` | 51 | |
| 472 | `Enviado` | 52 | |
| 473 | `En revisión` | 53 | |
| 474 | `Necesita cambios` | 54 | |
| 475 | `Publicado` | 55 | |
| 476 | `Solo lo aprobado por un Promotor llega a la app. La confianza se construye con cada aprobación: pasás de Aprendiz a Promotor Jr y luego a De confianza. Cada nivel da más autonomía, como editar algo publicado sin una nueva revisión y, en el último, publicar directamente.` | 58 | |
| 477 | `Lo publicado también se puede despublicar, archivar o mandar a la papelera —y volver atrás desde ahí—, siempre en dos pasos: primero hay que sacarlo del aire antes de poder borrarlo.` | 61 | |
| 478 | `Las secciones` | 65 | |
| 479 | `Extras` | 84 | |
| 480 | `Podés instalar el panel como app (PWA) desde el menú lateral.` | 86 | |
| 481 | `La salida de campo funciona sin conexión: lo que cargues se guarda en el teléfono y se sube solo cuando vuelve la señal.` | 87 | |
| 482 | `Podés cambiar entre modo claro y oscuro desde la barra superior.` | 88 | |
| 483 | `El acceso es solo por invitación. Pedí tu enlace a quien coordina el equipo.` | 89 | |

### Sección inexistente

`templates/sections/404.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 484 | `No encontramos esa sección` | 7 | |
| 485 | `Error 404` | 11 | |
| 486 | `Esta sección no existe` | 12 | |
| 487 | `Puede que el enlace esté roto o que no tengas permiso para acceder.` | 13 | |
| 488 | `Volver al inicio del panel` | 14 | |

## Entrar y salir

Acceso, registro, recuperación, invitaciones y errores de permiso.

### Iniciar sesión

`templates/auth/login.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 489 | `Iniciar sesión` | 12 | |
| 490 | `Entrá al panel de Promotores Turísticos.` | 16 | |
| 491 | `Tu contraseña se actualizó. Ya podés iniciar sesión.` | 19 | |
| 492 | `Email` | 34 | |
| 493 | `Contraseña` | 38 | |
| 494 | `Mantener la sesión iniciada` | 42 | |
| 495 | `Entrar` | 45 | |
| 496 | `¿Olvidaste tu contraseña?` | 49 | |
| 497 | `Acceso solo por invitación` | 50 | |

### Registro

`templates/auth/registro.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 498 | `Crear cuenta` | 14 | |
| 499 | `Esta invitación ya fue usada.` | 27 | |
| 500 | `Esta invitación venció. Pedí una nueva al equipo.` | 28 | |
| 501 | `Esta invitación fue revocada.` | 29 | |
| 502 | `El registro es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 30 | |
| 503 | `Ya tengo una cuenta` | 35 | |
| 504 | `Invitación válida: te unirás como %s.` ⚠️ | 44 | |
| 505 | `Nombre de usuario` | 55 | |
| 506 | `Email` | 59 | |
| 507 | `Teléfono` | 63 | |
| 508 | `Ej.: 0981 123 456` | 64 | |
| 509 | `Contraseña (6 o más caracteres)` | 67 | |

### Recuperar contraseña

`templates/auth/recuperar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 510 | `Recuperar contraseña` | 10 | |
| 511 | `Te enviamos un enlace para restablecer tu contraseña.` | 14 | |
| 512 | `Email` | 27 | |
| 513 | `Enviar enlace` | 30 | |
| 514 | `Volver a iniciar sesión` | 34 | |

### Contraseña nueva

`templates/auth/restablecer.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 515 | `Nueva contraseña` | 12 | |
| 516 | `Nueva contraseña (6 o más caracteres)` | 28 | |
| 517 | `Guardar contraseña` | 31 | |
| 518 | `El enlace no es válido o ya venció. Pedí uno nuevo.` | 34 | |
| 519 | `Pedir un nuevo enlace` | 36 | |

### Marco de acceso

`templates/auth-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 520 | `Acceso` | 8 | |

### Errores y avisos de acceso

`includes/class-auth.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 521 | `No tenés autorización para hacer esto.` | 42 | |
| 522 | `Enlace de invitación creado. Es válido durante 14 días: %s` ⚠️ | 48 | |
| 523 | `Tu sesión venció. Recargá la página.` | 142 | |
| 524 | `Necesitás una invitación válida para registrarte.` | 181 | |
| 525 | `Completá usuario, email, teléfono y una contraseña de al menos 6 caracteres.` | 191 | |
| 526 | `Ese email ya está registrado.` | 195 | |
| 527 | `Si la cuenta existe, te enviamos un email con las instrucciones.` | 240 | |
| 528 | `El enlace para restablecer la contraseña venció o no es válido.` | 258 | |

### Invitaciones

`includes/class-invitations.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 529 | `Válida` | 99 | |
| 530 | `Usada` | 100 | |
| 531 | `Expirada` | 101 | |
| 532 | `Revocada` | 102 | |
| 533 | `Inválida` | 103 | |

### Guardas de acceso

`includes/class-router.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 534 | `No tenés acceso a este panel.` | 260 | |
| 535 | `Acceso denegado` | 261 | |

### Guardas de sección

`includes/class-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 536 | `No tenés permiso para ver esta sección.` | 42 | |
| 537 | `Acceso denegado` | 43 | |

### Sin conexión (PWA)

`includes/class-pwa.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 538 | `Sin conexión` | 169 | |
| 539 | `Promotores Turísticos` | 176 | |
| 540 | `Estás sin conexión` | 177 | |
| 541 | `No pudimos cargar esta pantalla. Revisá tu conexión e intentá de nuevo.` | 178 | |
| 542 | `Reintentar` | 179 | |

## wp-admin y mensajes de sistema

Pantallas de administración y respuestas de las acciones.

### Pantallas de wp-admin

`includes/class-admin.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 543 | `Portal Turismo` | 63 | |
| 544 | `Registros` | 66 | |
| 545 | `Actualizaciones` | 68 | |
| 546 | `No tenés autorización para hacer esto.` | 83 | |
| 547 | `Usuarios` | 112 | |
| 548 | `Entradas` | 113 | |
| 549 | `Fecha` | 117 | |
| 550 | `Usuario` | 117 | |
| 551 | `Acción` | 118 | |
| 552 | `Elemento` | 118 | |
| 553 | `IP` | 119 | |
| 554 | `Detalle` | 119 | |
| 555 | `No hay registros.` | 123 | |
| 556 | `Actualizaciones del portal` | 180 | |
| 557 | `No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.` | 184 | |
| 558 | `Atención: la versión del encabezado del plugin (%1$s) no coincide con PROMOTUR_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.` ⚠️ | 191 | |
| 559 | `Versión instalada` | 200 | |
| 560 | `Última disponible` | 201 | |
| 561 | `Actualizar ahora` | 210 | |
| 562 | `Estás al día.` | 212 | |
| 563 | `Última comprobación` | 215 | |
| 564 | `nunca` 🔡 | 216 | |
| 565 | `Repositorio` | 218 | |
| 566 | `Buscar actualizaciones ahora` | 227 | |
| 567 | `Limpiar caché del actualizador` | 233 | |
| 568 | `Token de GitHub` | 237 | |
| 569 | `Definido en wp-config.php mediante PROMOTUR_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.` | 239 | |
| 570 | `El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.` | 241 | |
| 571 | `Token` | 247 | |
| 572 | `•••• guardado (dejá vacío para conservarlo)` | 248 | |
| 573 | `Eliminar el token guardado` | 250 | |
| 574 | `Guardar token` | 254 | |
| 575 | `Hay una nueva versión disponible: %s.` ⚠️ | 273 | |
| 576 | `No hay actualizaciones: ya tenés la última versión.` | 275 | |
| 577 | `El verificador de actualizaciones no está disponible.` | 278 | |
| 578 | `Caché del actualizador limpiada.` | 287 | |
| 579 | `El token está definido en wp-config.php y no se puede cambiar desde acá.` | 292 | |
| 580 | `Token eliminado.` | 299 | |
| 581 | `Token guardado.` | 302 | |
| 582 | `No hubo cambios en el token.` | 304 | |

### Respuestas del editor

`includes/class-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 583 | `No tenés permiso para hacer esto.` | 61 | |
| 584 | `Ese tipo de contenido no existe.` | 74 | |
| 585 | `No podés editar esto.` | 130 | |
| 586 | `(sin título)` | 146 | |
| 587 | `Borrador guardado.` | 207 | |
| 588 | `Guardado. Como editaste algo ya publicado, tendrá que pasar por una nueva revisión.` | 214 | |
| 589 | `De ese enlace no pudimos sacar el pin (los enlaces cortos no lo traen). Cargá la latitud y la longitud a mano, o pegá el enlace largo.` | 254 | |
| 590 | `Un recorrido lleva hasta %d paradas, y sin repetir el mismo sitio. Guardamos las que entraron.` ⚠️ | 309 | |
| 591 | `Esto no se puede enviar.` | 334 | |
| 592 | `Faltan datos obligatorios. Completá el checklist antes de enviar.` | 338 | |
| 593 | `Publicación directa por nivel de confianza. Se hará una auditoría posterior.` | 348 | |
| 594 | `¡Publicado! Se aplicó tu nivel de confianza.` | 349 | |
| 595 | `¡Enviado a revisión!` | 353 | |
| 596 | `Eso no existe o no es contenido del panel.` | 366 | |
| 597 | `Te asignaste la revisión.` | 375 | |
| 598 | `Aprobado y publicado.` | 386 | |
| 599 | `Escribí los comentarios para el autor.` | 394 | |
| 600 | `Devuelto al autor con comentarios.` | 398 | |
| 601 | `No recibimos ninguna imagen.` | 405 | |
| 602 | `Solo podés subir imágenes.` | 409 | |

### Respuestas de gestión

`includes/class-gestion-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 603 | `No tenés permiso para hacer esto.` | 29 | |
| 604 | `Tarea creada.` | 46 | |
| 605 | `La tarea no es válida.` | 53 | |
| 606 | `Reclamaste esta tarea. Ya podés trabajar en ella.` | 56 | |
| 607 | `Tarea completada. 🎉` | 70 | |
| 608 | `El usuario no es válido.` | 78 | |
| 609 | `Nivel actualizado.` | 81 | |

### Avisos del plugin

`caaguazu-portal.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 610 | `Caaguazú Portal necesita tener activo el plugin «Caaguazú Cuentas» para funcionar. El inicio de sesión de los Promotores ya no usa los usuarios de WordPress. Activá el plugin desde Plugins para volver a usar el panel.` | 95 | |
| 611 | `Portal de Promotores` | 116 | |
