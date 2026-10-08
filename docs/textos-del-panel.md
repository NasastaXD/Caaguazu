# Textos del panel

Todo lo que un usuario lee en el panel, sacado de las fuentes el 2026-10-08. **651 textos.**

Se regenera con `php tools/textos-del-panel.php > docs/textos-del-panel.md`.

- Escribí el reemplazo en la columna **Nuevo texto**; lo que quede en blanco se deja como está.
- Los marcados con ⚠️ llevan un hueco (`%s`, `%d`, `%1$s`) que el código rellena: hay que conservarlo tal cual y en el mismo orden.
- Los `[FALTA: …]` son huecos a propósito: textos que el diseño pide y que todavía no escribió nadie.
- Los marcados con 🔡 arrancan en minúscula.

## Empiezan en minúscula

Casi todas son fragmentos escritos para leerse **después de un número** ("4 esperan revisión") o para ir dentro de una frase. Si se quieren usar como título, hay que reescribirlas enteras, no sólo poner la mayúscula.

| # | Texto | Dónde |
| --- | --- | --- |
| 65 | `atrás` | Notificaciones |
| 226 | `sin texto` | Cola de revisión |
| 238 | `revisa %s` | Cola de revisión |
| 254 | `vence %s` | Tareas |
| 324 | `opcional` | Biblioteca |
| 387 | `opcional` | Mi perfil |
| 389 | `opcional, JPG/PNG/WEBP, hasta 5 MB` | Mi perfil |
| 606 | `nunca` | Pantallas de wp-admin |

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
| 8 | `Pegar datos` | 327 | |
| 9 | `Si ya tenés los datos escritos en otro lado, pegalos acá como JSON y se reparten solos en las casillas de abajo. No guarda nada: revisás lo que quedó y guardás vos.` | 329 | |
| 10 | `JSON` | 332 | |
| 11 | `Las claves son los nombres de los campos: «horario», «costo», «lat». También valen con el prefijo largo («_promotur_horario»), que es como se llaman en la lista de datos de la app.` | 335 | |
| 12 | `Llenar el formulario` | 339 | |
| 13 | `La foto no se puede pegar: se sube con el botón de «Foto de portada».` | 343 | |
| 14 | `—` | 397 | |
| 15 | `Subir foto` | 417 | |
| 16 | `GESTIÓN` | 663 | |
| 17 | `Inicio` | 665 | |
| 18 | `Mis contenidos` | 668 | |
| 19 | `Nueva ficha` | 672 | |
| 20 | `Salida de campo` | 673 | |
| 21 | `Cola de revisión` | 676 | |
| 22 | `Tareas` | 677 | |
| 23 | `CONTENIDO` | 681 | |
| 24 | `Inventario turístico` | 683 | |
| 25 | `Artículos` | 684 | |
| 26 | `Recorridos` | 685 | |
| 27 | `PORTAL` | 689 | |
| 28 | `Equipo` | 691 | |
| 29 | `Reportes` | 692 | |
| 30 | `Biblioteca` | 693 | |
| 31 | `Estructura` | 694 | |
| 32 | `App` | 702 | |
| 33 | `Mi perfil` | 722 | |
| 34 | `Ayuda` | 723 | |

### Menú lateral (pie)

`templates/partials/sidebar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 35 | `Abrir menú` | 47 | |
| 36 | `Buscar…` | 77 | |
| 37 | `Buscar` | 78 | |
| 38 | `Navegación del panel` | 82 | |
| 39 | `Instalar app` | 111 | |
| 40 | `Cerrar sesión` | 115 | |

### Barra superior

`templates/partials/topbar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 41 | `Abrir menú` | 21 | |
| 42 | `Navegación del panel` | 25 | |
| 43 | `Inicio` | 30 | |
| 44 | `Buscar…` | 39 | |
| 45 | `Buscar` | 39 | |
| 46 | `Cambiar tema` | 42 | |
| 47 | `Notificaciones` | 48 | |
| 48 | `Marcar todo como leído` | 60 | |
| 49 | `No hay novedades por ahora. ✨` | 66 | |

### Barra inferior (teléfono)

`templates/partials/bottomnav.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 50 | `Inicio` | 10 | |
| 51 | `Contenidos` | 11 | |
| 52 | `Campo` | 12 | |
| 53 | `Revisar` | 13 | |
| 54 | `Perfil` | 14 | |
| 55 | `Navegación rápida` | 20 | |

### Mensajes del JavaScript

`includes/class-assets.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 56 | `Instalar app` | 135 | |
| 57 | `Enviando…` | 136 | |
| 58 | `Algo salió mal. Probá de nuevo.` | 137 | |
| 59 | `Guardado` | 138 | |
| 60 | `¿Querés confirmar esta acción?` | 139 | |
| 61 | `Faltan algunos datos obligatorios.` | 140 | |
| 62 | `Foto subida.` | 141 | |
| 63 | `Copiado` | 142 | |

### Notificaciones

