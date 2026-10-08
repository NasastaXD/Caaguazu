# Textos del panel

Todo lo que un usuario lee en el panel, sacado de las fuentes el 2026-10-08. **655 textos.**

Se regenera con `php tools/textos-del-panel.php > docs/textos-del-panel.md`.

- Escribí el reemplazo en la columna **Nuevo texto**; lo que quede en blanco se deja como está.
- Los marcados con ⚠️ llevan un hueco (`%s`, `%d`, `%1$s`) que el código rellena: hay que conservarlo tal cual y en el mismo orden.
- Los `[FALTA: …]` son huecos a propósito: textos que el diseño pide y que todavía no escribió nadie.
- Los marcados con 🔡 arrancan en minúscula.

## Empiezan en minúscula

Casi todas son fragmentos escritos para leerse **después de un número** ("4 esperan revisión") o para ir dentro de una frase. Si se quieren usar como título, hay que reescribirlas enteras, no sólo poner la mayúscula.

| # | Texto | Dónde |
| --- | --- | --- |
| 69 | `atrás` | Notificaciones |
| 230 | `sin texto` | Cola de revisión |
| 242 | `revisa %s` | Cola de revisión |
| 258 | `vence %s` | Tareas |
| 328 | `opcional` | Biblioteca |
| 391 | `opcional` | Mi perfil |
| 393 | `opcional, JPG/PNG/WEBP, hasta 5 MB` | Mi perfil |
| 610 | `nunca` | Pantallas de wp-admin |

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
| 35 | `Ver la web` | 753 | |

### Menú lateral (pie)

`templates/partials/sidebar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 36 | `%s (se abre en otra pestaña)` ⚠️ | 41 | |
| 37 | `Abrir menú` | 51 | |
| 38 | `Buscar…` | 81 | |
| 39 | `Buscar` | 82 | |
| 40 | `Navegación del panel` | 86 | |
| 41 | `Instalar app` | 115 | |
| 42 | `Cerrar sesión` | 119 | |

### Barra superior

`templates/partials/topbar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 43 | `Abrir menú` | 21 | |
| 44 | `Navegación del panel` | 25 | |
| 45 | `Inicio` | 30 | |
| 46 | `Buscar…` | 39 | |
| 47 | `Buscar` | 39 | |
| 48 | `Ver la web` | 44 | |
| 49 | `Ver la web de turismo (se abre en otra pestaña)` | 44 | |
| 50 | `Cambiar tema` | 49 | |
| 51 | `Notificaciones` | 55 | |
| 52 | `Marcar todo como leído` | 67 | |
| 53 | `No hay novedades por ahora. ✨` | 73 | |

### Barra inferior (teléfono)

`templates/partials/bottomnav.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 54 | `Inicio` | 10 | |
| 55 | `Contenidos` | 11 | |
| 56 | `Campo` | 12 | |
| 57 | `Revisar` | 13 | |
| 58 | `Perfil` | 14 | |
| 59 | `Navegación rápida` | 20 | |

### Mensajes del JavaScript

`includes/class-assets.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 60 | `Instalar app` | 135 | |
| 61 | `Enviando…` | 136 | |
| 62 | `Algo salió mal. Probá de nuevo.` | 137 | |
| 63 | `Guardado` | 138 | |
| 64 | `¿Querés confirmar esta acción?` | 139 | |
| 65 | `Faltan algunos datos obligatorios.` | 140 | |
| 66 | `Foto subida.` | 141 | |
| 67 | `Copiado` | 142 | |

### Notificaciones

`includes/class-notifications.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 68 | `«%s» está esperando revisión` ⚠️ | 81 | |
| 69 | `atrás` 🔡 | 83 | |
| 70 | `«%s» necesita algunos cambios` ⚠️ | 106 | |
| 71 | `Notificaciones marcadas como leídas.` | 181 | |

### Estados del flujo editorial

`includes/class-editorial.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 72 | `Ficha` | 97 | |
| 73 | `Borrador` | 137 | |
| 74 | `Enviado` | 138 | |
| 75 | `En revisión` | 139 | |
| 76 | `Necesita cambios` | 140 | |
| 77 | `Aprobado` | 141 | |
| 78 | `Publicado` | 142 | |
| 79 | `Despublicado` | 143 | |
| 80 | `Archivado` | 144 | |
| 81 | `Retirar de revisión` | 240 | |
| 82 | `¿Sacarlo de la cola de revisión y volverlo a borrador?` | 242 | |
| 83 | `Despublicar` | 249 | |
| 84 | `Esto está publicado y la app lo está mostrando. ¿Sacarlo de circulación? El contenido se conserva entero.` | 251 | |
| 85 | `Publicar de nuevo` | 258 | |
| 86 | `Archivar` | 267 | |
| 87 | `¿Archivarlo? Sale de circulación y se puede recuperar cuando quieras.` | 269 | |
| 88 | `Título` | 370 | |
| 89 | `Nombre del recorrido` | 372 | |
| 90 | `Nombre del destino` | 374 | |
| 91 | `Faltan fuentes o referencias.` | 392 | |
| 92 | `Mejorá las fotos: cuidá la luz, el encuadre y la portada.` | 393 | |
| 93 | `Verificá los horarios y los costos.` | 394 | |
| 94 | `Revisá la ortografía y la redacción.` | 395 | |
| 95 | `Comprobá que el enlace de Google Maps caiga en el lugar correcto.` | 396 | |
| 96 | `Revisá el orden de las paradas: no cuenta lo mismo al revés.` | 397 | |

### Guardas de las acciones

