// Lo que la página no puede traer escrito: dónde está la API y adónde llevan
// los botones de «Bajá la app». Lo arma el plugin en cada pedido y lo sirve
// como ajustes.json (ver CZUWIOS_Servidor::ajustes()); las tiendas se cargan
// en wp-admin → Web turismo → Enlaces a la app.
//
// Si el archivo no llega —la página abierta sin el plugin, para probarla—, se
// sigue con la API de producción y sin tiendas: nada se rompe, sólo no hay
// botón de descarga.

const RESPALDO = {
  version: "",
  api: "https://caaguazu.net/wp-json/czu-app/v1/",
  tiendas: { android: "", ios: "" },
};

let ajustes = RESPALDO;

/**
 * La carpeta de la web vista desde los módulos, para pedir lo que no es código
 * (textos, imágenes) por la MISMA dirección que el código y con su misma
 * versión. Importa por dónde se sirve: el plugin entrega el código directo
 * desde su carpeta, sin pasar por WordPress, y todo lo que se pide relativo a
 * la página (`textos/es.json`) iría por `/turismo/`, o sea por PHP y por la
 * base de datos —que es justo lo que se cae cuando el hosting está al
 * límite—.
 *
 * Este archivo vive en `js/`: la carpeta de la web es la de arriba. La versión
 * se toma de la propia URL del módulo (`config.js?v=2.0.2`); sin ella, como en
 * un servidor de prueba, se pide sin versión.
 */
const ESTE_MODULO = new URL(import.meta.url);
export function urlSitio(ruta) {
  const u = new URL("../" + ruta, ESTE_MODULO);
  const version = ESTE_MODULO.searchParams.get("v");
  u.search = version ? "?v=" + encodeURIComponent(version) : "";
  return u.href;
}

/** Los ajustes que el plugin dejó escritos en la página, o null si no hay. */
function ajustesDeLaPagina() {
  try {
    const el = document.getElementById("czu-ajustes");
    return el ? JSON.parse(el.textContent) : null;
  } catch {
    return null;
  }
}

/**
 * fetch() con tope de tiempo. Sin esto, un pedido que no responde deja la
 * página en blanco: el arranque espera a los ajustes y a los textos antes de
 * dibujar nada, y con mala señal «esperar» no termina nunca.
 */
export function conTiempo(url, opciones = {}, ms = 5000) {
  const control = new AbortController();
  const reloj = setTimeout(() => control.abort(), ms);
  return fetch(url, { ...opciones, signal: control.signal }).finally(() => clearTimeout(reloj));
}

function fusionar(j) {
  ajustes = {
    ...RESPALDO,
    ...j,
    tiendas: { ...RESPALDO.tiendas, ...(j.tiendas || {}) },
  };
}

export async function cargarAjustes() {
  // Lo normal: vienen en la página, sin pedir nada.
  const enLaPagina = ajustesDeLaPagina();
  if (enLaPagina) {
    fusionar(enLaPagina);
    return ajustes;
  }
  // Sin eso —la página abierta desde un servidor de archivos, o por un
  // plugin de antes— se piden aparte.
  try {
    const r = await conTiempo("ajustes.json", { cache: "no-cache" }, 4000);
    if (r.ok) fusionar(await r.json());
  } catch {
    /* sin el plugin o sin red: se queda el respaldo */
  }
  return ajustes;
}

export function ajuste() {
  return ajustes;
}

/** Qué teléfono es, para ofrecer primero la tienda que le sirve. */
export function plataforma() {
  const ua = navigator.userAgent || "";
  if (/android/i.test(ua)) return "android";
  // iPadOS se presenta como Mac: lo delata la pantalla táctil.
  if (/iphone|ipad|ipod/i.test(ua) || (/macintosh/i.test(ua) && navigator.maxTouchPoints > 1)) return "ios";
  return "otra";
}
