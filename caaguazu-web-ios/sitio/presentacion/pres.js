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
  listo: "Tocá para hablar",
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
  atajos: "Espacio o Enter: hablar · Esc: cortar · F: pantalla completa · N: nueva charla",
};

const $ = (id) => document.getElementById(id);
const dormir = (ms) => new Promise((r) => setTimeout(r, ms));
const quieto = matchMedia("(prefers-reduced-motion: reduce)");

/* -------------------------------------------------------------------------
 * Configuración y red
 * ---------------------------------------------------------------------- */

/** Dónde está la API: la deja escrita el plugin en la página. */
function direccionApi() {
  try {
    const el = document.getElementById("czu-ajustes");
    const j = el ? JSON.parse(el.textContent) : null;
    if (j && typeof j.api === "string" && j.api) return j.api;
  } catch {
    /* sin el plugin: la de producción */
  }
  return "https://caaguazu.net/wp-json/czu-app/v1/";
}
const API = direccionApi();

class ErrorApi extends Error {
  constructor(estado, codigo, esperaSeg = 0) {
    super(codigo || String(estado));
    this.estado = estado;
    this.codigo = codigo;
    this.esperaSeg = esperaSeg;
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
    throw new ErrorApi(r.status, cuerpo?.error?.codigo, espera);
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

/** Un color de pres.css como [r, g, b]. */
function colorDe(variable) {
  const v = getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
  const m = /^#?([0-9a-f]{6})$/i.exec(v);
  const n = m ? parseInt(m[1], 16) : 0x999999;
  return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
}

const rgba = (c, a) => `rgba(${c[0] | 0},${c[1] | 0},${c[2] | 0},${Math.max(0, Math.min(1, a))})`;

/*
 * Tres capas, de afuera hacia adentro:
 *
 *  1. HALO azul profundo, que se abre con la voz.
 *  2. CINTAS: tres haces de hilos finos que giran alrededor de la esfera y se
 *     deforman con ondas. Cada hilo es la misma curva un poco corrida, y
 *     sumados con «lighter» dan esa luz de cinta de seda. Con la voz, las
 *     ondas crecen y los hilos se abren.
 *  3. LA ESFERA: vidrio rosa y violeta con dos ondas de luz que se mueven
 *     adentro, un reflejo arriba y un anillo blanco que brilla en el borde.
 *
 * Todo sale de los colores de pres.css: cambiar la paleta es cambiar esas
 * líneas, no esto.
 */
const Orbe = (() => {
  const lienzo = $("lienzo");
  const ctx = lienzo.getContext("2d");
  const C = {
    halo: colorDe("--orbe-halo"),
    cinta: colorDe("--orbe-cinta"),
    anillo: colorDe("--orbe-anillo"),
    luz: colorDe("--orbe-luz"),
    rosa: colorDe("--orbe-rosa"),
    violeta: colorDe("--orbe-violeta"),
    hondo: colorDe("--orbe-hondo"),
  };
  // Cuánto gira y cuánto se agita en cada estado, sin contar la voz.
  const RITMO = { inicio: 0.25, apagada: 0.06, listo: 0.3, escuchando: 0.55, pensando: 1.3, hablando: 0.7 };
  const BASE = { inicio: 0.12, apagada: 0.03, listo: 0.18, escuchando: 0.32, pensando: 0.45, hablando: 0.3 };

  const CINTAS = 3;
  const HILOS = 14;
  const PASOS = 120;

  let t = 0;
  let nivel = 0;
  let energia = 0.15;
  let antes = performance.now();
  let alCuadro = null;

  function medir() {
    const caja = lienzo.getBoundingClientRect();
    const dpr = Math.min(2, window.devicePixelRatio || 1);
    const lado = Math.max(1, Math.round(caja.width * dpr));
    if (lienzo.width !== lado) {
      lienzo.width = lado;
      lienzo.height = lado;
    }
  }
  new ResizeObserver(medir).observe(lienzo);

  function cuadro(ahora) {
    const dt = Math.min(0.05, (ahora - antes) / 1000);
    antes = ahora;
    alCuadro?.(ahora);

    const e = Estado.actual;
    const objetivo = nivelObjetivo(e, ahora);
    nivel += (objetivo - nivel) * (objetivo > nivel ? 0.35 : 0.1);
    energia += ((BASE[e] ?? 0.18) + nivel * 0.7 - energia) * 0.08;

    const mueve = !quieto.matches;
    if (mueve) t += dt * (RITMO[e] ?? 0.3) * (1 + nivel * 1.2);
    dibujar(mueve ? energia : 0.25);
    requestAnimationFrame(cuadro);
  }

  /** El radio de una cinta en el ángulo `a`: ondas que viajan y un lado más abierto. */
  function radioCinta(R, a, k, j, E) {
    const fk = k * 2.1;
    const amp = 0.07 + 0.2 * E;
    const onda =
      Math.sin(2 * a + fk + t * (0.9 + k * 0.25) + j * 0.045) * 0.6 +
      Math.sin(3 * a - fk * 1.3 - t * (0.6 + k * 0.2) + j * 0.07) * 0.4;
    // Las cintas no son parejas: de un lado se abren y del otro se pegan a la
    // esfera, y ese lado gira despacio.
    const lado = 0.55 + 0.45 * Math.sin(a + t * 0.35 + k * 2.3);
    return R * (1.02 + k * 0.03 + j * 0.007 * (1 + E * 2) + amp * onda * lado);
  }

  function dibujar(E) {
    const S = lienzo.width;
    const c = S / 2;
    // El lienzo es 160% del botón; la esfera usa poco más de la mitad del
    // botón y el resto es lugar para las cintas y el halo.
    const R = S / 3.7;

    ctx.globalCompositeOperation = "source-over";
    ctx.clearRect(0, 0, S, S);

    // 1. Halo.
    // Termina antes del borde del lienzo y cae suave: si no, se ve el corte.
    const halo = ctx.createRadialGradient(c, c, R * 0.9, c, c, R * 1.8);
    const fuerza = 0.4 + 0.3 * E;
    [[0, 1], [0.2, 0.55], [0.45, 0.22], [0.7, 0.07], [1, 0]].forEach(([p, a]) => halo.addColorStop(p, rgba(C.halo, fuerza * a)));
    ctx.fillStyle = halo;
    ctx.fillRect(0, 0, S, S);

    // 2. Cintas, por detrás y por delante de la esfera a la vez: se dibujan
    // antes y la esfera las tapa en el centro.
    ctx.globalCompositeOperation = "lighter";
    ctx.lineWidth = Math.max(1, S / 520);
    for (let k = 0; k < CINTAS; k++) {
      for (let j = 0; j < HILOS; j++) {
        const medio = 1 - Math.abs(j / (HILOS - 1) - 0.5) * 2;
        ctx.strokeStyle = rgba(C.cinta, (0.1 + 0.16 * E) * (0.3 + 0.7 * medio));
        ctx.beginPath();
        for (let i = 0; i <= PASOS; i++) {
          const a = (i / PASOS) * Math.PI * 2;
          const r = radioCinta(R, a, k, j, E);
          const x = c + Math.cos(a) * r;
          const y = c + Math.sin(a) * r * (0.96 + 0.04 * Math.sin(t * 0.5 + k));
          if (i) ctx.lineTo(x, y);
          else ctx.moveTo(x, y);
        }
        ctx.stroke();
      }
    }

    // 3. La esfera.
    ctx.globalCompositeOperation = "source-over";
    ctx.save();
    ctx.beginPath();
    ctx.arc(c, c, R, 0, Math.PI * 2);
    ctx.clip();

    const cuerpo = ctx.createRadialGradient(c - R * 0.25, c - R * 0.35, R * 0.05, c + R * 0.1, c + R * 0.15, R * 1.15);
    cuerpo.addColorStop(0, rgba(C.luz, 1));
    cuerpo.addColorStop(0.45, rgba(C.rosa, 1));
    cuerpo.addColorStop(0.85, rgba(C.violeta, 1));
    cuerpo.addColorStop(1, rgba(C.hondo, 1));
    ctx.fillStyle = cuerpo;
    ctx.fillRect(c - R, c - R, R * 2, R * 2);

    // Dos ondas de luz que giran adentro, en sentidos contrarios.
    ctx.globalCompositeOperation = "screen";
    [
      { giro: t * 0.35, color: C.rosa, alfa: 0.55, alto: 0.22 },
      { giro: -t * 0.27 + 2.2, color: C.violeta, alfa: 0.5, alto: 0.3 },
    ].forEach(({ giro, color, alfa, alto }, n) => {
      ctx.save();
      ctx.translate(c, c);
      ctx.rotate(giro);
      ctx.beginPath();
      ctx.moveTo(-R * 1.2, R * 1.2);
      for (let i = 0; i <= 40; i++) {
        const x = -R * 1.2 + (i / 40) * R * 2.4;
        const y = Math.sin(x / R * 2.2 + t * (0.8 + n * 0.3)) * R * (alto + 0.08 * E);
        ctx.lineTo(x, y);
      }
      ctx.lineTo(R * 1.2, R * 1.2);
      ctx.closePath();
      const g = ctx.createLinearGradient(0, -R * 0.3, 0, R);
      g.addColorStop(0, rgba(color, alfa));
      g.addColorStop(1, rgba(color, 0));
      ctx.fillStyle = g;
      ctx.fill();
      ctx.restore();
    });

    // Sombra en el borde y reflejo arriba: es lo que la hace de vidrio.
    ctx.globalCompositeOperation = "source-over";
    const borde = ctx.createRadialGradient(c, c, R * 0.6, c, c, R);
    borde.addColorStop(0, "rgba(0,0,0,0)");
    borde.addColorStop(1, rgba(C.hondo, 0.55));
    ctx.fillStyle = borde;
    ctx.fillRect(c - R, c - R, R * 2, R * 2);
    const reflejo = ctx.createRadialGradient(c - R * 0.3, c - R * 0.45, 0, c - R * 0.3, c - R * 0.45, R * 0.5);
    reflejo.addColorStop(0, "rgba(255,255,255,0.22)");
    reflejo.addColorStop(1, "rgba(255,255,255,0)");
    ctx.fillStyle = reflejo;
    ctx.fillRect(c - R, c - R, R * 2, R * 2);
    ctx.restore();

    // El anillo que brilla en el borde: ancho y azul afuera, fino y blanco
    // en el centro, y apenas ondulado.
    ctx.globalCompositeOperation = "lighter";
    const capas = [
      { ancho: 0.08, color: C.halo, alfa: 0.16 + 0.14 * E },
      { ancho: 0.03, color: C.anillo, alfa: 0.35 + 0.25 * E },
      { ancho: 0.01, color: [255, 255, 255], alfa: 0.9 },
    ];
    for (const capa of capas) {
      ctx.lineWidth = R * capa.ancho;
      ctx.strokeStyle = rgba(capa.color, capa.alfa);
      ctx.beginPath();
      for (let i = 0; i <= PASOS; i++) {
        const a = (i / PASOS) * Math.PI * 2;
        const r = R * (1.005 + (0.01 + 0.02 * E) * Math.sin(3 * a + t * 1.4) * Math.sin(a - t * 0.6));
        const x = c + Math.cos(a) * r;
        const y = c + Math.sin(a) * r;
        if (i) ctx.lineTo(x, y);
        else ctx.moveTo(x, y);
      }
      ctx.closePath();
      ctx.stroke();
    }
    ctx.globalCompositeOperation = "source-over";
  }

  medir();
  requestAnimationFrame(cuadro);
  return {
    /** Algo que tiene que correr una vez por cuadro (los subtítulos). */
    set alCuadro(fn) {
      alCuadro = fn;
    },
  };
})();

/** De dónde sale el movimiento de la esfera en cada estado. */
let pulsoRemoto = 0;
function nivelObjetivo(estado, ahora) {
  if (estado === "escuchando") {
    // Escuchando desde el teléfono no hay micrófono acá: lo que late es el
    // texto que va llegando.
    if (escuchaRemota) {
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

function poner(estado, { mensaje = "", error = false } = {}) {
  Estado.actual = estado;
  if (estado !== "escuchando") escuchaRemota = false;
  const el = $("estado");
  const texto =
    mensaje ||
    {
      // La pantalla no dice nada en reposo: la esfera alcanza. El teléfono,
      // que es quien tiene que tocar, sí.
      listo: modoControl && hayOidoLocal() ? TX.listo : "",
      escuchando: TX.escuchando,
      pensando: TX.pensando,
      apagada: TX.apagada,
    }[estado] ||
    "";
  el.textContent = texto;
  el.classList.toggle("es-error", error);
  $("orbe").setAttribute("aria-pressed", String(estado === "escuchando"));
}

/** Un aviso que dura unos segundos y vuelve a «listo». */
let relojAviso = 0;
function avisar(mensaje, { error = true } = {}) {
  poner("listo", { mensaje, error });
  clearTimeout(relojAviso);
  relojAviso = setTimeout(() => {
    if (Estado.actual === "listo") poner("listo");
  }, 4000);
}

function mostrarPregunta(texto) {
  const el = $("pregunta");
  el.textContent = texto || "";
  el.classList.toggle("visible", Boolean(texto));
}

/** Lo que se va entendiendo mientras se habla, en gris. */
function mostrarEscuchado(texto) {
  const el = $("respuesta");
  el.classList.add("parcial");
  el.textContent = texto || "";
  el.scrollTop = el.scrollHeight;
}

/* --- Subtítulos que siguen a la voz ---------------------------------- */

let subtitulo = null; // { palabras: [span], cortes: [índice de carácter], dichas }

function prepararSubtitulo(texto) {
  const el = $("respuesta");
  el.classList.remove("parcial");
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
  return new Promise((listo) => {
    let terminado = false;
    const terminar = () => {
      if (terminado) return;
      terminado = true;
      cortarVoz = null;
      decirHasta(Infinity);
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
  return config.oido === "servidor" ? Boolean(window.MediaRecorder && navigator.mediaDevices) : Boolean(Reconocedor);
}

let escucha = null; // { detener(), cancelar() }

function alternarEscucha() {
  if (Estado.actual === "inicio" || Estado.actual === "apagada") return;
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
  if (config.oido === "servidor") escucharConServidor();
  else escucharConNavegador();
}

function cancelarEscucha() {
  escucha?.cancelar();
  escucha = null;
}

/* --- Avisos al otro lado de la sala (sólo desde el teléfono) ------------ */

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

function escucharConNavegador(reintento = false) {
  const rec = new Reconocedor();
  rec.lang = config.idioma;
  rec.interimResults = true;
  rec.continuous = false;
  rec.maxAlternatives = 1;

  let final = "";
  let parcial = "";
  let fallo = "";
  let cancelada = false;

  rec.onresult = (e) => {
    parcial = "";
    for (let i = e.resultIndex; i < e.results.length; i++) {
      const r = e.results[i];
      if (r.isFinal) final += r[0].transcript;
      else parcial += r[0].transcript;
    }
    const visto = `${final} ${parcial}`.trim();
    mostrarEscuchado(visto);
    avisarSala("escuchando", visto);
  };
  rec.onerror = (e) => {
    fallo = e.error || "error";
  };
  rec.onend = () => {
    escucha = null;
    if (cancelada) return;
    // Un idioma que este navegador no tiene: se prueba con el castellano
    // genérico antes de rendirse.
    if (fallo === "language-not-supported" && !reintento) {
      config.idioma = "es-ES";
      escucharConNavegador(true);
      return;
    }
    const texto = (final || parcial).trim();
    if (texto) {
      preguntar(texto);
      return;
    }
    avisarSala("listo");
    $("respuesta").textContent = "";
    if (fallo === "not-allowed" || fallo === "service-not-allowed" || fallo === "audio-capture") avisar(TX.sinMicrofono);
    else if (fallo === "network") avisar(TX.sinRed);
    else avisar(TX.noEscuche, { error: false });
  };

  escucha = {
    detener: () => rec.stop(),
    cancelar: () => {
      cancelada = true;
      rec.abort();
      avisarSala("listo");
    },
  };
  mostrarPregunta("");
  mostrarEscuchado("");
  poner("escuchando");
  avisarSala("escuchando", "");
  try {
    rec.start();
  } catch {
    escucha = null;
    avisar(TX.sinMicrofono);
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
    } else if (oyoVoz && v < 0.06) {
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
    try {
      const r = await pedir("presentacion/oir", { method: "POST", body: datos }, 30000);
      const texto = String(r.texto || "").trim();
      if (!texto) {
        avisarSala("listo");
        avisar(TX.noEscuche, { error: false });
        return;
      }
      preguntar(texto);
    } catch (e) {
      avisarSala("listo");
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
document.addEventListener("pointermove", () => {
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
  // permiso una sola vez, antes de que haya público mirando). En el
  // teléfono con el dictado del navegador no hace falta: lo abre el dictado.
  if (!modoControl || config.oido === "servidor") {
    try {
      await abrirMicrofono();
    } catch {
      /* se avisa cuando se intente hablar */
    }
  }
  mantenerDespierta();
  $("inicio").classList.add("oculto");
  poner("listo");
  $("nota-microfono").textContent = config.oido === "servidor" ? "" : TX.notaNavegador;
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

$("orbe").addEventListener("click", alternarEscucha);
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
