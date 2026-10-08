// Compartir un lugar, articulo o recorrido: enlace directo con hash (anda
// tal cual porque el router lee location.hash al cargar) y, cuando no hay
// Web Share API (la mayoria de los navegadores de escritorio), una hoja con
// el enlace para copiar y un QR generado en el propio telefono/navegador de
// quien comparte, sin mandar la URL a ningun servicio de terceros.

import { t } from "./idioma.js";
import { escapar, Icono } from "./piezas.js";
import { crearQrSvg } from "./qr.js";

export async function compartir({ titulo, ruta }) {
  const url = new URL(`#/${ruta}`, location.href).href;

  if (navigator.share) {
    try {
      await navigator.share({ title: titulo, url });
      return;
    } catch {
      // Cancelado por la persona o sin permiso: cae a la hoja de abajo.
    }
  }

  abrirHoja(url, titulo);
}

function abrirHoja(url, titulo) {
  let zona = document.getElementById("zona-compartir");
  if (!zona) {
    zona = document.createElement("div");
    zona.id = "zona-compartir";
    document.body.appendChild(zona);
  }

  let qrHtml = "";
  try {
    qrHtml = crearQrSvg(url, 4);
  } catch {
    qrHtml = "";
  }

  zona.innerHTML = `
    <div class="fondo-hoja" id="fondo-compartir"></div>
    <div class="hoja-inferior">
      <div class="manija"></div>
      <h2 class="titulo-seccion">${escapar(t("diag.compartir"))}</h2>
      ${titulo ? `<div class="descripcion" style="margin-bottom:14px">${escapar(titulo)}</div>` : ""}

      <div style="display:flex;gap:10px;margin-bottom:var(--entre-secciones)">
        <div class="buscador" style="flex:1;margin-bottom:0">
          <input readonly value="${escapar(url)}" id="campo-enlace" onclick="this.select()">
        </div>
        <button class="boton-filtro" id="boton-copiar" aria-label="copiar">${Icono.copiar}</button>
      </div>

      <div style="display:flex;justify-content:center;margin-bottom:var(--entre-secciones)">
        <div id="qr-compartir" style="background:#fff;padding:14px;border-radius:var(--radio-lista);box-shadow:var(--sombra-tarjeta)">
          ${qrHtml}
        </div>
      </div>
    </div>
  `;

  const cerrar = () => (zona.innerHTML = "");
  zona.querySelector("#fondo-compartir").addEventListener("click", cerrar);

  const boton = zona.querySelector("#boton-copiar");
  boton.addEventListener("click", async () => {
    const copiado = await copiarAlPortapapeles(url);
    if (copiado) {
      boton.innerHTML = Icono.tilde;
      setTimeout(() => (boton.innerHTML = Icono.copiar), 1600);
    }
  });
}

async function copiarAlPortapapeles(texto) {
  try {
    await navigator.clipboard.writeText(texto);
    return true;
  } catch {
    // Sin permiso de portapapeles (http sin TLS, navegador viejo): el campo
    // de arriba ya deja el enlace seleccionable a mano.
    const campo = document.getElementById("campo-enlace");
    campo?.select();
    return false;
  }
}
