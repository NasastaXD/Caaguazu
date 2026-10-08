# Textos del panel

Todo lo que un usuario lee en el panel, sacado de las fuentes el 2026-10-08. **653 textos.**

Se regenera con `php tools/textos-del-panel.php > docs/textos-del-panel.md`.

- Escribí el reemplazo en la columna **Nuevo texto**; lo que quede en blanco se deja como está.
- Los marcados con ⚠️ llevan un hueco (`%s`, `%d`, `%1$s`) que el código rellena: hay que conservarlo tal cual y en el mismo orden.
- Los `[FALTA: …]` son huecos a propósito: textos que el diseño pide y que todavía no escribió nadie.
- Los marcados con 🔡 arrancan en minúscula.

## Empiezan en minúscula

Casi todas son fragmentos escritos para leerse **después de un número** ("4 esperan revisión") o para ir dentro de una frase. Si se quieren usar como título, hay que reescribirlas enteras, no sólo poner la mayúscula.

| # | Texto | Dónde |
| --- | --- | --- |
| 67 | `atrás` | Notificaciones |
| 228 | `sin texto` | Cola de revisión |
| 240 | `revisa %s` | Cola de revisión |
| 256 | `vence %s` | Tareas |
| 326 | `opcional` | Biblioteca |
| 389 | `opcional` | Mi perfil |
| 391 | `opcional, JPG/PNG/WEBP, hasta 5 MB` | Mi perfil |
| 608 | `nunca` | Pantallas de wp-admin |

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
| 16 | `GESTIÓN` | 680 | |
| 17 | `Inicio` | 682 | |
| 18 | `Mis contenidos` | 685 | |
| 19 | `Nueva ficha` | 689 | |
| 20 | `Salida de campo` | 690 | |
| 21 | `Cola de revisión` | 693 | |
| 22 | `Tareas` | 694 | |
| 23 | `CONTENIDO` | 698 | |
| 24 | `Inventario turístico` | 700 | |
| 25 | `Artículos` | 701 | |
| 26 | `Recorridos` | 702 | |
| 27 | `PORTAL` | 706 | |
| 28 | `Equipo` | 708 | |
| 29 | `Reportes` | 709 | |
| 30 | `Biblioteca` | 710 | |
| 31 | `Estructura` | 711 | |
| 32 | `App` | 719 | |
| 33 | `Mi perfil` | 739 | |
| 34 | `Ayuda` | 740 | |

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
| 46 | `Ver la web` | 44 | |
| 47 | `Ver la web de turismo (se abre en otra pestaña)` | 44 | |
| 48 | `Cambiar tema` | 49 | |
| 49 | `Notificaciones` | 55 | |
| 50 | `Marcar todo como leído` | 67 | |
| 51 | `No hay novedades por ahora. ✨` | 73 | |

### Barra inferior (teléfono)

`templates/partials/bottomnav.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 52 | `Inicio` | 10 | |
| 53 | `Contenidos` | 11 | |
| 54 | `Campo` | 12 | |
| 55 | `Revisar` | 13 | |
| 56 | `Perfil` | 14 | |
| 57 | `Navegación rápida` | 20 | |

### Mensajes del JavaScript

`includes/class-assets.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 58 | `Instalar app` | 135 | |
| 59 | `Enviando…` | 136 | |
| 60 | `Algo salió mal. Probá de nuevo.` | 137 | |
| 61 | `Guardado` | 138 | |
| 62 | `¿Querés confirmar esta acción?` | 139 | |
| 63 | `Faltan algunos datos obligatorios.` | 140 | |
| 64 | `Foto subida.` | 141 | |
| 65 | `Copiado` | 142 | |

### Notificaciones

`includes/class-notifications.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 66 | `«%s» está esperando revisión` ⚠️ | 81 | |
| 67 | `atrás` 🔡 | 83 | |
| 68 | `«%s» necesita algunos cambios` ⚠️ | 106 | |
| 69 | `Notificaciones marcadas como leídas.` | 181 | |

### Estados del flujo editorial

`includes/class-editorial.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 70 | `Ficha` | 97 | |
| 71 | `Borrador` | 137 | |
| 72 | `Enviado` | 138 | |
| 73 | `En revisión` | 139 | |
| 74 | `Necesita cambios` | 140 | |
| 75 | `Aprobado` | 141 | |
| 76 | `Publicado` | 142 | |
| 77 | `Despublicado` | 143 | |
| 78 | `Archivado` | 144 | |
| 79 | `Retirar de revisión` | 240 | |
| 80 | `¿Sacarlo de la cola de revisión y volverlo a borrador?` | 242 | |
| 81 | `Despublicar` | 249 | |
| 82 | `Esto está publicado y la app lo está mostrando. ¿Sacarlo de circulación? El contenido se conserva entero.` | 251 | |
| 83 | `Publicar de nuevo` | 258 | |
| 84 | `Archivar` | 267 | |
| 85 | `¿Archivarlo? Sale de circulación y se puede recuperar cuando quieras.` | 269 | |
| 86 | `Título` | 370 | |
| 87 | `Nombre del recorrido` | 372 | |
| 88 | `Nombre del destino` | 374 | |
| 89 | `Faltan fuentes o referencias.` | 392 | |
| 90 | `Mejorá las fotos: cuidá la luz, el encuadre y la portada.` | 393 | |
| 91 | `Verificá los horarios y los costos.` | 394 | |
| 92 | `Revisá la ortografía y la redacción.` | 395 | |
| 93 | `Comprobá que el enlace de Google Maps caiga en el lugar correcto.` | 396 | |
| 94 | `Revisá el orden de las paradas: no cuenta lo mismo al revés.` | 397 | |

### Guardas de las acciones

