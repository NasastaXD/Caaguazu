// El asistente (CEADI): preguntas sobre Caaguazú, contestadas con lo que está
// publicado en esta guía y con las fuentes que usó. Sin cuenta: la API lo
// sirve abierto, con un tope de preguntas por minuto y por día por conexión.
//
// La pestaña sólo aparece si GET /asistente dice que está disponible (ver
// app.js). La charla dura lo que dura la visita: se guarda en sessionStorage,
// así tocar una fuente y volver no la borra, y cerrar la pestaña sí.

import { Api, ErrorAsistente } from "../api.js";
import { t, tf } from "../idioma.js";
import { escapar, Icono, cabeceraPantalla, vacio } from "../piezas.js";

const CLAVE = "czu.charla";
const MAX = 1000; // lo que acepta la API por mensaje

function leerCharla() {
  try {
    const c = JSON.parse(sessionStorage.getItem(CLAVE) || "null");
    if (c && Array.isArray(c.mensajes)) return c;
  } catch {
    /* sin sessionStorage: arranca vacía */
  }
  return { conversacion: "", mensajes: [] };
}

function guardarCharla(c) {
  try {
    sessionStorage.setItem(CLAVE, JSON.stringify(c));
  } catch {
    /* no persiste entre pantallas, nada más */
  }
}

/** A dónde lleva cada fuente. Un recorrido vive en la app, no acá. */
function enlaceFuente(f) {
  if (f.tipo === "ficha") return `#/ficha/${f.id}`;
  if (f.tipo === "articulo") return `#/articulo/${f.id}`;
  return "#/app";
}

function globo(m) {
  if (m.rol === "yo") return `<div class="globo globo--yo">${escapar(m.texto)}</div>`;
  if (m.rol === "error") return `<div class="globo globo--error" role="alert">${escapar(m.texto)}</div>`;
  const fuentes = (m.fuentes ?? []).filter((f) => f && f.id);
  return `<div class="globo globo--ia">${escapar(m.texto)}${
    fuentes.length
      ? `<div class="globo__fuentes" aria-label="${escapar(t("ficha.fuentes"))}">${fuentes
          .map((f) => `<a class="chip" href="${enlaceFuente(f)}">${escapar(f.titulo || t("ficha.fuentes"))}</a>`)
          .join("")}</div>`
      : ""
  }</div>`;
}

export async function render(el, { hayAsistente }) {
  if (!hayAsistente) {
    el.innerHTML = `${cabeceraPantalla(t("web.asistente"), { volver: true })}${vacio(t("web.ia.apagado"), `<a class="boton boton--fantasma" href="#/inicio">${escapar(t("nav.principal"))}</a>`)}`;
    return;
  }

  document.body.classList.add("con-redactar");
  const charla = leerCharla();

  el.innerHTML = `
    ${cabeceraPantalla(t("web.asistente"), {
      derecha: `<button type="button" class="boton-icono" id="nueva" aria-label="${escapar(t("web.ia.nueva"))}" ${charla.mensajes.length ? "" : "hidden"}>${Icono.nueva}</button>`,
    })}
    <div id="presentacion" ${charla.mensajes.length ? "hidden" : ""}>
      <div class="ilustracion">${Icono.asistente}</div>
      <div class="chips" style="flex-direction:column;align-items:flex-start">
        ${["web.ia.ejemplo1", "web.ia.ejemplo2", "web.ia.ejemplo3"].map((k) => `<button type="button" class="chip" data-ejemplo>${escapar(t(k))}</button>`).join("")}
      </div>
      <p class="debil" style="margin-top:22px">${escapar(t("web.ia.cuidado"))}</p>
    </div>
    <div class="chat" id="chat" aria-live="polite">${charla.mensajes.map(globo).join("")}</div>
    <form class="redactar" id="redactar">
      <input id="pregunta" type="text" maxlength="${MAX}" autocomplete="off" enterkeyhint="send"
        placeholder="${escapar(t("web.ia.placeholder"))}" aria-label="${escapar(t("web.ia.placeholder"))}">
      <button type="submit" class="boton-icono" aria-label="${escapar(t("web.ia.enviar"))}" disabled>${Icono.enviar}</button>
    </form>
  `;

  const chat = el.querySelector("#chat");
  const form = el.querySelector("#redactar");
  const campo = el.querySelector("#pregunta");
  const enviar = form.querySelector("button");
  let esperando = false;

  const alFinal = () => window.scrollTo({ top: document.body.scrollHeight, behavior: matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth" });
  if (charla.mensajes.length) requestAnimationFrame(() => window.scrollTo(0, document.body.scrollHeight));

  campo.addEventListener("input", () => {
    enviar.disabled = esperando || !campo.value.trim();
  });

  el.querySelectorAll("[data-ejemplo]").forEach((b) =>
    b.addEventListener("click", () => preguntar(b.textContent.trim())),
  );

  el.querySelector("#nueva").addEventListener("click", () => {
    if (esperando) return;
    charla.conversacion = "";
    charla.mensajes = [];
    guardarCharla(charla);
    chat.innerHTML = "";
    el.querySelector("#presentacion").hidden = false;
    el.querySelector("#nueva").hidden = true;
    campo.focus();
  });

  form.addEventListener("submit", (ev) => {
    ev.preventDefault();
    preguntar(campo.value.trim());
  });

  async function preguntar(texto) {
    if (!texto || esperando) return;
    esperando = true;
    campo.value = "";
    enviar.disabled = true;
    el.querySelector("#presentacion").hidden = true;
    el.querySelector("#nueva").hidden = false;

    const mio = { rol: "yo", texto };
    charla.mensajes.push(mio);
    guardarCharla(charla);
    chat.insertAdjacentHTML("beforeend", globo(mio));
    chat.insertAdjacentHTML("beforeend", `<div class="globo globo--ia escribiendo" id="escribiendo" aria-label="${escapar(t("estado.cargando"))}"><span></span><span></span><span></span></div>`);
    alFinal();

    let respuesta;
    try {
      const r = await Api.preguntar({ mensaje: texto.slice(0, MAX), conversacion: charla.conversacion });
      charla.conversacion = r.conversacion || charla.conversacion;
      respuesta = { rol: "ia", texto: r.respuesta || "", fuentes: r.fuentes || [] };
    } catch (e) {
      respuesta = { rol: "error", texto: explicar(e) };
    }

    // La persona pudo haberse ido a otra pantalla mientras esperaba: la
    // respuesta se guarda igual, para encontrarla al volver.
    charla.mensajes.push(respuesta);
    guardarCharla(charla);
    esperando = false;
    if (!document.body.contains(chat)) return;

    el.querySelector("#escribiendo")?.remove();
    chat.insertAdjacentHTML("beforeend", globo(respuesta));
    enviar.disabled = !campo.value.trim();
    alFinal();
  }
}

function explicar(e) {
  if (!(e instanceof ErrorAsistente)) return t("web.ia.error");
  if (e.estado === 429) return e.esperaSeg ? tf("web.ia.espera", e.esperaSeg) : t("web.ia.esperaSinTiempo");
  if (e.estado === 503) return t("web.ia.apagado");
  if (e.estado === 0) return t("web.ia.sinRed");
  return t("web.ia.error");
}
