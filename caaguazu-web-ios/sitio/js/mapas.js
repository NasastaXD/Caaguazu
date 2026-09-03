// Salidas hacia Google Maps. Calcado de MapasExternos.kt: se delega en la app
// de mapas del telefono en vez de construir navegacion propia.

export const MAX_PARADAS_INTERMEDIAS = 9;

export function enlacePunto(lat, lng, nombre) {
  return `https://www.google.com/maps/search/?api=1&query=${lat},${lng}${
    nombre ? `(${encodeURIComponent(nombre)})` : ""
  }`;
}

/** Devuelve null si el recorrido no entra en un solo enlace (mas de 9 paradas
 * intermedias): quien llama decide como avisar. */
export function enlaceRecorrido(puntos, aPie = true) {
  if (puntos.length < 2) return null;
  if (puntos.length - 2 > MAX_PARADAS_INTERMEDIAS) return null;

  const origen = puntos[0];
  const destino = puntos[puntos.length - 1];
  const medio = puntos.slice(1, -1);

  const params = new URLSearchParams({
    api: "1",
    origin: `${origen.lat},${origen.lng}`,
    destination: `${destino.lat},${destino.lng}`,
    travelmode: aPie ? "walking" : "driving",
  });
  if (medio.length > 0) {
    params.set("waypoints", medio.map((p) => `${p.lat},${p.lng}`).join("|"));
  }
  return `https://www.google.com/maps/dir/?${params.toString()}`;
}
