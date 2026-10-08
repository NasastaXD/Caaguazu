=== Caaguazú SSO CEAD ===
Contributors: municipalidadcaaguazu
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.1.3
License: GPLv2 or later

Acceso de un clic desde el panel del CEAD al Portal de Promotores Turísticos, sin registro nuevo ni contraseña propia del portal.

== Description ==

El CEAD tiene un curso de Servicios Turísticos. Este plugin deja que su alumnado
y docentes entren al Portal de Promotores Turísticos con un clic desde el panel
del colegio, ya identificados y con el rol que les corresponde acá.

El CEAD afirma quién es la persona (un código opaco de un solo uso, servidor a
servidor); este plugin decide qué cuenta y qué permisos le corresponden en el
Portal. No crea usuarios de WordPress ni toca su cookie — todo corre sobre
`caaguazu-cuentas`, el sistema de cuentas universal del ecosistema.

**IMPORTANTE — este plugin va en caaguazu.net, NO en el sitio del CEAD.**

La integración tiene dos mitades, en dos WordPress distintos, que no comparten
código: sólo el secreto compartido y el contrato HTTP.

| Dónde | Qué corre ahí | Qué hace |
|---|---|---|
| **caaguazu.net** | **este plugin** (`caaguazu-sso-cead`) | Recibe `/acceso-cead?code=`, canjea el código contra el CEAD, resuelve la cuenta, aplica el rol y abre sesión. |
| **sitio del CEAD** | su propio plugin (`cead-acad`) | Marca el curso participante, genera el código, expone `/wp-json/cead-sso/v1/redeem` y pinta el botón "Ir al portal". |

Por eso este plugin declara `caaguazu-cuentas` y `caaguazu-portal` como
dependencias: los tres viven en caaguazu.net. La mitad del CEAD **no depende de
ninguno de ellos** — es WordPress pelado más el endpoint del contrato.

**Reglas de negocio, ya decididas para esta integración:**

* Un email que ya tiene cuenta en el portal, pero sin vincular a un `cead_uid`
  todavía, se **rechaza** — no se vincula solo por email. Es la puerta de un
  robo de cuenta (quien controle ese email en el CEAD pasaría a manejar la
  cuenta existente). Un admin lo vincula a mano desde
  **Herramientas → Acceso desde el CEAD**.
* Rol del CEAD → rol del panel `promotor` de `caaguazu-portal`: quien cursa
  entra como Alumno, quien enseña como Profesor. Un rol que no caiga en
  el mapa se rechaza (no se inventa un permiso), pero queda registrado y se
  habilita de un clic desde esa misma pantalla.
* Entran al panel `promotor` que ya existe — no a uno aparte.

**1.1.1** — los nombres de rol que se muestran acá (Alumno, Profesor) se
sincronizan con el rename de `caaguazu-portal` 3.9.0. Sólo texto: la clave
interna del rol y el mapa de la sección de abajo no cambiaron.

== El mapa de roles (1.1.0) ==

El CEAD es un WordPress y manda **roles de WordPress**; el portal usa su propio
sistema de cuentas, con roles `promotur_*`. Los dos lados hablan de lo mismo con
vocabularios distintos, y hasta 1.0.0 el puente entre ambos era una constante de
dos entradas: `alumno_turismo` y `docente_turismo`. Cualquier otra cosa —
`alumno`, `cead_alumno`, `estudiante`, `Docente`, `subscriber` — se rechazaba
con «Tu rol no está habilitado», y **el registro de intentos no guardaba cuál
había sido el rol**: `CEADSSO_Log::record()` esperaba la clave `rol_cead` y los
claims traían `rol`, así que la columna quedaba en NULL siempre y la pantalla
mostraba «—» justo donde había que mirar. Un rechazo por rol no se podía
diagnosticar.

Desde 1.1.0:

* Los nombres se comparan **normalizados**: sin acentos, sin mayúsculas, sin el
  prefijo del colegio y sin el sufijo del curso. `Cead_Docente_Turismo`,
  `docente` y `DOCENTE` son el mismo rol.
* El mapa base cubre las formas que un WordPress suele mandar, y es **editable**
  desde Herramientas → Acceso desde el CEAD. Los roles administrativos del
  colegio (`administrator`, `editor`) **no** están en él a propósito: ser
  administrador del CEAD no es ser promotor turístico de Caaguazú.
* El rol crudo **se registra**, se ve en el log y se habilita de un clic.
* wp-admin avisa cuando llegó un rol que no supo traducir.

La normalización tiene sus casos comprobados en `tools/verificar-logica.php`.

== Comprobar la integración ==

**Herramientas → Acceso desde el CEAD** muestra el estado de las cinco piezas
que tienen que estar bien a la vez, con qué hacer en cada caso:

1. Los dos plugins de los que depende, activos.
2. Las dos constantes de `wp-config.php` (y si el secreto es corto, o la URL no
   es https).
