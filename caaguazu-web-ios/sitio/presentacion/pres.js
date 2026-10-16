// La presentación: una esfera que escucha, piensa y contesta en voz alta.
//
// Una sola página con dos caras:
//
//  - PANTALLA (lo normal): la esfera, y abajo lo que se preguntó y lo que
//    contesta, palabra por palabra mientras lo dice. Se habla tocando la
//    esfera, con la barra espaciadora, Enter o el control remoto de
//    diapositivas (que manda las mismas teclas que una flecha).
//  - TELÉFONO (`?sala=CÓDIGO&modo=control`): el micrófono de la pantalla. Se
//    habla cerca de la boca, lejos del ruido del salón; la pregunta viaja al
//    servidor y la pantalla —que pregunta cada segundo por su sala— la muestra
//    y la dice.
//
// En un teléfono (como micrófono de la pantalla o solo) se habla MANTENIENDO
// APRETADA la pantalla, como un walkie-talkie: escucha mientras el dedo está
// apoyado y pregunta al soltar.
//
// Nada de lo que se dice queda guardado acá: la charla vive en el servidor
// (la memoria del asistente) y en esta pestaña mientras está abierta.
//
// JavaScript plano, sin imports ni build, como el resto de la web.

const TX = {
  hablar: "Hablar",
  empezar: "Empezar",
  marca: "Caaguazú",
  apagada: "Presentación apagada",
  reintentar: "Reintentar",
  escuchando: "Escuchando",
  pensando: "Pensando",
  noEscuche: "No te escuché",
  sinMicrofono: "Sin micrófono",
  sinOido: "Navegador sin dictado",
  error: "No pude responder",
  espera: "Esperá un momento",
  sinRed: "Sin conexión",
  microfono: "Micrófono",
  microfonoSistema: "El del sistema",
  telefono: "Teléfono",
  crearSala: "Usar un teléfono",
  cerrarSala: "Dejar de usarlo",
  nuevaCharla: "Nueva charla",
  ajustes: "Ajustes",
  pantallaCompleta: "Pantalla completa",
  sala: "Sala",
  notaNavegador: "El dictado del navegador usa el micrófono del sistema: elegilo en la configuración del equipo.",
  esperas: ["Un segundo…", "Dejame pensar…", "Buena pregunta…", "Ya te cuento…"],
  atajos: "Espacio o Enter: hablar · Esc: cortar · F: pantalla completa · N: nueva charla",
  mantener: "Mantené apretado para hablar",
  dictadoBloqueado: "Este teléfono no deja dictar a la página",
};

const $ = (id) => document.getElementById(id);
const dormir = (ms) => new Promise((r) => setTimeout(r, ms));
const quieto = matchMedia("(prefers-reduced-motion: reduce)");

/* -------------------------------------------------------------------------
 * Configuración y red
 * ---------------------------------------------------------------------- */

/** Lo que el plugin deja escrito en la página: la API y la carpeta directa. */
function ajustesDeLaPagina() {
  try {
    const el = document.getElementById("czu-ajustes");
    const j = el ? JSON.parse(el.textContent) : null;
    return j && typeof j === "object" ? j : {};
  } catch {
    return {};
  }
}

/** Dónde está la API. Sin el plugin, la de producción. */
function direccionApi() {
  const api = ajustesDeLaPagina().api;
  return typeof api === "string" && api ? api : "https://caaguazu.net/wp-json/czu-app/v1/";
}
const API = direccionApi();

class ErrorApi extends Error {
  constructor(estado, codigo, esperaSeg = 0, causa = "") {
    super(codigo || String(estado));
    this.estado = estado;
    this.codigo = codigo;
    this.esperaSeg = esperaSeg;
    // Lo que contestó el servicio de transcripción (ya sin keys): sirve para
    // saber si es la key, el modelo o la red.
    this.causa = causa;
  }
}

/** fetch con tope de tiempo: en un salón, esperar para siempre es quedar mudo. */
async function pedir(ruta, opciones = {}, ms = 30000) {
  const control = new AbortController();
  const reloj = setTimeout(() => control.abort(), ms);
  let r;
  try {
    r = await fetch(API + ruta, { cache: "no-store", ...opciones, signal: control.signal });
  } catch {
    throw new ErrorApi(0, "sin_red");
  } finally {
    clearTimeout(reloj);
  }
  const cuerpo = await r.json().catch(() => ({}));
  if (!r.ok) {
    const espera = Number(r.headers.get("Retry-After")) || Number(cuerpo?.error?.detalle?.espera_seg) || 0;
    throw new ErrorApi(r.status, cuerpo?.error?.codigo, espera, cuerpo?.error?.detalle?.causa || "");
  }
  return cuerpo;
}

const enviarJson = (ruta, datos, ms) =>
  pedir(ruta, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(datos) }, ms);

/* -------------------------------------------------------------------------
 * Lo que dice la URL
 * ---------------------------------------------------------------------- */

const CARACTERES_SALA = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
const salaValida = (s) => (/^[A-Za-z0-9]{4,12}$/.test(String(s || "")) ? String(s).toUpperCase() : "");

const parametros = new URLSearchParams(location.search);
let sala = salaValida(parametros.get("sala"));
const modoControl = Boolean(sala) && parametros.get("modo") === "control";

// ¿Un teléfono o una tableta? Ahí se habla tocando en cualquier parte de la
// pantalla, el micrófono es de uno solo a la vez y hace falta ver que la
// esfera escucha.
const esTactil = matchMedia("(pointer: coarse)").matches;
// Dónde se escribe lo que pasa. La pantalla del escenario no escribe nada;
// quien maneja desde un teléfono necesita ver que lo escuchan, y poder leer por
// qué no. `?limpio=1` apaga el texto también en el teléfono.
const conTexto = modoControl || (esTactil && parametros.get("limpio") !== "1");
const depurando = parametros.get("debug") === "1";

// En un teléfono se habla manteniendo apretado, y sólo así. Escuchar todo el
// tiempo tomaba el ruido de la sala (contestaba sin que nadie le hablara) y,
// con ruido, no sabía dónde terminaba la frase (no contestaba nunca). Tocar
// para prender y apagar daba otros errores: quedaba prendido sin querer. Con
// el dedo apoyado no hay duda de cuándo se habla. La pantalla del escenario se
// maneja con teclado o mouse.
const mantener = modoControl || esTactil;
document.body.classList.toggle("mantener", mantener);

/**
 * Con `?debug=1`, un registro en pantalla de lo que hace el oído. En un
 * teléfono no hay consola, y «no me escucha» sin la razón (permiso, micrófono
 * ocupado, idioma, red) no se puede arreglar.
 */
let panelRastro = null;
const inicioRastro = performance.now();
function rastro(...partes) {
  if (!depurando) return;
  if (!panelRastro) {
    panelRastro = document.createElement("pre");
    panelRastro.className = "rastro";
    document.body.appendChild(panelRastro);
  }
  const linea = `${((performance.now() - inicioRastro) / 1000).toFixed(1).padStart(5)}s ${partes.join(" ")}`;
  const previas = panelRastro.textContent ? panelRastro.textContent.split("\n") : [];
  panelRastro.textContent = previas.concat(linea).slice(-24).join("\n");
}

/* -------------------------------------------------------------------------
 * El sonido: un solo AudioContext, creado con el primer toque
 * ---------------------------------------------------------------------- */

let audio = null; // AudioContext
const reproductor = new Audio();
reproductor.preload = "auto";
let analizadorSalida = null;
let analizadorEntrada = null;
let flujoMicrofono = null;

function iniciarAudio() {
  if (audio) return;
  const Ctx = window.AudioContext || window.webkitAudioContext;
  if (!Ctx) return;
  audio = new Ctx();
  try {
    // La voz pasa por un analizador para que la esfera se mueva con ella.
    const fuente = audio.createMediaElementSource(reproductor);
    analizadorSalida = audio.createAnalyser();
    analizadorSalida.fftSize = 512;
    fuente.connect(analizadorSalida);
    analizadorSalida.connect(audio.destination);
  } catch {
    analizadorSalida = null; // suena igual, sin mover la esfera con la voz
  }
}

/** Volumen de 0 a 1 de lo que pasa por un analizador. */
function volumen(analizador) {
  if (!analizador) return 0;
  const datos = new Uint8Array(analizador.fftSize);
  analizador.getByteTimeDomainData(datos);
  let suma = 0;
  for (const v of datos) {
    const x = (v - 128) / 128;
    suma += x * x;
  }
  return Math.min(1, Math.sqrt(suma / datos.length) * 4);
}