`includes/class-acciones.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 95 | `Esa acción no existe.` | 161 | |
| 96 | `Esa acción sólo acepta envíos.` | 165 | |
| 97 | `Tu sesión venció. Volvé a entrar.` | 172 | |
| 98 | `Tu sesión venció. Recargá la página.` | 179 | |
| 99 | `No tenés autorización para hacer esto.` | 184 | |

### Nombres de los roles

`includes/class-roles.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 100 | `Profesor` | 68 | |
| 101 | `Alumno` | 69 | |
| 102 | `Visitante` | 70 | |

## Las secciones

Una tabla por pantalla del panel.

### Inicio

`templates/sections/home.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 103 | `Esperan revisión` | 35 | |
| 104 | `Publicados` | 36 | |
| 105 | `Esperan tu corrección` | 39 | |
| 106 | `En proceso` | 40 | |
| 107 | `Inicio` | 52 | |
| 108 | `Tu actividad de hoy` | 57 | |
| 109 | `Hola, %s 👋` ⚠️ | 60 | |
| 110 | `Actividad reciente` | 85 | |
| 111 | `Fichas, artículos y recorridos creados, enviados o publicados en los últimos 7 días.` | 87 | |
| 112 | `Actividad de los últimos %d día` ⚠️ | 109 | |
| 113 | `Actividad de los últimos %d días` ⚠️ | 109 | |
| 114 | `Accesos rápidos` | 125 | |
| 115 | `Crear una ficha` | 130 | |
| 116 | `Mis contenidos` | 133 | |
| 117 | `Cola de revisión` | 136 | |
| 118 | `Equipo` | 139 | |
| 119 | `Mi perfil` | 141 | |

### Mis contenidos

`templates/sections/mis-contenidos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 120 | `Contenidos del equipo` | 76 | |
| 121 | `Mis contenidos` | 76 | |
| 122 | `En curso` | 79 | |
| 123 | `Publicados` | 80 | |
| 124 | `Despublicados` | 81 | |
| 125 | `Archivados` | 82 | |
| 126 | `Papelera` | 83 | |
| 127 | `Lo que escribe todo el equipo` | 111 | |
| 128 | `Tu producción` | 111 | |
| 129 | `+ Nueva ficha` | 114 | |
| 130 | `De quién` | 119 | |
| 131 | `Míos` | 120 | |
| 132 | `Del equipo` | 120 | |
| 133 | `Filtrar por estado` | 131 | |
| 134 | `La papelera está vacía.` | 144 | |
| 135 | `Nadie del equipo tiene nada en ese estado.` | 146 | |
| 136 | `Nadie del equipo tiene nada en curso.` | 148 | |
| 137 | `No tenés nada en ese estado.` | 150 | |
| 138 | `Todavía no creaste nada. Podés empezar por una ficha, un artículo o un recorrido.` | 152 | |
| 139 | `Nueva ficha` | 154 | |
| 140 | `Nuevo artículo` | 155 | |
| 141 | `Nuevo recorrido` | 156 | |
| 142 | `Lo borrado se recupera acá, como borrador. Nada se pierde de verdad hasta que alguien lo vacíe.` | 161 | |
| 143 | `(sin título)` | 166 | |
| 144 | `Recuperar` | 170 | |

### Editor de ficha

`templates/sections/editor.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 145 | `No podés editar esta ficha.` | 16 | |
| 146 | `Editar ficha` | 28 | |
| 147 | `Nueva ficha` | 28 | |
| 148 | `Ficha del destino` | 36 | |
| 149 | `Comentarios del revisor` | 44 | |
| 150 | `Nombre del destino` | 63 | |
| 151 | `Descripción` | 68 | |
| 152 | `Clasificación` | 73 | |
| 153 | `Categoría` | 76 | |
| 154 | `Etiquetas` | 79 | |
| 155 | `Separadas por comas: «con niños», «gratis», «llega colectivo».` | 81 | |
| 156 | `📍 Usar mi ubicación actual` | 97 | |
| 157 | `Guardar borrador` | 104 | |
| 158 | `Enviar a revisión` | 105 | |
| 159 | `Checklist de mínimos` | 112 | |
| 160 | `Completá estos puntos antes de enviar la ficha a revisión.` | 113 | |

### Campos de la ficha

`includes/class-destinos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 161 | `Destinos` | 68 | |
| 162 | `Destino` | 69 | |
| 163 | `Nuevo destino` | 70 | |
| 164 | `Editar destino` | 71 | |
| 165 | `Buscar destinos` | 72 | |
| 166 | `Categorías` | 119 | |
| 167 | `Categoría` | 119 | |
| 168 | `Zonas` | 126 | |
| 169 | `Zona` | 126 | |
| 170 | `Etiquetas` | 130 | |
| 171 | `Etiqueta` | 130 | |
| 172 | `Qué es` | 160 | |
| 173 | `Tipo` | 163 | |
| 174 | `Sitio — está siempre` | 167 | |
| 175 | `Evento — pasa en una fecha` | 168 | |
| 176 | `Un evento es un lugar con fecha: la fiesta patronal, una feria, un festival. Todo lo demás se carga igual.` | 170 | |
| 177 | `Empieza` | 173 | |
| 178 | `Día y hora de inicio.` | 177 | |
| 179 | `Termina` | 180 | |
| 180 | `Si dura un solo día, alcanza con la hora de cierre. Si es de varios días, poné el último.` | 184 | |
| 181 | `Identidad` | 189 | |
| 182 | `Foto de portada` | 191 | |
| 183 | `Crédito de las fotos` | 192 | |
| 184 | `Video (URL, opcional)` | 193 | |
| 185 | `Ubicación` | 213 | |
| 186 | `Enlace de Google Maps` | 216 | |
| 187 | `Buscá el lugar en Google Maps, tocá «Compartir» y pegá acá el enlace. De ahí sacamos el pin solos.` | 220 | |
| 188 | `Latitud (alternativa al enlace)` | 223 | |
| 189 | `Sólo si el enlace no alcanza: un enlace corto, o un lugar que Google no tiene.` | 226 | |
| 190 | `Longitud (alternativa al enlace)` | 229 | |
| 191 | `Estado del camino` | 233 | |
| 192 | `Datos prácticos` | 245 | |
| 193 | `Horario` | 247 | |
| 194 | `Costo / entrada` | 248 | |
| 195 | `Rango de precio` | 256 | |
| 196 | `Sin especificar` | 260 | |
| 197 | `Gratis` | 261 | |
| 198 | `$ — Muy barato` | 262 | |
| 199 | `$$ — Barato` | 263 | |
| 200 | `$$$ — Intermedio` | 264 | |
| 201 | `$$$$ — Caro` | 265 | |
| 202 | `Contacto del lugar` | 268 | |
| 203 | `Fuentes y referencias` | 272 | |
| 204 | `Fuentes / referencias` | 274 | |
| 205 | `Descripción` | 456 | |
| 206 | `Ubicación (enlace de Google Maps o coordenadas)` | 461 | |

