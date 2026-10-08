// Compartir un lugar, un artículo o la web entera: el enlace directo con hash
// (anda tal cual porque el router lee location.hash al cargar). Con la hoja
// de compartir del teléfono cuando existe —Safari en iPhone la tiene— y, si
// no, una hoja propia con el enlace para copiar y un QR generado acá mismo,
// sin mandar la URL a ningún servicio de terceros.

import { t } from "./idioma.js";
import { escapar, Icono, abrirHoja } from "./piezas.js";
import { crearQrSvg } from "./qr.js";

export async function compartir({ titulo, ruta }) {
  const url = new URL(`#/${ruta}`, location.href).href;

  if (navigator.share) {
    try {
      await navigator.share({ title: titulo, url });
      return;
    } catch (e) {
      // Cancelar no es un error para mostrar: la persona cerró la hoja.
      if (e?.name === "AbortError") return;
    }
  }

  let qr = "";
  try {
    qr = crearQrSvg(url, 4);
  } catch {
    qr = "";
  }

  const hoja = abrirHoja(
    `${titulo ? `<p class="apagado" style="margin:-6px 0 14px">${escapar(titulo)}</p>` : ""}
    <div class="enlace-copiar">
      <input readonly value="${escapar(url)}" aria-label="${escapar(t("accion.copiarEnlace"))}">
      <button type="button" class="boton-icono" data-copiar aria-label="${escapar(t("accion.copiarEnlace"))}">${Icono.copiar}</button>
    </div>
    ${qr ? `<div class="qr"><div class="qr__papel">${qr}</div></div>` : ""}`,
    { titulo: t("diag.compartir") },
  );

  const campo = hoja.querySelector("input");
  campo.addEventListener("focus", () => campo.select());
  const boton = hoja.querySelector("[data-copiar]");
  boton.addEventListener("click", async () => {
    try {
      await navigator.clipboard.writeText(url);
      boton.innerHTML = Icono.tilde;
      boton.setAttribute("aria-label", t("accion.enlaceCopiado"));
      setTimeout(() => {
        boton.innerHTML = Icono.copiar;
        boton.setAttribute("aria-label", t("accion.copiarEnlace"));
      }, 1600);
    } catch {
      // Sin permiso de portapapeles: el campo queda seleccionado para copiar a mano.
      campo.focus();
      campo.select();
    }
  });
}