/** Abre el micrófono elegido (o el del sistema) y lo conecta al medidor. */
async function abrirMicrofono() {
  if (!navigator.mediaDevices?.getUserMedia) throw new Error("sin_microfono");
  const elegido = leer("czu.pres.mic");
  const pedido = {
    audio: {
      ...(elegido ? { deviceId: { exact: elegido } } : {}),
      echoCancellation: true,
      noiseSuppression: true,
      autoGainControl: true,
    },
  };
  let flujo;
  try {
    flujo = await navigator.mediaDevices.getUserMedia(pedido);
  } catch (e) {
    // El micrófono guardado ya no está enchufado: se vuelve al del sistema.
    if (elegido && e?.name === "OverconstrainedError") {
      guardar("czu.pres.mic", "");
      return abrirMicrofono();
    }
    throw e;
  }
  flujoMicrofono?.getTracks().forEach((t) => t.stop());
  flujoMicrofono = flujo;
  if (audio) {
    analizadorEntrada = audio.createAnalyser();
    analizadorEntrada.fftSize = 512;
    audio.createMediaStreamSource(flujo).connect(analizadorEntrada);
  }
  return flujo;
}

function leer(clave) {
  try {
    return localStorage.getItem(clave) || "";
  } catch {
    return "";
  }
}
function guardar(clave, valor) {
  try {
    if (valor) localStorage.setItem(clave, valor);
    else localStorage.removeItem(clave);
  } catch {
    /* sin almacenamiento: se elige de nuevo la próxima vez */
  }
}

/* -------------------------------------------------------------------------
 * La esfera
 * ---------------------------------------------------------------------- */

/*
 * La esfera es la animación que dibujó un amigo del proyecto, tal cual: el GIF
 * original pasado a video (esfera.mp4, los mismos 148 cuadros), porque a un
 * video se le puede cambiar la velocidad y a un GIF no.
 *
 * Lo que la hace reaccionar va encima, sin tocar la animación:
 *  - la VELOCIDAD: lenta en reposo, normal escuchando, rápida pensando, y
 *    hablando sigue el volumen de la voz;
 *  - el TAMAÑO y la LUZ, que crecen con la energía;
 *  - un RESPLANDOR azul detrás, que se abre con la voz.
 */
const Orbe = (() => {
  const video = $("esfera");
  const escena = $("escena-orbe");
  const brillo = $("brillo");

  // Se pide directo a la carpeta del plugin cuando se puede (ver
  // CZUWIOS_Servidor::ajustes()): el servidor web atiende los pedidos por
  // partes que Safari exige para un video, y no pasa por WordPress.
  // Hay dos copias: MP4 (H.264), que reproducen Chrome, Edge y Safari, y
  // WebM (VP9) para los navegadores que no traen H.264 — algunos Chromium y
  // Firefox de Linux. Una esfera que no arranca sería la imagen quieta.
  const directa = ajustesDeLaPagina().directa;
  const formato = video.canPlayType('video/mp4; codecs="avc1.42E01E"') ? "mp4" : "webm";
  video.src = `${directa ? `${directa}presentacion/` : ""}esfera.${formato}`;

  const VELOCIDAD = { inicio: 0.6, apagada: 0.25, listo: 0.6, escuchando: 1.25, pensando: 1.6, hablando: 0.9 };
  const BASE = { inicio: 0.12, apagada: 0.02, listo: 0.15, escuchando: 0.5, pensando: 0.5, hablando: 0.3 };

  let nivel = 0;
  let energia = 0.15;
  let velocidad = 1;
  let alCuadro = null;

  function reproducir() {
    if (quieto.matches) {
      video.pause();
      return;
    }
    video.play().catch(() => {});
  }
  quieto.addEventListener?.("change", reproducir);
  reproducir();

  function cuadro(ahora) {
    alCuadro?.(ahora);

    const e = Estado.actual;
    const objetivo = nivelObjetivo(e, ahora);
    nivel += (objetivo - nivel) * (objetivo > nivel ? 0.35 : 0.1);
    energia += ((BASE[e] ?? 0.15) + nivel * 0.7 - energia) * 0.08;
    const E = Math.min(1, energia);

    // Cambiar la velocidad en cada cuadro hace saltar el video: sólo cuando
    // la diferencia se nota.
    const quiere = Math.max(0.25, Math.min(2.2, (VELOCIDAD[e] ?? 0.6) + nivel * 0.9));
    if (!quieto.matches && Math.abs(quiere - velocidad) > 0.06) {
      velocidad = quiere;
      video.playbackRate = velocidad;
    }

    const mueve = !quieto.matches;
    escena.style.setProperty("--escala", mueve ? String(0.94 + 0.14 * E) : "1");
    video.style.setProperty("--luz", String(0.75 + 0.55 * E));
    video.style.setProperty("--color", String(1 + 0.35 * E));
    brillo.style.setProperty("--brillo", String(Math.min(0.85, E * 0.9)));
    brillo.style.setProperty("--abre", mueve ? String(1 + 0.6 * E) : "1");

    requestAnimationFrame(cuadro);
  }

  requestAnimationFrame(cuadro);
  return {
    /** Algo que tiene que correr una vez por cuadro (los subtítulos). */
    set alCuadro(fn) {
      alCuadro = fn;
    },
    reproducir,
  };
})();

/** De dónde sale el movimiento de la esfera en cada estado. */
let pulsoRemoto = 0;
function nivelObjetivo(estado, ahora) {
  if (estado === "escuchando") {
    // Sin micrófono propio abierto —escuchando desde el teléfono, o con el
    // dictado de un celular, que no deja abrir el micrófono dos veces— lo que
    // late es lo que va llegando: que se abrió el micrófono, que hay voz, el
    // texto.
    if (escuchaRemota || escuchaPorEventos) {
      pulsoRemoto *= 0.96;
      return Math.min(1, pulsoRemoto + 0.15 + 0.1 * Math.sin(ahora / 180));
    }
    return volumen(analizadorEntrada);
  }
  if (estado === "hablando") {
    if (analizadorSalida && !reproductor.paused) return volumen(analizadorSalida);
    // La voz del navegador no se deja medir: se imita una voz.
    return 0.35 + 0.25 * Math.abs(Math.sin(ahora / 140)) * Math.abs(Math.sin(ahora / 370));
  }
  return 0;
}

/* -------------------------------------------------------------------------
 * Estados y lo que se ve
 * ---------------------------------------------------------------------- */

const Estado = {
  actual: "inicio",
};
let escuchaRemota = false;
let escuchaPorEventos = false;

/*
 * Si la respuesta tarda, la pantalla no se queda en silencio: a los tres
 * segundos de pensar aparece una frase corta que va rotando. Es lo único que
 * se escribe en la pantalla mientras piensa, y se borra con la respuesta.
 */
let relojEspera = 0;
let indiceEspera = 0;
function empezarEspera() {
  clearEspera();
  relojEspera = setTimeout(() => {
    mostrarEspera();
    relojEspera = setInterval(mostrarEspera, 3500);
  }, 3000);
}
function mostrarEspera() {
  if (Estado.actual !== "pensando") return;
  const el = $("respuesta");
  el.classList.add("espera");
  el.textContent = TX.esperas[indiceEspera++ % TX.esperas.length];
}
function clearEspera() {
  clearTimeout(relojEspera);
  clearInterval(relojEspera);
  const el = $("respuesta");
  // Sólo se borra lo que puso la espera: si ya llegó la respuesta, sus
  // palabras no se tocan.
  if (el.classList.contains("espera")) {
    el.textContent = "";
    el.classList.remove("espera");
  }
}

function poner(estado, { mensaje = "", error = false } = {}) {
  Estado.actual = estado;
  if (estado === "pensando") empezarEspera();
  else clearEspera();
  if (estado !== "escuchando") {
    escuchaRemota = false;
    escuchaPorEventos = false;
    delete document.body.dataset.mic;
  }
  // El estado queda escrito en el body para quien lo necesite (las pruebas,
  // un lector de pantalla por aria-pressed), no en la pantalla.
  document.body.dataset.estado = estado;
  const el = $("estado");
  // La pantalla es sólo la esfera: lo que hace se ve en cómo se mueve, y lo
  // único que se escribe es un problema que alguien tiene que resolver. El
  // teléfono, que es quien habla, dice qué está pasando y, quieto, cómo se
  // habla: mantener apretado no se adivina mirando.
  const texto = conTexto
    ? mensaje ||
      {
        escuchando: TX.escuchando,
        pensando: TX.pensando,
        apagada: TX.apagada,
        listo: mantener ? TX.mantener : "",
      }[estado] ||
      ""
    : error || estado === "apagada"
      ? mensaje || TX.apagada
      : "";
  el.textContent = texto;
  el.classList.toggle("es-error", error);
  $("orbe").setAttribute("aria-pressed", String(estado === "escuchando"));
}

