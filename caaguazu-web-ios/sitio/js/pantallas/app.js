// «Recorridos» vive en la app: armar una ruta, guardarla y seguirla con el
// mapa sin conexión son cosas de la app, no de una página. Esta pantalla lo
// explica y lleva a la tienda que le toca a cada teléfono.

import { t } from "../idioma.js";
import { ajuste, plataforma } from "../config.js";
import { escapar, Icono, cabeceraPantalla, botonAjustes } from "../piezas.js";

const TIENDAS = {
  android: { etiqueta: "Google Play", chico: "Disponible en", icono: Icono.android, orden: 0 },
  ios: { etiqueta: "App Store", chico: "Descargar en", icono: Icono.manzana, orden: 1 },
};

export async function render(el) {
  const enlaces = ajuste().tiendas ?? {};
  const soDelTelefono = plataforma();

  // El teléfono que entra ve primero su tienda; el de escritorio ve las dos.
  // La primera es siempre el botón fuerte —oscuro—, las demás van de costado.
  const mostrar = Object.keys(TIENDAS)
    .filter((clave) => enlaces[clave])
    .sort((a, b) => (a === soDelTelefono ? -1 : b === soDelTelefono ? 1 : TIENDAS[a].orden - TIENDAS[b].orden));

  const faltaLaDelTelefono = (soDelTelefono === "ios" || soDelTelefono === "android") && !enlaces[soDelTelefono];

  el.innerHTML = `
    ${cabeceraPantalla(t("nav.recorridos"), { volver: true, derecha: botonAjustes() })}

    <div class="ilustracion">${Icono.ruta}</div>
    <h2 class="subtitulo" style="font-size:1.25rem">${escapar(t("web.app.titulo"))}</h2>
    <p class="apagado" style="margin:8px 0 18px">${escapar(t("web.app.texto"))}</p>

    <ul class="lista" style="list-style:none;padding:0;margin:0 0 26px">
      <li class="fila" style="min-height:0"><span class="fila__cuerpo">${escapar(t("web.app.punto1"))}</span></li>
      <li class="fila" style="min-height:0"><span class="fila__cuerpo">${escapar(t("web.app.punto2"))}</span></li>
      <li class="fila" style="min-height:0"><span class="fila__cuerpo">${escapar(t("web.app.punto3"))}</span></li>
    </ul>

    ${mostrar.length ? `
      <div class="tiendas">
        ${mostrar.map((clave) => `
          <a class="tienda ${clave === mostrar[0] ? "" : "tienda--otra"}" href="${escapar(enlaces[clave])}" target="_blank" rel="noopener">
            ${TIENDAS[clave].icono}
            <span>
              <span class="tienda__chico">${escapar(TIENDAS[clave].chico)}</span>
              <span class="tienda__grande">${escapar(TIENDAS[clave].etiqueta)}</span>
            </span>
          </a>`).join("")}
      </div>` : `
      <div class="vacio"><p>${escapar(t("web.app.sinTiendas"))}</p></div>`}

    ${faltaLaDelTelefono ? `<p class="debil" style="margin-top:14px">${escapar(t("web.app.faltaTuTienda"))}</p>` : ""}
  `;
}