`includes/class-notifications.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 64 | `«%s» está esperando revisión` ⚠️ | 81 | |
| 65 | `atrás` 🔡 | 83 | |
| 66 | `«%s» necesita algunos cambios` ⚠️ | 106 | |
| 67 | `Notificaciones marcadas como leídas.` | 181 | |

### Estados del flujo editorial

`includes/class-editorial.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 68 | `Ficha` | 97 | |
| 69 | `Borrador` | 137 | |
| 70 | `Enviado` | 138 | |
| 71 | `En revisión` | 139 | |
| 72 | `Necesita cambios` | 140 | |
| 73 | `Aprobado` | 141 | |
| 74 | `Publicado` | 142 | |
| 75 | `Despublicado` | 143 | |
| 76 | `Archivado` | 144 | |
| 77 | `Retirar de revisión` | 240 | |
| 78 | `¿Sacarlo de la cola de revisión y volverlo a borrador?` | 242 | |
| 79 | `Despublicar` | 249 | |
| 80 | `Esto está publicado y la app lo está mostrando. ¿Sacarlo de circulación? El contenido se conserva entero.` | 251 | |
| 81 | `Publicar de nuevo` | 258 | |
| 82 | `Archivar` | 267 | |
| 83 | `¿Archivarlo? Sale de circulación y se puede recuperar cuando quieras.` | 269 | |
| 84 | `Título` | 370 | |
| 85 | `Nombre del recorrido` | 372 | |
| 86 | `Nombre del destino` | 374 | |
| 87 | `Faltan fuentes o referencias.` | 392 | |
| 88 | `Mejorá las fotos: cuidá la luz, el encuadre y la portada.` | 393 | |
| 89 | `Verificá los horarios y los costos.` | 394 | |
| 90 | `Revisá la ortografía y la redacción.` | 395 | |
| 91 | `Comprobá que el enlace de Google Maps caiga en el lugar correcto.` | 396 | |
| 92 | `Revisá el orden de las paradas: no cuenta lo mismo al revés.` | 397 | |

### Guardas de las acciones

`includes/class-acciones.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 93 | `Esa acción no existe.` | 161 | |
| 94 | `Esa acción sólo acepta envíos.` | 165 | |
| 95 | `Tu sesión venció. Volvé a entrar.` | 172 | |
| 96 | `Tu sesión venció. Recargá la página.` | 179 | |
| 97 | `No tenés autorización para hacer esto.` | 184 | |

### Nombres de los roles

`includes/class-roles.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 98 | `Profesor` | 68 | |
| 99 | `Alumno` | 69 | |
| 100 | `Visitante` | 70 | |

## Las secciones

Una tabla por pantalla del panel.

### Inicio

`templates/sections/home.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 101 | `Esperan revisión` | 35 | |
| 102 | `Publicados` | 36 | |
| 103 | `Esperan tu corrección` | 39 | |
| 104 | `En proceso` | 40 | |
| 105 | `Inicio` | 52 | |
| 106 | `Tu actividad de hoy` | 57 | |
| 107 | `Hola, %s 👋` ⚠️ | 60 | |
| 108 | `Actividad reciente` | 85 | |
| 109 | `Fichas, artículos y recorridos creados, enviados o publicados en los últimos 7 días.` | 87 | |
| 110 | `Actividad de los últimos %d día` ⚠️ | 109 | |
| 111 | `Actividad de los últimos %d días` ⚠️ | 109 | |
| 112 | `Accesos rápidos` | 125 | |
| 113 | `Crear una ficha` | 130 | |
| 114 | `Mis contenidos` | 133 | |
| 115 | `Cola de revisión` | 136 | |
| 116 | `Equipo` | 139 | |
| 117 | `Mi perfil` | 141 | |

### Mis contenidos

`templates/sections/mis-contenidos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 118 | `Contenidos del equipo` | 76 | |
| 119 | `Mis contenidos` | 76 | |
| 120 | `En curso` | 79 | |
| 121 | `Publicados` | 80 | |
| 122 | `Despublicados` | 81 | |
| 123 | `Archivados` | 82 | |
| 124 | `Papelera` | 83 | |
| 125 | `Lo que escribe todo el equipo` | 111 | |
| 126 | `Tu producción` | 111 | |
| 127 | `+ Nueva ficha` | 114 | |
| 128 | `De quién` | 119 | |
| 129 | `Míos` | 120 | |
| 130 | `Del equipo` | 120 | |
| 131 | `Filtrar por estado` | 131 | |
| 132 | `La papelera está vacía.` | 144 | |
| 133 | `Nadie del equipo tiene nada en ese estado.` | 146 | |
| 134 | `Nadie del equipo tiene nada en curso.` | 148 | |
| 135 | `No tenés nada en ese estado.` | 150 | |
| 136 | `Todavía no creaste nada. Podés empezar por una ficha, un artículo o un recorrido.` | 152 | |
| 137 | `Nueva ficha` | 154 | |
| 138 | `Nuevo artículo` | 155 | |
| 139 | `Nuevo recorrido` | 156 | |
| 140 | `Lo borrado se recupera acá, como borrador. Nada se pierde de verdad hasta que alguien lo vacíe.` | 161 | |
| 141 | `(sin título)` | 166 | |
| 142 | `Recuperar` | 170 | |

### Editor de ficha