`includes/class-acciones.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 97 | `Esa acción no existe.` | 161 | |
| 98 | `Esa acción sólo acepta envíos.` | 165 | |
| 99 | `Tu sesión venció. Volvé a entrar.` | 172 | |
| 100 | `Tu sesión venció. Recargá la página.` | 179 | |
| 101 | `No tenés autorización para hacer esto.` | 184 | |

### Nombres de los roles

`includes/class-roles.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 102 | `Profesor` | 68 | |
| 103 | `Alumno` | 69 | |
| 104 | `Visitante` | 70 | |

## Las secciones

Una tabla por pantalla del panel.

### Inicio

`templates/sections/home.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 105 | `Esperan revisión` | 35 | |
| 106 | `Publicados` | 36 | |
| 107 | `Esperan tu corrección` | 39 | |
| 108 | `En proceso` | 40 | |
| 109 | `Inicio` | 52 | |
| 110 | `Tu actividad de hoy` | 57 | |
| 111 | `Hola, %s 👋` ⚠️ | 60 | |
| 112 | `Actividad reciente` | 85 | |
| 113 | `Fichas, artículos y recorridos creados, enviados o publicados en los últimos 7 días.` | 87 | |
| 114 | `Actividad de los últimos %d día` ⚠️ | 109 | |
| 115 | `Actividad de los últimos %d días` ⚠️ | 109 | |
| 116 | `Accesos rápidos` | 125 | |
| 117 | `Crear una ficha` | 130 | |
| 118 | `Mis contenidos` | 133 | |
| 119 | `Cola de revisión` | 136 | |
| 120 | `Equipo` | 139 | |
| 121 | `Mi perfil` | 141 | |

### Mis contenidos

`templates/sections/mis-contenidos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 122 | `Contenidos del equipo` | 76 | |
| 123 | `Mis contenidos` | 76 | |
| 124 | `En curso` | 79 | |
| 125 | `Publicados` | 80 | |
| 126 | `Despublicados` | 81 | |
| 127 | `Archivados` | 82 | |
| 128 | `Papelera` | 83 | |
| 129 | `Lo que escribe todo el equipo` | 111 | |
| 130 | `Tu producción` | 111 | |
| 131 | `+ Nueva ficha` | 114 | |
| 132 | `De quién` | 119 | |
| 133 | `Míos` | 120 | |
| 134 | `Del equipo` | 120 | |
| 135 | `Filtrar por estado` | 131 | |
| 136 | `La papelera está vacía.` | 144 | |
| 137 | `Nadie del equipo tiene nada en ese estado.` | 146 | |
| 138 | `Nadie del equipo tiene nada en curso.` | 148 | |
| 139 | `No tenés nada en ese estado.` | 150 | |
| 140 | `Todavía no creaste nada. Podés empezar por una ficha, un artículo o un recorrido.` | 152 | |
| 141 | `Nueva ficha` | 154 | |
| 142 | `Nuevo artículo` | 155 | |
| 143 | `Nuevo recorrido` | 156 | |
| 144 | `Lo borrado se recupera acá, como borrador. Nada se pierde de verdad hasta que alguien lo vacíe.` | 161 | |
| 145 | `(sin título)` | 166 | |
| 146 | `Recuperar` | 170 | |

### Editor de ficha

`templates/sections/editor.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 147 | `No podés editar esta ficha.` | 16 | |
| 148 | `Editar ficha` | 28 | |
| 149 | `Nueva ficha` | 28 | |
| 150 | `Ficha del destino` | 36 | |
| 151 | `Comentarios del revisor` | 44 | |
| 152 | `Nombre del destino` | 63 | |
| 153 | `Descripción` | 68 | |
| 154 | `Clasificación` | 73 | |
| 155 | `Categoría` | 76 | |
| 156 | `Etiquetas` | 79 | |
| 157 | `Separadas por comas: «con niños», «gratis», «llega colectivo».` | 81 | |
| 158 | `📍 Usar mi ubicación actual` | 97 | |
| 159 | `Guardar borrador` | 104 | |
| 160 | `Enviar a revisión` | 105 | |
| 161 | `Checklist de mínimos` | 112 | |
| 162 | `Completá estos puntos antes de enviar la ficha a revisión.` | 113 | |

### Campos de la ficha

`includes/class-destinos.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 163 | `Destinos` | 68 | |
| 164 | `Destino` | 69 | |
| 165 | `Nuevo destino` | 70 | |
| 166 | `Editar destino` | 71 | |
| 167 | `Buscar destinos` | 72 | |
| 168 | `Categorías` | 119 | |
| 169 | `Categoría` | 119 | |
| 170 | `Zonas` | 126 | |
| 171 | `Zona` | 126 | |
| 172 | `Etiquetas` | 130 | |
| 173 | `Etiqueta` | 130 | |
| 174 | `Qué es` | 160 | |
| 175 | `Tipo` | 163 | |
| 176 | `Sitio — está siempre` | 167 | |
| 177 | `Evento — pasa en una fecha` | 168 | |
| 178 | `Un evento es un lugar con fecha: la fiesta patronal, una feria, un festival. Todo lo demás se carga igual.` | 170 | |
| 179 | `Empieza` | 173 | |
| 180 | `Día y hora de inicio.` | 177 | |
| 181 | `Termina` | 180 | |
| 182 | `Si dura un solo día, alcanza con la hora de cierre. Si es de varios días, poné el último.` | 184 | |
| 183 | `Identidad` | 189 | |
| 184 | `Foto de portada` | 191 | |
| 185 | `Crédito de las fotos` | 192 | |
| 186 | `Video (URL, opcional)` | 193 | |
| 187 | `Ubicación` | 213 | |
| 188 | `Enlace de Google Maps` | 216 | |
| 189 | `Buscá el lugar en Google Maps, tocá «Compartir» y pegá acá el enlace. De ahí sacamos el pin solos.` | 220 | |
| 190 | `Latitud (alternativa al enlace)` | 223 | |
| 191 | `Sólo si el enlace no alcanza: un enlace corto, o un lugar que Google no tiene.` | 226 | |
| 192 | `Longitud (alternativa al enlace)` | 229 | |
| 193 | `Estado del camino` | 233 | |
| 194 | `Datos prácticos` | 245 | |
| 195 | `Horario` | 247 | |
| 196 | `Costo / entrada` | 248 | |
| 197 | `Rango de precio` | 256 | |
| 198 | `Sin especificar` | 260 | |
| 199 | `Gratis` | 261 | |
| 200 | `$ — Muy barato` | 262 | |
| 201 | `$$ — Barato` | 263 | |
| 202 | `$$$ — Intermedio` | 264 | |
| 203 | `$$$$ — Caro` | 265 | |
| 204 | `Contacto del lugar` | 268 | |
| 205 | `Fuentes y referencias` | 272 | |
| 206 | `Fuentes / referencias` | 274 | |
| 207 | `Descripción` | 456 | |
| 208 | `Ubicación (enlace de Google Maps o coordenadas)` | 461 | |

