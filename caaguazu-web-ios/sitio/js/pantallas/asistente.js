// El asistente: una charla que responde dudas con lo publicado en el panel.
// Calcado de ui/asistente/Asistente.kt de Turismo-app-czu.
//
// La inteligencia vive del lado del servidor (POST /asistente): acá no hay
// claves, ni modelo, ni historial que se pueda adulterar. El espejo pregunta,
// muestra y enlaza. Cada respuesta trae debajo las piezas de donde salió, y
// eso es lo que lo separa de un chat cualquiera: quien pregunta puede ver con
// sus ojos de dónde salió un horario antes de ir.
//
// app.js carga este módulo con import() dinámico, nunca con un import
// estático: si este archivo falla —un caché desparejo, un error de
// sintaxis—, el resto del espejo sigue andando y simplemente no hay botón.

import { Api } from "../api.js";
import { t } from "../idioma.js";
import { escapar, Icono } from "../piezas.js";

// El estado vive acá, a nivel de módulo, igual que el filtro de buscar.js:
// abrir una fuente y volver, o cerrar la charla y abrirla de nuevo, no puede
// borrar lo que se venía hablando. Se pierde al recargar la página —cambiar
// el idioma recarga—, y está bien: la charla dura lo que dura la visita.

/** Lo dice el panel. Sin esto en true, el botón no se dibuja. */
let disponible = false;
/** Los turnos de la charla: { deLaPersona, texto, fuentes }. */
let mensajes = [];
/** Lo escrito y todavía no mandado. Sobrevive a abrir una fuente. */
let borrador = "";
let esperando = false;
/** Si la última pregunta se quedó sin respuesta. */
let fallo = false;
/** Lo que devolvió el servidor: con esto sigue la misma charla. */
let conversacion = null;
/** La pregunta que falló, para que reintentar no obligue a escribirla de nuevo. */
let pendiente = null;
/** Dónde estaba la lista cuando se abrió una fuente, para volver ahí mismo. */
let lugar = null;

const escuchas = new Set();

// Lista blanca de lo que puede ser una fuente, con la ruta del espejo que la
// abre. Lo que no sea uno de estos tres no arma ningún enlace.
const RUTA_DE = new Map([
  ["ficha", "ficha"],
  ["articulo", "articulo"],
  ["recorrido", "recorrido"],
]);

/** Cuatro líneas de 22: más que eso, el campo pasa a desplazarse por dentro. */
const ALTO_MAXIMO_CAMPO = 88;

// El teclado de iOS no achica la página: corre el viewport visual por encima
// de ella, y un `position: fixed; bottom: 0` queda tapado o salta. `100dvh`
// no lo descuenta y Safari no tiene `interactive-widget=resizes-content`, así
// que la charla —un contenedor fijo a pantalla completa— se ajusta a mano al
// alto y al corrimiento de lo que de verdad se ve. Se engancha una sola vez:
// el módulo se evalúa una vez por página.
window.visualViewport?.addEventListener("resize", ajustarAlTeclado);
window.visualViewport?.addEventListener("scroll", ajustarAlTeclado);

export function estaDisponible() {
  return disponible;
}

export function alCambiarDisponible(fn) {
  escuchas.add(fn);
}

function marcarDisponible(valor) {
  if (valor === disponible) return;
  disponible = valor;
  for (const fn of escuchas) {
    try {
      fn(valor);
    } catch {
      /* un oyente roto no deja sin aviso a los demás */
    }
  }
}

/**
 * ¿Hay asistente? Lo decide el panel. Un `disponible: true` lo enciende y un
 * `disponible` distinto de true lo apaga. Un 404 —una API anterior a la 0.9.0—
 * también lo apaga. Un fallo de red o un 5xx no dice nada del asistente: se
 * queda lo que se sabía, igual que la app («sin señal vale lo último que se
 * supo»). Si no, un bache de señal al mandar una pregunta haría desaparecer el
 * botón por el resto de la visita.
 */
export async function consultarDisponible({ fresco = false } = {}) {
  let ahora = disponible;
  try {
    const r = await Api.asistente({ fresco });
    ahora = r?.disponible === true;
  } catch (e) {
    if (e?.status === 404) ahora = false;
  }
  marcarDisponible(ahora);
  return ahora;
}