`templates/sections/editor.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 143 | `No podés editar esta ficha.` | 16 | |
| 144 | `Editar ficha` | 28 | |
| 145 | `Nueva ficha` | 28 | |
| 146 | `Ficha del destino` | 36 | |
| 147 | `Comentarios del revisor` | 44 | |
| 148 | `Nombre del destino` | 63 | |
| 149 | `Descripción` | 68 | |
| 150 | `Clasificación` | 73 | |
| 151 | `Categoría` | 76 | |
| 152 | `Etiquetas` | 79 | |
| 153 | `Separadas por comas: «con niños», «gratis», «llega colectivo».` | 81 | |
| 154 | `📍 Usar mi ubicación actual` | 97 | |
| 155 | `Guardar borrador` | 104 | |
| 156 | `Enviar a revisión` | 105 | |
| 157 | `Checklist de mínimos` | 112 | |
| 158 | `Completá estos puntos antes de enviar la ficha a revisión.` | 113 | |

### Campos de la ficha

`includes/class-destinos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 159 | `Destinos` | 68 | |
| 160 | `Destino` | 69 | |
| 161 | `Nuevo destino` | 70 | |
| 162 | `Editar destino` | 71 | |
| 163 | `Buscar destinos` | 72 | |
| 164 | `Categorías` | 119 | |
| 165 | `Categoría` | 119 | |
| 166 | `Zonas` | 126 | |
| 167 | `Zona` | 126 | |
| 168 | `Etiquetas` | 130 | |
| 169 | `Etiqueta` | 130 | |
| 170 | `Qué es` | 160 | |
| 171 | `Tipo` | 163 | |
| 172 | `Sitio — está siempre` | 167 | |
| 173 | `Evento — pasa en una fecha` | 168 | |
| 174 | `Un evento es un lugar con fecha: la fiesta patronal, una feria, un festival. Todo lo demás se carga igual.` | 170 | |
| 175 | `Empieza` | 173 | |
| 176 | `Día y hora de inicio.` | 177 | |
| 177 | `Termina` | 180 | |
| 178 | `Si dura un solo día, alcanza con la hora de cierre. Si es de varios días, poné el último.` | 184 | |
| 179 | `Identidad` | 189 | |
| 180 | `Foto de portada` | 191 | |
| 181 | `Crédito de las fotos` | 192 | |
| 182 | `Video (URL, opcional)` | 193 | |
| 183 | `Ubicación` | 213 | |
| 184 | `Enlace de Google Maps` | 216 | |
| 185 | `Buscá el lugar en Google Maps, tocá «Compartir» y pegá acá el enlace. De ahí sacamos el pin solos.` | 220 | |
| 186 | `Latitud (alternativa al enlace)` | 223 | |
| 187 | `Sólo si el enlace no alcanza: un enlace corto, o un lugar que Google no tiene.` | 226 | |
| 188 | `Longitud (alternativa al enlace)` | 229 | |
| 189 | `Estado del camino` | 233 | |
| 190 | `Datos prácticos` | 245 | |
| 191 | `Horario` | 247 | |
| 192 | `Costo / entrada` | 248 | |
| 193 | `Rango de precio` | 256 | |
| 194 | `Sin especificar` | 260 | |
| 195 | `Gratis` | 261 | |
| 196 | `$ — Muy barato` | 262 | |
| 197 | `$$ — Barato` | 263 | |
| 198 | `$$$ — Intermedio` | 264 | |
| 199 | `$$$$ — Caro` | 265 | |
| 200 | `Contacto del lugar` | 268 | |
| 201 | `Fuentes y referencias` | 272 | |
| 202 | `Fuentes / referencias` | 274 | |
| 203 | `Descripción` | 456 | |
| 204 | `Ubicación (enlace de Google Maps o coordenadas)` | 461 | |

### Salida de campo

`templates/sections/captura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 205 | `Salida de campo` | 8 | |
| 206 | `Captura en el lugar` | 11 | |
| 207 | `Sacá una foto, anotá lo importante y guardá la ubicación, incluso si no tenés señal. Todo queda guardado en tu dispositivo y podés sincronizarlo como borrador cuando vuelva la conexión.` | 13 | |
| 208 | `Nombre del lugar` | 17 | |
| 209 | `Nota rápida` | 18 | |
| 210 | `Foto` | 21 | |
| 211 | `Ubicación (GPS)` | 25 | |
| 212 | `Tomar ubicación` | 27 | |
| 213 | `Guardar captura` | 33 | |
| 214 | `Capturas pendientes` | 39 | |
| 215 | `Sincronizar` | 40 | |

### Cola de revisión

`templates/sections/revision.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 216 | `No encontramos esto.` | 19 | |
| 217 | `Revisión` | 28 | |
| 218 | `Volver a la cola` | 33 | |
| 219 | `Por %s` ⚠️ | 39 | |
| 220 | `Entradilla` | 74 | |
| 221 | `Cuerpo` | 80 | |
| 222 | `Descripción` | 80 | |
| 223 | `Ubicación` | 87 | |
| 224 | `Abrir el pin en Google Maps` | 88 | |
| 225 | `Paradas, en orden` | 93 | |
| 226 | `sin texto` 🔡 | 99 | |
| 227 | `Acciones` | 130 | |
| 228 | `Asignarme la revisión` | 133 | |
| 229 | `Comentarios para el autor` | 137 | |
| 230 | `Qué corregir o mejorar…` | 138 | |
| 231 | `Devolver con cambios` | 147 | |
| 232 | `Aprobar y publicar` | 149 | |
| 233 | `Historial` | 157 | |
| 234 | `Cola de revisión` | 185 | |
| 235 | `Taller editorial` | 188 | |
| 236 | `No hay nada esperando revisión. 🎉` | 192 | |
| 237 | `%1$s · esperó %2$s` ⚠️ | 208 | |
| 238 | `revisa %s` ⚠️ 🔡 | 213 | |