/** Un aviso que dura unos segundos y vuelve a «listo». */
let relojAviso = 0;
function avisar(mensaje, { error = true, fijo = false } = {}) {
  clearTimeout(relojAviso);
  poner("listo", { mensaje, error });
  // Un problema que no se arregla solo (sin permiso de micrófono) queda en
  // pantalla; el resto se va a los cuatro segundos.
  if (fijo) return;
  relojAviso = setTimeout(() => {
    if (Estado.actual === "listo") poner("listo");
  }, 4000);
}

function mostrarPregunta(texto) {
  // En la pantalla no se repite lo que se preguntó: se escuchó en la sala.
  if (!conTexto) texto = "";
  const el = $("pregunta");
  el.textContent = texto || "";
  el.classList.toggle("visible", Boolean(texto));
}

/** Lo que se va entendiendo mientras se habla, en gris. */
function mostrarEscuchado(texto) {
  // Tampoco lo que se va entendiendo: en la pantalla sólo se lee la respuesta.
  if (!conTexto) texto = "";
  const el = $("respuesta");
  el.classList.remove("desvanecida");
  el.classList.add("parcial");
  el.textContent = texto || "";
  el.scrollTop = el.scrollHeight;
}

/* --- Subtítulos que siguen a la voz ---------------------------------- */

let subtitulo = null; // { palabras: [span], cortes: [índice de carácter], dichas }

/**
 * Terminada la respuesta, queda unos segundos para terminar de leerla y se
 * apaga: la pantalla vuelve a ser sólo la esfera.
 */
let relojDesvanecer = 0;
function desvanecerRespuesta() {
  if (conTexto) return;
  clearTimeout(relojDesvanecer);
  relojDesvanecer = setTimeout(() => $("respuesta").classList.add("desvanecida"), 6000);
}

function prepararSubtitulo(texto) {
  clearTimeout(relojDesvanecer);
  const el = $("respuesta");
  el.classList.remove("parcial", "desvanecida", "espera");
  el.textContent = "";
  el.scrollTop = 0;
  const palabras = [];
  const cortes = [];
  let pos = 0;
  for (const trozo of String(texto).split(/(\s+)/)) {
    if (!trozo) continue;
    if (/^\s+$/.test(trozo)) {
      el.appendChild(document.createTextNode(" "));
    } else {
      const span = document.createElement("span");
      span.className = "palabra";
      span.textContent = trozo;
      el.appendChild(span);
      palabras.push(span);
      cortes.push(pos);
    }
    pos += trozo.length;
  }
  subtitulo = { palabras, cortes, dichas: 0 };
}

/** Marca como dichas las primeras `n` palabras y corre el texto hasta ellas. */
function decirHasta(n) {
  if (!subtitulo) return;
  const { palabras } = subtitulo;
  n = Math.max(0, Math.min(palabras.length, n));
  if (n <= subtitulo.dichas) return;
  for (let i = subtitulo.dichas; i < n; i++) palabras[i].classList.add("dicha");
  subtitulo.dichas = n;
  const actual = palabras[n - 1];
  const caja = $("respuesta");
  const renglon = parseFloat(getComputedStyle(caja).lineHeight) || 40;
  const objetivo = Math.max(0, actual.offsetTop - caja.offsetTop - renglon);
  if (Math.abs(caja.scrollTop - objetivo) > 2) caja.scrollTo({ top: objetivo, behavior: quieto.matches ? "auto" : "smooth" });
}

Orbe.alCuadro = () => {
  if (Estado.actual !== "hablando" || !subtitulo || reproductor.paused) return;
  const d = reproductor.duration;
  if (!d || !isFinite(d)) return;
  // Un poco por delante de la voz: leer lo que se está por oír se siente
  // sincronizado; leerlo después, atrasado.
  decirHasta(Math.ceil((reproductor.currentTime / d) * subtitulo.palabras.length * 1.04));
};

/* --- La voz ----------------------------------------------------------- */

let cortarVoz = null;

/** Dice un texto: con el audio del servidor si vino, si no con el navegador. */
function decir(texto, audioUrl) {
  callar();
  prepararSubtitulo(texto);
  poner("hablando");
  avisarPantalla("hablando");
  return new Promise((listo) => {
    let terminado = false;
    const terminar = () => {
      if (terminado) return;
      terminado = true;
      cortarVoz = null;
      decirHasta(Infinity);
      desvanecerRespuesta();
      avisarPantalla("listo");
      if (Estado.actual === "hablando") poner("listo");
      listo();
    };
    cortarVoz = terminar;

    if (audioUrl) {
      audio?.resume?.();
      // Un audio que no se puede reproducir avisa dos veces (el error del
      // elemento y la promesa de play): la voz del navegador entra una sola.
      let cayo = false;
      const planB = () => {
        if (cayo || terminado) return;
        cayo = true;
        reproductor.onended = null;
        reproductor.onerror = null;
        vozDelNavegador(texto, terminar);
      };
      reproductor.onended = terminar;
      reproductor.onerror = planB;
      reproductor.src = audioUrl;
      reproductor.play().catch(planB);
    } else {
      vozDelNavegador(texto, terminar);
    }
  });
}

function callar() {
  reproductor.onended = null;
  reproductor.onerror = null;
  reproductor.pause();
  if (window.speechSynthesis) speechSynthesis.cancel();
  const c = cortarVoz;
  cortarVoz = null;
  c?.();
}

/** La mejor voz en castellano que tenga el equipo. */
function vozEnCastellano() {
  const voces = window.speechSynthesis?.getVoices?.() ?? [];
  const es = voces.filter((v) => /^es(-|_|$)/i.test(v.lang));
  const orden = ["es-PY", "es-AR", "es-419", "es-US", "es-MX", "es-ES"];
  const puntaje = (v) => {
    const i = orden.findIndex((l) => v.lang.replace("_", "-").toLowerCase() === l.toLowerCase());
    return (i < 0 ? 10 : i) - (/google|natural|premium|enhanced/i.test(v.name) ? 5 : 0);
  };
  return es.sort((a, b) => puntaje(a) - puntaje(b))[0] || null;
}

function vozDelNavegador(texto, terminar) {
  if (!window.speechSynthesis) {
    // Ni voz: el texto queda escrito, que es mejor que nada.
    terminar();
    return;
  }
  speechSynthesis.cancel();
  const dicho = new SpeechSynthesisUtterance(texto);
  const voz = vozEnCastellano();
  if (voz) dicho.voice = voz;
  dicho.lang = voz?.lang || "es-ES";
  dicho.rate = 1;
  dicho.onboundary = (e) => {
    if (!subtitulo || typeof e.charIndex !== "number") return;
    const n = subtitulo.cortes.findIndex((c) => c > e.charIndex);
    decirHasta(n < 0 ? subtitulo.palabras.length : n);
  };
  // Chrome a veces no avisa el final de un texto largo: un reloj de respaldo.
  const tope = setTimeout(terminar, 2000 + (subtitulo?.palabras.length || 10) * 450);
  dicho.onend = dicho.onerror = () => {
    clearTimeout(tope);
    terminar();
  };
  speechSynthesis.speak(dicho);
}

/* -------------------------------------------------------------------------
 * El oído
 * ---------------------------------------------------------------------- */

let config = { disponible: false, oido: "navegador", idioma: "es-PY", voz: "navegador" };
const Reconocedor = window.SpeechRecognition || window.webkitSpeechRecognition;

function hayOidoLocal() {
  if (config.oido === "tiempo_real") return Boolean(window.WebSocket && navigator.mediaDevices && (window.AudioContext || window.webkitAudioContext));
  return config.oido === "servidor" ? Boolean(window.MediaRecorder && navigator.mediaDevices) : Boolean(Reconocedor);
}

/* -------------------------------------------------------------------------
 * Mantener apretado (el teléfono)
 *
 * Escucha mientras el dedo está apoyado en la pantalla y pregunta al soltar.
 * Un toque corto no es hablar (se lo toma por un roce): recuerda cómo se hace.
 * Mientras la pantalla de la sala dice la respuesta, apretar no hace nada: el
 * teléfono, en el mismo salón que los parlantes, se escucharía a sí mismo.
 * ---------------------------------------------------------------------- */

let esperandoPantalla = false; // la pantalla está diciendo la respuesta
let sostenida = false; // el dedo sigue apoyado
let apretado = null; // { id, desde }
const TOQUE_MINIMO = 350; // ms: menos que esto es un roce, no una pregunta

function apretar(e) {
  if (!mantener || apretado || e.button > 0) return;
  if (Estado.actual === "inicio" || Estado.actual === "apagada") return;
  e.preventDefault();
  if (Estado.actual === "pensando") return;
  if (esperandoPantalla) {
    avisar(TX.espera, { error: false });
    return;
  }
  apretado = { id: e.pointerId, desde: performance.now() };
  try {
    e.currentTarget.setPointerCapture?.(e.pointerId);
  } catch {
    /* sin captura: igual llega el pointerup al documento */
  }
  tocar();
  if (Estado.actual === "hablando") callar();
  if (escucha) cancelarEscucha();
  if (!hayOidoLocal()) {
    apretado = null;
    avisar(config.oido === "navegador" ? TX.sinOido : TX.sinMicrofono);
    return;
  }
  sostenida = true;
  rastro("apretado: escucha");
  escucharSegunOido();
}