### Salida de campo

`templates/sections/captura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 209 | `Salida de campo` | 8 | |
| 210 | `Captura en el lugar` | 11 | |
| 211 | `Sacá una foto, anotá lo importante y guardá la ubicación, incluso si no tenés señal. Todo queda guardado en tu dispositivo y podés sincronizarlo como borrador cuando vuelva la conexión.` | 13 | |
| 212 | `Nombre del lugar` | 17 | |
| 213 | `Nota rápida` | 18 | |
| 214 | `Foto` | 21 | |
| 215 | `Ubicación (GPS)` | 25 | |
| 216 | `Tomar ubicación` | 27 | |
| 217 | `Guardar captura` | 33 | |
| 218 | `Capturas pendientes` | 39 | |
| 219 | `Sincronizar` | 40 | |

### Cola de revisión

`templates/sections/revision.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 220 | `No encontramos esto.` | 19 | |
| 221 | `Revisión` | 28 | |
| 222 | `Volver a la cola` | 33 | |
| 223 | `Por %s` ⚠️ | 39 | |
| 224 | `Entradilla` | 74 | |
| 225 | `Cuerpo` | 80 | |
| 226 | `Descripción` | 80 | |
| 227 | `Ubicación` | 87 | |
| 228 | `Abrir el pin en Google Maps` | 88 | |
| 229 | `Paradas, en orden` | 93 | |
| 230 | `sin texto` 🔡 | 99 | |
| 231 | `Acciones` | 130 | |
| 232 | `Asignarme la revisión` | 133 | |
| 233 | `Comentarios para el autor` | 137 | |
| 234 | `Qué corregir o mejorar…` | 138 | |
| 235 | `Devolver con cambios` | 147 | |
| 236 | `Aprobar y publicar` | 149 | |
| 237 | `Historial` | 157 | |
| 238 | `Cola de revisión` | 185 | |
| 239 | `Taller editorial` | 188 | |
| 240 | `No hay nada esperando revisión. 🎉` | 192 | |
| 241 | `%1$s · esperó %2$s` ⚠️ | 208 | |
| 242 | `revisa %s` ⚠️ 🔡 | 213 | |

### Tareas

`templates/sections/tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 243 | `Tareas` | 11 | |
| 244 | `Asignaciones` | 14 | |
| 245 | `Tareas y pendientes por cubrir` | 15 | |
| 246 | `+ Nueva tarea o hueco` | 19 | |
| 247 | `Título` | 21 | |
| 248 | `Detalle` | 22 | |
| 249 | `Tipo` | 24 | |
| 250 | `Tarea asignada` | 26 | |
| 251 | `Hueco disponible` | 27 | |
| 252 | `Vence` | 30 | |
| 253 | `Destino (opcional)` | 31 | |
| 254 | `Asignar a (Alumnos)` | 37 | |
| 255 | `Crear` | 42 | |
| 256 | `No hay tareas por ahora.` | 49 | |
| 257 | `Hueco` | 61 | |
| 258 | `vence %s` ⚠️ 🔡 | 63 | |
| 259 | `Reclamar` | 69 | |
| 260 | `Marcar como completada` | 72 | |

### Tareas (estados y avisos)

`includes/class-tareas.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 261 | `Tareas` | 27 | |
| 262 | `Tarea` | 27 | |
| 263 | `Pendiente` | 38 | |
| 264 | `En curso` | 39 | |
| 265 | `Completada` | 40 | |
| 266 | `La tarea necesita un título.` | 58 | |

### Equipo