### Tareas

`templates/sections/tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 239 | `Tareas` | 11 | |
| 240 | `Asignaciones` | 14 | |
| 241 | `Tareas y pendientes por cubrir` | 15 | |
| 242 | `+ Nueva tarea o hueco` | 19 | |
| 243 | `Título` | 21 | |
| 244 | `Detalle` | 22 | |
| 245 | `Tipo` | 24 | |
| 246 | `Tarea asignada` | 26 | |
| 247 | `Hueco disponible` | 27 | |
| 248 | `Vence` | 30 | |
| 249 | `Destino (opcional)` | 31 | |
| 250 | `Asignar a (Alumnos)` | 37 | |
| 251 | `Crear` | 42 | |
| 252 | `No hay tareas por ahora.` | 49 | |
| 253 | `Hueco` | 61 | |
| 254 | `vence %s` ⚠️ 🔡 | 63 | |
| 255 | `Reclamar` | 69 | |
| 256 | `Marcar como completada` | 72 | |

### Tareas (estados y avisos)

`includes/class-tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 257 | `Tareas` | 27 | |
| 258 | `Tarea` | 27 | |
| 259 | `Pendiente` | 38 | |
| 260 | `En curso` | 39 | |
| 261 | `Completada` | 40 | |
| 262 | `La tarea necesita un título.` | 58 | |

### Equipo

`templates/sections/equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 263 | `Equipo` | 16 | |
| 264 | `Tu equipo` | 19 | |
| 265 | `Invitar a alguien` | 24 | |
| 266 | `Generá un enlace de invitación con el rol, el vencimiento y cuántas cuentas puede crear.` | 25 | |
| 267 | `Rol` | 30 | |
| 268 | `Vence en (días)` | 38 | |
| 269 | `Vacío o 0: no vence nunca.` | 40 | |
| 270 | `Cuántas cuentas puede crear` | 43 | |
| 271 | `Vacío o 0: sin límite.` | 45 | |
| 272 | `Crear enlace` | 54 | |
| 273 | `%1$d publicadas · %2$d en total` ⚠️ | 76 | |
| 274 | `Suspendida` | 86 | |
| 275 | `Cambiar rol` | 98 | |
| 276 | `Reactivar` | 105 | |
| 277 | `Suspender` | 105 | |
| 278 | `Deja de tener acceso al panel. Su cuenta y lo que publicó quedan como están. ¿Seguimos?` | 110 | |
| 279 | `Sacar del panel` | 113 | |
| 280 | `Invitaciones abiertas` | 123 | |
| 281 | `No hay ninguna esperando. Los enlaces que crees acá arriba aparecen en esta lista hasta que alguien los use o se venzan.` | 126 | |
| 282 | `El enlace deja de servir. ¿Seguimos?` | 143 | |
| 283 | `Revocar` | 146 | |
| 284 | `Enlace de invitación` | 152 | |
| 285 | `Copiar` | 153 | |

### Equipo (avisos)

`includes/class-equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 286 | `Esa persona no existe.` | 51 | |
| 287 | `A vos mismo no te podés editar desde acá.` | 54 | |
| 288 | `Ese rol no existe.` | 72 | |
| 289 | `%1$s ahora es %2$s.` ⚠️ | 85 | |
| 290 | `Suspendimos a %s. No va a poder entrar hasta que la reactives.` ⚠️ | 114 | |
| 291 | `%s puede volver a entrar.` ⚠️ | 119 | |
| 292 | `%s ya no entra al panel. Su cuenta y lo que publicó quedan como están.` ⚠️ | 142 | |
| 293 | `Esa invitación ya no existe.` | 150 | |
| 294 | `Invitación revocada. Ese enlace ya no sirve.` | 153 | |

### Reportes