function soltar(e) {
  if (!apretado || (e && e.pointerId !== undefined && e.pointerId !== apretado.id)) return;
  const duro = performance.now() - apretado.desde;
  apretado = null;
  sostenida = false;
  if (duro < TOQUE_MINIMO) {
    rastro(`toque de ${Math.round(duro)} ms: no es hablar`);
    cancelarEscucha();
    avisar(TX.mantener, { error: false });
    return;
  }
  if (!escucha) {
    rastro(`soltado a los ${(duro / 1000).toFixed(1)} s, pero no había nada escuchando`);
    return;
  }
  rastro(`soltado a los ${(duro / 1000).toFixed(1)} s: se pregunta`);
  escucha.detener();
}

function escucharSegunOido() {
  if (config.oido === "tiempo_real") escucharTiempoReal();
  else if (config.oido === "servidor") escucharConServidor();
  else escucharConNavegador();
}

let escucha = null; // { detener(), cancelar() }

/** Al tocar, la esfera responde ya, antes de saber si el micrófono anda. */
function tocar() {
  document.body.classList.remove("toque");
  void document.body.offsetWidth; // reinicia la animación si se toca dos veces
  document.body.classList.add("toque");
  navigator.vibrate?.(12);
}

function alternarEscucha() {
  // Un teléfono se mantiene apretado: ver «Mantener apretado».
  if (mantener) return;
  if (Estado.actual === "inicio" || Estado.actual === "apagada") return;
  tocar();
  rastro("toque, estaba:", Estado.actual, escucha ? "(cierra la escucha)" : "");
  if (escucha) {
    escucha.detener();
    return;
  }
  // Tocar mientras habla corta la voz y escucha: es como se interrumpe a
  // alguien en una charla.
  if (Estado.actual === "hablando") callar();
  if (Estado.actual === "pensando") return;
  if (!hayOidoLocal()) {
    avisar(Reconocedor || config.oido === "servidor" ? TX.sinMicrofono : TX.sinOido);
    return;
  }
  escucharSegunOido();
}

function cancelarEscucha() {
  escucha?.cancelar();
  escucha = null;
  cerrarTiempoReal();
}

/* --- Avisos al otro lado de la sala ------------------------------------- */

/**
 * La pantalla le dice a la sala cuándo está hablando. El teléfono lo necesita:
 * está en el mismo salón que los parlantes y, si escuchara mientras la pantalla
 * habla, se contestaría a sí mismo.
 */
function avisarPantalla(estado) {
  if (!sala || modoControl) return;
  enviarJson(`presentacion/sala/${sala}`, { estado }, 5000).catch(() => {});
}

/**
 * El teléfono ya tiene la respuesta, pero la dice la pantalla: hasta que ésta
 * no termine, el teléfono no escucha. Mira la sala cada poco; si la pantalla
 * nunca habla (no hay ninguna abierta) no se queda esperando para siempre.
 */
async function esperarPantalla() {
  esperandoPantalla = true;
  const t0 = Date.now();
  let hablo = false;
  while (esperandoPantalla) {
    await dormir(700);
    let estado = "";
    try {
      // `desde` altísimo: sólo interesa el estado, no volver a bajar el audio.
      estado = (await pedir(`presentacion/sala/${sala}?desde=1000000000`, {}, 4000)).estado;
    } catch {
      /* un corte: se sigue mirando */
    }
    const t = Date.now() - t0;
    if (estado === "hablando") hablo = true;
    else if (hablo) {
      rastro("la pantalla terminó de hablar");
      break;
    } else if (t > 7000) {
      rastro("la pantalla no habló en 7 s (¿hay una abierta?): se sigue");
      break;
    }
    if (t > 90000) {
      rastro("tope de espera de la pantalla");
      break;
    }
  }
  await dormir(600); // la cola del eco de la sala
  esperandoPantalla = false;
}

let ultimoAviso = 0;
let avisoPendiente = null;
function avisarSala(estado, parcial = "") {
  if (!modoControl) return;
  const ahora = Date.now();
  const mandar = () => {
    ultimoAviso = Date.now();
    avisoPendiente = null;
    enviarJson(`presentacion/sala/${sala}`, { estado, parcial }, 5000).catch(() => {});
  };
  clearTimeout(avisoPendiente);
  // Lo parcial se manda como mucho dos veces por segundo; el cambio de estado, ya.
  if (estado !== "escuchando" || !parcial || ahora - ultimoAviso > 500) mandar();
  else avisoPendiente = setTimeout(mandar, 500 - (ahora - ultimoAviso));
}

/**
 * Idiomas del dictado, del que pide el panel al más genérico. Un celular que no
 * tiene `es-PY` a veces avisa (`language-not-supported`) y a veces no oye nada:
 * se prueba con castellano de Latinoamérica y de España antes de rendirse.
 */
const idiomasDictado = () => [...new Set([config.idioma, "es-419", "es-ES"])];

/**
 * Dictado del navegador. Con el dedo apoyado, cada pausa cierra el dictado y
 * se abre otro que sigue la misma frase (`previo`): el modo continuo de Chrome
 * en Android repite lo ya dicho en cada resultado.
 */
function escucharConNavegador(intento = 0, previo = "") {
  const idiomas = idiomasDictado();
  const rec = new Reconocedor();
  rec.lang = idiomas[Math.min(intento, idiomas.length - 1)];
  rec.interimResults = true;
  rec.continuous = false;
  rec.maxAlternatives = 1;

  let final = previo ? `${previo} ` : "";
  let parcial = "";
  let fallo = "";
  let cancelada = false;

  // Perro guardián: con ruido de fondo el reconocedor puede no cerrar nunca la
  // frase y la página se queda «escuchando» para siempre. Si lo entendido lleva
  // un rato sin cambiar se corta (el texto que haya se contesta), y ninguna
  // escucha dura más de un tope.
  let relojQuieto = 0;
  const cortar = (motivo) => {
    rastro("se corta la escucha:", motivo);
    try {
      rec.stop();
    } catch {
      /* ya estaba parada */
    }
  };
  const relojTope = setTimeout(() => cortar("tope de tiempo"), 25000);

  // Sin medidor propio, la esfera late con estos avisos: el micrófono se abrió,
  // hay un sonido, hay voz, llegó texto. Cada uno es una prueba de que algo
  // anda, y la falta de todos es la prueba de que no.
  escuchaPorEventos = !flujoMicrofono?.active;
  document.body.dataset.mic = "pendiente";
  rastro("dictado", rec.lang, escuchaPorEventos ? "(sin medidor propio)" : "(con medidor)");

  rec.onstart = () => rastro("start");
  rec.onaudiostart = () => {
    document.body.dataset.mic = "abierto";
    pulsoRemoto = 0.6;
    rastro("audiostart: el micrófono se abrió");
  };
  rec.onsoundstart = () => {
    pulsoRemoto = 0.75;
    rastro("soundstart: hay sonido");
  };
  rec.onspeechstart = () => {
    pulsoRemoto = 0.9;
    rastro("speechstart: hay voz");
  };
  rec.onspeechend = () => rastro("speechend");
  rec.onaudioend = () => rastro("audioend");
  rec.onnomatch = () => rastro("nomatch");
  rec.onresult = (e) => {
    parcial = "";
    for (let i = e.resultIndex; i < e.results.length; i++) {
      const r = e.results[i];
      if (r.isFinal) final += r[0].transcript;
      else parcial += r[0].transcript;
    }
    const visto = `${final} ${parcial}`.trim();
    clearTimeout(relojQuieto);
    // Con el dedo apoyado, la frase termina al soltar: no hay que adivinarlo.
    if (visto && !sostenida) relojQuieto = setTimeout(() => cortar("la frase no cambia hace 2,5 s"), 2500);
    pulsoRemoto = 0.95;
    rastro("resultado:", visto);
    mostrarEscuchado(visto);
    avisarSala("escuchando", visto);
  };
  rec.onerror = (e) => {
    fallo = e.error || "error";
    rastro("ERROR:", fallo, e.message || "");
  };
  rec.onend = () => {
    clearTimeout(relojQuieto);
    clearTimeout(relojTope);
    escucha = null;
    const texto = `${final} ${parcial}`.trim();
    rastro("end", fallo ? `(error ${fallo})` : "", texto ? `texto: ${texto}` : "sin texto", cancelada ? "(cancelada)" : "");
    if (cancelada) return;
    // Un idioma que este navegador no tiene: se prueba con el siguiente.
    if (fallo === "language-not-supported" && intento < idiomas.length - 1) {
      escucharConNavegador(intento + 1, previo);
      return;
    }
    // Una pausa con el dedo todavía apoyado: se sigue escuchando la misma frase.
    if (sostenida && !["not-allowed", "service-not-allowed", "audio-capture"].includes(fallo)) {
      escucharConNavegador(intento, texto);
      return;
    }
    if (texto) {
      preguntar(texto);
      return;
    }
    avisarSala("listo");
    $("respuesta").textContent = "";
    if (fallo === "service-not-allowed") {
      // Safari en iPhone: el dictado sólo arranca con un toque terminado (un
      // clic), no con el dedo apoyado. Con «mantener apretado» no hay forma:
      // hace falta el oído en tiempo real (ElevenLabs), que usa el micrófono
      // abierto en «Empezar». Se queda dicho, para quien maneja el panel.
      rastro("el navegador no deja dictar al apoyar el dedo: usar el oído «ElevenLabs en tiempo real» en el panel");
      avisar(TX.dictadoBloqueado, { fijo: true });
    } else if (fallo === "not-allowed") {
      // Sin permiso del micrófono no hay nada que reintentar: se queda dicho.
      avisar(TX.sinMicrofono, { fijo: true });
    } else if (fallo === "audio-capture") {
      avisar(TX.sinMicrofono);
    } else if (fallo === "network") {
      avisar(TX.sinRed);
    } else {
      avisar(TX.noEscuche, { error: false });
    }
  };

  escucha = {
    detener: () => rec.stop(),
    cancelar: () => {
      cancelada = true;
      rec.abort();
      avisarSala("listo");
    },
  };
  // Al seguir una frase (`previo`) no se borra lo que ya se ve.
  if (!previo) {
    mostrarPregunta("");
    mostrarEscuchado("");
    poner("escuchando");
    avisarSala("escuchando", "");
  }
  document.body.dataset.mic = "pendiente";
  try {
    rec.start();
  } catch (e) {
    escucha = null;
    rastro("ERROR al arrancar:", e?.name || e);
    // Si no se pudo seguir la frase, se pregunta con lo que ya se entendió.
    if (previo) preguntar(previo);
    else avisar(TX.sinMicrofono);
  }
}