/**
 * La pantalla de la charla. Sincrónica de punta a punta, sin un solo await:
 * el foco del campo tiene que llegar dentro del mismo toque que abrió la
 * charla, o iOS no sube el teclado.
 *
 * Abre sin la barra inferior —la saca el router— porque la entrada vive abajo
 * y el teclado sube hasta ella: con la barra en el medio, el teclado taparía
 * la barra y la entrada quedaría flotando sobre un hueco.
 */
export function render(contenedor) {
  contenedor.innerHTML = `
    <section id="charla" class="pantalla-asistente">
      <header class="charla-cabecera">
        <h1 class="titulo-pantalla">${escapar(t("asistente.titulo"))}</h1>
        <div class="charla-acciones" id="charla-acciones"></div>
      </header>
      <div class="charla-lista" id="charla-lista"></div>
      <p class="solo-lectores" id="charla-anuncio" aria-live="polite"></p>
      <div class="charla-entrada">
        <div class="charla-campo"><textarea id="charla-campo" rows="1" maxlength="1000" placeholder="${escapar(t("asistente.campo"))}"></textarea></div>
        <button type="button" class="boton-enviar" id="charla-enviar" aria-label="${escapar(t("asistente.enviar"))}">${Icono.enviar ?? ""}</button>
      </div>
    </section>`;

  const raiz = contenedor.querySelector("#charla");
  const campo = raiz.querySelector("#charla-campo");
  const boton = raiz.querySelector("#charla-enviar");
  const lista = raiz.querySelector("#charla-lista");

  campo.value = borrador;
  ajustarAlto(campo);

  // Con la charla vacía lo único que hay para hacer es escribir: el teclado
  // sube solo y nadie tiene que buscar dónde tocar. Con mensajes no se
  // enfoca: al volver de una fuente, el teclado taparía la respuesta.
  if (!mensajes.length) campo.focus({ preventScroll: true });

  // Enter mete un salto de línea, como en la app: se manda con el botón. Una
  // pregunta larga tiene que poder releerse antes de mandarla.
  campo.addEventListener("input", () => {
    borrador = campo.value;
    ajustarAlto(campo);
    pintarEnviar();
  });

  // Que tocar «mandar» no le saque el foco al campo: así el teclado sigue
  // arriba para la próxima pregunta, como en la app.
  boton.addEventListener("mousedown", (ev) => ev.preventDefault());
  boton.addEventListener("click", () => preguntar(campo.value));

  // Un solo oyente para todo lo que se repinta: las acciones y la lista se
  // reescriben enteras, y así no hay que volver a enganchar nada.
  raiz.addEventListener("click", (ev) => {
    if (ev.target.closest("#charla-volver")) volver();
    else if (ev.target.closest("#charla-nueva")) nuevaCharla();
    else if (ev.target.closest("#charla-reintentar")) reintentar();
    else if (ev.target.closest(".charla-fuentes a")) {
      ev.preventDefault();
      lugar = { arriba: lista.scrollTop, turnos: mensajes.length, esperando };
      // Que volver sepa de dónde viene va en el historial y no en la URL: así
      // un enlace compartido no arrastra la charla. pushState no dispara
      // hashchange, así que el router se entera a mano, igual que con el botón
      // del asistente en app.js.
      const enlace = ev.target.closest(".charla-fuentes a");
      history.pushState({ desdeAsistente: true }, "", enlace.getAttribute("href"));
      window.dispatchEvent(new HashChangeEvent("hashchange"));
    }
  });

  repintar();
  ajustarAlTeclado();

  // Al volver de una fuente, la lista queda donde estaba: quien bajó hasta la
  // tercera fuente de una respuesta larga quiere seguir ahí, no releerla
  // desde arriba. Si mientras tanto llegó la respuesta, o falló, se muestra
  // eso.
  if (lugar && lugar.turnos === mensajes.length && lugar.esperando === esperando) {
    lista.scrollTop = lugar.arriba;
  } else {
    subirUltimaPregunta(false);
  }
  lugar = null;
}

/**
 * Reescribe las acciones de la cabecera y la lista. Nunca el campo: si se
 * rehace el textarea, iOS le saca el foco y cierra el teclado.
 */