`templates/sections/equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 267 | `Equipo` | 16 | |
| 268 | `Tu equipo` | 19 | |
| 269 | `Invitar a alguien` | 24 | |
| 270 | `Generá un enlace de invitación con el rol, el vencimiento y cuántas cuentas puede crear.` | 25 | |
| 271 | `Rol` | 30 | |
| 272 | `Vence en (días)` | 38 | |
| 273 | `Vacío o 0: no vence nunca.` | 40 | |
| 274 | `Cuántas cuentas puede crear` | 43 | |
| 275 | `Vacío o 0: sin límite.` | 45 | |
| 276 | `Crear enlace` | 54 | |
| 277 | `%1$d publicadas · %2$d en total` ⚠️ | 76 | |
| 278 | `Suspendida` | 86 | |
| 279 | `Cambiar rol` | 98 | |
| 280 | `Reactivar` | 105 | |
| 281 | `Suspender` | 105 | |
| 282 | `Deja de tener acceso al panel. Su cuenta y lo que publicó quedan como están. ¿Seguimos?` | 110 | |
| 283 | `Sacar del panel` | 113 | |
| 284 | `Invitaciones abiertas` | 123 | |
| 285 | `No hay ninguna esperando. Los enlaces que crees acá arriba aparecen en esta lista hasta que alguien los use o se venzan.` | 126 | |
| 286 | `El enlace deja de servir. ¿Seguimos?` | 143 | |
| 287 | `Revocar` | 146 | |
| 288 | `Enlace de invitación` | 152 | |
| 289 | `Copiar` | 153 | |

### Equipo (avisos)

`includes/class-equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 290 | `Esa persona no existe.` | 51 | |
| 291 | `A vos mismo no te podés editar desde acá.` | 54 | |
| 292 | `Ese rol no existe.` | 72 | |
| 293 | `%1$s ahora es %2$s.` ⚠️ | 85 | |
| 294 | `Suspendimos a %s. No va a poder entrar hasta que la reactives.` ⚠️ | 114 | |
| 295 | `%s puede volver a entrar.` ⚠️ | 119 | |
| 296 | `%s ya no entra al panel. Su cuenta y lo que publicó quedan como están.` ⚠️ | 142 | |
| 297 | `Esa invitación ya no existe.` | 150 | |
| 298 | `Invitación revocada. Ese enlace ya no sirve.` | 153 | |

### Reportes

`templates/sections/reportes.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 299 | `Reportes` | 12 | |
| 300 | `Métricas` | 15 | |
| 301 | `Actividad del portal` | 16 | |
| 302 | `Producción por autor` | 18 | |
| 303 | `%1$d publicadas / %2$d` ⚠️ | 27 | |
| 304 | `Estado del contenido` | 33 | |
| 305 | `Fichas publicadas sin portada` | 36 | |
| 306 | `Fichas sin verificar hace +6 meses` | 46 | |

### Biblioteca

`templates/sections/biblioteca.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 307 | `Biblioteca` | 21 | |
| 308 | `Medios` | 26 | |
| 309 | `Biblioteca de medios` | 27 | |
| 310 | `Subir fotos` | 37 | |
| 311 | `Podés elegir varias de una vez. JPG, PNG, WEBP o GIF.` | 40 | |
| 312 | `Subir` | 42 | |
| 313 | `Buscar una foto` | 50 | |
| 314 | `Sólo las mías` | 54 | |
| 315 | `Filtrar` | 56 | |
| 316 | `No encontramos ninguna foto con ese nombre.` | 64 | |
| 317 | `Todavía no hay fotos. Subí las primeras acá arriba.` | 66 | |
| 318 | `%d foto` ⚠️ | 77 | |
| 319 | `%d fotos` ⚠️ | 77 | |
| 320 | `Sin descripción` | 101 | |
| 321 | `Es la portada de %d ficha.` ⚠️ | 112 | |
| 322 | `Es la portada de %d fichas.` ⚠️ | 112 | |
| 323 | `Esta foto la subió otra persona, así que sólo podés verla.` | 120 | |
| 324 | `Nombre` | 128 | |
| 325 | `Descripción` | 132 | |
| 326 | `Qué se ve en la foto` | 134 | |
| 327 | `Crédito` | 137 | |
| 328 | `opcional` 🔡 | 137 | |
| 329 | `Quién la sacó` | 139 | |
| 330 | `Guardar` | 143 | |
| 331 | `Se borra la foto y no se puede deshacer. ¿Seguimos?` | 149 | |
| 332 | `Borrar foto` | 152 | |
| 333 | `Anteriores` | 165 | |
| 334 | `Página %1$d de %2$d` ⚠️ | 171 | |
| 335 | `Siguientes` | 179 | |

### Biblioteca (avisos)

`includes/class-medios.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 336 | `No elegiste ninguna foto.` | 141 | |
| 337 | `No pudimos subir ninguna: revisá que sean JPG, PNG, WEBP o GIF.` | 182 | |
| 338 | `Subimos %1$d foto. %2$d quedó afuera por el formato.` ⚠️ | 187 | |
| 339 | `Subimos %1$d fotos. %2$d quedaron afuera por el formato.` ⚠️ | 187 | |
| 340 | `Subimos %d foto.` ⚠️ | 194 | |
| 341 | `Subimos %d fotos.` ⚠️ | 194 | |
| 342 | `Esa foto no existe.` | 218 | |
| 343 | `Esa foto la subió otra persona.` | 221 | |
| 344 | `Listo, guardamos la foto.` | 234 | |
| 345 | `No la borramos: es la portada de %d ficha. Cambiala ahí primero.` ⚠️ | 250 | |
| 346 | `No la borramos: es la portada de %d fichas. Cambiala ahí primero.` ⚠️ | 250 | |
| 347 | `Foto borrada.` | 259 | |

### Estructura

`templates/sections/estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 348 | `Estructura` | 22 | |
| 349 | `Organización` | 27 | |
| 350 | `Estructura del sitio` | 28 | |
| 351 | `Esto lo organiza un Profesor. Podés ver cómo está armado, pero no cambiarlo.` | 34 | |
| 352 | `Todavía no hay ninguna.` | 51 | |
| 353 | `Descripción` | 70 | |
| 354 | `Imagen` | 74 | |
| 355 | `Subir foto` | 80 | |
| 356 | `Traducciones` | 89 | |
| 357 | `Guardar` | 104 | |
| 358 | `%d ficha` ⚠️ | 115 | |
| 359 | `%d fichas` ⚠️ | 115 | |
| 360 | `Se borra y no se puede deshacer. ¿Seguimos?` | 122 | |
| 361 | `Borrar` | 126 | |
| 362 | `Agregar` | 143 | |
| 363 | `El ícono y el color con que la app muestra cada categoría se eligen en App →` | 151 | |

### Estructura (nombres y avisos)

