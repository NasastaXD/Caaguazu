# Textos del panel

Todo lo que un usuario lee en el panel, sacado de las fuentes el 2026-08-27. **537 textos.**

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
| 174 | `revisa %s` | Cola de revisión |
| 190 | `vence %s` | Tareas |
| 260 | `opcional` | Biblioteca |
| 329 | `opcional` | Mi perfil |
| 494 | `nunca` | Pantallas de wp-admin |

## El armazón

Lo que se ve en todas las pantallas.

### Menú lateral (rótulos y secciones)

`includes/helpers.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 1 | `Administrador` | 162 | |
| 2 | `Invitado` | 164 | |
| 3 | `GESTIÓN` | 289 | |
| 4 | `Inicio` | 291 | |
| 5 | `Mis contenidos` | 294 | |
| 6 | `Nueva ficha` | 298 | |
| 7 | `Salida de campo` | 299 | |
| 8 | `Cola de revisión` | 302 | |
| 9 | `Tareas` | 303 | |
| 10 | `PORTAL` | 307 | |
| 11 | `Equipo` | 309 | |
| 12 | `Reportes` | 310 | |
| 13 | `Biblioteca` | 311 | |
| 14 | `Estructura` | 312 | |
| 15 | `App` | 320 | |
| 16 | `Mi perfil` | 340 | |
| 17 | `Ayuda` | 341 | |

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
| 29 | `Cambiar tema` | 42 | |
| 30 | `Notificaciones` | 48 | |
| 31 | `Marcar todo como leído` | 60 | |
| 32 | `No hay novedades por ahora. ✨` | 66 | |

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
| 39 | `Instalar app` | 73 | |
| 40 | `Enviando…` | 74 | |
| 41 | `Algo salió mal. Probá de nuevo.` | 75 | |
| 42 | `Guardado` | 76 | |
| 43 | `¿Querés confirmar esta acción?` | 77 | |
| 44 | `Faltan algunos datos obligatorios.` | 78 | |

### Notificaciones

`includes/class-notifications.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 45 | `«%s» está esperando revisión` ⚠️ | 48 | |
| 46 | `atrás` 🔡 | 50 | |
| 47 | `«%s» necesita algunos cambios` ⚠️ | 72 | |
| 48 | `Notificaciones marcadas como leídas.` | 135 | |

### Estados del flujo editorial

`includes/class-editorial.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 49 | `Borrador` | 30 | |
| 50 | `Enviado` | 31 | |
| 51 | `En revisión` | 32 | |
| 52 | `Necesita cambios` | 33 | |
| 53 | `Aprobado` | 34 | |
| 54 | `Publicado` | 35 | |
| 55 | `Despublicado` | 36 | |
| 56 | `Archivado` | 37 | |
| 57 | `Nombre del destino` | 86 | |
| 58 | `Descripción` | 93 | |
| 59 | `Faltan fuentes o referencias.` | 123 | |
| 60 | `Mejorá las fotos: cuidá la luz, el encuadre y la portada.` | 124 | |
| 61 | `Verificá los horarios y los costos.` | 125 | |
| 62 | `Revisá la ortografía y la redacción.` | 126 | |
| 63 | `Precisá cómo llegar.` | 127 | |

### Guardas de las acciones

`includes/class-acciones.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 64 | `Esa acción no existe.` | 147 | |
| 65 | `Esa acción sólo acepta envíos.` | 151 | |
| 66 | `Tu sesión venció. Volvé a entrar.` | 158 | |
| 67 | `Tu sesión venció. Recargá la página.` | 165 | |
| 68 | `No tenés autorización para hacer esto.` | 170 | |

### Nombres de los roles

