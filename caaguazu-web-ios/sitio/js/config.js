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
 * fetch() con tope de tiempo. Sin esto, un pedido que no responde deja la
 * página en blanco: el arranque espera a los ajustes y a los textos antes de
 * dibujar nada, y con mala señal «esperar» no termina nunca.
 */
export function conTiempo(url, opciones = {}, ms = 5000) {
  const control = new AbortController();
  const reloj = setTimeout(() => control.abort(), ms);
  return fetch(url, { ...opciones, signal: control.signal }).finally(() => clearTimeout(reloj));
}

export async function cargarAjustes() {
  try {
    const r = await conTiempo("ajustes.json", { cache: "no-cache" }, 4000);
    if (r.ok) {
      const j = await r.json();
      ajustes = {
        ...RESPALDO,
        ...j,
        tiendas: { ...RESPALDO.tiendas, ...(j.tiendas || {}) },
      };
    }
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