/**
 * Graba y manda a transcribir. Corta solo cuando, después de haber oído voz,
 * hay un silencio de más de un segundo; o a los 30 segundos.
 */
async function escucharConServidor() {
  let flujo;
  try {
    flujo = flujoMicrofono?.active ? flujoMicrofono : await abrirMicrofono();
  } catch {
    avisar(TX.sinMicrofono);
    return;
  }
  const tipos = ["audio/webm;codecs=opus", "audio/webm", "audio/mp4", "audio/ogg;codecs=opus"];
  const tipo = tipos.find((t) => window.MediaRecorder.isTypeSupported?.(t)) || "";
  let grabador;
  try {
    grabador = new MediaRecorder(flujo, tipo ? { mimeType: tipo } : undefined);
  } catch {
    avisar(TX.sinMicrofono);
    return;
  }

  const partes = [];
  let cancelada = false;
  let oyoVoz = false;
  let silencioDesde = 0;
  const inicio = performance.now();

  const vigilar = setInterval(() => {
    const v = volumen(analizadorEntrada);
    const ahora = performance.now();
    if (v > 0.12) {
      oyoVoz = true;
      silencioDesde = 0;
    } else if (oyoVoz && v < 0.06 && !sostenida) {
      // Con el dedo apoyado, corta el dedo, no el silencio.
      silencioDesde ||= ahora;
      if (ahora - silencioDesde > 1200) detener();
    }
    if (ahora - inicio > 30000) detener();
  }, 100);

  function detener() {
    clearInterval(vigilar);
    if (grabador.state !== "inactive") grabador.stop();
  }

  grabador.ondataavailable = (e) => {
    if (e.data?.size) partes.push(e.data);
  };
  grabador.onstop = async () => {
    escucha = null;
    clearInterval(vigilar);
    if (cancelada) return;
    const blob = new Blob(partes, { type: grabador.mimeType || tipo || "audio/webm" });
    if (blob.size < 1500) {
      avisarSala("listo");
      avisar(TX.noEscuche, { error: false });
      return;
    }
    poner("pensando");
    const ext = /mp4/.test(blob.type) ? "mp4" : /ogg/.test(blob.type) ? "ogg" : "webm";
    const datos = new FormData();
    datos.append("audio", blob, `voz.${ext}`);
    const t0 = performance.now();
    rastro(`grabación: ${blob.size} bytes, ${blob.type || "sin tipo"}; se manda al servidor`);
    try {
      const r = await pedir("presentacion/oir", { method: "POST", body: datos }, 30000);
      const texto = String(r.texto || "").trim();
      rastro(`transcripción en ${((performance.now() - t0) / 1000).toFixed(1)}s: ${texto ? `«${texto}»` : "vacía (no se oyó nada)"}`);
      if (!texto) {
        avisarSala("listo");
        avisar(TX.noEscuche, { error: false });
        return;
      }
      preguntar(texto);
    } catch (e) {
      avisarSala("listo");
      // Lo que dijo el servidor: sin esto, un «no se pudo» no dice si fue la key,
      // el modelo o la red.
      rastro(`ERROR de transcripción (${e.estado ?? "sin respuesta"}, ${e.codigo || "sin código"}): ${e.causa || e.message || "sin detalle"}`);
      explicar(e);
    }
  };

  escucha = {
    detener,
    cancelar: () => {
      cancelada = true;
      detener();
      avisarSala("listo");
    },
  };
  mostrarPregunta("");
  mostrarEscuchado("");
  poner("escuchando");
  avisarSala("escuchando", "");
  grabador.start();
  document.body.dataset.mic = "abierto";
  rastro("grabando con", grabador.mimeType || tipo || "el formato del navegador");
}

/* -------------------------------------------------------------------------
 * Escuchar en tiempo real: ElevenLabs Scribe v2 Realtime, por WebSocket
 *
 * El audio del micrófono viaja a ElevenLabs, que devuelve lo que va entendiendo
 * (`partial_transcript`) y, en cada pausa, el tramo terminado
 * (`committed_transcript`).
 *
 * Una conexión por pregunta, y sólo mientras se habla: nada viaja (ni se paga)
 * entre una pregunta y otra.
 *  - En el teléfono, con el dedo apoyado, los tramos se juntan y se pregunta al
 *    soltar. El audio se guarda desde el primer instante y sale cuando la
 *    conexión está lista: lo dicho mientras conecta no se pierde.
 *  - En la computadora (tecla o clic), se pregunta en la primera pausa; otra
 *    tecla corta antes y pregunta con lo que haya.
 * Al terminar de hablar se manda SILENCIO (ceros) y se pide cerrar el tramo,
 * para que la última palabra llegue antes de cerrar.
 *
 * La key no se toca acá: el servidor pide un token de un solo uso y manda la
 * dirección ya armada (POST presentacion/token). Formato según la
 * documentación de ElevenLabs: PCM de 16 bits a 16 kHz, en base 64, dentro de
 * `{ message_type: "input_audio_chunk", audio_base_64, commit, sample_rate }`.
 * ---------------------------------------------------------------------- */

const rt = {
  ws: null,
  src: null,
  proc: null,
  mudo: null,
  activo: false, // se quiere escuchar (reconectar si se cae)
  listo: false, // llegó session_started
  soltada: false, // ya se terminó de hablar: se manda silencio y se espera el cierre
  commit: false, // el próximo envío pide cerrar el tramo
  dicho: "", // los tramos ya cerrados de esta pregunta
  ultimoParcial: "", // lo que va entendiendo del tramo abierto
  relojCierre: 0,
  resto: new Float32Array(0),
  cola: [],
  colaN: 0,
  reintento: 0,
  relojReconexion: 0,
  enviados: 0,
};

const MUESTRAS_POR_ENVIO = 3200; // 200 ms a 16 kHz (la documentación recomienda entre 0,1 y 1 s)
const MUESTRAS_MAX_ENVIO = 16000; // 1 s: lo guardado mientras conecta sale en trozos de a uno
const MUESTRAS_GUARDADAS = 16000 * 15; // lo que se guarda mientras conecta, como mucho

async function escucharTiempoReal() {
  cerrarTiempoReal();
  rt.activo = true;
  rt.listo = false;
  rt.soltada = false;
  rt.commit = false;
  rt.dicho = "";
  rt.ultimoParcial = "";
  escucha = {
    detener: terminarTiempoReal,
    cancelar: () => {
      escucha = null;
      cerrarTiempoReal();
      avisarSala("listo");
    },
  };
  mostrarPregunta("");
  mostrarEscuchado("");
  poner("escuchando");
  document.body.dataset.mic = "pendiente";
  avisarSala("escuchando", "");
  await conectarTiempoReal();
}