`includes/class-roles.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 69 | `Promotor` | 63 | |
| 70 | `Mini Promotor` | 64 | |
| 71 | `Visitante` | 65 | |

## Las secciones

Una tabla por pantalla del panel.

### Inicio

`templates/sections/home.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 72 | `Esperan revisión` | 34 | |
| 73 | `Publicados` | 35 | |
| 74 | `Esperan tu corrección` | 38 | |
| 75 | `En proceso` | 39 | |
| 76 | `Inicio` | 51 | |
| 77 | `Tu actividad de hoy` | 56 | |
| 78 | `Hola, %s 👋` ⚠️ | 59 | |
| 79 | `Actividad reciente` | 84 | |
| 80 | `Actividad de los últimos %d día` ⚠️ | 107 | |
| 81 | `Actividad de los últimos %d días` ⚠️ | 107 | |
| 82 | `Accesos rápidos` | 123 | |
| 83 | `Crear una ficha` | 128 | |
| 84 | `Mis contenidos` | 131 | |
| 85 | `Cola de revisión` | 134 | |
| 86 | `Equipo` | 137 | |
| 87 | `Mi perfil` | 139 | |

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
| 108 | `Destinos` | 63 | |
| 109 | `Destino` | 64 | |
| 110 | `Nuevo destino` | 65 | |
| 111 | `Editar destino` | 66 | |
| 112 | `Buscar destinos` | 67 | |
| 113 | `Categorías` | 112 | |
| 114 | `Categoría` | 112 | |
| 115 | `Zonas` | 115 | |
| 116 | `Zona` | 115 | |
| 117 | `Etiquetas` | 119 | |
| 118 | `Etiqueta` | 119 | |
| 119 | `Identidad` | 132 | |
| 120 | `Gancho (una línea)` | 134 | |
| 121 | `Foto de portada` | 135 | |
| 122 | `Crédito de las fotos` | 136 | |
| 123 | `Video (URL, opcional)` | 137 | |
| 124 | `Ubicación y acceso` | 141 | |
| 125 | `Latitud (pin)` | 143 | |
| 126 | `Longitud (pin)` | 144 | |
| 127 | `Referencia («a 3 km de…»)` | 145 | |
| 128 | `Cómo llegar (auto / colectivo / a pie)` | 146 | |
| 129 | `Estado del camino` | 147 | |
| 130 | `Accesibilidad` | 148 | |
| 131 | `Datos prácticos` | 152 | |
| 132 | `Horario y mejor momento para visitar` | 154 | |
| 133 | `Temporada ideal / cuándo evitar` | 155 | |
| 134 | `Costo / entrada` | 156 | |
| 135 | `Rango de precio` | 164 | |
| 136 | `Sin especificar` | 168 | |
| 137 | `Gratis` | 169 | |
| 138 | `$ — Muy barato` | 170 | |
| 139 | `$$ — Barato` | 171 | |
| 140 | `$$$ — Intermedio` | 172 | |
| 141 | `$$$$ — Caro` | 173 | |
| 142 | `Servicios (baños, comida, sombra…)` | 176 | |
| 143 | `Duración sugerida` | 177 | |
| 144 | `Contacto del lugar` | 178 | |
| 145 | `Fuentes y referencias` | 182 | |
| 146 | `Fuentes / referencias` | 184 | |

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

### Equipo

`templates/sections/equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 199 | `Equipo` | 17 | |
| 200 | `Tu equipo` | 20 | |
| 201 | `Invitar a alguien` | 24 | |
| 202 | `Generá un enlace de invitación con el rol que quieras. El enlace es válido durante 14 días.` | 25 | |
| 203 | `Mini Promotor` | 29 | |
| 204 | `Promotor` | 30 | |
| 205 | `Visitante` | 31 | |
| 206 | `Crear enlace` | 33 | |
| 207 | `%1$d publicadas · %2$d en total` ⚠️ | 54 | |
| 208 | `Nivel de confianza:` | 64 | |
| 209 | `Guardar` | 71 | |
| 210 | `Suspendida` | 80 | |
| 211 | `Rol` | 87 | |
| 212 | `Cambiar rol` | 92 | |
| 213 | `Reactivar` | 99 | |
| 214 | `Suspender` | 99 | |
| 215 | `Deja de tener acceso al panel. Su cuenta y lo que publicó quedan como están. ¿Seguimos?` | 104 | |
| 216 | `Sacar del panel` | 107 | |
| 217 | `Invitaciones abiertas` | 117 | |
| 218 | `No hay ninguna esperando. Los enlaces que crees acá arriba aparecen en esta lista hasta que alguien los use o se venzan.` | 120 | |
| 219 | `Vence el %s` ⚠️ | 133 | |
| 220 | `El enlace deja de servir. ¿Seguimos?` | 139 | |
| 221 | `Revocar` | 142 | |

### Equipo (avisos)

`includes/class-equipo.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 222 | `Esa persona no existe.` | 51 | |
| 223 | `A vos mismo no te podés editar desde acá.` | 54 | |
| 224 | `Ese rol no existe.` | 72 | |
| 225 | `%1$s ahora es %2$s.` ⚠️ | 85 | |
| 226 | `Suspendimos a %s. No va a poder entrar hasta que la reactives.` ⚠️ | 114 | |
| 227 | `%s puede volver a entrar.` ⚠️ | 119 | |
| 228 | `%s ya no entra al panel. Su cuenta y lo que publicó quedan como están.` ⚠️ | 142 | |
| 229 | `Esa invitación ya no existe.` | 150 | |
| 230 | `Invitación revocada. Ese enlace ya no sirve.` | 153 | |

