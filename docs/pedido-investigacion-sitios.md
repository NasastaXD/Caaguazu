# Pedido de investigación: los sitios turísticos de Caaguazú

> Este documento se le pasa entero a una herramienta de investigación profunda
> (Gemini Deep Research, Claude Research). Lo que devuelva se carga en el panel
> como **datos de prueba**: entran como borradores marcados «prueba», los ven
> sólo los profesores y se borran todos juntos con un botón. Nada de esto se
> publica sin que una persona lo revise.

---

## Lo que te pido

Investigá a fondo el **distrito de Caaguazú**, en el departamento de Caaguazú,
Paraguay. Es el municipio cuya cabecera es la ciudad de Caaguazú, sobre la
Ruta PY02, a unos 180 km de Asunción, y tiene unos 942 km². **No es el
departamento entero**: no incluyas Coronel Oviedo, J. Eulogio Estigarribia,
Repatriación ni otros distritos, salvo que un sitio esté en el límite y lo
aclares.

Quiero **todos los lugares y eventos que un visitante podría querer conocer**,
con datos que se puedan comprobar. La entrega es un **archivo JSON** con la
forma que está más abajo, más un resumen corto de qué encontraste y qué no.

### Qué buscar

Por lo menos esto, y todo lo que encuentres además:

1. **Naturaleza**: arroyos, saltos, balnearios, reservas, parques, miradores,
   cerros, lagunas, montes nativos.
2. **Historia y patrimonio**: el Ykua La Patria (manantial fundacional), casas
   antiguas, monumentos, plazas, museos, sitios de la fundación de 1845, la
   estación o vestigios del ferrocarril si los hay.
3. **Religioso**: la iglesia Inmaculada Concepción y otras capillas o
   santuarios con valor histórico o con fiesta patronal propia.
4. **La madera**: Caaguazú se presenta como la «Capital de la Madera».
   Aserraderos que se puedan visitar, talleres de artesanos, ferias, mueblerías
   tradicionales, la Ruta de la Madera si existe como circuito.
5. **Comida**: restaurantes, comedores, parrillas, lugares de comida típica
   (chipa, sopa paraguaya, mbejú, chipa guasu, tereré), el Mercado de Abasto.
6. **Alojamiento**: hoteles, posadas, estancias turísticas, campings.
7. **Compras y artesanía**: mercados, ferias, tiendas de artesanía local.
8. **Eventos y festividades** del año: fiesta patronal, aniversario de la
   ciudad, festivales, ferias de la madera, expos, carreras, eventos
   culturales. Con **fecha o época del año** de cada uno.
9. **Colonias y compañías rurales** del distrito con algo para ver (colonias
   de inmigrantes, comunidades indígenas que reciban visitas **sólo si
   publicaron ellas mismas que lo hacen**).

### Las reglas

- **No inventes nada.** Un dato que no encontraste va como `null`. Prefiero
  veinte sitios con datos verdaderos que sesenta con datos supuestos.
- **Cada sitio lleva sus fuentes**: las URLs de donde salió cada dato (sitio
  oficial de la municipalidad, SENATUR, diarios paraguayos —ABC Color, Última
  Hora, La Nación—, páginas oficiales de los propios lugares, Google Maps,
  OpenStreetMap, Wikipedia).
- **Ubicación exacta**: latitud y longitud con 5 decimales como mínimo, y el
  enlace de Google Maps del lugar si existe. Si sólo sabés la ubicación
  aproximada, decilo en `ubicacion_precision`.
- **Datos que cambian** (horario, precio, teléfono): poné la fecha de la fuente
  en `fecha_dato`. Un horario de 2019 sirve, pero hay que saber que es de 2019.
- **Fotos**: nada de bancos de imágenes. Sólo fotos **con licencia libre**
  (Wikimedia Commons, Flickr con Creative Commons) o del sitio oficial del
  propio lugar, con autor y licencia. Si no hay, `fotos: []`. No pongas fotos
  de otros lugares «parecidos».
- **Descripción**: dos o tres oraciones, en castellano, concretas. Qué es, por
  qué ir y un dato que lo distinga. Sin adjetivos de folleto («paradisíaco»,
  «imperdible», «mágico»).
- **Nombres propios** tal como los usa la gente del lugar, con la grafía
  guaraní correcta si la tienen (Ykua, Yvytu, etc.).