`includes/class-estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 364 | `Categorías` | 42 | |
| 365 | `Categoría` | 43 | |
| 366 | `Etiquetas` | 51 | |
| 367 | `Etiqueta` | 52 | |
| 368 | `Eso no se puede editar desde acá.` | 111 | |
| 369 | `Escribí un nombre.` | 120 | |
| 370 | `Ya existe una con ese nombre.` | 123 | |
| 371 | `Creamos «%s».` ⚠️ | 138 | |
| 372 | `Listo, guardamos los cambios.` | 196 | |
| 373 | `Eso ya no existe.` | 204 | |
| 374 | `No la borramos: %d ficha la usa. Movelas primero.` ⚠️ | 210 | |
| 375 | `No la borramos: %d fichas la usan. Movelas primero.` ⚠️ | 210 | |
| 376 | `Borramos «%s».` ⚠️ | 224 | |

### Buscar

`templates/sections/buscar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 377 | `Buscar` | 18 | |
| 378 | `Escribí algo en el buscador de arriba para encontrar fichas, artículos o recorridos.` | 23 | |
| 379 | `%1$d resultado para «%2$s»` ⚠️ | 28 | |
| 380 | `%1$d resultados para «%2$s»` ⚠️ | 28 | |
| 381 | `No encontramos resultados. Probá con otras palabras.` | 32 | |

### Mi perfil

`templates/sections/perfil.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 382 | `Mi perfil` | 25 | |
| 383 | `Fichas publicadas` | 39 | |
| 384 | `Mi portafolio` | 48 | |
| 385 | `Todavía no tenés nada publicado.` | 50 | |
| 386 | `Publicado` | 58 | |
| 387 | `Mis datos` | 66 | |
| 388 | `Nombre` | 73 | |
| 389 | `Correo` | 80 | |
| 390 | `Teléfono` | 85 | |
| 391 | `opcional` 🔡 | 85 | |
| 392 | `Foto` | 91 | |
| 393 | `opcional, JPG/PNG/WEBP, hasta 5 MB` 🔡 | 91 | |
| 394 | `Con el correo entrás al panel: si lo cambiás, la próxima vez iniciás sesión con el nuevo.` | 94 | |
| 395 | `Guardar cambios` | 97 | |
| 396 | `Contraseña` | 102 | |
| 397 | `Contraseña actual` | 109 | |
| 398 | `Contraseña nueva` | 115 | |
| 399 | `Repetila` | 119 | |
| 400 | `Cambiar contraseña` | 125 | |

### Mi perfil (avisos)

`includes/class-cuenta.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 401 | `Estás entrando como administrador de WordPress, que no tiene cuenta del panel que editar.` | 78 | |
| 402 | `Escribí tu nombre.` | 86 | |
| 403 | `Ese correo no parece válido.` | 89 | |
| 404 | `Ya hay una cuenta con ese correo.` | 95 | |
| 405 | `No pudimos guardar los cambios. Probá de nuevo.` | 104 | |
| 406 | `Listo, guardamos tus datos.` | 107 | |
| 407 | `La foto tiene que ser JPG, PNG o WEBP.` | 130 | |
| 408 | `La foto pesa más de 5 MB. Subí una más liviana.` | 133 | |
| 409 | `La contraseña actual no coincide.` | 229 | |
| 410 | `Las dos contraseñas nuevas tienen que ser iguales.` | 232 | |
| 411 | `No pudimos cambiar la contraseña. Probá de nuevo.` | 240 | |
| 412 | `Listo, cambiaste tu contraseña.` | 246 | |

### App (control de la app móvil)

`templates/sections/app.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 413 | `App` | 21 | |
| 414 | `Fuera de servicio` | 25 | |
| 415 | `La cabina de mando de la app está desconectada` | 26 | |
| 416 | `Los textos y los medios de la aplicación se editan por ahora desde la administración del sitio. Se vuelve a enchufar cuando la API de la app esté en la versión que esta pantalla necesita.` | 27 | |
| 417 | `Volver al inicio del panel` | 28 | |
| 418 | `Aplicación` | 52 | |
| 419 | `Textos` | 61 | |
| 420 | `Idioma` | 62 | |
| 421 | `Clave` | 88 | |
| 422 | `Texto` | 92 | |
| 423 | `Guardar cambios` | 99 | |
| 424 | `Medios` | 108 | |
| 425 | `Ir a la biblioteca` | 111 | |
| 426 | `Tipo` | 138 | |
| 427 | `Imagen` | 140 | |
| 428 | `Animación` | 141 | |
| 429 | `URL o ID` | 145 | |
| 430 | `Texto alternativo` | 149 | |
| 431 | `Formato` | 153 | |
| 432 | `Categorías` | 170 | |
| 433 | `Todavía no hay categorías cargadas. Se crean en Estructura y después se les elige acá el icono y el color.` | 174 | |
| 434 | `Estructura` | 175 | |
| 435 | `Nombre` | 185 | |
| 436 | `Color` | 189 | |
| 437 | `Icono` | 194 | |

### App (avisos)

`includes/class-app-control.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 438 | `No tenés autorización para hacer esto.` | 134 | |
| 439 | `Guardado` | 195 | |

### Ayuda