### Reportes

`templates/sections/reportes.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 231 | `Reportes` | 12 | |
| 232 | `Métricas` | 15 | |
| 233 | `Actividad del portal` | 16 | |
| 234 | `Producción por autor` | 18 | |
| 235 | `%1$d publicadas / %2$d` ⚠️ | 27 | |
| 236 | `Estado del contenido` | 33 | |
| 237 | `Fichas publicadas sin portada` | 36 | |
| 238 | `Fichas sin verificar hace +6 meses` | 46 | |

### Biblioteca

`templates/sections/biblioteca.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 239 | `Biblioteca` | 21 | |
| 240 | `Medios` | 26 | |
| 241 | `Biblioteca de medios` | 27 | |
| 242 | `Subir fotos` | 37 | |
| 243 | `Podés elegir varias de una vez. JPG, PNG, WEBP o GIF.` | 40 | |
| 244 | `Subir` | 42 | |
| 245 | `Buscar una foto` | 50 | |
| 246 | `Sólo las mías` | 54 | |
| 247 | `Filtrar` | 56 | |
| 248 | `No encontramos ninguna foto con ese nombre.` | 64 | |
| 249 | `Todavía no hay fotos. Subí las primeras acá arriba.` | 66 | |
| 250 | `%d foto` ⚠️ | 77 | |
| 251 | `%d fotos` ⚠️ | 77 | |
| 252 | `Sin descripción` | 101 | |
| 253 | `Es la portada de %d ficha.` ⚠️ | 112 | |
| 254 | `Es la portada de %d fichas.` ⚠️ | 112 | |
| 255 | `Esta foto la subió otra persona, así que sólo podés verla.` | 120 | |
| 256 | `Nombre` | 128 | |
| 257 | `Descripción` | 132 | |
| 258 | `Qué se ve en la foto` | 134 | |
| 259 | `Crédito` | 137 | |
| 260 | `opcional` 🔡 | 137 | |
| 261 | `Quién la sacó` | 139 | |
| 262 | `Guardar` | 143 | |
| 263 | `Se borra la foto y no se puede deshacer. ¿Seguimos?` | 149 | |
| 264 | `Borrar foto` | 152 | |
| 265 | `Anteriores` | 165 | |
| 266 | `Página %1$d de %2$d` ⚠️ | 171 | |
| 267 | `Siguientes` | 179 | |

### Biblioteca (avisos)