### Salida de campo

`templates/sections/captura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 207 | `Salida de campo` | 8 | |
| 208 | `Captura en el lugar` | 11 | |
| 209 | `Sacá una foto, anotá lo importante y guardá la ubicación, incluso si no tenés señal. Todo queda guardado en tu dispositivo y podés sincronizarlo como borrador cuando vuelva la conexión.` | 13 | |
| 210 | `Nombre del lugar` | 17 | |
| 211 | `Nota rápida` | 18 | |
| 212 | `Foto` | 21 | |
| 213 | `Ubicación (GPS)` | 25 | |
| 214 | `Tomar ubicación` | 27 | |
| 215 | `Guardar captura` | 33 | |
| 216 | `Capturas pendientes` | 39 | |
| 217 | `Sincronizar` | 40 | |

### Cola de revisión

`templates/sections/revision.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 218 | `No encontramos esto.` | 19 | |
| 219 | `Revisión` | 28 | |
| 220 | `Volver a la cola` | 33 | |
| 221 | `Por %s` ⚠️ | 39 | |
| 222 | `Entradilla` | 74 | |
| 223 | `Cuerpo` | 80 | |
| 224 | `Descripción` | 80 | |
| 225 | `Ubicación` | 87 | |
| 226 | `Abrir el pin en Google Maps` | 88 | |
| 227 | `Paradas, en orden` | 93 | |
| 228 | `sin texto` 🔡 | 99 | |
| 229 | `Acciones` | 130 | |
| 230 | `Asignarme la revisión` | 133 | |
| 231 | `Comentarios para el autor` | 137 | |
| 232 | `Qué corregir o mejorar…` | 138 | |
| 233 | `Devolver con cambios` | 147 | |
| 234 | `Aprobar y publicar` | 149 | |
| 235 | `Historial` | 157 | |
| 236 | `Cola de revisión` | 185 | |
| 237 | `Taller editorial` | 188 | |
| 238 | `No hay nada esperando revisión. 🎉` | 192 | |
| 239 | `%1$s · esperó %2$s` ⚠️ | 208 | |
| 240 | `revisa %s` ⚠️ 🔡 | 213 | |

### Tareas

`templates/sections/tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 241 | `Tareas` | 11 | |
| 242 | `Asignaciones` | 14 | |
| 243 | `Tareas y pendientes por cubrir` | 15 | |
| 244 | `+ Nueva tarea o hueco` | 19 | |
| 245 | `Título` | 21 | |
| 246 | `Detalle` | 22 | |
| 247 | `Tipo` | 24 | |
| 248 | `Tarea asignada` | 26 | |
| 249 | `Hueco disponible` | 27 | |
| 250 | `Vence` | 30 | |
| 251 | `Destino (opcional)` | 31 | |
| 252 | `Asignar a (Alumnos)` | 37 | |
| 253 | `Crear` | 42 | |
| 254 | `No hay tareas por ahora.` | 49 | |
| 255 | `Hueco` | 61 | |
| 256 | `vence %s` ⚠️ 🔡 | 63 | |
| 257 | `Reclamar` | 69 | |
| 258 | `Marcar como completada` | 72 | |

### Tareas (estados y avisos)

`includes/class-tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 259 | `Tareas` | 27 | |
| 260 | `Tarea` | 27 | |
| 261 | `Pendiente` | 38 | |
| 262 | `En curso` | 39 | |
| 263 | `Completada` | 40 | |
| 264 | `La tarea necesita un título.` | 58 | |

### Equipo