- Si un lugar **cerró**, no lo incluyas; si no podés saber si sigue abierto,
  incluilo con `"vigencia": "dudosa"`.

---

## La forma de la entrega

Un solo JSON, con esta estructura. Las claves se respetan tal cual (son las
que lee el importador del panel; los valores del ejemplo son inventados, sólo
muestran la forma). Lo que no tengas va como `null` o
lista vacía, nunca como texto del tipo «no disponible».

```json
{
  "generado": "2026-10-12",
  "herramienta": "Gemini Deep Research",
  "resumen": "Qué encontraste, qué categorías quedaron flojas y por qué.",
  "sitios": [
    {
      "titulo": "Ykua La Patria",
      "tipo": "sitio",
      "categoria": "Historia y patrimonio",
      "etiquetas": ["gratis", "al aire libre", "fundación"],
      "descripcion": "Dos o tres oraciones concretas.",

      "lat": -25.00000,
      "lng": -56.00000,
      "ubicacion_precision": "exacta",
      "google_maps": "https://maps.app.goo.gl/…",
      "direccion": "Calle, barrio o referencia",
      "compania_o_barrio": "Centro",
      "estado_camino": "asfalto",

      "horario": "Lunes a domingo, de 7 a 18",
      "costo": "Gratis",
      "rango_precio": 0,
      "contacto": "+595 … / correo / página",
      "web": "https://…",
      "redes": ["https://facebook.com/…"],

      "fechas": null,

      "fotos": [
        {
          "url": "https://upload.wikimedia.org/…jpg",
          "autor": "Nombre del autor",
          "licencia": "CC BY-SA 4.0",
          "pagina": "https://commons.wikimedia.org/wiki/File:…"
        }
      ],

      "fuentes": ["https://…", "https://…"],
      "fecha_dato": "2025-03",
      "vigencia": "confirmada",
      "notas": "Lo que haga falta saber y no entre arriba."
    }
  ]
}
```

### Qué va en cada campo

| Campo | Valores | Nota |
| --- | --- | --- |
| `tipo` | `"sitio"` o `"evento"` | Un evento es algo que pasa en una fecha. |
| `categoria` | uno de la lista de abajo | Una sola. |
| `etiquetas` | lista de 1 a 4 palabras cortas, en minúscula | Para filtrar: `gratis`, `con chicos`, `al aire libre`, `accesible`, `de noche`… |
| `ubicacion_precision` | `"exacta"`, `"aproximada"`, `"desconocida"` | |
| `estado_camino` | `"asfalto"`, `"ripio"`, `"tierra"` o `null` | El último tramo para llegar. |
| `rango_precio` | `0` gratis · `1` barato · `2` medio · `3` caro · `4` muy caro · `null` | Con relación a Paraguay. |
| `fechas` | `null` en un sitio; en un evento, el objeto de abajo | |
| `vigencia` | `"confirmada"`, `"dudosa"` | |

**Categorías** (usá exactamente estos nombres):
`Naturaleza` · `Historia y patrimonio` · `Religioso` · `Madera y artesanía` ·
`Gastronomía` · `Alojamiento` · `Compras` · `Eventos y fiestas` ·
`Servicios al visitante`.

**Fechas de un evento**:

```json
"fechas": {
  "inicio": "2026-12-08T08:00:00-03:00",
  "fin": "2026-12-08T22:00:00-03:00",
  "recurrencia": "anual, cada 8 de diciembre",
  "proxima_confirmada": false
}
```

Si sólo sabés la época («primera quincena de agosto»), poné la fecha más
probable de la próxima edición, `proxima_confirmada: false` y la época en
`recurrencia`.

---

## Además, aparte del JSON

Al final, en texto:

1. **Datos generales del distrito**, con fuente: población, fecha de
   fundación, fiestas oficiales, cómo llegar desde Asunción y desde Ciudad del
   Este (ómnibus, empresas, tiempo de viaje), clima por estación, números de
   emergencia locales (policía, bomberos, hospital regional).
2. **Lo que no pudiste confirmar**: sitios que aparecen nombrados pero sin
   ubicación ni fuente firme, para que alguien del equipo vaya a verlos.
3. **Fuentes que conviene seguir**: páginas oficiales o cuentas que publican la
   agenda de eventos del distrito.