`includes/class-medios.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 268 | `No elegiste ninguna foto.` | 141 | |
| 269 | `No pudimos subir ninguna: revisá que sean JPG, PNG, WEBP o GIF.` | 182 | |
| 270 | `Subimos %1$d foto. %2$d quedó afuera por el formato.` ⚠️ | 187 | |
| 271 | `Subimos %1$d fotos. %2$d quedaron afuera por el formato.` ⚠️ | 187 | |
| 272 | `Subimos %d foto.` ⚠️ | 194 | |
| 273 | `Subimos %d fotos.` ⚠️ | 194 | |
| 274 | `Esa foto no existe.` | 218 | |
| 275 | `Esa foto la subió otra persona.` | 221 | |
| 276 | `Listo, guardamos la foto.` | 234 | |
| 277 | `No la borramos: es la portada de %d ficha. Cambiala ahí primero.` ⚠️ | 250 | |
| 278 | `No la borramos: es la portada de %d fichas. Cambiala ahí primero.` ⚠️ | 250 | |
| 279 | `Foto borrada.` | 259 | |

### Estructura

`templates/sections/estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 280 | `Estructura` | 16 | |
| 281 | `Organización` | 21 | |
| 282 | `Estructura del sitio` | 22 | |
| 283 | `Esto lo organiza un Promotor. Podés ver cómo está armado, pero no cambiarlo.` | 28 | |
| 284 | `Todavía no hay ninguna.` | 40 | |
| 285 | `Guardar` | 53 | |
| 286 | `%d ficha` ⚠️ | 64 | |
| 287 | `%d fichas` ⚠️ | 64 | |
| 288 | `Se borra y no se puede deshacer. ¿Seguimos?` | 71 | |
| 289 | `Borrar` | 75 | |
| 290 | `Agregar` | 92 | |
| 291 | `El ícono y el color con que la app muestra cada categoría se eligen en App →` | 100 | |

### Estructura (nombres y avisos)

`includes/class-estructura.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 292 | `Categorías` | 42 | |
| 293 | `Categoría` | 43 | |
| 294 | `De qué tipo es el lugar: salto, museo, feria. La app agrupa por acá y les pone ícono y color.` | 44 | |
| 295 | `Zonas` | 47 | |
| 296 | `Zona` | 48 | |
| 297 | `Dónde queda: el distrito o la región del departamento.` | 49 | |
| 298 | `Etiquetas` | 52 | |
| 299 | `Etiqueta` | 53 | |
| 300 | `Lo que no entra en las otras dos: «con niños», «gratis», «llega colectivo».` | 54 | |
| 301 | `Eso no se puede editar desde acá.` | 79 | |
| 302 | `Escribí un nombre.` | 88 | |
| 303 | `Ya existe una con ese nombre.` | 91 | |
| 304 | `Creamos «%s».` ⚠️ | 106 | |
| 305 | `Listo, cambiamos el nombre.` | 121 | |
| 306 | `Eso ya no existe.` | 129 | |
| 307 | `No la borramos: %d ficha la usa. Movelas primero.` ⚠️ | 135 | |
| 308 | `No la borramos: %d fichas la usan. Movelas primero.` ⚠️ | 135 | |
| 309 | `Borramos «%s».` ⚠️ | 149 | |

### Buscar

`templates/sections/buscar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 310 | `Buscar` | 18 | |
| 311 | `Escribí algo en el buscador de arriba para encontrar fichas.` | 23 | |
| 312 | `%1$d resultado para «%2$s»` ⚠️ | 28 | |
| 313 | `%1$d resultados para «%2$s»` ⚠️ | 28 | |
| 314 | `No encontramos resultados. Probá con otras palabras.` | 32 | |

### Mi perfil