`templates/sections/equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 265 | `Equipo` | 16 | |
| 266 | `Tu equipo` | 19 | |
| 267 | `Invitar a alguien` | 24 | |
| 268 | `Generá un enlace de invitación con el rol, el vencimiento y cuántas cuentas puede crear.` | 25 | |
| 269 | `Rol` | 30 | |
| 270 | `Vence en (días)` | 38 | |
| 271 | `Vacío o 0: no vence nunca.` | 40 | |
| 272 | `Cuántas cuentas puede crear` | 43 | |
| 273 | `Vacío o 0: sin límite.` | 45 | |
| 274 | `Crear enlace` | 54 | |
| 275 | `%1$d publicadas · %2$d en total` ⚠️ | 76 | |
| 276 | `Suspendida` | 86 | |
| 277 | `Cambiar rol` | 98 | |
| 278 | `Reactivar` | 105 | |
| 279 | `Suspender` | 105 | |
| 280 | `Deja de tener acceso al panel. Su cuenta y lo que publicó quedan como están. ¿Seguimos?` | 110 | |
| 281 | `Sacar del panel` | 113 | |
| 282 | `Invitaciones abiertas` | 123 | |
| 283 | `No hay ninguna esperando. Los enlaces que crees acá arriba aparecen en esta lista hasta que alguien los use o se venzan.` | 126 | |
| 284 | `El enlace deja de servir. ¿Seguimos?` | 143 | |
| 285 | `Revocar` | 146 | |
| 286 | `Enlace de invitación` | 152 | |
| 287 | `Copiar` | 153 | |

### Equipo (avisos)

`includes/class-equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 288 | `Esa persona no existe.` | 51 | |
| 289 | `A vos mismo no te podés editar desde acá.` | 54 | |
| 290 | `Ese rol no existe.` | 72 | |
| 291 | `%1$s ahora es %2$s.` ⚠️ | 85 | |
| 292 | `Suspendimos a %s. No va a poder entrar hasta que la reactives.` ⚠️ | 114 | |
| 293 | `%s puede volver a entrar.` ⚠️ | 119 | |
| 294 | `%s ya no entra al panel. Su cuenta y lo que publicó quedan como están.` ⚠️ | 142 | |
| 295 | `Esa invitación ya no existe.` | 150 | |
| 296 | `Invitación revocada. Ese enlace ya no sirve.` | 153 | |

### Reportes

`templates/sections/reportes.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 297 | `Reportes` | 12 | |
| 298 | `Métricas` | 15 | |
| 299 | `Actividad del portal` | 16 | |
| 300 | `Producción por autor` | 18 | |
| 301 | `%1$d publicadas / %2$d` ⚠️ | 27 | |
| 302 | `Estado del contenido` | 33 | |
| 303 | `Fichas publicadas sin portada` | 36 | |
| 304 | `Fichas sin verificar hace +6 meses` | 46 | |

### Biblioteca

`templates/sections/biblioteca.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 305 | `Biblioteca` | 21 | |
| 306 | `Medios` | 26 | |
| 307 | `Biblioteca de medios` | 27 | |
| 308 | `Subir fotos` | 37 | |
| 309 | `Podés elegir varias de una vez. JPG, PNG, WEBP o GIF.` | 40 | |
| 310 | `Subir` | 42 | |
| 311 | `Buscar una foto` | 50 | |
| 312 | `Sólo las mías` | 54 | |
| 313 | `Filtrar` | 56 | |
| 314 | `No encontramos ninguna foto con ese nombre.` | 64 | |
| 315 | `Todavía no hay fotos. Subí las primeras acá arriba.` | 66 | |
| 316 | `%d foto` ⚠️ | 77 | |
| 317 | `%d fotos` ⚠️ | 77 | |
| 318 | `Sin descripción` | 101 | |
| 319 | `Es la portada de %d ficha.` ⚠️ | 112 | |
| 320 | `Es la portada de %d fichas.` ⚠️ | 112 | |
| 321 | `Esta foto la subió otra persona, así que sólo podés verla.` | 120 | |
| 322 | `Nombre` | 128 | |
| 323 | `Descripción` | 132 | |
| 324 | `Qué se ve en la foto` | 134 | |
| 325 | `Crédito` | 137 | |
| 326 | `opcional` 🔡 | 137 | |
| 327 | `Quién la sacó` | 139 | |
| 328 | `Guardar` | 143 | |
| 329 | `Se borra la foto y no se puede deshacer. ¿Seguimos?` | 149 | |
| 330 | `Borrar foto` | 152 | |
| 331 | `Anteriores` | 165 | |
| 332 | `Página %1$d de %2$d` ⚠️ | 171 | |
| 333 | `Siguientes` | 179 | |

### Biblioteca (avisos)

`includes/class-medios.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 334 | `No elegiste ninguna foto.` | 141 | |
| 335 | `No pudimos subir ninguna: revisá que sean JPG, PNG, WEBP o GIF.` | 182 | |
| 336 | `Subimos %1$d foto. %2$d quedó afuera por el formato.` ⚠️ | 187 | |
| 337 | `Subimos %1$d fotos. %2$d quedaron afuera por el formato.` ⚠️ | 187 | |
| 338 | `Subimos %d foto.` ⚠️ | 194 | |
| 339 | `Subimos %d fotos.` ⚠️ | 194 | |
| 340 | `Esa foto no existe.` | 218 | |
| 341 | `Esa foto la subió otra persona.` | 221 | |
| 342 | `Listo, guardamos la foto.` | 234 | |
| 343 | `No la borramos: es la portada de %d ficha. Cambiala ahí primero.` ⚠️ | 250 | |
| 344 | `No la borramos: es la portada de %d fichas. Cambiala ahí primero.` ⚠️ | 250 | |
| 345 | `Foto borrada.` | 259 | |

### Estructura

`templates/sections/estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 346 | `Estructura` | 22 | |
| 347 | `Organización` | 27 | |
| 348 | `Estructura del sitio` | 28 | |
| 349 | `Esto lo organiza un Profesor. Podés ver cómo está armado, pero no cambiarlo.` | 34 | |
| 350 | `Todavía no hay ninguna.` | 51 | |
| 351 | `Descripción` | 70 | |
| 352 | `Imagen` | 74 | |
| 353 | `Subir foto` | 80 | |
| 354 | `Traducciones` | 89 | |
| 355 | `Guardar` | 104 | |
| 356 | `%d ficha` ⚠️ | 115 | |
| 357 | `%d fichas` ⚠️ | 115 | |
| 358 | `Se borra y no se puede deshacer. ¿Seguimos?` | 122 | |
| 359 | `Borrar` | 126 | |
| 360 | `Agregar` | 143 | |
| 361 | `El ícono y el color con que la app muestra cada categoría se eligen en App →` | 151 | |