function repintar() {
  const acciones = document.getElementById("charla-acciones");
  const lista = document.getElementById("charla-lista");
  if (!acciones || !lista) return;

  // Mientras espera no se puede empezar de cero: la respuesta que está en
  // camino caería en la charla nueva.
  const nueva = mensajes.length && !esperando
    ? `<button type="button" class="boton-icono" id="charla-nueva" aria-label="${escapar(t("asistente.nueva"))}" title="${escapar(t("asistente.nueva"))}">${Icono.nueva ?? ""}</button>`
    : "";
  acciones.innerHTML = `${nueva}<button type="button" class="boton-icono" id="charla-volver" aria-label="${escapar(t("accion.volver"))}">${Icono.volver}</button>`;

  lista.innerHTML = mensajes.map(pintarMensaje).join("") + pintarEstado();
  // Lo que se anuncia vive en una región fija, fuera de la lista que se
  // repinta: VoiceOver no siempre anuncia una región viva recién insertada.
  const anuncio = document.getElementById("charla-anuncio");
  if (anuncio) anuncio.textContent = esperando ? t("asistente.pensando") : fallo ? t("estado.error") : "";
  pintarEnviar();
}

// El texto va pegado a sus etiquetas: con `white-space: pre-wrap`, cualquier
// salto o sangría del template se vería en pantalla.
function pintarMensaje(m) {
  if (m.deLaPersona) return `<div class="charla-pregunta">${escapar(m.texto)}</div>`;

  // La respuesta es la salida de un modelo: va siempre escapada, como texto,
  // aunque el servidor diga que es texto plano. Los saltos de línea los
  // respeta el CSS, sin armar HTML a mano.
  const fuentes = m.fuentes.length
    ? `<div class="charla-fuentes"><div class="charla-rotulo">${escapar(t("ficha.fuentes"))}</div>${m.fuentes.map(pildoraFuente).join("")}</div>`
    : "";
  return `<div class="charla-respuesta"><div class="charla-texto">${escapar(m.texto)}</div>${fuentes}</div>`;
}

/**
 * Una píldora por pieza citada. Abre la misma pantalla de detalle que el
 * resto del espejo. Que el artículo o el recorrido sepan que volver es volver
 * a la charla lo dice el estado del historial, no la URL (ver el clic en la
 * raíz de la charla).
 */
function pildoraFuente(f) {
  return `<a class="pildora-suave" href="#/${RUTA_DE.get(f.tipo)}/${f.id}">${iconoDe(f)}<span>${escapar(f.titulo)}</span></a>`;
}

/** El ícono dice qué es la pieza antes de leer su nombre. */
function iconoDe(f) {
  if (f.tipo === "articulo") return Icono.articulo;
  if (f.tipo === "recorrido") return Icono.recorrido;
  if (f.tipo_item === "evento") return Icono.calendario;
  return Icono.pin;
}

function pintarEstado() {
  if (esperando) {
    return `<div class="descripcion charla-estado">${escapar(t("asistente.pensando"))}</div>`;
  }
  if (fallo) {
    return `<div class="charla-fallo"><div class="charla-texto">${escapar(t("estado.error"))}</div><button type="button" class="pildora-suave" id="charla-reintentar"><span>${escapar(t("estado.reintentar"))}</span></button></div>`;
  }
  return "";
}

/**
 * Mandar es la acción de la pantalla, así que va en el verde. Sin nada
 * escrito, o mientras se espera, queda en relleno de control: un verde que
 * no hace nada al tocarlo sería mentir. Sólo cambian la clase y el disabled.
 */
function pintarEnviar() {
  const campo = document.getElementById("charla-campo");
  const boton = document.getElementById("charla-enviar");
  if (!campo || !boton) return;
  const listo = Boolean(campo.value.trim()) && !esperando;
  boton.classList.toggle("listo", listo);
  boton.disabled = !listo;
}

/** El campo crece con lo escrito hasta cuatro líneas. */
function ajustarAlto(campo) {
  campo.style.height = "auto";
  campo.style.height = `${Math.min(campo.scrollHeight, ALTO_MAXIMO_CAMPO)}px`;
}

function preguntar(texto) {
  const limpio = String(texto ?? "").trim();
  if (!limpio || esperando) return;
  mensajes.push({ deLaPersona: true, texto: limpio, fuentes: [] });
  borrador = "";
  const campo = document.getElementById("charla-campo");
  if (campo) {
    campo.value = "";
    ajustarAlto(campo);
  }
  enviar(limpio);
}