async function conectarTiempoReal() {
  let flujo;
  try {
    flujo = flujoMicrofono?.active ? flujoMicrofono : await abrirMicrofono();
  } catch (e) {
    rastro("ERROR micrófono:", e?.name || e);
    fallaTiempoReal(TX.sinMicrofono, { fatal: e?.name === "NotAllowedError" || e?.name === "SecurityError" });
    return;
  }
  if (!rt.activo) return;
  // El micrófono empieza a guardarse ya, mientras se pide el token y se conecta.
  if (!rt.proc) conectarAudioTiempoReal(flujo);
  let r;
  const t0 = performance.now();
  try {
    r = await pedir("presentacion/token", { method: "POST" }, 12000);
  } catch (e) {
    rastro(`ERROR token (${e.estado ?? "sin respuesta"}, ${e.codigo || "sin código"}): ${e.causa || e.message || "sin detalle"}`);
    // Un 409/503 es que el oído en tiempo real está apagado: no se reintenta.
    fallaTiempoReal(e.estado === 0 ? TX.sinRed : TX.error, { fatal: e.estado === 409 || e.estado === 503 });
    return;
  }
  if (!rt.activo) return; // mientras tanto se cerró
  rastro(`token en ${((performance.now() - t0) / 1000).toFixed(1)}s; conectando`);
  let ws;
  try {
    ws = new WebSocket(r.url);
  } catch (e) {
    rastro("ERROR al abrir el WebSocket:", e?.message || e);
    fallaTiempoReal(TX.sinRed, {});
    return;
  }
  rt.ws = ws;
  ws.onopen = () => rastro("WebSocket abierto");
  ws.onmessage = mensajeTiempoReal;
  ws.onerror = () => rastro("WebSocket: error de conexión");
  ws.onclose = (ev) => {
    rastro("WebSocket cerrado", ev.code, ev.reason || "");
    if (rt.ws === ws) rt.ws = null;
    rt.listo = false;
    if (rt.activo) reconectarTiempoReal();
  };
}

/** El micrófono pasa por un procesador que lo baja a 16 kHz y lo manda. */
function conectarAudioTiempoReal(flujo) {
  if (!audio) iniciarAudio();
  audio?.resume?.();
  if (!audio) {
    fallaTiempoReal(TX.sinMicrofono, { fatal: true });
    return;
  }
  rt.src = audio.createMediaStreamSource(flujo);
  // ScriptProcessor está en desuso, pero es lo que andan todos los navegadores
  // de teléfono; el reemplazo (AudioWorklet) pide un archivo aparte y no suma
  // nada acá.
  rt.proc = audio.createScriptProcessor(4096, 1, 1);
  rt.mudo = audio.createGain();
  rt.mudo.gain.value = 0; // el procesador tiene que estar conectado para correr, pero sin sonar
  rt.src.connect(rt.proc);
  rt.proc.connect(rt.mudo);
  rt.mudo.connect(audio.destination);
  rt.proc.onaudioprocess = (e) => enviarAudioTiempoReal(e.inputBuffer.getChannelData(0));
}

function soltarAudioTiempoReal() {
  try {
    if (rt.proc) rt.proc.onaudioprocess = null;
    rt.proc?.disconnect();
    rt.src?.disconnect();
    rt.mudo?.disconnect();
  } catch {
    /* ya estaba suelto */
  }
  rt.proc = rt.src = rt.mudo = null;
  rt.resto = new Float32Array(0);
  rt.cola = [];
  rt.colaN = 0;
}

function enviarAudioTiempoReal(entrada) {
  if (!audio) return;
  // Terminado de hablar, en vez del micrófono va silencio: ver arriba.
  const muestras = rt.soltada ? new Float32Array(entrada.length) : entrada;

  // De la frecuencia del equipo (44,1 o 48 kHz) a 16 kHz, promediando.
  const razon = audio.sampleRate / 16000;
  const buf = new Float32Array(rt.resto.length + muestras.length);
  buf.set(rt.resto);
  buf.set(muestras, rt.resto.length);
  const n = Math.floor(buf.length / razon);
  const pcm = new Int16Array(n);
  for (let i = 0; i < n; i++) {
    const a = Math.floor(i * razon);
    const b = Math.min(buf.length, Math.max(a + 1, Math.floor((i + 1) * razon)));
    let suma = 0;
    for (let j = a; j < b; j++) suma += buf[j];
    pcm[i] = Math.max(-1, Math.min(1, suma / (b - a))) * 32767;
  }
  rt.resto = buf.slice(Math.floor(n * razon));
  rt.cola.push(pcm);
  rt.colaN += n;

  const ws = rt.ws;
  if (!ws || ws.readyState !== 1 || !rt.listo) {
    // Todavía conectando: se guarda (con un tope, por si nunca conecta).
    while (rt.colaN > MUESTRAS_GUARDADAS && rt.cola.length > 1) rt.colaN -= rt.cola.shift().length;
    return;
  }
  if (rt.colaN < MUESTRAS_POR_ENVIO) return;

  const todo = new Int16Array(rt.colaN);
  let o = 0;
  for (const c of rt.cola) {
    todo.set(c, o);
    o += c.length;
  }
  rt.cola = [];
  rt.colaN = 0;
  // Lo guardado mientras conectaba puede pasar del segundo: sale en trozos.
  for (let desde = 0; desde < todo.length; desde += MUESTRAS_MAX_ENVIO) {
    const trozo = todo.subarray(desde, Math.min(todo.length, desde + MUESTRAS_MAX_ENVIO));
    const bytes = new Uint8Array(trozo.buffer, trozo.byteOffset, trozo.byteLength);
    let binario = "";
    for (let i = 0; i < bytes.length; i += 0x8000) binario += String.fromCharCode.apply(null, bytes.subarray(i, i + 0x8000));
    const commit = rt.commit && desde + MUESTRAS_MAX_ENVIO >= todo.length;
    if (commit) rt.commit = false;
    ws.send(JSON.stringify({ message_type: "input_audio_chunk", audio_base_64: btoa(binario), commit, sample_rate: 16000 }));
    rt.enviados++;
    if (rt.enviados === 1) rastro("primer audio enviado");
  }
}

function mensajeTiempoReal(ev) {
  let m;
  try {
    m = JSON.parse(ev.data);
  } catch {
    return;
  }
  switch (m.message_type) {
    case "session_started":
      rt.listo = true;
      rt.reintento = 0;
      rastro("sesión abierta", m.session_id || "");
      if (!rt.soltada) {
        document.body.dataset.mic = "abierto";
        avisarSala("escuchando", "");
      }
      break;
    case "partial_transcript":
      // Es lo que va entendiendo; un parcial vacío es sólo la señal de que la
      // conexión sigue viva.
      if (!m.text) break;
      rt.ultimoParcial = m.text;
      if (!sostenida && !rt.soltada) vigilarFrase(m.text);
      pulsoRemoto = 0.9;
      mostrarEscuchado(`${rt.dicho} ${m.text}`.trim());
      avisarSala("escuchando", `${rt.dicho} ${m.text}`.trim());
      break;
    case "committed_transcript":
      soltarVigilancia();
      tramoTiempoReal(m.text);
      break;
    case "warning":
      rastro("aviso:", m.warning || "");
      break;
    case "auth_error":
    case "quota_exceeded":
    case "unaccepted_terms":
      // Key, saldo o términos: reconectar no lo arregla.
      rastro("ERROR", m.message_type + ":", m.error || "");
      fallaTiempoReal(TX.error, { fatal: true });
      break;
    case "session_time_limit_exceeded":
      rastro("la sesión llegó a su tope de tiempo; se vuelve a conectar");
      rt.ws?.close();
      break;
    default:
      if (m.error) rastro("ERROR", m.message_type + ":", m.error);
  }
}

/**
 * Perro guardián del corte de frase en la computadora. El VAD de ElevenLabs
 * corta por silencio; con ruido de fondo puede no cortar nunca y la página se
 * queda escuchando sin contestar. Si lo entendido lleva un rato sin cambiar
 * —o la frase ya dura demasiado—, se da por terminada con lo que haya.
 */
function vigilarFrase(texto) {
  // El mismo texto repetido no es avance: no reinicia la cuenta.
  if (texto === rt.parcial && rt.relojFrase) return;
  rt.parcial = texto;
  rt.inicioFrase ||= Date.now();
  clearTimeout(rt.relojFrase);
  const quieto = Math.max(2000, ((config.silencio || 1.5) + 1) * 1000);
  const resto = Math.max(0, 20000 - (Date.now() - rt.inicioFrase));
  rt.relojFrase = setTimeout(() => {
    rastro("la frase no se cortó sola; se da por terminada:", rt.parcial);
    soltarVigilancia();
    cerrarFraseTiempoReal();
  }, Math.min(quieto, resto));
}