### Estructura (nombres y avisos)

`includes/class-estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 362 | `Categorías` | 42 | |
| 363 | `Categoría` | 43 | |
| 364 | `Etiquetas` | 51 | |
| 365 | `Etiqueta` | 52 | |
| 366 | `Eso no se puede editar desde acá.` | 111 | |
| 367 | `Escribí un nombre.` | 120 | |
| 368 | `Ya existe una con ese nombre.` | 123 | |
| 369 | `Creamos «%s».` ⚠️ | 138 | |
| 370 | `Listo, guardamos los cambios.` | 196 | |
| 371 | `Eso ya no existe.` | 204 | |
| 372 | `No la borramos: %d ficha la usa. Movelas primero.` ⚠️ | 210 | |
| 373 | `No la borramos: %d fichas la usan. Movelas primero.` ⚠️ | 210 | |
| 374 | `Borramos «%s».` ⚠️ | 224 | |

### Buscar

`templates/sections/buscar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 375 | `Buscar` | 18 | |
| 376 | `Escribí algo en el buscador de arriba para encontrar fichas, artículos o recorridos.` | 23 | |
| 377 | `%1$d resultado para «%2$s»` ⚠️ | 28 | |
| 378 | `%1$d resultados para «%2$s»` ⚠️ | 28 | |
| 379 | `No encontramos resultados. Probá con otras palabras.` | 32 | |

### Mi perfil

`templates/sections/perfil.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 380 | `Mi perfil` | 25 | |
| 381 | `Fichas publicadas` | 39 | |
| 382 | `Mi portafolio` | 48 | |
| 383 | `Todavía no tenés nada publicado.` | 50 | |
| 384 | `Publicado` | 58 | |
| 385 | `Mis datos` | 66 | |
| 386 | `Nombre` | 73 | |
| 387 | `Correo` | 80 | |
| 388 | `Teléfono` | 85 | |
| 389 | `opcional` 🔡 | 85 | |
| 390 | `Foto` | 91 | |
| 391 | `opcional, JPG/PNG/WEBP, hasta 5 MB` 🔡 | 91 | |
| 392 | `Con el correo entrás al panel: si lo cambiás, la próxima vez iniciás sesión con el nuevo.` | 94 | |
| 393 | `Guardar cambios` | 97 | |
| 394 | `Contraseña` | 102 | |
| 395 | `Contraseña actual` | 109 | |
| 396 | `Contraseña nueva` | 115 | |
| 397 | `Repetila` | 119 | |
| 398 | `Cambiar contraseña` | 125 | |

### Mi perfil (avisos)

`includes/class-cuenta.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 399 | `Estás entrando como administrador de WordPress, que no tiene cuenta del panel que editar.` | 78 | |
| 400 | `Escribí tu nombre.` | 86 | |
| 401 | `Ese correo no parece válido.` | 89 | |
| 402 | `Ya hay una cuenta con ese correo.` | 95 | |
| 403 | `No pudimos guardar los cambios. Probá de nuevo.` | 104 | |
| 404 | `Listo, guardamos tus datos.` | 107 | |
| 405 | `La foto tiene que ser JPG, PNG o WEBP.` | 130 | |
| 406 | `La foto pesa más de 5 MB. Subí una más liviana.` | 133 | |
| 407 | `La contraseña actual no coincide.` | 229 | |
| 408 | `Las dos contraseñas nuevas tienen que ser iguales.` | 232 | |
| 409 | `No pudimos cambiar la contraseña. Probá de nuevo.` | 240 | |
| 410 | `Listo, cambiaste tu contraseña.` | 246 | |

### App (control de la app móvil)

`templates/sections/app.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 411 | `App` | 21 | |
| 412 | `Fuera de servicio` | 25 | |
| 413 | `La cabina de mando de la app está desconectada` | 26 | |
| 414 | `Los textos y los medios de la aplicación se editan por ahora desde la administración del sitio. Se vuelve a enchufar cuando la API de la app esté en la versión que esta pantalla necesita.` | 27 | |
| 415 | `Volver al inicio del panel` | 28 | |
| 416 | `Aplicación` | 52 | |
| 417 | `Textos` | 61 | |
| 418 | `Idioma` | 62 | |
| 419 | `Clave` | 88 | |
| 420 | `Texto` | 92 | |
| 421 | `Guardar cambios` | 99 | |
| 422 | `Medios` | 108 | |
| 423 | `Ir a la biblioteca` | 111 | |
| 424 | `Tipo` | 138 | |
| 425 | `Imagen` | 140 | |
| 426 | `Animación` | 141 | |
| 427 | `URL o ID` | 145 | |
| 428 | `Texto alternativo` | 149 | |
| 429 | `Formato` | 153 | |
| 430 | `Categorías` | 170 | |
| 431 | `Todavía no hay categorías cargadas. Se crean en Estructura y después se les elige acá el icono y el color.` | 174 | |
| 432 | `Estructura` | 175 | |
| 433 | `Nombre` | 185 | |
| 434 | `Color` | 189 | |
| 435 | `Icono` | 194 | |

### App (avisos)

`includes/class-app-control.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 436 | `No tenés autorización para hacer esto.` | 134 | |
| 437 | `Guardado` | 195 | |

### Ayuda

