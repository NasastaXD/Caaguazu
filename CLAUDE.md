# Convenciones de este repo

## Todo cambio sale con release

**Siempre que se toque código de un componente, subir su versión en el mismo
commit.** Sin eso no hay release, y sin release no hay nada que instalar para
probar: el paso de "bajar el zip y subirlo al sitio" es lo que separa un cambio
escrito de un cambio testeado.

| Componente | Dónde va la versión | Tag | Zip |
| --- | --- | --- | --- |
| Theme | header `Version:` de `caaguazu-theme/style.css` | `theme-X.Y.Z` | `caaguazu-theme.zip` |
| Panel | header `Version:` de `caaguazu-portal/caaguazu-portal.php` **y** `PROMOTUR_VERSION` | `portal-X.Y.Z` | `caaguazu-portal.zip` |
| API de la app | header `Version:` de `caaguazu-app-api/caaguazu-app-api.php` **y** `CZUAPI_VERSION` | `app-api-X.Y.Z` | `caaguazu-app-api.zip` |
| SSO CEAD | header `Version:` de `caaguazu-sso-cead/caaguazu-sso-cead.php` **y** `CEADSSO_VERSION` | `sso-X.Y.Z` | `caaguazu-sso-cead.zip` |

Los tres plugins llevan el número **dos veces** —el header que lee WordPress y
la constante que usa el código—. Si se actualiza uno solo, el plugin miente
sobre su propia versión y el updater compara contra el número equivocado.

Y sumar la entrada al `== Changelog ==` de su `readme.txt`. El changelog es lo
que alguien lee para saber qué probar.

### Cómo sale el release

[`.github/workflows/release.yml`](.github/workflows/release.yml) publica los
cuatro componentes, cada uno **sólo si su tag todavía no existe** — o sea, sólo
cuando su versión subió. Se dispara de dos formas:

- **Al mergear a `main`**, automáticamente.
- **A mano**, desde la pestaña Actions → *Publicar releases* → *Run workflow*,
  eligiendo la rama y qué componente publicar. Esto es lo que permite probar una
  rama antes de mergearla.

Mientras una rama esté sin mergear y sin dispatch, **entregar los zips
directamente**: `bash bin/build-zip.sh` los arma los cuatro (o
`bash bin/build-zip.sh portal` para uno solo).

> **Regla que no se rompe:** cada release lleva **un solo zip**, y cada updater
> se queda con el release que trae el suyo. Es lo que evita que el updater del
> theme se coma un release del panel. Ver el comentario largo del workflow.

## Antes de dar nada por bueno

```bash
npm run verificar   # diseño + lógica + rutas + auditoría + las 23 pantallas
```

Son cinco cosas, y las cinco salen con código 1 si algo falla:

- `tools/verificar-diseno.php` — las reglas del sistema de diseño del panel
  (colores, radios, sombras, tipografía, clases sin estilo, URLs a mano).
- `tools/verificar-logica.php` — las dos únicas funciones que **transforman** un
  dato en vez de moverlo, que son las dos que fallan en silencio: el parseo del
  enlace de Google Maps y la normalización de roles del CEAD.
- `tools/verificar-rutas.php` — a qué ruta resuelve cada URL del panel. El
  comodín de sección convive con una docena de rutas específicas y WordPress se
  queda con la primera que matchea, así que el **orden** del mapa de reglas es
  parte de la definición: mal ordenado deja login, registro, el enlace de
  invitación y la PWA inalcanzables, sin tirar ningún error.
- `tools/verificar-auditoria.php` — que todo lo que se anota en el registro de
  auditoría se vea en «Registros». Esa pantalla filtra por una lista de
  acciones, así que una acción que se escriba y no esté en la lista queda
  guardada donde nadie la mira — y uno lee esa pantalla vacía como «no pasó
  nada». Ya tapó el alta de una cuenta y el evento que existía justamente para
  diagnosticar altas rotas.
- `tools/auditar-movil.mjs` — nada se sale de la pantalla, nada que se toque
  baja de 44px.

Y para mirar una pantalla sin levantar WordPress:

```bash
php tools/vista-previa-panel.php sections/recorridos nuevo > /tmp/x.html
```

El segundo argumento es el segmento de detalle: sin él, las secciones que hacen
de lista y de editor a la vez sólo se ven en su mitad de lista.

## Idioma

Todo en castellano rioplatense: código, comentarios, commits, documentación y
los textos que ve la gente. El panel no tiene selector de idioma; los idiomas
que sí existen (ES / EN / GN) son los de la app.