function soltarVigilancia() {
  clearTimeout(rt.relojFrase);
  rt.relojFrase = 0;
  rt.parcial = "";
  rt.inicioFrase = 0;
}

/** Un tramo cerrado: se junta con lo anterior. */
function tramoTiempoReal(texto) {
  texto = String(texto || "").trim();
  rt.ultimoParcial = "";
  if (texto) rt.dicho = `${rt.dicho} ${texto}`.trim();
  rastro("tramo:", texto || "(vacío)");
  if (sostenida) {
    mostrarEscuchado(rt.dicho);
    return;
  }
  if (rt.soltada) {
    // Ya se soltó: puede venir otro tramo pegado; se espera un instante.
    clearTimeout(rt.relojCierre);
    rt.relojCierre = setTimeout(cerrarFraseTiempoReal, 400);
    return;
  }
  // En la computadora, la primera pausa es el final de la pregunta. Un «mm» o
  // un ruido suelto no es una pregunta: se sigue escuchando.
  if (rt.dicho.replace(/[\s.,;:¿?¡!…-]/g, "").length < 3) {
    rastro("frase demasiado corta, se ignora:", rt.dicho);
    rt.dicho = "";
    return;
  }
  cerrarFraseTiempoReal();
}

/** Se terminó de hablar (se soltó, u otra tecla): lo que falte, y se pregunta. */
function terminarTiempoReal() {
  if (!rt.activo || rt.soltada) return;
  rt.soltada = true;
  rt.commit = true;
  soltarVigilancia();
  // Lo que falta llega en cuanto se cierra el tramo. Si no conectó todavía, lo
  // guardado sale al conectar: se espera un poco más.
  clearTimeout(rt.relojCierre);
  rt.relojCierre = setTimeout(cerrarFraseTiempoReal, rt.listo ? 1200 : 5000);
}

function cerrarFraseTiempoReal() {
  clearTimeout(rt.relojCierre);
  const texto = `${rt.dicho} ${rt.ultimoParcial}`.trim();
  escucha = null;
  cerrarTiempoReal();
  if (texto.replace(/[\s.,;:¿?¡!…-]/g, "").length < 3) {
    rastro("no se entendió nada:", texto || "(vacío)");
    avisarSala("listo");
    $("respuesta").textContent = "";
    avisar(TX.noEscuche, { error: false });
    return;
  }
  rastro("frase:", texto);
  preguntar(texto);
}

function reconectarTiempoReal() {
  rt.reintento++;
  if (rt.reintento > 4) {
    rastro("no se pudo reconectar");
    fallaTiempoReal(TX.sinRed, {});
    return;
  }
  const espera = Math.min(6000, 700 * rt.reintento);
  rastro(`reconectando en ${espera} ms (intento ${rt.reintento})`);
  clearTimeout(rt.relojReconexion);
  rt.relojReconexion = setTimeout(() => {
    if (rt.activo && !rt.ws) conectarTiempoReal();
  }, espera);
}

function cerrarTiempoReal() {
  soltarVigilancia();
  clearTimeout(rt.relojCierre);
  rt.activo = false;
  rt.listo = false;
  rt.soltada = false;
  rt.commit = false;
  rt.reintento = 0;
  clearTimeout(rt.relojReconexion);
  soltarAudioTiempoReal();
  const ws = rt.ws;
  rt.ws = null;
  if (ws) {
    ws.onclose = null;
    ws.onmessage = null;
    ws.onopen = null;
    try {
      ws.close();
    } catch {
      /* ya estaba cerrado */
    }
  }
}

/**
 * Algo falló. Un problema sin arreglo (permiso, key, saldo) deja el mensaje en
 * pantalla; el resto se va solo a los segundos.
 */
function fallaTiempoReal(mensaje, { fatal = false } = {}) {
  cerrarTiempoReal();
  escucha = null;
  avisarSala("listo");
  avisar(mensaje, { fijo: fatal });
}

/* -------------------------------------------------------------------------
 * Preguntar
 * ---------------------------------------------------------------------- */

let conversacion = "";
let vuelta = 0;

async function preguntar(texto) {
  const mia = ++vuelta;
  mostrarPregunta(texto);
  $("respuesta").textContent = "";
  poner("pensando");
  try {
    const r = await enviarJson(
      "presentacion",
      {
        mensaje: texto.slice(0, 1000),
        conversacion: conversacion || undefined,
        sala: modoControl ? sala : undefined,
        // El teléfono no necesita el audio: lo dice la pantalla.
        audio: !modoControl,
      },
      60000,
    );
    if (mia !== vuelta) return;
    conversacion = r.conversacion || conversacion;
    if (modoControl) {
      prepararSubtitulo(r.respuesta || "");
      decirHasta(Infinity);
      esperarPantalla();
      poner("listo");
      return;
    }
    await decir(r.respuesta || "", r.audio || null);
  } catch (e) {
    if (mia === vuelta) explicar(e);
  }
}

function explicar(e) {
  if (!(e instanceof ErrorApi)) return avisar(TX.error);
  if (e.estado === 429) return avisar(TX.espera);
  if (e.estado === 503) return poner("apagada");
  if (e.estado === 0) return avisar(TX.sinRed);
  return avisar(TX.error);
}

function nuevaCharla() {
  vuelta++;
  conversacion = "";
  cancelarEscucha();
  callar();
  mostrarPregunta("");
  $("respuesta").textContent = "";
  poner("listo");
}

/* -------------------------------------------------------------------------
 * La sala: la pantalla mira lo que manda el teléfono
 * ---------------------------------------------------------------------- */

let turnoSala = 0;
let salaVigilada = "";

async function vigilarSala() {
  if (!sala || modoControl || salaVigilada === sala) return;
  const mia = (salaVigilada = sala);
  // La primera vuelta sólo aprende en qué turno está: lo que se dijo antes de
  // abrir esta pantalla no se vuelve a decir.
  let desde = 1e9;
  let parcialAnterior = "";
  while (salaVigilada === mia) {
    try {
      const s = await pedir(`presentacion/sala/${mia}?desde=${desde}`, {}, 8000);
      if (desde === 1e9) {
        turnoSala = s.turno;
      } else if (s.turno > turnoSala) {
        turnoSala = s.turno;
        if (s.respuesta && !escucha) {
          vuelta++;
          mostrarPregunta(s.pregunta);
          decir(s.respuesta, s.audio || null);
        }
      } else if (!escucha && Estado.actual !== "hablando") {
        if (s.estado === "escuchando") {
          if (Estado.actual !== "escuchando") {
            mostrarPregunta("");
            poner("escuchando");
            escuchaRemota = true;
          }
          if (s.parcial !== parcialAnterior) {
            pulsoRemoto = 0.8;
            parcialAnterior = s.parcial;
            mostrarEscuchado(s.parcial);
          }
        } else if (s.estado === "pensando" && Estado.actual !== "pensando") {
          mostrarPregunta(s.pregunta);
          $("respuesta").textContent = "";
          poner("pensando");
        } else if (s.estado === "listo" && escuchaRemota) {
          $("respuesta").textContent = "";
          poner("listo");
        } else if (s.estado === "error" && Estado.actual === "pensando") {
          avisar(TX.error);
        }
      }
      desde = turnoSala;
    } catch {
      /* un corte de red: se sigue intentando */
    }
    await dormir(Estado.actual === "escuchando" || Estado.actual === "pensando" ? 600 : 1000);
  }
}

function crearSala() {
  let codigo = "";
  const azar = crypto.getRandomValues(new Uint32Array(6));
  for (const n of azar) codigo += CARACTERES_SALA[n % CARACTERES_SALA.length];
  sala = codigo;
  history.replaceState(null, "", `${location.pathname}?sala=${codigo}`);
  pintarSala();
  vigilarSala();
}

function cerrarSala() {
  sala = "";
  salaVigilada = "";
  history.replaceState(null, "", location.pathname);
  pintarSala();
}

function enlaceControl() {
  return `${location.origin}${location.pathname}?sala=${sala}&modo=control`;
}

function pintarSala() {
  $("sala-vacia").hidden = Boolean(sala);
  $("sala-lista").hidden = !sala;
  if (!sala) return;
  $("codigo").textContent = sala;
  $("enlace").textContent = enlaceControl();
  const qr = $("qr");
  qr.textContent = "";
  if (window.qrcode) {
    const q = window.qrcode(0, "M");
    q.addData(enlaceControl());
    q.make();
    qr.innerHTML = q.createSvgTag(5, 20);
  }
}

/* -------------------------------------------------------------------------
 * Ajustes: micrófono, teléfono, charla
 * ---------------------------------------------------------------------- */