`templates/sections/perfil.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 315 | `Mi perfil` | 23 | |
| 316 | `Tu progreso de confianza` | 39 | |
| 317 | `Nivel máximo: publicás directamente y después se hace una auditoría. Gracias por tu compromiso.` | 53 | |
| 318 | `Promotor Jr: podés editar fichas publicadas sin pasar por una nueva revisión. Seguí sumando aprobaciones para llegar a «De confianza».` | 55 | |
| 319 | `Aprendiz: todo tu contenido pasa por revisión. A medida que sumás aprobaciones, vas ganando autonomía.` | 57 | |
| 320 | `Fichas publicadas` | 67 | |
| 321 | `Mi portafolio` | 76 | |
| 322 | `Todavía no tenés fichas publicadas.` | 78 | |
| 323 | `Publicado` | 86 | |
| 324 | `Estás entrando como administrador del sitio, con acceso prestado. Este acceso no tiene cuenta del panel, así que no hay datos que editar acá.` | 94 | |
| 325 | `Mis datos` | 98 | |
| 326 | `Nombre` | 105 | |
| 327 | `Correo` | 112 | |
| 328 | `Teléfono` | 117 | |
| 329 | `opcional` 🔡 | 117 | |
| 330 | `Foto` | 123 | |
| 331 | `Con el correo entrás al panel: si lo cambiás, la próxima vez iniciás sesión con el nuevo.` | 126 | |
| 332 | `Guardar cambios` | 129 | |
| 333 | `Contraseña` | 134 | |
| 334 | `Contraseña actual` | 141 | |
| 335 | `Contraseña nueva` | 147 | |
| 336 | `Repetila` | 151 | |
| 337 | `Cambiar contraseña` | 157 | |

### Mi perfil (avisos)

`includes/class-cuenta.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 338 | `Estás entrando como administrador de WordPress, que no tiene cuenta del panel que editar.` | 50 | |
| 339 | `Escribí tu nombre.` | 58 | |
| 340 | `Ese correo no parece válido.` | 61 | |
| 341 | `Ya hay una cuenta con ese correo.` | 67 | |
| 342 | `No pudimos guardar los cambios. Probá de nuevo.` | 76 | |
| 343 | `Listo, guardamos tus datos.` | 79 | |
| 344 | `La foto tiene que ser JPG, PNG o WEBP.` | 102 | |
| 345 | `La contraseña actual no coincide.` | 137 | |
| 346 | `Las dos contraseñas nuevas tienen que ser iguales.` | 140 | |
| 347 | `No pudimos cambiar la contraseña. Probá de nuevo.` | 148 | |
| 348 | `Listo, cambiaste tu contraseña.` | 154 | |

### Niveles de confianza

`includes/class-stats.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 349 | `Aprendiz` | 34 | |
| 350 | `Promotor Jr` | 35 | |
| 351 | `De confianza` | 36 | |

### App (control de la app móvil)

`templates/sections/app.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 352 | `App` | 21 | |
| 353 | `Aplicación` | 27 | |
| 354 | `Textos` | 36 | |
| 355 | `Idioma` | 37 | |
| 356 | `Clave` | 63 | |
| 357 | `Texto` | 67 | |
| 358 | `Guardar cambios` | 74 | |
| 359 | `Medios` | 83 | |
| 360 | `Ir a la biblioteca` | 86 | |
| 361 | `Tipo` | 113 | |
| 362 | `Imagen` | 115 | |
| 363 | `Animación` | 116 | |
| 364 | `URL o ID` | 120 | |
| 365 | `Texto alternativo` | 124 | |
| 366 | `Formato` | 128 | |
| 367 | `Categorías` | 145 | |
| 368 | `Todavía no hay categorías cargadas. Se crean en Estructura y después se les elige acá el icono y el color.` | 149 | |
| 369 | `Estructura` | 150 | |
| 370 | `Nombre` | 160 | |
| 371 | `Color` | 164 | |
| 372 | `Icono` | 169 | |

### App (avisos)