`templates/sections/reportes.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 295 | `Reportes` | 12 | |
| 296 | `Métricas` | 15 | |
| 297 | `Actividad del portal` | 16 | |
| 298 | `Producción por autor` | 18 | |
| 299 | `%1$d publicadas / %2$d` ⚠️ | 27 | |
| 300 | `Estado del contenido` | 33 | |
| 301 | `Fichas publicadas sin portada` | 36 | |
| 302 | `Fichas sin verificar hace +6 meses` | 46 | |

### Biblioteca

`templates/sections/biblioteca.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 303 | `Biblioteca` | 21 | |
| 304 | `Medios` | 26 | |
| 305 | `Biblioteca de medios` | 27 | |
| 306 | `Subir fotos` | 37 | |
| 307 | `Podés elegir varias de una vez. JPG, PNG, WEBP o GIF.` | 40 | |
| 308 | `Subir` | 42 | |
| 309 | `Buscar una foto` | 50 | |
| 310 | `Sólo las mías` | 54 | |
| 311 | `Filtrar` | 56 | |
| 312 | `No encontramos ninguna foto con ese nombre.` | 64 | |
| 313 | `Todavía no hay fotos. Subí las primeras acá arriba.` | 66 | |
| 314 | `%d foto` ⚠️ | 77 | |
| 315 | `%d fotos` ⚠️ | 77 | |
| 316 | `Sin descripción` | 101 | |
| 317 | `Es la portada de %d ficha.` ⚠️ | 112 | |
| 318 | `Es la portada de %d fichas.` ⚠️ | 112 | |
| 319 | `Esta foto la subió otra persona, así que sólo podés verla.` | 120 | |
| 320 | `Nombre` | 128 | |
| 321 | `Descripción` | 132 | |
| 322 | `Qué se ve en la foto` | 134 | |
| 323 | `Crédito` | 137 | |
| 324 | `opcional` 🔡 | 137 | |
| 325 | `Quién la sacó` | 139 | |
| 326 | `Guardar` | 143 | |
| 327 | `Se borra la foto y no se puede deshacer. ¿Seguimos?` | 149 | |
| 328 | `Borrar foto` | 152 | |
| 329 | `Anteriores` | 165 | |
| 330 | `Página %1$d de %2$d` ⚠️ | 171 | |
| 331 | `Siguientes` | 179 | |

### Biblioteca (avisos)

`includes/class-medios.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 332 | `No elegiste ninguna foto.` | 141 | |
| 333 | `No pudimos subir ninguna: revisá que sean JPG, PNG, WEBP o GIF.` | 182 | |
| 334 | `Subimos %1$d foto. %2$d quedó afuera por el formato.` ⚠️ | 187 | |
| 335 | `Subimos %1$d fotos. %2$d quedaron afuera por el formato.` ⚠️ | 187 | |
| 336 | `Subimos %d foto.` ⚠️ | 194 | |
| 337 | `Subimos %d fotos.` ⚠️ | 194 | |
| 338 | `Esa foto no existe.` | 218 | |
| 339 | `Esa foto la subió otra persona.` | 221 | |
| 340 | `Listo, guardamos la foto.` | 234 | |
| 341 | `No la borramos: es la portada de %d ficha. Cambiala ahí primero.` ⚠️ | 250 | |
| 342 | `No la borramos: es la portada de %d fichas. Cambiala ahí primero.` ⚠️ | 250 | |
| 343 | `Foto borrada.` | 259 | |

### Estructura

`templates/sections/estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 344 | `Estructura` | 22 | |
| 345 | `Organización` | 27 | |
| 346 | `Estructura del sitio` | 28 | |
| 347 | `Esto lo organiza un Profesor. Podés ver cómo está armado, pero no cambiarlo.` | 34 | |
| 348 | `Todavía no hay ninguna.` | 51 | |
| 349 | `Descripción` | 70 | |
| 350 | `Imagen` | 74 | |
| 351 | `Subir foto` | 80 | |
| 352 | `Traducciones` | 89 | |
| 353 | `Guardar` | 104 | |
| 354 | `%d ficha` ⚠️ | 115 | |
| 355 | `%d fichas` ⚠️ | 115 | |
| 356 | `Se borra y no se puede deshacer. ¿Seguimos?` | 122 | |
| 357 | `Borrar` | 126 | |
| 358 | `Agregar` | 143 | |
| 359 | `El ícono y el color con que la app muestra cada categoría se eligen en App →` | 151 | |

### Estructura (nombres y avisos)

`includes/class-estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 360 | `Categorías` | 42 | |
| 361 | `Categoría` | 43 | |
| 362 | `Etiquetas` | 51 | |
| 363 | `Etiqueta` | 52 | |
| 364 | `Eso no se puede editar desde acá.` | 111 | |
| 365 | `Escribí un nombre.` | 120 | |
| 366 | `Ya existe una con ese nombre.` | 123 | |
| 367 | `Creamos «%s».` ⚠️ | 138 | |
| 368 | `Listo, guardamos los cambios.` | 196 | |
| 369 | `Eso ya no existe.` | 204 | |
| 370 | `No la borramos: %d ficha la usa. Movelas primero.` ⚠️ | 210 | |
| 371 | `No la borramos: %d fichas la usan. Movelas primero.` ⚠️ | 210 | |
| 372 | `Borramos «%s».` ⚠️ | 224 | |

### Buscar

`templates/sections/buscar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 373 | `Buscar` | 18 | |
| 374 | `Escribí algo en el buscador de arriba para encontrar fichas, artículos o recorridos.` | 23 | |
| 375 | `%1$d resultado para «%2$s»` ⚠️ | 28 | |
| 376 | `%1$d resultados para «%2$s»` ⚠️ | 28 | |
| 377 | `No encontramos resultados. Probá con otras palabras.` | 32 | |

### Mi perfil

