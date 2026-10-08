// Envoltorio fino sobre vendor/qrcode.js (Kazuhiko Arase, MIT). Se vendorea
// en vez de pedirlo a un servicio de terceros que generaria el QR en su
// servidor: la app evita servicios externos por decision explicita del
// proyecto, y esto es lo mismo aplicado a la version web.

export function crearQrSvg(texto, celda = 5) {
  // typeNumber 0 = que el propio generador elija el tamaño segun el texto.
  const qr = window.qrcode(0, "M");
  qr.addData(texto);
  qr.make();
  // Margen de 4 modulos: por debajo de eso un lector de camara real puede
  // no encontrar el codigo, aunque en pantalla se vea perfecto.
  return qr.createSvgTag(celda, celda * 4);
}