`templates/sections/ayuda.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 438 | `Ayuda` | 16 | |
| 439 | `Inicio` | 25 | |
| 440 | `Tu resumen del día: lo que espera revisión, lo que necesita correcciones tuyas, y accesos rápidos según tu rol.` | 25 | |
| 441 | `Buscar` | 26 | |
| 442 | `Buscar entre las fichas, artículos y recorridos del panel.` | 26 | |
| 443 | `Nueva ficha` | 27 | |
| 444 | `El editor guiado de una ficha —un sitio o un evento con fecha—, con checklist de mínimos que avisa si falta algo antes de enviar a revisión.` | 27 | |
| 445 | `Salida de campo` | 28 | |
| 446 | `Sacá una foto, anotá información y guardá la ubicación GPS en el lugar, incluso sin señal. Se sincroniza como borrador cuando vuelve la conexión.` | 28 | |
| 447 | `Mis contenidos` | 29 | |
| 448 | `Todo lo que cargaste —fichas, artículos, recorridos—, ordenado por estado, con filtro para ver lo archivado y lo borrado. Si revisás, también podés ver lo de todo el equipo, borradores incluidos.` | 29 | |
| 449 | `Inventario turístico` | 30 | |
| 450 | `El catálogo de fichas publicadas del departamento. De acá se eligen las paradas al armar un recorrido.` | 30 | |
| 451 | `Artículos` | 31 | |
| 452 | `Las notas que la app muestra: título, foto de portada, entradilla, cuerpo y fuentes. Pasan por el mismo flujo de revisión que una ficha.` | 31 | |
| 453 | `Recorridos` | 32 | |
| 454 | `Se arman eligiendo sitios del inventario —hasta nueve—, cada uno con su propio texto, audio o video, en el orden del paseo.` | 32 | |
| 455 | `Cola de revisión` | 33 | |
| 456 | `La cola de lo que espera revisión: asignate una pieza, aprobala y publicala, o devolvela al autor con comentarios.` | 33 | |
| 457 | `Tareas` | 34 | |
| 458 | `Encargos con fecha límite. Los Alumnos pueden reclamar los que están disponibles y marcarlos como hechos.` | 34 | |
| 459 | `Equipo` | 35 | |
| 460 | `Quién entra al panel y con qué rol. Cambiar el rol, suspender, sacar del panel, e invitar gente nueva.` | 35 | |
| 461 | `Reportes` | 36 | |
| 462 | `Producción por autor y salud del contenido: lo publicado sin portada, y lo que no se verifica hace más de seis meses.` | 36 | |
| 463 | `Biblioteca` | 37 | |
| 464 | `La galería de fotos del panel: subir de a tandas, describir, dar crédito y borrar.` | 37 | |
| 465 | `Estructura` | 38 | |
| 466 | `Las categorías y etiquetas de las fichas: crear, renombrar en su lugar, y borrar lo que no esté en uso.` | 38 | |
| 467 | `Mi perfil` | 39 | |
| 468 | `Tu cuenta —nombre, correo, teléfono, foto y contraseña— y el portafolio de lo que publicaste.` | 39 | |
| 469 | `Cómo funciona` | 42 | |
| 470 | `¿Qué hace cada sección?` | 43 | |
| 471 | `Este es el panel de los Promotores Turísticos: acá se escribe, se revisa y se publica todo lo que la app de Caaguazú muestra —fichas de destinos y eventos, artículos y recorridos—. Los Alumnos crean; los Profesores revisan y publican.` | 45 | |
| 472 | `El flujo editorial` | 48 | |
| 473 | `Borrador` | 51 | |
| 474 | `Enviado` | 52 | |
| 475 | `En revisión` | 53 | |
| 476 | `Necesita cambios` | 54 | |
| 477 | `Publicado` | 55 | |
| 478 | `Solo lo aprobado por un Profesor llega a la app. Un Profesor publica directo, sin pasar por revisión, y también puede editar algo ya publicado sin que vuelva a la cola; un Alumno siempre pasa por revisión.` | 58 | |
| 479 | `Lo publicado también se puede despublicar, archivar o mandar a la papelera —y volver atrás desde ahí—, siempre en dos pasos: primero hay que sacarlo del aire antes de poder borrarlo.` | 61 | |
| 480 | `Las secciones` | 65 | |
| 481 | `Extras` | 84 | |
| 482 | `Podés instalar el panel como app (PWA) desde el menú lateral.` | 86 | |
| 483 | `La salida de campo funciona sin conexión: lo que cargues se guarda en el teléfono y se sube solo cuando vuelve la señal.` | 87 | |
| 484 | `Podés cambiar entre modo claro y oscuro desde la barra superior.` | 88 | |
| 485 | `El acceso es solo por invitación. Pedí tu enlace a quien coordina el equipo.` | 89 | |

### Sección inexistente

`templates/sections/404.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 486 | `No encontramos esa sección` | 7 | |
| 487 | `Error 404` | 11 | |
| 488 | `Esta sección no existe` | 12 | |
| 489 | `Puede que el enlace esté roto o que no tengas permiso para acceder.` | 13 | |
| 490 | `Volver al inicio del panel` | 14 | |

## Entrar y salir

Acceso, registro, recuperación, invitaciones y errores de permiso.

### Iniciar sesión