`templates/sections/perfil.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 378 | `Mi perfil` | 25 | |
| 379 | `Fichas publicadas` | 39 | |
| 380 | `Mi portafolio` | 48 | |
| 381 | `Todavía no tenés nada publicado.` | 50 | |
| 382 | `Publicado` | 58 | |
| 383 | `Mis datos` | 66 | |
| 384 | `Nombre` | 73 | |
| 385 | `Correo` | 80 | |
| 386 | `Teléfono` | 85 | |
| 387 | `opcional` 🔡 | 85 | |
| 388 | `Foto` | 91 | |
| 389 | `opcional, JPG/PNG/WEBP, hasta 5 MB` 🔡 | 91 | |
| 390 | `Con el correo entrás al panel: si lo cambiás, la próxima vez iniciás sesión con el nuevo.` | 94 | |
| 391 | `Guardar cambios` | 97 | |
| 392 | `Contraseña` | 102 | |
| 393 | `Contraseña actual` | 109 | |
| 394 | `Contraseña nueva` | 115 | |
| 395 | `Repetila` | 119 | |
| 396 | `Cambiar contraseña` | 125 | |

### Mi perfil (avisos)

`includes/class-cuenta.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 397 | `Estás entrando como administrador de WordPress, que no tiene cuenta del panel que editar.` | 78 | |
| 398 | `Escribí tu nombre.` | 86 | |
| 399 | `Ese correo no parece válido.` | 89 | |
| 400 | `Ya hay una cuenta con ese correo.` | 95 | |
| 401 | `No pudimos guardar los cambios. Probá de nuevo.` | 104 | |
| 402 | `Listo, guardamos tus datos.` | 107 | |
| 403 | `La foto tiene que ser JPG, PNG o WEBP.` | 130 | |
| 404 | `La foto pesa más de 5 MB. Subí una más liviana.` | 133 | |
| 405 | `La contraseña actual no coincide.` | 229 | |
| 406 | `Las dos contraseñas nuevas tienen que ser iguales.` | 232 | |
| 407 | `No pudimos cambiar la contraseña. Probá de nuevo.` | 240 | |
| 408 | `Listo, cambiaste tu contraseña.` | 246 | |

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
| 456 | `Encargos con fecha límite. Los Alumnos pueden reclamar los que están disponibles y marcarlos como hechos.` | 34 | |
| 457 | `Equipo` | 35 | |
| 458 | `Quién entra al panel y con qué rol. Cambiar el rol, suspender, sacar del panel, e invitar gente nueva.` | 35 | |
| 459 | `Reportes` | 36 | |
| 460 | `Producción por autor y salud del contenido: lo publicado sin portada, y lo que no se verifica hace más de seis meses.` | 36 | |
| 461 | `Biblioteca` | 37 | |
| 462 | `La galería de fotos del panel: subir de a tandas, describir, dar crédito y borrar.` | 37 | |
| 463 | `Estructura` | 38 | |
| 464 | `Las categorías y etiquetas de las fichas: crear, renombrar en su lugar, y borrar lo que no esté en uso.` | 38 | |
| 465 | `Mi perfil` | 39 | |
| 466 | `Tu cuenta —nombre, correo, teléfono, foto y contraseña— y el portafolio de lo que publicaste.` | 39 | |
| 467 | `Cómo funciona` | 42 | |
| 468 | `¿Qué hace cada sección?` | 43 | |
| 469 | `Este es el panel de los Promotores Turísticos: acá se escribe, se revisa y se publica todo lo que la app de Caaguazú muestra —fichas de destinos y eventos, artículos y recorridos—. Los Alumnos crean; los Profesores revisan y publican.` | 45 | |
| 470 | `El flujo editorial` | 48 | |
| 471 | `Borrador` | 51 | |
| 472 | `Enviado` | 52 | |
| 473 | `En revisión` | 53 | |
| 474 | `Necesita cambios` | 54 | |
| 475 | `Publicado` | 55 | |
| 476 | `Solo lo aprobado por un Profesor llega a la app. Un Profesor publica directo, sin pasar por revisión, y también puede editar algo ya publicado sin que vuelva a la cola; un Alumno siempre pasa por revisión.` | 58 | |
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
| 498 | `Crear cuenta` | 23 | |
| 499 | `Crear tu cuenta` | 26 | |
| 500 | `Del Portal de Promotores Turísticos de Caaguazú: acá el equipo escribe lo que después muestra la app de turismo.` | 27 | |
| 501 | `Este enlace ya se usó las veces que tenía permitidas. Pedí uno nuevo a quien te invitó.` | 38 | |
| 502 | `Este enlace venció. Pedí uno nuevo a quien te invitó.` | 41 | |
| 503 | `Este enlace fue dado de baja. Pedí uno nuevo a quien te invitó.` | 44 | |
| 504 | `Para crear una cuenta hace falta un enlace de invitación. Lo genera un Profesor desde el panel, en Equipo; pediselo a quien te sumó al curso.` | 47 | |
| 505 | `Ya tengo una cuenta` | 52 | |
| 506 | `Tu invitación es válida. Vas a entrar como %s.` ⚠️ | 70 | |
| 507 | `Como Profesor vas a poder cargar y publicar contenido sin que lo revise nadie, revisar lo que cargan los Alumnos, y sumar gente al equipo.` | 83 | |
| 508 | `Como Alumno vas a poder cargar fichas, artículos y recorridos. Lo que escribas lo revisa un Profesor antes de que salga en la app.` | 84 | |
| 509 | `Cómo sigue` | 90 | |
| 510 | `Completás estos cuatro datos.` | 92 | |
| 511 | `Entrás al panel al instante: no hay que esperar que nadie apruebe nada.` | 93 | |
| 512 | `De ahí en más entrás con tu correo y tu contraseña. Este enlace no se usa más.` | 94 | |
| 513 | `Tu nombre` | 104 | |
| 514 | `Como querés que te vean en el equipo, y como se firma lo que publiques. Podés cambiarlo después.` | 106 | |
| 515 | `Correo` | 109 | |
| 516 | `Con este correo vas a entrar de acá en adelante. Usá uno al que entres de verdad: es por donde se recupera la contraseña.` | 111 | |
| 517 | `Teléfono` | 114 | |
| 518 | `Ej.: 0981 123 456` | 115 | |
| 519 | `Para que el equipo te pueda ubicar. No se publica en ningún lado ni sale en la app.` | 116 | |
| 520 | `Contraseña` | 119 | |
| 521 | `Seis caracteres o más. Es nueva, no la de tu correo.` | 121 | |
| 522 | `Crear cuenta y entrar` | 124 | |
| 523 | `Este enlace sirve hasta el %s.` ⚠️ | 131 | |