`templates/sections/ayuda.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 440 | `Ayuda` | 16 | |
| 441 | `Inicio` | 25 | |
| 442 | `Tu resumen del día: lo que espera revisión, lo que necesita correcciones tuyas, y accesos rápidos según tu rol.` | 25 | |
| 443 | `Buscar` | 26 | |
| 444 | `Buscar entre las fichas, artículos y recorridos del panel.` | 26 | |
| 445 | `Nueva ficha` | 27 | |
| 446 | `El editor guiado de una ficha —un sitio o un evento con fecha—, con checklist de mínimos que avisa si falta algo antes de enviar a revisión.` | 27 | |
| 447 | `Salida de campo` | 28 | |
| 448 | `Sacá una foto, anotá información y guardá la ubicación GPS en el lugar, incluso sin señal. Se sincroniza como borrador cuando vuelve la conexión.` | 28 | |
| 449 | `Mis contenidos` | 29 | |
| 450 | `Todo lo que cargaste —fichas, artículos, recorridos—, ordenado por estado, con filtro para ver lo archivado y lo borrado. Si revisás, también podés ver lo de todo el equipo, borradores incluidos.` | 29 | |
| 451 | `Inventario turístico` | 30 | |
| 452 | `El catálogo de fichas publicadas del departamento. De acá se eligen las paradas al armar un recorrido.` | 30 | |
| 453 | `Artículos` | 31 | |
| 454 | `Las notas que la app muestra: título, foto de portada, entradilla, cuerpo y fuentes. Pasan por el mismo flujo de revisión que una ficha.` | 31 | |
| 455 | `Recorridos` | 32 | |
| 456 | `Se arman eligiendo sitios del inventario —hasta nueve—, cada uno con su propio texto, audio o video, en el orden del paseo.` | 32 | |
| 457 | `Cola de revisión` | 33 | |
| 458 | `La cola de lo que espera revisión: asignate una pieza, aprobala y publicala, o devolvela al autor con comentarios.` | 33 | |
| 459 | `Tareas` | 34 | |
| 460 | `Encargos con fecha límite. Los Alumnos pueden reclamar los que están disponibles y marcarlos como hechos.` | 34 | |
| 461 | `Equipo` | 35 | |
| 462 | `Quién entra al panel y con qué rol. Cambiar el rol, suspender, sacar del panel, e invitar gente nueva.` | 35 | |
| 463 | `Reportes` | 36 | |
| 464 | `Producción por autor y salud del contenido: lo publicado sin portada, y lo que no se verifica hace más de seis meses.` | 36 | |
| 465 | `Biblioteca` | 37 | |
| 466 | `La galería de fotos del panel: subir de a tandas, describir, dar crédito y borrar.` | 37 | |
| 467 | `Estructura` | 38 | |
| 468 | `Las categorías y etiquetas de las fichas: crear, renombrar en su lugar, y borrar lo que no esté en uso.` | 38 | |
| 469 | `Mi perfil` | 39 | |
| 470 | `Tu cuenta —nombre, correo, teléfono, foto y contraseña— y el portafolio de lo que publicaste.` | 39 | |
| 471 | `Cómo funciona` | 42 | |
| 472 | `¿Qué hace cada sección?` | 43 | |
| 473 | `Este es el panel de los Promotores Turísticos: acá se escribe, se revisa y se publica todo lo que la app de Caaguazú muestra —fichas de destinos y eventos, artículos y recorridos—. Los Alumnos crean; los Profesores revisan y publican.` | 45 | |
| 474 | `El flujo editorial` | 48 | |
| 475 | `Borrador` | 51 | |
| 476 | `Enviado` | 52 | |
| 477 | `En revisión` | 53 | |
| 478 | `Necesita cambios` | 54 | |
| 479 | `Publicado` | 55 | |
| 480 | `Solo lo aprobado por un Profesor llega a la app. Un Profesor publica directo, sin pasar por revisión, y también puede editar algo ya publicado sin que vuelva a la cola; un Alumno siempre pasa por revisión.` | 58 | |
| 481 | `Lo publicado también se puede despublicar, archivar o mandar a la papelera —y volver atrás desde ahí—, siempre en dos pasos: primero hay que sacarlo del aire antes de poder borrarlo.` | 61 | |
| 482 | `Las secciones` | 65 | |
| 483 | `Extras` | 84 | |
| 484 | `Podés instalar el panel como app (PWA) desde el menú lateral.` | 86 | |
| 485 | `La salida de campo funciona sin conexión: lo que cargues se guarda en el teléfono y se sube solo cuando vuelve la señal.` | 87 | |
| 486 | `Podés cambiar entre modo claro y oscuro desde la barra superior.` | 88 | |
| 487 | `El acceso es solo por invitación. Pedí tu enlace a quien coordina el equipo.` | 89 | |

### Sección inexistente

`templates/sections/404.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 488 | `No encontramos esa sección` | 7 | |
| 489 | `Error 404` | 11 | |
| 490 | `Esta sección no existe` | 12 | |
| 491 | `Puede que el enlace esté roto o que no tengas permiso para acceder.` | 13 | |
| 492 | `Volver al inicio del panel` | 14 | |

## Entrar y salir

Acceso, registro, recuperación, invitaciones y errores de permiso.

### Iniciar sesión