`includes/class-app-control.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 373 | `No tenés autorización para hacer esto.` | 134 | |
| 374 | `Guardado` | 195 | |

### Ayuda

`templates/sections/ayuda.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 375 | `Ayuda` | 5 | |
| 376 | `Inicio` | 9 | |
| 377 | `Tu resumen del día: fichas que esperan revisión, contenido que necesita correcciones y accesos rápidos según tu rol.` | 9 | |
| 378 | `Nueva ficha` | 10 | |
| 379 | `El editor guiado para crear destinos. Completá los campos y el checklist; el sistema te avisa si falta algo antes de enviar la ficha a revisión.` | 10 | |
| 380 | `Salida de campo` | 11 | |
| 381 | `Sacá fotos, anotá información y guardá la ubicación GPS mientras estás en el lugar, incluso sin señal. Después podés sincronizar todo como borrador cuando vuelva la conexión.` | 11 | |
| 382 | `Mis contenidos` | 12 | |
| 383 | `Todas tus fichas, ordenadas por estado: borrador, enviada, en revisión, necesita cambios o publicada.` | 12 | |
| 384 | `Cola de revisión` | 13 | |
| 385 | `Para Promotores: revisá las fichas enviadas, asignate una, aprobala y publicala o devolvela con comentarios para que el autor haga los cambios necesarios.` | 13 | |
| 386 | `Tareas` | 14 | |
| 387 | `Asignaciones con fecha límite y una lista de lo que todavía falta cubrir. Los Mini Promotores pueden reclamar los huecos disponibles.` | 14 | |
| 388 | `Curaduría` | 15 | |
| 389 | `Elegí qué destinos aparecen destacados en la portada y configurá un banner de temporada. Los cambios se reflejan en la web pública sin tocar el código.` | 15 | |
| 390 | `Moderación` | 16 | |
| 391 | `Aprobá o descartá reseñas, respondé o derivá consultas de visitantes y atendé los reportes de información desactualizada.` | 16 | |
| 392 | `Equipo` | 17 | |
| 393 | `Gestioná a los Mini Promotores: revisá su producción, nivel de confianza y enlaces de invitación.` | 17 | |
| 394 | `Reportes` | 18 | |
| 395 | `Consultá la producción por autor, los destinos más vistos, las búsquedas sin resultado y el estado general del contenido.` | 18 | |
| 396 | `Mi perfil` | 19 | |
| 397 | `Consultá tu portafolio público, las vistas de tus fichas y tu progreso de nivel de confianza.` | 19 | |
| 398 | `Cómo funciona` | 22 | |
| 399 | `¿Qué hace cada sección?` | 23 | |
| 400 | `Este es el portal de los Promotores Turísticos: una web turística pública con un espacio de trabajo editorial detrás. Los Mini Promotores crean las fichas de destino y los Promotores las revisan y publican.` | 25 | |
| 401 | `El flujo editorial` | 28 | |
| 402 | `Borrador` | 31 | |
| 403 | `Enviado` | 32 | |
| 404 | `En revisión` | 33 | |
| 405 | `Necesita cambios` | 34 | |
| 406 | `Publicado` | 35 | |
| 407 | `Solo las fichas aprobadas por un Promotor llegan al público. La confianza se construye con cada aprobación: pasás de Aprendiz a Promotor Jr y luego a De confianza. Cada nivel te da más autonomía, como editar fichas publicadas sin una nueva revisión y, finalmente, publicar directamente.` | 38 | |
| 408 | `Las secciones` | 42 | |
| 409 | `Extras` | 56 | |
| 410 | `Podés instalar el portal como app (PWA) y consultar parte del contenido sin conexión desde el menú lateral.` | 58 | |
| 411 | `Cada ficha pública puede tener reseñas, indicaciones para llegar, un código QR para imprimir y un botón para agregarla a «Mi viaje».` | 59 | |
| 412 | `Podés cambiar entre modo claro y oscuro desde la barra superior.` | 60 | |
| 413 | `El acceso es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 61 | |

### Sección inexistente

`templates/sections/404.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 414 | `No encontramos esa sección` | 7 | |
| 415 | `Error 404` | 11 | |
| 416 | `Esta sección no existe` | 12 | |
| 417 | `Puede que el enlace esté roto o que no tengas permiso para acceder.` | 13 | |
| 418 | `Volver al inicio del panel` | 14 | |

## Entrar y salir

Acceso, registro, recuperación, invitaciones y errores de permiso.

### Iniciar sesión

`templates/auth/login.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 419 | `Iniciar sesión` | 12 | |
| 420 | `Entrá al panel de Promotores Turísticos.` | 16 | |
| 421 | `Tu contraseña se actualizó. Ya podés iniciar sesión.` | 19 | |
| 422 | `Email` | 34 | |
| 423 | `Contraseña` | 38 | |
| 424 | `Mantener la sesión iniciada` | 42 | |
| 425 | `Entrar` | 45 | |
| 426 | `¿Olvidaste tu contraseña?` | 49 | |
| 427 | `Acceso solo por invitación` | 50 | |

### Registro

`templates/auth/registro.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 428 | `Crear cuenta` | 14 | |
| 429 | `Esta invitación ya fue usada.` | 27 | |
| 430 | `Esta invitación venció. Pedí una nueva al equipo.` | 28 | |
| 431 | `Esta invitación fue revocada.` | 29 | |
| 432 | `El registro es solo por invitación. Pedí tu enlace al equipo de Turismo.` | 30 | |
| 433 | `Ya tengo una cuenta` | 35 | |
| 434 | `Invitación válida: te unirás como %s.` ⚠️ | 44 | |
| 435 | `Nombre de usuario` | 55 | |
| 436 | `Email` | 59 | |
| 437 | `Teléfono` | 63 | |
| 438 | `Ej.: 0981 123 456` | 64 | |
| 439 | `Contraseña (6 o más caracteres)` | 67 | |

