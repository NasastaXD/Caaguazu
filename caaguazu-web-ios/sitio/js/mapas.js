// Salidas hacia Google Maps. Calcado de MapasExternos.kt: se delega en la
// app de mapas del teléfono en vez de construir navegación propia.
//
// Hasta 1.2.0 esto también armaba la ruta de un recorrido entero. Los
// recorridos se mudaron a la app (ver pantallas/app.js), y con ellos eso.

export function enlacePunto(lat, lng, nombre) {
  return `https://www.google.com/maps/search/?api=1&query=${lat},${lng}${
    nombre ? `(${encodeURIComponent(nombre)})` : ""
  }`;
}