3. La regla de reescritura de `/acceso-cead` realmente guardada.
4. El panel `promotor` registrado en el sistema de cuentas — la comprobación que
   más importa y la que menos se ve: si el panel no está registrado en el
   momento del canje, el grant se guarda con las capabilities vacías y la
   persona entra «con permiso» para recibir un 403 en la primera pantalla.
5. Que cada destino del mapa de roles exista de verdad en el panel.

Y un botón que **prueba el endpoint del CEAD**: manda un código deliberadamente
inválido para ver si del otro lado hay alguien atendiendo. No consume el acceso
de nadie. Distingue tres cosas que desde afuera se ven igual: que el endpoint no
exista todavía (404 `rest_no_route`, o sea que la mitad emisora no está
instalada), que exista y rechace nuestra firma (el secreto no coincide, o los
relojes están desincronizados), y que exista y funcione.

== Instalación ==

Todo esto es **en caaguazu.net** (ver el cuadro de arriba: en el sitio del CEAD
no se instala nada de este plugin).

1. Requiere `caaguazu-cuentas` y `caaguazu-portal` activos en ese mismo sitio.
2. Subir `caaguazu-sso-cead` a `/wp-content/plugins/` de caaguazu.net y activar.
3. Definir en el `wp-config.php` de caaguazu.net (nunca como opción editable
   desde la base), antes de la línea `/* That's all, stop editing! */`:

   ```php
   define( 'CEAD_TUR_SSO_SECRET', '…64 hex; generar con: openssl rand -hex 32…' );
   define( 'CEAD_TUR_SSO_URL', 'https://<sitio-del-cead>/wp-json/cead-sso/v1/redeem' );
   ```

   El **secreto va idéntico en los dos sitios** (un lado firma, el otro
   verifica). La **URL es direccional**: acá se guarda la del CEAD; el CEAD, a
   su vez, guarda `https://caaguazu.net/acceso-cead` para armar su botón.
   `CEAD_TUR_SSO_URL` no se define en el sitio del CEAD — ese sitio *sirve* ese
   endpoint, no se lo llama a sí mismo.

4. Ir a **Ajustes → Enlaces permanentes** y guardar (activa la rewrite rule
   de `/acceso-cead`).
5. El botón "Ir al portal" del panel del CEAD apunta a
   `https://caaguazu.net/acceso-cead?code=<código>`.

== Auditoría ==

Cada intento de canje (éxito, rechazo o error) queda en
**Herramientas → Acceso desde el CEAD**, con el motivo cuando se rechazó.

== Seguridad ==

* El navegador nunca ve datos de la persona — solo un código sin significado
  en la URL. El intercambio real (código → email/nombre/rol) es servidor a
  servidor, firmado con HMAC-SHA256 y una ventana de 5 minutos contra
  desfase de reloj.
* La ruta pública (`/acceso-cead`) no acepta ningún destino de redirección
  desde la URL — siempre termina en el panel del Portal. Nada de `next=` ni
  `redirect_to=`: eso sería un open-redirect justo después de abrir sesión.
* Sesión de SSO sin "recordarme" (dura lo que dura cualquier sesión del
  sistema de cuentas, no más) — el acceso vive del vínculo con el CEAD.

== Changelog ==

= 1.1.3 =
* **Quien dirige la carrera de turismo del CEAD ahora entra como Profesor.**
  `direccion`, `coordinacion`, `director` y `directora` se suman al mapa base:
  dirigir este programa es estar del lado de quien enseña. `administrator` y
  `editor` siguen afuera a propósito — ser administrador del CEAD no es ser
  promotor turístico de Caaguazú.
* **Un rol escrito como frase perdía el sufijo del curso y quedaba a medias.**
  «Dirección de Turismo» se normalizaba a `direccion_de`, con la preposición
  colgando, que no coincide con ningún rol. El nombre visible de un rol se
  escribe así, y quien lo escribe del otro lado no tiene por qué saber cómo lo
  partimos acá.

= 1.1.2 =
* **Un rol que llegaba con espacios rebotaba, y no había forma de verlo.** El
  canje limpiaba el rol con `sanitize_key()` antes de que lo viera el
  normalizador, y `sanitize_key()` no convierte los separadores: los borra.
  «Docente Turismo» llegaba como `docenteturismo`, que ya no pierde el sufijo
  del curso ni coincide con nada, y la persona veía «Tu rol en el CEAD todavía
  no está habilitado para entrar al portal». El normalizador —que maneja
  espacios, guiones, acentos y mayúsculas— nunca llegaba a hacer su trabajo.
  Ahora el rol viaja entero y se normaliza donde corresponde.
* **La pantalla que arregla un rechazo decía que no había nada que arreglar.**
  La lista de «roles que llegaron y se rechazaron» se cachea diez minutos, así
  que quien rebotaba avisaba, el admin abría Herramientas → Acceso desde el
  CEAD y veía «Ninguno». Un rechazo nuevo ahora invalida ese caché.
* `tools/verificar-logica.php` comprueba las dos formas del nombre visible
  («Docente Turismo», «Alumno Turismo») y que nadie vuelva a mutilar el rol
  antes de normalizarlo.