### Recuperar contraseña

`templates/auth/recuperar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 524 | `Recuperar contraseña` | 10 | |
| 525 | `Te enviamos un enlace para restablecer tu contraseña.` | 14 | |
| 526 | `Email` | 27 | |
| 527 | `Enviar enlace` | 30 | |
| 528 | `Volver a iniciar sesión` | 34 | |

### Contraseña nueva

`templates/auth/restablecer.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 529 | `Nueva contraseña` | 12 | |
| 530 | `Nueva contraseña (6 o más caracteres)` | 28 | |
| 531 | `Guardar contraseña` | 31 | |
| 532 | `El enlace no es válido o ya venció. Pedí uno nuevo.` | 34 | |
| 533 | `Pedir un nuevo enlace` | 36 | |

### Marco de acceso

`templates/auth-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 534 | `Acceso` | 8 | |

### Errores y avisos de acceso

`includes/class-auth.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 535 | `No tenés autorización para hacer esto.` | 51 | |
| 536 | `No se pudo crear la invitación: la base de datos rechazó el registro. Avisale a quien administra el sitio.` | 64 | |
| 537 | `Enlace de invitación creado. Lo tenés abajo, en «Invitaciones abiertas».` | 66 | |
| 538 | `Tu sesión venció. Recargá la página.` | 162 | |
| 539 | `Necesitás una invitación válida para registrarte.` | 244 | |
| 540 | `Completá usuario, email, teléfono y una contraseña de al menos 6 caracteres.` | 262 | |
| 541 | `Ese email ya está registrado.` | 270 | |
| 542 | `Si la cuenta existe, te enviamos un email con las instrucciones.` | 320 | |
| 543 | `El enlace para restablecer la contraseña venció o no es válido.` | 338 | |

### Invitaciones

`includes/class-invitations.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 544 | `Válida` | 179 | |
| 545 | `Agotada` | 180 | |
| 546 | `Expirada` | 181 | |
| 547 | `Revocada` | 182 | |
| 548 | `Inválida` | 183 | |
| 549 | `No vence` | 211 | |
| 550 | `Vence el %s` ⚠️ | 214 | |
| 551 | `%d cuenta creada, sin límite` ⚠️ | 222 | |
| 552 | `%d cuentas creadas, sin límite` ⚠️ | 222 | |
| 553 | `%1$d de %2$d` ⚠️ | 225 | |

### Guardas de acceso

`includes/class-router.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 554 | `No tenés acceso a este panel.` | 292 | |
| 555 | `Acceso denegado` | 293 | |

### Guardas de sección

`includes/class-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 556 | `No tenés permiso para ver esta sección.` | 42 | |
| 557 | `Acceso denegado` | 43 | |

### Sin conexión (PWA)

`includes/class-pwa.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 558 | `Sin conexión` | 169 | |
| 559 | `Promotores Turísticos` | 176 | |
| 560 | `Estás sin conexión` | 177 | |
| 561 | `No pudimos cargar esta pantalla. Revisá tu conexión e intentá de nuevo.` | 178 | |
| 562 | `Reintentar` | 179 | |

## wp-admin y mensajes de sistema

Pantallas de administración y respuestas de las acciones.

### Pantallas de wp-admin