`templates/auth/login.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 493 | `Iniciar sesión` | 12 | |
| 494 | `Entrá al panel de Promotores Turísticos.` | 16 | |
| 495 | `Tu contraseña se actualizó. Ya podés iniciar sesión.` | 19 | |
| 496 | `Email` | 34 | |
| 497 | `Contraseña` | 38 | |
| 498 | `Mantener la sesión iniciada` | 42 | |
| 499 | `Entrar` | 45 | |
| 500 | `¿Olvidaste tu contraseña?` | 49 | |
| 501 | `Acceso solo por invitación` | 50 | |

### Registro

`templates/auth/registro.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 502 | `Crear cuenta` | 23 | |
| 503 | `Crear tu cuenta` | 26 | |
| 504 | `Del Portal de Promotores Turísticos de Caaguazú: acá el equipo escribe lo que después muestra la app de turismo.` | 27 | |
| 505 | `Este enlace ya se usó las veces que tenía permitidas. Pedí uno nuevo a quien te invitó.` | 38 | |
| 506 | `Este enlace venció. Pedí uno nuevo a quien te invitó.` | 41 | |
| 507 | `Este enlace fue dado de baja. Pedí uno nuevo a quien te invitó.` | 44 | |
| 508 | `Para crear una cuenta hace falta un enlace de invitación. Lo genera un Profesor desde el panel, en Equipo; pediselo a quien te sumó al curso.` | 47 | |
| 509 | `Ya tengo una cuenta` | 52 | |
| 510 | `Tu invitación es válida. Vas a entrar como %s.` ⚠️ | 70 | |
| 511 | `Como Profesor vas a poder cargar y publicar contenido sin que lo revise nadie, revisar lo que cargan los Alumnos, y sumar gente al equipo.` | 83 | |
| 512 | `Como Alumno vas a poder cargar fichas, artículos y recorridos. Lo que escribas lo revisa un Profesor antes de que salga en la app.` | 84 | |
| 513 | `Cómo sigue` | 90 | |
| 514 | `Completás estos cuatro datos.` | 92 | |
| 515 | `Entrás al panel al instante: no hay que esperar que nadie apruebe nada.` | 93 | |
| 516 | `De ahí en más entrás con tu correo y tu contraseña. Este enlace no se usa más.` | 94 | |
| 517 | `Tu nombre` | 104 | |
| 518 | `Como querés que te vean en el equipo, y como se firma lo que publiques. Podés cambiarlo después.` | 106 | |
| 519 | `Correo` | 109 | |
| 520 | `Con este correo vas a entrar de acá en adelante. Usá uno al que entres de verdad: es por donde se recupera la contraseña.` | 111 | |
| 521 | `Teléfono` | 114 | |
| 522 | `Ej.: 0981 123 456` | 115 | |
| 523 | `Para que el equipo te pueda ubicar. No se publica en ningún lado ni sale en la app.` | 116 | |
| 524 | `Contraseña` | 119 | |
| 525 | `Seis caracteres o más. Es nueva, no la de tu correo.` | 121 | |
| 526 | `Crear cuenta y entrar` | 124 | |
| 527 | `Este enlace sirve hasta el %s.` ⚠️ | 131 | |

### Recuperar contraseña

`templates/auth/recuperar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 528 | `Recuperar contraseña` | 10 | |
| 529 | `Te enviamos un enlace para restablecer tu contraseña.` | 14 | |
| 530 | `Email` | 27 | |
| 531 | `Enviar enlace` | 30 | |
| 532 | `Volver a iniciar sesión` | 34 | |

### Contraseña nueva

`templates/auth/restablecer.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 533 | `Nueva contraseña` | 12 | |
| 534 | `Nueva contraseña (6 o más caracteres)` | 28 | |
| 535 | `Guardar contraseña` | 31 | |
| 536 | `El enlace no es válido o ya venció. Pedí uno nuevo.` | 34 | |
| 537 | `Pedir un nuevo enlace` | 36 | |

### Marco de acceso

`templates/auth-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 538 | `Acceso` | 8 | |

### Errores y avisos de acceso

`includes/class-auth.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 539 | `No tenés autorización para hacer esto.` | 51 | |
| 540 | `No se pudo crear la invitación: la base de datos rechazó el registro. Avisale a quien administra el sitio.` | 64 | |
| 541 | `Enlace de invitación creado. Lo tenés abajo, en «Invitaciones abiertas».` | 66 | |
| 542 | `Tu sesión venció. Recargá la página.` | 162 | |
| 543 | `Necesitás una invitación válida para registrarte.` | 244 | |
| 544 | `Completá usuario, email, teléfono y una contraseña de al menos 6 caracteres.` | 262 | |
| 545 | `Ese email ya está registrado.` | 270 | |
| 546 | `Si la cuenta existe, te enviamos un email con las instrucciones.` | 320 | |
| 547 | `El enlace para restablecer la contraseña venció o no es válido.` | 338 | |

### Invitaciones

`includes/class-invitations.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 548 | `Válida` | 179 | |
| 549 | `Agotada` | 180 | |
| 550 | `Expirada` | 181 | |
| 551 | `Revocada` | 182 | |
| 552 | `Inválida` | 183 | |
| 553 | `No vence` | 211 | |
| 554 | `Vence el %s` ⚠️ | 214 | |
| 555 | `%d cuenta creada, sin límite` ⚠️ | 222 | |
| 556 | `%d cuentas creadas, sin límite` ⚠️ | 222 | |
| 557 | `%1$d de %2$d` ⚠️ | 225 | |

### Guardas de acceso

`includes/class-router.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 558 | `No tenés acceso a este panel.` | 292 | |
| 559 | `Acceso denegado` | 293 | |

### Guardas de sección

`includes/class-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 560 | `No tenés permiso para ver esta sección.` | 42 | |
| 561 | `Acceso denegado` | 43 | |

### Sin conexión (PWA)

`includes/class-pwa.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 562 | `Sin conexión` | 169 | |
| 563 | `Promotores Turísticos` | 176 | |
| 564 | `Estás sin conexión` | 177 | |
| 565 | `No pudimos cargar esta pantalla. Revisá tu conexión e intentá de nuevo.` | 178 | |
| 566 | `Reintentar` | 179 | |

## wp-admin y mensajes de sistema

Pantallas de administración y respuestas de las acciones.

### Pantallas de wp-admin