async function enviar(texto) {
  esperando = true;
  fallo = false;
  pendiente = texto;
  repintar();
  subirUltimaPregunta(true);

  try {
    const r = await Api.preguntar(texto, conversacion);
    // Una respuesta sin texto es una respuesta rota, igual que en la app,
    // donde no pasaría la decodificación: se ofrece reintentar.
    if (typeof r?.respuesta !== "string") throw new Error("respuesta sin texto");
    conversacion = typeof r.conversacion === "string" && r.conversacion ? r.conversacion : null;
    mensajes.push({ deLaPersona: false, texto: r.respuesta, fuentes: validas(r.fuentes) });
    pendiente = null;
  } catch (e) {
    fallo = true;
    // Puede que lo hayan apagado desde el panel con la charla abierta. Si el
    // servidor lo dice, el botón se va sin volver a preguntar. Ante otro 5xx
    // se pregunta de nuevo salteando la copia del navegador, que seguiría
    // diciendo que sí hasta cinco minutos.
    if (e?.codigo === "asistente_apagado") marcarDisponible(false);
    else if (e?.status >= 500) consultarDisponible({ fresco: true });
  } finally {
    esperando = false;
    // Si la persona abrió una fuente mientras esperaba, la charla no está en
    // pantalla: no se escribe nada, y al volver se pinta con lo que haya.
    if (document.getElementById("charla")) {
      repintar();
      subirUltimaPregunta(true);
    }
  }
}

/**
 * Las fuentes se revisan una por una: una que venga rota se omite y las
 * demás siguen. El tipo pasa por la lista blanca y el id tiene que ser un
 * entero positivo antes de armar ningún enlace con ellos.
 */
function validas(fuentes) {
  if (!Array.isArray(fuentes)) return [];
  return fuentes
    .filter((f) =>
      RUTA_DE.has(f?.tipo) &&
      Number.isInteger(f.id) && f.id > 0 &&
      String(f.titulo ?? "").trim() !== "")
    .map((f) => ({ tipo: f.tipo, id: f.id, titulo: String(f.titulo), tipo_item: f.tipo_item ?? null }));
}

function reintentar() {
  if (pendiente && !esperando) enviar(pendiente);
}

/** Empezar de cero. El servidor arma otra conversación con la próxima pregunta. */
function nuevaCharla() {
  if (esperando) return;
  mensajes = [];
  conversacion = null;
  pendiente = null;
  fallo = false;
  borrador = "";
  const campo = document.getElementById("charla-campo");
  if (campo) {
    campo.value = "";
    ajustarAlto(campo);
  }
  repintar();
}

/**
 * Entrando por el botón de la barra, la charla es una entrada propia del
 * historial y volver es volver adonde se estaba. Por un enlace directo o
 * compartido no hay nada atrás que sea del espejo: se va al inicio sin dejar
 * la charla en el historial. Instalado en el inicio del iPhone no hay botón
 * atrás del navegador, así que todo depende de este.
 */
function volver() {
  if (history.state?.asistente) history.back();
  else location.replace("#/inicio");
}

/**
 * La última pregunta queda arriba, y debajo lo que vino: la respuesta se lee
 * desde su primera línea. Bajar hasta el final de una respuesta larga
 * obligaría a subir para empezar a leerla.
 *
 * Es el único movimiento de la charla, y se respeta si el teléfono tiene las
 * animaciones apagadas.
 */
function subirUltimaPregunta(animar) {
  const lista = document.getElementById("charla-lista");
  const preguntas = lista?.querySelectorAll(".charla-pregunta");
  const ultima = preguntas?.[preguntas.length - 1];
  if (!ultima) return;
  const suave = animar && !window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  lista.scrollTo({ top: ultima.offsetTop - 4, behavior: suave ? "smooth" : "auto" });
}

function ajustarAlTeclado() {
  const raiz = document.getElementById("charla");
  const vv = window.visualViewport;
  if (!raiz || !vv) return;
  raiz.style.height = `${vv.height}px`;
  raiz.style.transform = `translateY(${vv.offsetTop}px)`;
  // Con el teclado arriba, el margen del indicador de inicio (safe-area)
  // dejaría un hueco entre el campo y el teclado: se saca mientras está
  // abierto. Se compara contra el mayor de los dos altos de la ventana porque
  // no todas las versiones de Safari achican el mismo con el teclado.
  const alto = Math.max(window.innerHeight, document.documentElement.clientHeight);
  raiz.classList.toggle("teclado-abierto", vv.height < alto - 80);
}