`includes/class-admin.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 563 | `Portal Turismo` | 85 | |
| 564 | `Registros` | 88 | |
| 565 | `Invitaciones` | 91 | |
| 566 | `Actualizaciones` | 93 | |
| 567 | `No tenés autorización para hacer esto.` | 108 | |
| 568 | `Usuarios` | 128 | |
| 569 | `Entradas` | 129 | |
| 570 | `Fecha` | 133 | |
| 571 | `Usuario` | 133 | |
| 572 | `Acción` | 134 | |
| 573 | `Elemento` | 134 | |
| 574 | `IP` | 135 | |
| 575 | `Detalle` | 135 | |
| 576 | `No hay registros.` | 139 | |
| 577 | `Invitar a alguien` | 187 | |
| 578 | `Rol` | 194 | |
| 579 | `Vence en (días)` | 204 | |
| 580 | `Vacío o 0: no vence nunca.` | 212 | |
| 581 | `Cuántas cuentas puede crear` | 216 | |
| 582 | `Vacío o 0: sin límite.` | 219 | |
| 583 | `Email (opcional)` | 223 | |
| 584 | `Crear enlace` | 227 | |
| 585 | `Invitaciones abiertas` | 230 | |
| 586 | `No hay ninguna esperando.` | 235 | |
| 587 | `Vence` | 240 | |
| 588 | `Usos` | 241 | |
| 589 | `Enlace` | 242 | |
| 590 | `Copiar` | 256 | |
| 591 | `El enlace deja de servir. ¿Seguimos?` | 261 | |
| 592 | `Revocar` | 266 | |
| 593 | `Copiado` | 284 | |
| 594 | `No se pudo crear la invitación: la base de datos rechazó el registro. Revisá que las tablas del plugin estén al día.` | 318 | |
| 595 | `Enlace de invitación creado. Lo tenés abajo, en «Invitaciones abiertas».` | 320 | |
| 596 | `Invitación revocada. Ese enlace ya no sirve.` | 326 | |
| 597 | `Esa invitación ya no existe.` | 328 | |
| 598 | `Actualizaciones del portal` | 368 | |
| 599 | `No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.` | 372 | |
| 600 | `Atención: la versión del encabezado del plugin (%1$s) no coincide con PROMOTUR_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.` ⚠️ | 379 | |
| 601 | `Versión instalada` | 388 | |
| 602 | `Última disponible` | 389 | |
| 603 | `Actualizar ahora` | 398 | |
| 604 | `Estás al día.` | 400 | |
| 605 | `Última comprobación` | 403 | |
| 606 | `nunca` 🔡 | 404 | |
| 607 | `Repositorio` | 406 | |
| 608 | `Buscar actualizaciones ahora` | 415 | |
| 609 | `Limpiar caché del actualizador` | 421 | |
| 610 | `Token de GitHub` | 425 | |
| 611 | `Definido en wp-config.php mediante PROMOTUR_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.` | 427 | |
| 612 | `El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.` | 429 | |
| 613 | `Token` | 435 | |
| 614 | `•••• guardado (dejá vacío para conservarlo)` | 436 | |
| 615 | `Eliminar el token guardado` | 438 | |
| 616 | `Guardar token` | 442 | |
| 617 | `Hay una nueva versión disponible: %s.` ⚠️ | 461 | |
| 618 | `No hay actualizaciones: ya tenés la última versión.` | 463 | |
| 619 | `El verificador de actualizaciones no está disponible.` | 466 | |
| 620 | `Caché del actualizador limpiada.` | 475 | |
| 621 | `El token está definido en wp-config.php y no se puede cambiar desde acá.` | 480 | |
| 622 | `Token eliminado.` | 487 | |
| 623 | `Token guardado.` | 490 | |
| 624 | `No hubo cambios en el token.` | 492 | |

### Respuestas del editor

`includes/class-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 625 | `No tenés permiso para hacer esto.` | 61 | |
| 626 | `Ese tipo de contenido no existe.` | 74 | |
| 627 | `No podés editar esto.` | 130 | |
| 628 | `(sin título)` | 146 | |
| 629 | `Borrador guardado.` | 207 | |
| 630 | `Guardado. Como editaste algo ya publicado, tendrá que pasar por una nueva revisión.` | 214 | |
| 631 | `De ese enlace no pudimos sacar el pin (los enlaces cortos no lo traen). Cargá la latitud y la longitud a mano, o pegá el enlace largo.` | 254 | |
| 632 | `Un recorrido lleva hasta %d paradas, y sin repetir el mismo sitio. Guardamos las que entraron.` ⚠️ | 309 | |
| 633 | `Esto no se puede enviar.` | 334 | |
| 634 | `Faltan datos obligatorios. Completá el checklist antes de enviar.` | 338 | |
| 635 | `Publicación directa: tu rol no pasa por revisión.` | 348 | |
| 636 | `¡Publicado!` | 349 | |
| 637 | `¡Enviado a revisión!` | 353 | |
| 638 | `Eso no existe o no es contenido del panel.` | 366 | |
| 639 | `Te asignaste la revisión.` | 375 | |
| 640 | `Aprobado y publicado.` | 386 | |
| 641 | `Escribí los comentarios para el autor.` | 394 | |
| 642 | `Devuelto al autor con comentarios.` | 398 | |
| 643 | `No recibimos ninguna imagen.` | 405 | |
| 644 | `Solo podés subir imágenes.` | 409 | |

### Respuestas de gestión

`includes/class-gestion-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 645 | `No tenés permiso para hacer esto.` | 29 | |
| 646 | `Tarea creada.` | 46 | |
| 647 | `La tarea no es válida.` | 53 | |
| 648 | `Reclamaste esta tarea. Ya podés trabajar en ella.` | 56 | |
| 649 | `Tarea completada. 🎉` | 70 | |

### Avisos del plugin

`caaguazu-portal.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 650 | `Caaguazú Portal necesita tener activo el plugin «Caaguazú Cuentas» para funcionar. El inicio de sesión de los Promotores ya no usa los usuarios de WordPress. Activá el plugin desde Plugins para volver a usar el panel.` | 98 | |
| 651 | `Portal de Promotores` | 119 | |