async function listarMicrofonos() {
  const sel = $("microfono");
  const elegido = leer("czu.pres.mic");
  let entradas = [];
  try {
    entradas = (await navigator.mediaDevices.enumerateDevices()).filter((d) => d.kind === "audioinput");
  } catch {
    /* sin permiso todavía */
  }
  sel.textContent = "";
  const sistema = new Option(TX.microfonoSistema, "");
  sel.add(sistema);
  entradas
    .filter((d) => d.deviceId && d.deviceId !== "default" && d.deviceId !== "communications")
    .forEach((d, i) => sel.add(new Option(d.label || `${TX.microfono} ${i + 1}`, d.deviceId)));
  sel.value = [...sel.options].some((o) => o.value === elegido) ? elegido : "";
}

let medidorVivo = false;
function moverMedidor() {
  if (medidorVivo) return;
  medidorVivo = true;
  const barra = $("medidor");
  const vuelta = () => {
    if ($("ajustes").hidden) {
      medidorVivo = false;
      return;
    }
    barra.style.width = `${Math.round(volumen(analizadorEntrada) * 100)}%`;
    requestAnimationFrame(vuelta);
  };
  requestAnimationFrame(vuelta);
}

function abrirAjustes(abrir = $("ajustes").hidden) {
  $("ajustes").hidden = !abrir;
  $("abrir-ajustes").setAttribute("aria-expanded", String(abrir));
  if (abrir) {
    listarMicrofonos();
    moverMedidor();
  }
}

function pantallaCompleta() {
  if (document.fullscreenElement) document.exitFullscreen?.();
  else document.documentElement.requestFullscreen?.().catch(() => {});
}

/* -------------------------------------------------------------------------
 * Que la pantalla no se apague, y que el mouse no estorbe
 * ---------------------------------------------------------------------- */

let bloqueo = null;
async function mantenerDespierta() {
  try {
    bloqueo = await navigator.wakeLock?.request("screen");
  } catch {
    /* sin permiso o sin soporte: el equipo decide */
  }
}
document.addEventListener("visibilitychange", () => {
  if (document.visibilityState === "visible" && bloqueo?.released !== false) mantenerDespierta();
});

let relojMouse = 0;
document.addEventListener("pointermove", (e) => {
  // Arrastrar un dedo no es «mover el mouse»: no hay controles que mostrar.
  if (e.pointerType === "touch") return;
  document.body.classList.add("mostrar-controles");
  document.body.classList.remove("sin-cursor");
  clearTimeout(relojMouse);
  relojMouse = setTimeout(() => {
    if (!$("ajustes").hidden) return;
    document.body.classList.remove("mostrar-controles");
    if (!modoControl) document.body.classList.add("sin-cursor");
  }, 2500);
});

/* -------------------------------------------------------------------------
 * Teclado: el de la computadora y el del control remoto de diapositivas
 * ---------------------------------------------------------------------- */

const TECLAS_HABLAR = new Set([" ", "Enter", "PageDown", "PageUp", "ArrowRight", "ArrowDown", "ArrowLeft", "ArrowUp", "b", "B", "."]);

document.addEventListener("keydown", (e) => {
  if (e.repeat || e.altKey || e.ctrlKey || e.metaKey) return;
  // Dentro de los ajustes, las teclas son de los ajustes.
  if (e.target.closest?.("#ajustes") && e.key !== "Escape") return;
  if (Estado.actual === "inicio") {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      $("empezar").click();
    }
    return;
  }
  if (TECLAS_HABLAR.has(e.key)) {
    e.preventDefault();
    alternarEscucha();
  } else if (e.key === "Escape") {
    if (!$("ajustes").hidden) abrirAjustes(false);
    cancelarEscucha();
    callar();
    if (Estado.actual !== "apagada") poner("listo");
  } else if (e.key === "f" || e.key === "F") {
    pantallaCompleta();
  } else if (e.key === "n" || e.key === "N") {
    nuevaCharla();
  } else if (e.key === "s" || e.key === "S") {
    abrirAjustes();
  }
});

/* -------------------------------------------------------------------------
 * Arranque
 * ---------------------------------------------------------------------- */

function textosFijos() {
  $("orbe").setAttribute("aria-label", TX.hablar);
  $("abrir-ajustes").setAttribute("aria-label", TX.ajustes);
  $("pantalla-completa").setAttribute("aria-label", TX.pantallaCompleta);
  $("et-microfono").textContent = TX.microfono;
  $("et-telefono").textContent = TX.telefono;
  $("crear-sala").textContent = TX.crearSala;
  $("cerrar-sala").textContent = TX.cerrarSala;
  $("nueva-charla").textContent = TX.nuevaCharla;
  $("atajos").textContent = TX.atajos;
  $("empezar").textContent = TX.empezar;
  $("inicio-titulo").textContent = modoControl ? `${TX.sala} ${sala}` : TX.marca;
  if (modoControl) {
    document.body.classList.add("modo-control");
    $("sala-chip").hidden = false;
    $("sala-chip").textContent = `${TX.sala} ${sala}`;
  }
}

async function empezar() {
  iniciarAudio();
  await audio?.resume?.().catch(() => {});
  // En Safari la voz del navegador sólo arranca si la primera vez fue en un toque.
  if (window.speechSynthesis) speechSynthesis.speak(new SpeechSynthesisUtterance(""));
  // El micrófono se abre ahora para que la esfera lo escuche (y para pedir el
  // permiso una sola vez, antes de que haya público mirando) SÓLO donde se
  // puede compartir: con la transcripción en el servidor —que es quien lo usa—
  // o en una computadora. En un celular el micrófono es de uno solo a la vez:
  // abrirlo para medirlo y a la vez para el dictado deja al dictado sordo, sin
  // ningún error. Ahí lo abre el dictado, y la esfera late con sus avisos.
  const midePropio = config.oido === "servidor" || config.oido === "tiempo_real" || (!modoControl && !esTactil);
  if (midePropio) {
    try {
      await abrirMicrofono();
      rastro("micrófono abierto para medirlo");
    } catch (e) {
      rastro("micrófono:", e?.name || e);
    }
  } else {
    rastro("micrófono sin abrir (lo abre el dictado)");
  }
  mantenerDespierta();
  Orbe.reproducir();
  $("inicio").classList.add("oculto");
  poner("listo");
  $("nota-microfono").textContent = config.oido === "navegador" ? TX.notaNavegador : "";
  pintarSala();
  vigilarSala();
}

async function arrancar() {
  textosFijos();
  try {
    config = { ...config, ...(await pedir("presentacion", {}, 10000)) };
  } catch {
    config.disponible = false;
  }

  rastro((navigator.userAgent || "").replace(/\).*/, ")"));
  rastro("oído:", config.oido, "· voz:", config.voz, "· dictado:", Reconocedor ? "sí" : "NO", "· táctil:", esTactil ? "sí" : "no", "· sala:", modoControl ? "control" : sala ? "pantalla" : "-");
  navigator.permissions?.query({ name: "microphone" }).then((p) => rastro("permiso del micrófono:", p.state)).catch(() => {});
  const boton = $("empezar");
  if (!config.disponible) {
    poner("apagada");
    $("inicio-titulo").textContent = TX.apagada;
    boton.textContent = TX.reintentar;
    boton.disabled = false;
    boton.addEventListener("click", () => location.reload(), { once: true });
    return;
  }
  boton.disabled = false;
  boton.addEventListener("click", empezar, { once: true });
  boton.focus();
}

// En la computadora, la esfera se toca (o se aprieta una tecla). En un
// teléfono se mantiene apretada la pantalla entera: ver «Mantener apretado».
if (mantener) {
  const escena = document.querySelector(".escena");
  escena.addEventListener("pointerdown", apretar);
  document.addEventListener("pointerup", soltar);
  document.addEventListener("pointercancel", soltar);
  // Un dedo apoyado un rato abre el menú de copiar o la lupa: acá no.
  escena.addEventListener("contextmenu", (e) => e.preventDefault());
} else {
  $("orbe").addEventListener("click", alternarEscucha);
}

// Si el teléfono se va a segundo plano con el dedo apoyado, no queda escuchando.
document.addEventListener("visibilitychange", () => {
  if (document.visibilityState !== "hidden" || !apretado) return;
  apretado = null;
  sostenida = false;
  cancelarEscucha();
  poner("listo");
});
$("abrir-ajustes").addEventListener("click", () => abrirAjustes());
$("pantalla-completa").addEventListener("click", pantallaCompleta);
$("crear-sala").addEventListener("click", crearSala);
$("cerrar-sala").addEventListener("click", cerrarSala);
$("nueva-charla").addEventListener("click", nuevaCharla);
$("microfono").addEventListener("change", async (e) => {
  guardar("czu.pres.mic", e.target.value);
  try {
    await abrirMicrofono();
  } catch {
    avisar(TX.sinMicrofono);
  }
});
window.speechSynthesis?.addEventListener?.("voiceschanged", () => {});

arrancar();
