// Claro, oscuro o «como el teléfono». El primer pintado lo resuelve el script
// en línea de index.html —para que no destelle—; esto lo mantiene después:
// cuando la persona elige en Ajustes, y cuando el teléfono cambia solo de día
// a noche con la opción «como el teléfono».

const CLAVE = "czu.tema";
const OPCIONES = ["sistema", "claro", "oscuro"];
const consulta = window.matchMedia ? window.matchMedia("(prefers-color-scheme: dark)") : null;

export function temaElegido() {
  try {
    const v = localStorage.getItem(CLAVE);
    return OPCIONES.includes(v) ? v : "sistema";
  } catch {
    return "sistema";
  }
}

export function elegirTema(opcion) {
  try {
    localStorage.setItem(CLAVE, opcion);
  } catch {
    /* sin storage dura hasta recargar, que alcanza */
  }
  aplicarTema();
}

export function aplicarTema() {
  const elegido = temaElegido();
  const oscuro = elegido === "oscuro" || (elegido === "sistema" && Boolean(consulta?.matches));
  document.documentElement.setAttribute("data-theme", oscuro ? "dark" : "light");

  // La barra de estado del teléfono toma el color del lienzo, no uno fijo.
  const fondo = getComputedStyle(document.documentElement).getPropertyValue("--lienzo").trim();
  const meta = document.getElementById("color-tema");
  if (meta && fondo) meta.setAttribute("content", fondo);
}

consulta?.addEventListener?.("change", () => {
  if (temaElegido() === "sistema") aplicarTema();
});