`templates/auth/login.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 491 | `Iniciar sesión` | 12 | |
| 492 | `Entrá al panel de Promotores Turísticos.` | 16 | |
| 493 | `Tu contraseña se actualizó. Ya podés iniciar sesión.` | 19 | |
| 494 | `Email` | 34 | |
| 495 | `Contraseña` | 38 | |
| 496 | `Mantener la sesión iniciada` | 42 | |
| 497 | `Entrar` | 45 | |
| 498 | `¿Olvidaste tu contraseña?` | 49 | |
| 499 | `Acceso solo por invitación` | 50 | |

### Registro

`templates/auth/registro.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 500 | `Crear cuenta` | 23 | |
| 501 | `Crear tu cuenta` | 26 | |
| 502 | `Del Portal de Promotores Turísticos de Caaguazú: acá el equipo escribe lo que después muestra la app de turismo.` | 27 | |
| 503 | `Este enlace ya se usó las veces que tenía permitidas. Pedí uno nuevo a quien te invitó.` | 38 | |
| 504 | `Este enlace venció. Pedí uno nuevo a quien te invitó.` | 41 | |
| 505 | `Este enlace fue dado de baja. Pedí uno nuevo a quien te invitó.` | 44 | |
| 506 | `Para crear una cuenta hace falta un enlace de invitación. Lo genera un Profesor desde el panel, en Equipo; pediselo a quien te sumó al curso.` | 47 | |
| 507 | `Ya tengo una cuenta` | 52 | |
| 508 | `Tu invitación es válida. Vas a entrar como %s.` ⚠️ | 70 | |
| 509 | `Como Profesor vas a poder cargar y publicar contenido sin que lo revise nadie, revisar lo que cargan los Alumnos, y sumar gente al equipo.` | 83 | |
| 510 | `Como Alumno vas a poder cargar fichas, artículos y recorridos. Lo que escribas lo revisa un Profesor antes de que salga en la app.` | 84 | |
| 511 | `Cómo sigue` | 90 | |
| 512 | `Completás estos cuatro datos.` | 92 | |
| 513 | `Entrás al panel al instante: no hay que esperar que nadie apruebe nada.` | 93 | |
| 514 | `De ahí en más entrás con tu correo y tu contraseña. Este enlace no se usa más.` | 94 | |
| 515 | `Tu nombre` | 104 | |
| 516 | `Como querés que te vean en el equipo, y como se firma lo que publiques. Podés cambiarlo después.` | 106 | |
| 517 | `Correo` | 109 | |
| 518 | `Con este correo vas a entrar de acá en adelante. Usá uno al que entres de verdad: es por donde se recupera la contraseña.` | 111 | |
| 519 | `Teléfono` | 114 | |
| 520 | `Ej.: 0981 123 456` | 115 | |
| 521 | `Para que el equipo te pueda ubicar. No se publica en ningún lado ni sale en la app.` | 116 | |
| 522 | `Contraseña` | 119 | |
| 523 | `Seis caracteres o más. Es nueva, no la de tu correo.` | 121 | |
| 524 | `Crear cuenta y entrar` | 124 | |
| 525 | `Este enlace sirve hasta el %s.` ⚠️ | 131 | |

### Recuperar contraseña

`templates/auth/recuperar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 526 | `Recuperar contraseña` | 10 | |
| 527 | `Te enviamos un enlace para restablecer tu contraseña.` | 14 | |
| 528 | `Email` | 27 | |
| 529 | `Enviar enlace` | 30 | |
| 530 | `Volver a iniciar sesión` | 34 | |

### Contraseña nueva

`templates/auth/restablecer.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 531 | `Nueva contraseña` | 12 | |
| 532 | `Nueva contraseña (6 o más caracteres)` | 28 | |
| 533 | `Guardar contraseña` | 31 | |
| 534 | `El enlace no es válido o ya venció. Pedí uno nuevo.` | 34 | |
| 535 | `Pedir un nuevo enlace` | 36 | |

### Marco de acceso

`templates/auth-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 536 | `Acceso` | 8 | |

### Errores y avisos de acceso

`includes/class-auth.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 537 | `No tenés autorización para hacer esto.` | 51 | |
| 538 | `No se pudo crear la invitación: la base de datos rechazó el registro. Avisale a quien administra el sitio.` | 64 | |
| 539 | `Enlace de invitación creado. Lo tenés abajo, en «Invitaciones abiertas».` | 66 | |
| 540 | `Tu sesión venció. Recargá la página.` | 162 | |
| 541 | `Necesitás una invitación válida para registrarte.` | 244 | |
| 542 | `Completá usuario, email, teléfono y una contraseña de al menos 6 caracteres.` | 262 | |
| 543 | `Ese email ya está registrado.` | 270 | |
| 544 | `Si la cuenta existe, te enviamos un email con las instrucciones.` | 320 | |
| 545 | `El enlace para restablecer la contraseña venció o no es válido.` | 338 | |

### Invitaciones

`includes/class-invitations.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 546 | `Válida` | 179 | |
| 547 | `Agotada` | 180 | |
| 548 | `Expirada` | 181 | |
| 549 | `Revocada` | 182 | |
| 550 | `Inválida` | 183 | |
| 551 | `No vence` | 211 | |
| 552 | `Vence el %s` ⚠️ | 214 | |
| 553 | `%d cuenta creada, sin límite` ⚠️ | 222 | |
| 554 | `%d cuentas creadas, sin límite` ⚠️ | 222 | |
| 555 | `%1$d de %2$d` ⚠️ | 225 | |

### Guardas de acceso

`includes/class-router.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 556 | `No tenés acceso a este panel.` | 292 | |
| 557 | `Acceso denegado` | 293 | |

### Guardas de sección

`includes/class-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 558 | `No tenés permiso para ver esta sección.` | 42 | |
| 559 | `Acceso denegado` | 43 | |

### Sin conexión (PWA)

`includes/class-pwa.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 560 | `Sin conexión` | 169 | |
| 561 | `Promotores Turísticos` | 176 | |
| 562 | `Estás sin conexión` | 177 | |
| 563 | `No pudimos cargar esta pantalla. Revisá tu conexión e intentá de nuevo.` | 178 | |
| 564 | `Reintentar` | 179 | |

## wp-admin y mensajes de sistema

Pantallas de administración y respuestas de las acciones.

### Pantallas de wp-admin