### Recuperar contraseña

`templates/auth/recuperar.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 440 | `Recuperar contraseña` | 10 | |
| 441 | `Te enviamos un enlace para restablecer tu contraseña.` | 14 | |
| 442 | `Email` | 27 | |
| 443 | `Enviar enlace` | 30 | |
| 444 | `Volver a iniciar sesión` | 34 | |

### Contraseña nueva

`templates/auth/restablecer.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 445 | `Nueva contraseña` | 12 | |
| 446 | `Nueva contraseña (6 o más caracteres)` | 28 | |
| 447 | `Guardar contraseña` | 31 | |
| 448 | `El enlace no es válido o ya venció. Pedí uno nuevo.` | 34 | |
| 449 | `Pedir un nuevo enlace` | 36 | |

### Marco de acceso

`templates/auth-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 450 | `Acceso` | 8 | |

### Errores y avisos de acceso

`includes/class-auth.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 451 | `No tenés autorización para hacer esto.` | 43 | |
| 452 | `Enlace de invitación creado. Es válido durante 14 días: %s` ⚠️ | 49 | |
| 453 | `Tu sesión venció. Recargá la página.` | 143 | |
| 454 | `Necesitás una invitación válida para registrarte.` | 182 | |
| 455 | `Completá usuario, email, teléfono y una contraseña de al menos 6 caracteres.` | 192 | |
| 456 | `Ese email ya está registrado.` | 196 | |
| 457 | `Si la cuenta existe, te enviamos un email con las instrucciones.` | 241 | |
| 458 | `El enlace para restablecer la contraseña venció o no es válido.` | 259 | |

### Invitaciones

`includes/class-invitations.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 459 | `Válida` | 99 | |
| 460 | `Usada` | 100 | |
| 461 | `Expirada` | 101 | |
| 462 | `Revocada` | 102 | |
| 463 | `Inválida` | 103 | |

### Guardas de acceso

`includes/class-router.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 464 | `No tenés acceso a este panel.` | 203 | |
| 465 | `Acceso denegado` | 204 | |

### Guardas de sección

`includes/class-shell.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 466 | `No tenés permiso para ver esta sección.` | 42 | |
| 467 | `Acceso denegado` | 43 | |

### Sin conexión (PWA)

`includes/class-pwa.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 468 | `Sin conexión` | 169 | |
| 469 | `Promotores Turísticos` | 176 | |
| 470 | `Estás sin conexión` | 177 | |
| 471 | `No pudimos cargar esta pantalla. Revisá tu conexión e intentá de nuevo.` | 178 | |
| 472 | `Reintentar` | 179 | |

## wp-admin y mensajes de sistema

Pantallas de administración y respuestas de las acciones.

### Pantallas de wp-admin