`includes/class-admin.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 567 | `Portal Turismo` | 85 | |
| 568 | `Registros` | 88 | |
| 569 | `Invitaciones` | 91 | |
| 570 | `Actualizaciones` | 93 | |
| 571 | `No tenés autorización para hacer esto.` | 108 | |
| 572 | `Usuarios` | 128 | |
| 573 | `Entradas` | 129 | |
| 574 | `Fecha` | 133 | |
| 575 | `Usuario` | 133 | |
| 576 | `Acción` | 134 | |
| 577 | `Elemento` | 134 | |
| 578 | `IP` | 135 | |
| 579 | `Detalle` | 135 | |
| 580 | `No hay registros.` | 139 | |
| 581 | `Invitar a alguien` | 187 | |
| 582 | `Rol` | 194 | |
| 583 | `Vence en (días)` | 204 | |
| 584 | `Vacío o 0: no vence nunca.` | 212 | |
| 585 | `Cuántas cuentas puede crear` | 216 | |
| 586 | `Vacío o 0: sin límite.` | 219 | |
| 587 | `Email (opcional)` | 223 | |
| 588 | `Crear enlace` | 227 | |
| 589 | `Invitaciones abiertas` | 230 | |
| 590 | `No hay ninguna esperando.` | 235 | |
| 591 | `Vence` | 240 | |
| 592 | `Usos` | 241 | |
| 593 | `Enlace` | 242 | |
| 594 | `Copiar` | 256 | |
| 595 | `El enlace deja de servir. ¿Seguimos?` | 261 | |
| 596 | `Revocar` | 266 | |
| 597 | `Copiado` | 284 | |
| 598 | `No se pudo crear la invitación: la base de datos rechazó el registro. Revisá que las tablas del plugin estén al día.` | 318 | |
| 599 | `Enlace de invitación creado. Lo tenés abajo, en «Invitaciones abiertas».` | 320 | |
| 600 | `Invitación revocada. Ese enlace ya no sirve.` | 326 | |
| 601 | `Esa invitación ya no existe.` | 328 | |
| 602 | `Actualizaciones del portal` | 368 | |
| 603 | `No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.` | 372 | |
| 604 | `Atención: la versión del encabezado del plugin (%1$s) no coincide con PROMOTUR_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.` ⚠️ | 379 | |
| 605 | `Versión instalada` | 388 | |
| 606 | `Última disponible` | 389 | |
| 607 | `Actualizar ahora` | 398 | |
| 608 | `Estás al día.` | 400 | |
| 609 | `Última comprobación` | 403 | |
| 610 | `nunca` 🔡 | 404 | |
| 611 | `Repositorio` | 406 | |
| 612 | `Buscar actualizaciones ahora` | 415 | |
| 613 | `Limpiar caché del actualizador` | 421 | |
| 614 | `Token de GitHub` | 425 | |
| 615 | `Definido en wp-config.php mediante PROMOTUR_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.` | 427 | |
| 616 | `El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.` | 429 | |
| 617 | `Token` | 435 | |
| 618 | `•••• guardado (dejá vacío para conservarlo)` | 436 | |
| 619 | `Eliminar el token guardado` | 438 | |
| 620 | `Guardar token` | 442 | |
| 621 | `Hay una nueva versión disponible: %s.` ⚠️ | 461 | |
| 622 | `No hay actualizaciones: ya tenés la última versión.` | 463 | |
| 623 | `El verificador de actualizaciones no está disponible.` | 466 | |
| 624 | `Caché del actualizador limpiada.` | 475 | |
| 625 | `El token está definido en wp-config.php y no se puede cambiar desde acá.` | 480 | |
| 626 | `Token eliminado.` | 487 | |
| 627 | `Token guardado.` | 490 | |
| 628 | `No hubo cambios en el token.` | 492 | |

### Respuestas del editor

`includes/class-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 629 | `No tenés permiso para hacer esto.` | 61 | |
| 630 | `Ese tipo de contenido no existe.` | 74 | |
| 631 | `No podés editar esto.` | 130 | |
| 632 | `(sin título)` | 146 | |
| 633 | `Borrador guardado.` | 207 | |
| 634 | `Guardado. Como editaste algo ya publicado, tendrá que pasar por una nueva revisión.` | 214 | |
| 635 | `De ese enlace no pudimos sacar el pin (los enlaces cortos no lo traen). Cargá la latitud y la longitud a mano, o pegá el enlace largo.` | 254 | |
| 636 | `Un recorrido lleva hasta %d paradas, y sin repetir el mismo sitio. Guardamos las que entraron.` ⚠️ | 309 | |
| 637 | `Esto no se puede enviar.` | 334 | |
| 638 | `Faltan datos obligatorios. Completá el checklist antes de enviar.` | 338 | |
| 639 | `Publicación directa: tu rol no pasa por revisión.` | 348 | |
| 640 | `¡Publicado!` | 349 | |
| 641 | `¡Enviado a revisión!` | 353 | |
| 642 | `Eso no existe o no es contenido del panel.` | 366 | |
| 643 | `Te asignaste la revisión.` | 375 | |
| 644 | `Aprobado y publicado.` | 386 | |
| 645 | `Escribí los comentarios para el autor.` | 394 | |
| 646 | `Devuelto al autor con comentarios.` | 398 | |
| 647 | `No recibimos ninguna imagen.` | 405 | |
| 648 | `Solo podés subir imágenes.` | 409 | |

### Respuestas de gestión

`includes/class-gestion-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 649 | `No tenés permiso para hacer esto.` | 29 | |
| 650 | `Tarea creada.` | 46 | |
| 651 | `La tarea no es válida.` | 53 | |
| 652 | `Reclamaste esta tarea. Ya podés trabajar en ella.` | 56 | |
| 653 | `Tarea completada. 🎉` | 70 | |

### Avisos del plugin

`caaguazu-portal.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 654 | `Caaguazú Portal necesita tener activo el plugin «Caaguazú Cuentas» para funcionar. El inicio de sesión de los Promotores ya no usa los usuarios de WordPress. Activá el plugin desde Plugins para volver a usar el panel.` | 98 | |
| 655 | `Portal de Promotores` | 119 | |