`includes/class-admin.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 565 | `Portal Turismo` | 85 | |
| 566 | `Registros` | 88 | |
| 567 | `Invitaciones` | 91 | |
| 568 | `Actualizaciones` | 93 | |
| 569 | `No tenés autorización para hacer esto.` | 108 | |
| 570 | `Usuarios` | 128 | |
| 571 | `Entradas` | 129 | |
| 572 | `Fecha` | 133 | |
| 573 | `Usuario` | 133 | |
| 574 | `Acción` | 134 | |
| 575 | `Elemento` | 134 | |
| 576 | `IP` | 135 | |
| 577 | `Detalle` | 135 | |
| 578 | `No hay registros.` | 139 | |
| 579 | `Invitar a alguien` | 187 | |
| 580 | `Rol` | 194 | |
| 581 | `Vence en (días)` | 204 | |
| 582 | `Vacío o 0: no vence nunca.` | 212 | |
| 583 | `Cuántas cuentas puede crear` | 216 | |
| 584 | `Vacío o 0: sin límite.` | 219 | |
| 585 | `Email (opcional)` | 223 | |
| 586 | `Crear enlace` | 227 | |
| 587 | `Invitaciones abiertas` | 230 | |
| 588 | `No hay ninguna esperando.` | 235 | |
| 589 | `Vence` | 240 | |
| 590 | `Usos` | 241 | |
| 591 | `Enlace` | 242 | |
| 592 | `Copiar` | 256 | |
| 593 | `El enlace deja de servir. ¿Seguimos?` | 261 | |
| 594 | `Revocar` | 266 | |
| 595 | `Copiado` | 284 | |
| 596 | `No se pudo crear la invitación: la base de datos rechazó el registro. Revisá que las tablas del plugin estén al día.` | 318 | |
| 597 | `Enlace de invitación creado. Lo tenés abajo, en «Invitaciones abiertas».` | 320 | |
| 598 | `Invitación revocada. Ese enlace ya no sirve.` | 326 | |
| 599 | `Esa invitación ya no existe.` | 328 | |
| 600 | `Actualizaciones del portal` | 368 | |
| 601 | `No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.` | 372 | |
| 602 | `Atención: la versión del encabezado del plugin (%1$s) no coincide con PROMOTUR_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.` ⚠️ | 379 | |
| 603 | `Versión instalada` | 388 | |
| 604 | `Última disponible` | 389 | |
| 605 | `Actualizar ahora` | 398 | |
| 606 | `Estás al día.` | 400 | |
| 607 | `Última comprobación` | 403 | |
| 608 | `nunca` 🔡 | 404 | |
| 609 | `Repositorio` | 406 | |
| 610 | `Buscar actualizaciones ahora` | 415 | |
| 611 | `Limpiar caché del actualizador` | 421 | |
| 612 | `Token de GitHub` | 425 | |
| 613 | `Definido en wp-config.php mediante PROMOTUR_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.` | 427 | |
| 614 | `El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.` | 429 | |
| 615 | `Token` | 435 | |
| 616 | `•••• guardado (dejá vacío para conservarlo)` | 436 | |
| 617 | `Eliminar el token guardado` | 438 | |
| 618 | `Guardar token` | 442 | |
| 619 | `Hay una nueva versión disponible: %s.` ⚠️ | 461 | |
| 620 | `No hay actualizaciones: ya tenés la última versión.` | 463 | |
| 621 | `El verificador de actualizaciones no está disponible.` | 466 | |
| 622 | `Caché del actualizador limpiada.` | 475 | |
| 623 | `El token está definido en wp-config.php y no se puede cambiar desde acá.` | 480 | |
| 624 | `Token eliminado.` | 487 | |
| 625 | `Token guardado.` | 490 | |
| 626 | `No hubo cambios en el token.` | 492 | |

### Respuestas del editor

`includes/class-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 627 | `No tenés permiso para hacer esto.` | 61 | |
| 628 | `Ese tipo de contenido no existe.` | 74 | |
| 629 | `No podés editar esto.` | 130 | |
| 630 | `(sin título)` | 146 | |
| 631 | `Borrador guardado.` | 207 | |
| 632 | `Guardado. Como editaste algo ya publicado, tendrá que pasar por una nueva revisión.` | 214 | |
| 633 | `De ese enlace no pudimos sacar el pin (los enlaces cortos no lo traen). Cargá la latitud y la longitud a mano, o pegá el enlace largo.` | 254 | |
| 634 | `Un recorrido lleva hasta %d paradas, y sin repetir el mismo sitio. Guardamos las que entraron.` ⚠️ | 309 | |
| 635 | `Esto no se puede enviar.` | 334 | |
| 636 | `Faltan datos obligatorios. Completá el checklist antes de enviar.` | 338 | |
| 637 | `Publicación directa: tu rol no pasa por revisión.` | 348 | |
| 638 | `¡Publicado!` | 349 | |
| 639 | `¡Enviado a revisión!` | 353 | |
| 640 | `Eso no existe o no es contenido del panel.` | 366 | |
| 641 | `Te asignaste la revisión.` | 375 | |
| 642 | `Aprobado y publicado.` | 386 | |
| 643 | `Escribí los comentarios para el autor.` | 394 | |
| 644 | `Devuelto al autor con comentarios.` | 398 | |
| 645 | `No recibimos ninguna imagen.` | 405 | |
| 646 | `Solo podés subir imágenes.` | 409 | |

### Respuestas de gestión

`includes/class-gestion-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 647 | `No tenés permiso para hacer esto.` | 29 | |
| 648 | `Tarea creada.` | 46 | |
| 649 | `La tarea no es válida.` | 53 | |
| 650 | `Reclamaste esta tarea. Ya podés trabajar en ella.` | 56 | |
| 651 | `Tarea completada. 🎉` | 70 | |

### Avisos del plugin

`caaguazu-portal.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 652 | `Caaguazú Portal necesita tener activo el plugin «Caaguazú Cuentas» para funcionar. El inicio de sesión de los Promotores ya no usa los usuarios de WordPress. Activá el plugin desde Plugins para volver a usar el panel.` | 98 | |
| 653 | `Portal de Promotores` | 119 | |