`includes/class-admin.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 473 | `Portal Turismo` | 46 | |
| 474 | `Registros` | 49 | |
| 475 | `Actualizaciones` | 51 | |
| 476 | `No tenés autorización para hacer esto.` | 66 | |
| 477 | `Usuarios` | 95 | |
| 478 | `Entradas` | 96 | |
| 479 | `Fecha` | 100 | |
| 480 | `Usuario` | 100 | |
| 481 | `Acción` | 101 | |
| 482 | `Elemento` | 101 | |
| 483 | `IP` | 102 | |
| 484 | `Detalle` | 102 | |
| 485 | `No hay registros.` | 106 | |
| 486 | `Actualizaciones del portal` | 163 | |
| 487 | `No se pudo iniciar el verificador de actualizaciones (plugin-update-checker). Revisá que la carpeta vendor/ esté presente.` | 167 | |
| 488 | `Atención: la versión del encabezado del plugin (%1$s) no coincide con PROMOTUR_VERSION (%2$s). El sistema de actualizaciones usa la versión del encabezado; mantenelas iguales para evitar problemas al publicar nuevas versiones.` ⚠️ | 174 | |
| 489 | `Versión instalada` | 183 | |
| 490 | `Última disponible` | 184 | |
| 491 | `Actualizar ahora` | 193 | |
| 492 | `Estás al día.` | 195 | |
| 493 | `Última comprobación` | 198 | |
| 494 | `nunca` 🔡 | 199 | |
| 495 | `Repositorio` | 201 | |
| 496 | `Buscar actualizaciones ahora` | 210 | |
| 497 | `Limpiar caché del actualizador` | 216 | |
| 498 | `Token de GitHub` | 220 | |
| 499 | `Definido en wp-config.php mediante PROMOTUR_GITHUB_TOKEN. No se puede editar desde acá y tiene prioridad sobre el token guardado en la base de datos.` | 222 | |
| 500 | `El repositorio es público, así que normalmente no necesitás un token. Configurá uno si el repositorio pasa a ser privado o si alcanzás el límite de peticiones de GitHub.` | 224 | |
| 501 | `Token` | 230 | |
| 502 | `•••• guardado (dejá vacío para conservarlo)` | 231 | |
| 503 | `Eliminar el token guardado` | 233 | |
| 504 | `Guardar token` | 237 | |
| 505 | `Hay una nueva versión disponible: %s.` ⚠️ | 256 | |
| 506 | `No hay actualizaciones: ya tenés la última versión.` | 258 | |
| 507 | `El verificador de actualizaciones no está disponible.` | 261 | |
| 508 | `Caché del actualizador limpiada.` | 270 | |
| 509 | `El token está definido en wp-config.php y no se puede cambiar desde acá.` | 275 | |
| 510 | `Token eliminado.` | 282 | |
| 511 | `Token guardado.` | 285 | |
| 512 | `No hubo cambios en el token.` | 287 | |

### Respuestas del editor

`includes/class-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 513 | `No tenés permiso para hacer esto.` | 44 | |
| 514 | `No podés editar esta ficha.` | 83 | |
| 515 | `(sin título)` | 93 | |
| 516 | `Borrador guardado.` | 127 | |
| 517 | `Guardado. Como editaste una ficha publicada, tendrá que pasar por una nueva revisión.` | 135 | |
| 518 | `La ficha no es válida.` | 152 | |
| 519 | `Faltan datos obligatorios. Completá el checklist antes de enviarla.` | 156 | |
| 520 | `Publicación directa por nivel de confianza. Se hará una auditoría posterior.` | 163 | |
| 521 | `¡Publicado! Se aplicó tu nivel de confianza.` | 164 | |
| 522 | `¡Ficha enviada a revisión!` | 167 | |
| 523 | `Te asignaste la revisión.` | 178 | |
| 524 | `Ficha aprobada y publicada.` | 193 | |
| 525 | `Escribí los comentarios para el autor.` | 205 | |
| 526 | `Ficha devuelta al autor con comentarios.` | 209 | |
| 527 | `No recibimos ninguna imagen.` | 216 | |
| 528 | `Solo podés subir imágenes.` | 220 | |

### Respuestas de gestión

`includes/class-gestion-ajax.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 529 | `No tenés permiso para hacer esto.` | 29 | |
| 530 | `Tarea creada.` | 46 | |
| 531 | `La tarea no es válida.` | 53 | |
| 532 | `Reclamaste esta tarea. Ya podés trabajar en ella.` | 56 | |
| 533 | `Tarea completada. 🎉` | 70 | |
| 534 | `El usuario no es válido.` | 78 | |
| 535 | `Nivel actualizado.` | 81 | |

### Avisos del plugin

`caaguazu-portal.php`

| # | Texto actual | Línea | Nuevo texto |
| --- | --- | --- | --- |
| 536 | `Caaguazú Portal necesita tener activo el plugin «Caaguazú Cuentas» para funcionar. El inicio de sesión de los Promotores ya no usa los usuarios de WordPress. Activá el plugin desde Plugins para volver a usar el panel.` | 88 | |
| 537 | `Portal de Promotores` | 109 | |
