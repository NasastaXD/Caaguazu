# Despliegue de caaguazu-web

Para: quien tenga acceso a hosting/dominio de `caaguazu.net` (el "agente de
caaguazu" u otra persona con ese acceso). Esta sesión construyó el código
pero no tiene control de hosting ni de DNS, así que estos son los pasos para
alguien que sí lo tenga.

## Qué es esto

Es un sitio estático — HTML, CSS y JS puro, sin build ni dependencias de
servidor — que espeja lo que muestra la app Android mientras no exista una
app nativa de iOS. Todo el contenido lo trae en vivo desde
`https://caaguazu.net/wp-json/czu-app/v1/`, que ya tiene CORS abierto (se
verificó con `curl -I` antes de construir esto: `Access-Control-Allow-Origin`
refleja cualquier origen), así que no hace falta ningún proxy ni cambio del
lado del servidor para que funcione.

## Opción recomendada: un subdominio

`turismo.caaguazu.net` o similar, apuntando a un hosting estático:

1. **GitHub Pages** (gratis, sin infraestructura nueva): en la configuración
   del repo, Settings → Pages → Deploy from a branch, eligiendo esta rama y
   la carpeta `caaguazu-web/`. GitHub sirve `https://<usuario>.github.io/...`
   y desde ahí se puede apuntar un CNAME del subdominio.
2. **Como carpeta del mismo hosting de WordPress**: subir el contenido de
   `caaguazu-web/` tal cual a `caaguazu.net/turismo-app/` (o donde se
   decida) por FTP/SFTP o el panel del hosting. No necesita PHP ni base de
   datos — son archivos estáticos que cualquier servidor web sirve.
3. **Netlify/Vercel** (gratis, deploy automático desde GitHub): conectar el
   repo, apuntar el build a la carpeta `caaguazu-web/` sin comando de build
   (root directory = `caaguazu-web`, build command = vacío, publish
   directory = `.`).

Cualquiera de las tres sirve; la única diferencia es quién controla el
dominio final.

## Qué NO hace falta tocar

- **La API** (`caaguazu-app-api`): no necesita ningún cambio. Ya sirve todo
  lo que este sitio consume, con CORS ya abierto.
- **El tema** (`caaguazu-theme`): tampoco. Este sitio es independiente del
  tema de WordPress — no vive dentro de él ni depende de sus plantillas.

## Verificación post-deploy

1. Abrir el sitio en un iPhone (o simularlo en Chrome DevTools con el user
   agent de Safari iOS) y confirmar que "Agregar a inicio" ofrece el ícono
   correcto y abre en modo standalone (sin la barra de Safari).
2. Confirmar que las fotos, categorías y fichas cargan — si no, lo primero
   a revisar es si el nuevo dominio quedó bloqueado por CORS del lado del
   servidor (no debería, pero un cambio de configuración ahí podría
   romperlo).
3. Probar el cambio de idioma en Perfil y confirmar que persiste al
   recargar.

## Cuándo se da de baja

Cuando exista una app nativa de iOS. En ese momento este sitio (y su
despliegue) se puede desarmar sin dejar nada pendiente: no tiene base de
datos propia, no tiene usuarios registrados, no tiene nada que migrar — todo
lo que guarda vive en el `localStorage` de cada navegador (favoritos,
recorrido propio, idioma elegido), no en ningún servidor.
