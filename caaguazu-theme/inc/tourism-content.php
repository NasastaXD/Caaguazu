<?php
/**
 * Contenido de la sección Turismo, migrado desde el sitio de turismo aparte.
 * Texto real extraído y limpiado (sin Tailwind/React); jerarquía: 'turismo' es la
 * página raíz, luego las 6 secciones, luego sus subpáginas. Usado por inc/tourism-seeder.php.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_tourism_pages() {
	return array (
  'turismo' => 
  array (
    'old_slug' => '',
    'parent' => NULL,
    'title' => 'Turismo en Caaguazú',
    'excerpt' => 'Capital de la Madera del Paraguay: 181 años de historia, oficio maderero, gastronomía y cultura guaraní.',
    'body' => '<p>[gn]Ka\'a Guasu[/gn] significa monte grande en guaraní. Cuando los primeros pobladores llegaron en 1845, los bosques eran tan densos que llamaban a la región simplemente «el monte grande». Esa selva le dio el nombre a Caaguazú, la madera, y el oficio que todavía hoy la define.</p>
<h2>Destinos destacados</h2>
[promotur_destacados]
<div class="eco-grid">
<a class="eco-card" href="#tourism-link:la-capital-de-la-madera#"><div class="body"><span class="eco-tag">Historia y oficio</span><h3>La Capital de la Madera</h3><p class="desc">181 años de historia, especies madereras y los artesanos que sostienen el oficio.</p><span class="arrow">Descubrir →</span></div></a>
<a class="eco-card" href="#tourism-link:que-hacer#"><div class="body"><span class="eco-tag">Atractivos</span><h3>Qué hacer</h3><p class="desc">Ykua La Patria, el parque Techapyrã, la Iglesia Inmaculada Concepción y el Mercado de Abasto.</p><span class="arrow">Descubrir →</span></div></a>
<a class="eco-card" href="#tourism-link:sabores-de-caaguazu#"><div class="body"><span class="eco-tag">Gastronomía</span><h3>Sabores de Caaguazú</h3><p class="desc">Platos típicos, dónde comer y la cultura del mate y tereré.</p><span class="arrow">Descubrir →</span></div></a>
<a class="eco-card" href="#tourism-link:vivir-caaguazu#"><div class="body"><span class="eco-tag">Cultura</span><h3>Vivir Caaguazú</h3><p class="desc">Festividades, galería de imágenes y el guaraní que se escucha en la calle.</p><span class="arrow">Descubrir →</span></div></a>
<a class="eco-card" href="#tourism-link:planifica-tu-visita#"><div class="body"><span class="eco-tag">Info práctica</span><h3>Planificá tu visita</h3><p class="desc">Cómo llegar, dónde alojarte y la mejor época para venir.</p><span class="arrow">Descubrir →</span></div></a>
<a class="eco-card" href="#tourism-link:contacto#"><div class="body"><span class="eco-tag">Turismo</span><h3>Contacto</h3><p class="desc">Secretaría de Turismo de la Municipalidad de Caaguazú.</p><span class="arrow">Descubrir →</span></div></a>
</div>',
  ),
  'la-capital-de-la-madera' => 
  array (
    'old_slug' => 'la-capital-de-la-madera',
    'parent' => 'turismo',
    'title' => 'La Capital de la Madera — Caaguazú',
    'excerpt' => 'Cómo una ciudad se talló a sí misma durante 181 años. Historia, oficio, artesanos y especies madereras.',
    'body' => '<p>ACTO 1 — RAÍCES</p><h2>El Ka\'a Guasu original</h2><p>Las 11 familias junto al manantial. Los primeros rolleros y hacheros en la Picada de 7 Leguas, descrita por los cronistas como <em>«un túnel serpenteante abovedado por entretejidas ramas de perenne verdor»</em>.</p><p>ACTO 2 — TRONCO</p><h2>Auge, crisis y resiliencia</h2><p>Auge como primer productor nacional hacia 1970. Crisis de los \'90 por la deforestación intensiva del lapacho. Reinvención a través de la reforestación con eucalipto: diversificación sin perder el alma maderera.</p><p>ACTO 3 — RAMAS</p><h2>El presente del oficio</h2><p>90 aserraderos, 5.000 carpinterías, 10.000 familias. La cadena familiar: hombres ensamblan, mujeres pintan, hijos venden en casillas de la Ruta 7.</p><p>CRONOLOGÍA</p><h2>181 años en hitos</h2><ol><li>1706Merced Real<p>Las tierras conocidas como Ka\'a Guasú son adjudicadas a Don Cristóbal Villalba mediante Merced Real de 60 leguas.</p></li><li>1844Guardia del Empalado<p>Carlos Antonio López ordena establecer una guardia militar defensiva en la región ante incursiones bandeirantes.</p></li><li>1845Fundación oficial<p>8 de mayo. Once familias guaireñas se asientan junto al manantial Ykua La Patria. Nace Caaguazú.</p></li><li>1864-70Triple Alianza<p>Ykua La Patria sacia la sed de los soldados paraguayos durante la guerra más devastadora del continente.</p></li><li>1882Municipio<p>Caaguazú es formalmente establecida como municipio de la República.</p></li><li>1965Iglesia Inmaculada Concepción<p>Se construye el templo principal con sus 60 m² de murales de Jorge Aguirre.</p></li><li>≈1970Capital de la Madera<p>Caaguazú se consolida como el primer productor y exportador maderero del Paraguay.</p></li><li>1978Forestal Caaguazú S.A.<p>Se funda la primera empresa moderna de gestión forestal del distrito.</p></li><li>≈1990Crisis maderera<p>La deforestación intensiva del lapacho original obliga a repensar todo el modelo productivo.</p></li><li>1993Asociación de Madereros<p>Se formaliza el gremio que hoy agrupa a aserraderos y carpinterías del distrito.</p></li><li>≈2000Reforestación<p>Reactivación a través del cultivo planificado de eucalipto en ciclos de 7 a 10 años.</p></li><li>2026Hoy<p>181 años. 98.200 habitantes. 90 aserraderos. 5.000 carpinterías. 10.000 familias.</p></li></ol><p>ESPECIES</p><h2>Las maderas de Caaguazú</h2><h3>Lapacho</h3><p>Construcción, muebles finos</p><p>Muy duro y resistente a la intemperie. La madera histórica de Caaguazú, hoy escasa por la deforestación del siglo XX.</p><h3>Cedro</h3><p>Mueblería, imaginería religiosa</p><p>Madera noble, fácil de trabajar, aroma característico. Predilecta del artesano tradicional.</p><h3>Curupay</h3><p>Estructural, vigas</p><p>Resistencia mecánica excepcional. Usada cuando el lapacho ya no alcanza.</p><h3>Petereby</h3><p>Carpintería tradicional</p><p>Veteado distintivo, trabajabilidad media. Mueble doméstico y puertas.</p><h3>Eucalipto</h3><p>Reforestación, paletas, embalaje</p><p>La especie del presente. Ciclo de 7 a 10 años, base de la reactivación productiva post-2000.</p><blockquote>«En la ciudad de Caaguazú, el 80 % de la población tiene vinculación con la madera.»</blockquote><a href="#tourism-link:la-capital-de-la-madera/artesanos#">Conocé a los artesanos →</a><a href="#tourism-link:la-capital-de-la-madera/la-ruta-de-la-madera#">Recorré la Ruta de la Madera →</a>',
  ),
  'historia' => 
  array (
    'old_slug' => 'la-capital-de-la-madera/historia',
    'parent' => 'la-capital-de-la-madera',
    'title' => 'Historia de Caaguazú — 1845 hasta hoy',
    'excerpt' => 'Desde la Merced Real de 1706 hasta los 98.200 habitantes de hoy: 181 años de fundación, guerra, oficio y resiliencia.',
    'body' => '<h2>Antes del nombre</h2><p>En 1706 la Corona Española entregó por Merced Real 60 leguas de la región Ka\'a Guasú a Don Cristóbal Villalba. La selva era tan densa que los cronistas hablaban de «un túnel serpenteante abovedado por entretejidas ramas de perenne verdor» — la Picada de 7 Leguas, único acceso seguro.</p><h2>8 de mayo de 1845</h2><p>Once familias guaireñas se asentaron junto a un manantial cristalino. Lo llamaron Ykua La Patria. Ese mismo manantial, dos décadas después, saciaría la sed de los soldados paraguayos durante la Guerra de la Triple Alianza.</p><h2>Del monte al taller</h2><p>El siglo XX trajo los rolleros, los hacheros, los primeros aserraderos. Hacia 1970 Caaguazú ya era el primer productor maderero del país. La crisis del lapacho en los \'90 obligó a reinventarse: hoy el eucalipto plantado sostiene una cadena de 90 aserraderos y 5.000 carpinterías.</p><a href="#tourism-link:la-capital-de-la-madera#">← Volver al hub</a><a href="#tourism-link:que-hacer/ykua-la-patria#">Visitar Ykua La Patria →</a>',
  ),
  'la-ruta-de-la-madera' => 
  array (
    'old_slug' => 'la-capital-de-la-madera/la-ruta-de-la-madera',
    'parent' => 'la-capital-de-la-madera',
    'title' => 'La Ruta de la Madera — Caaguazú',
    'excerpt' => 'Sobre la Ruta 7, kilómetros de talleres abiertos donde la madera se trabaja, se exhibe y se vende en vivo.',
    'body' => '<h2>Qué vas a ver</h2><p>Aserraderos en funcionamiento, carpinteros ensamblando muebles, niños vendiendo juguetes de madera en casillas pintadas a mano. Sillas, mesas, roperos, parquets, puertas talladas — todo expuesto a cielo abierto, todo en venta.</p><h2>Cómo recorrerla</h2><ul><li>Tramo principal: aproximadamente del km 175 al km 185 de la Ruta 7.</li><li>Mejor horario: 9 a 17 h, cualquier día hábil.</li><li>En auto propio o en cualquier bus que cubra Asunción ↔ Ciudad del Este (pedí al chofer parada en Caaguazú).</li><li>Negociación habitual: regateá con respeto. Pagá en efectivo en guaraníes.</li></ul><a href="#tourism-link:planifica-tu-visita/como-llegar#">Cómo llegar →</a><a href="#tourism-link:la-capital-de-la-madera/artesanos#">Conocer artesanos →</a>',
  ),
  'artesania-y-oficios' => 
  array (
    'old_slug' => 'la-capital-de-la-madera/artesania-y-oficios',
    'parent' => 'la-capital-de-la-madera',
    'title' => 'Artesanía y oficios — Caaguazú',
    'excerpt' => 'Carpinteros, parqueteros, talladores, jugueteros: los oficios de la madera que sostienen a 10.000 familias caaguaceñas.',
    'body' => '<h2>La cadena familiar</h2><p>En Caaguazú la carpintería rara vez es una empresa: es un taller anexo a la casa, donde los oficios se reparten entre generaciones. Los hombres suelen ensamblar y cepillar. Las mujeres pintan y barnizan. Los hijos atienden la casilla a la orilla de la Ruta 7.</p><h2>Especialidades</h2><ul><li><strong>Mueblería tradicional</strong> — roperos, mesas y aparadores en cedro y petereby.</li><li><strong>Parquet</strong> — pisos de Caaguazú instalados en el Polideportivo Olimpia y obras de envergadura nacional.</li><li><strong>Juguetes</strong> — autitos, trompos, caballos de madera vendidos en las casillas de ruta.</li><li><strong>Imaginería religiosa</strong> — santos tallados en cedro para parroquias del departamento.</li></ul><a href="#tourism-link:la-capital-de-la-madera/artesanos#">Conocer artesanos →</a><a href="#tourism-link:la-capital-de-la-madera/la-ruta-de-la-madera#">Recorrer la Ruta →</a>',
  ),
  'artesanos' => 
  array (
    'old_slug' => 'la-capital-de-la-madera/artesanos',
    'parent' => 'la-capital-de-la-madera',
    'title' => 'Artesanos de Caaguazú — Los rostros de la madera',
    'excerpt' => 'Perfiles de los carpinteros, parqueteros y talladores que sostienen 181 años de oficio maderero.',
    'body' => '<p>Detrás de cada mueble, juguete o piso de Caaguazú hay una historia de oficio heredado. Conocé a los carpinteros, parqueteros, pintoras y jugueteros que sostienen 181 años de tradición maderera.</p>'
      . '<div class="eco-grid"><a class="eco-card" href="' . esc_url( get_post_type_archive_link( 'caaguazu_artisan' ) ) . '"><div class="body"><span class="eco-tag">Directorio</span><h3>Ver perfiles de artesanos</h3><p class="desc">Nombre, oficio, zona y la frase que los define — un directorio vivo, no una lista fija.</p><span class="arrow">Conocerlos →</span></div></a></div>',
  ),
  'que-hacer' => 
  array (
    'old_slug' => 'que-hacer',
    'parent' => 'turismo',
    'title' => 'Qué hacer en Caaguazú — Atractivos y experiencias',
    'excerpt' => 'Ykua La Patria, la Ruta de la Madera, el parque Techapyrã, la Iglesia Inmaculada Concepción y el Mercado de Abasto.',
    'body' => '<a href="#tourism-link:que-hacer/ykua-la-patria#"><img src="https://images.unsplash.com/photo-1502082553048-f009c37129b9?w=1600&auto=format&fit=crop&q=80" alt="Manantial natural rodeado de vegetación" loading="lazy"><p>Sitio fundacional</p><h3>Ykua La Patria</h3><p>El manantial donde nació la ciudad. Parque histórico con monolito conmemorativo de 1845 y aguas que saciaron a los soldados de la Triple Alianza.</p></a><a href="#tourism-link:la-capital-de-la-madera/la-ruta-de-la-madera#"><img src="https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?w=1200&auto=format&fit=crop&q=80" alt="Detalle de troncos y veta de madera apilada" loading="lazy"><p>Experiencia única</p><h3>La Ruta de la Madera</h3><p>Exposición permanente sobre la Ruta 7. Carpinteros trabajando en vivo a ambos lados del camino, talleres abiertos, juguetes y muebles a la vista.</p></a><a href="#tourism-link:que-hacer/patrimonio-religioso#"><img src="https://images.unsplash.com/photo-1548407260-da850faa41e3?w=1200&auto=format&fit=crop&q=80" alt="Fachada de iglesia católica al atardecer" loading="lazy"><p>Patrimonio religioso</p><h3>Inmaculada Concepción</h3><p>60 m² de murales del artista Jorge Aguirre. Iluminación monumental nocturna y misas patronales el 8 de diciembre.</p></a><a href="#tourism-link:que-hacer/mercado-municipal#"><img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=1200&auto=format&fit=crop&q=80" alt="Puesto de mercado con productos frescos" loading="lazy"><p>Vida local</p><h3>Mercado de Abasto</h3><p>Corazón comercial de la ciudad. Productos frescos del campo, comida rápida tradicional, hierbas medicinales y poha ñana.</p></a><a href="#tourism-link:que-hacer/parques-y-naturaleza#"><img src="https://images.unsplash.com/photo-1426604966848-d7adac402bff?w=1200&auto=format&fit=crop&q=80" alt="Parque verde con árboles altos" loading="lazy"><p>Familiar</p><h3>Parque Techapyrã</h3><p>Único parque del país con dinosaurios a escala real. 5 hectáreas a 1 km del centro, ideal para familias.</p></a>',
  ),
  'ykua-la-patria' => 
  array (
    'old_slug' => 'que-hacer/ykua-la-patria',
    'parent' => 'que-hacer',
    'title' => 'Ykua La Patria — El manantial fundacional de Caaguazú',
    'excerpt' => 'Sitio histórico y parque urbano donde nació la ciudad en 1845. Aguas que saciaron a los soldados de la Triple Alianza.',
    'body' => '<h2>Qué es</h2><p>Parque histórico y manantial natural en el corazón de Caaguazú. En el sitio se conserva el monolito conmemorativo de la fundación (1845) y un mirador hacia las aguas que dieron origen a la ciudad.</p><h2>Datos prácticos</h2><ul><li><strong>Dirección:</strong> Centro de Caaguazú · acceso libre</li><li><strong>Horario:</strong> Parque abierto todo el día</li><li><strong>Entrada:</strong> Gratis</li><li><strong>Accesibilidad:</strong> Senderos planos, apto para sillas de ruedas en la zona principal</li></ul><h2>Por qué importa</h2><p>Durante la Guerra de la Triple Alianza (1864–1870), los soldados paraguayos en marcha hacia el frente bebieron de estas aguas. El manantial pasó así de origen fundacional a símbolo nacional.</p><a href="#tourism-link:que-hacer#">← Otras atracciones</a><a href="#tourism-link:la-capital-de-la-madera/historia#">Conocer la historia →</a>',
  ),
  'patrimonio-religioso' => 
  array (
    'old_slug' => 'que-hacer/patrimonio-religioso',
    'parent' => 'que-hacer',
    'title' => 'Patrimonio religioso — Iglesia Inmaculada Concepción',
    'excerpt' => 'Templo principal de Caaguazú: 60 m² de murales de Jorge Aguirre e iluminación monumental nocturna.',
    'body' => '<h2>El templo</h2><p>Inaugurada en 1965, la Iglesia Inmaculada Concepción es el corazón espiritual de Caaguazú. Sus paredes interiores albergan 60 m² de murales del muralista paraguayo Jorge Aguirre — uno de los conjuntos pictóricos religiosos más significativos del interior del país.</p><h2>Cuándo visitarla</h2><ul><li><strong>Misas regulares:</strong> Domingos 7, 10 y 19 h</li><li><strong>Fiesta Patronal:</strong> 8 de diciembre — procesión, novenas y festivales</li><li><strong>Iluminación monumental:</strong> Todas las noches después de la puesta del sol</li></ul>',
  ),
  'mercado-municipal' => 
  array (
    'old_slug' => 'que-hacer/mercado-municipal',
    'parent' => 'que-hacer',
    'title' => 'Mercado de Abasto — Vida local en Caaguazú',
    'excerpt' => 'El mercado central: productos frescos, comida típica, hierbas medicinales y poha ñana.',
    'body' => '<h2>Qué encontrás</h2><ul><li><strong>Frescos del campo:</strong> mandioca, batata, zapallo, choclo, frutas estacionales.</li><li><strong>Comida rápida tradicional:</strong> empanadas, chipa guasu, mbeju, sopa paraguaya.</li><li><strong>Hierbas medicinales ([gn]poha ñana[/gn]):</strong> el saber ancestral guaraní en manos de yuyeras y yuyeros.</li><li><strong>Artesanía menor:</strong> guampas, bombillas, cestos.</li></ul><h2>Cuándo ir</h2><p>Lo más vivo es entre las 6 y las 10 h. Llevá billetes chicos y bolsa propia.</p>',
  ),
  'parques-y-naturaleza' => 
  array (
    'old_slug' => 'que-hacer/parques-y-naturaleza',
    'parent' => 'que-hacer',
    'title' => 'Parques y naturaleza — Caaguazú',
    'excerpt' => 'Parque Techapyrã, único en el país con dinosaurios a escala real, y espacios verdes para familias.',
    'body' => '<h2>Techapyrã</h2><p>Único parque del Paraguay con reproducciones de dinosaurios a escala real. Cinco hectáreas a un kilómetro del centro de Caaguazú — ideal para familias, escolares y fotografía.</p><h2>Datos prácticos</h2><ul><li><strong>Ubicación:</strong> A 1 km del centro de Caaguazú</li><li><strong>Horario:</strong> Martes a domingo, 9 a 18 h</li><li><strong>Mejor momento:</strong> Mañanas templadas, evitando el mediodía del verano</li><li><strong>Recomendado:</strong> Llevá agua, repelente y gorra</li></ul>',
  ),
  'sabores-de-caaguazu' => 
  array (
    'old_slug' => 'sabores-de-caaguazu',
    'parent' => 'turismo',
    'title' => 'Sabores de Caaguazú — Cocina típica paraguaya',
    'excerpt' => 'Ryguasu chyryry, sopa paraguaya, chipa guasu, mbeju, y la cultura del mate y tereré.',
    'body' => '<p>La mesa caaguaceña es el Paraguay rural en su versión más honesta: maíz, mandioca, gallina criolla, queso paraguay. Y en cada casa, sin excepción, la guampa y la yerba.</p><a href="#tourism-link:sabores-de-caaguazu/platos-tipicos#">Platos típicos →</a><a href="#tourism-link:sabores-de-caaguazu/donde-comer#">Dónde comer →</a><a href="#tourism-link:sabores-de-caaguazu/mate-y-terere#">Mate y tereré →</a>',
  ),
  'platos-tipicos' => 
  array (
    'old_slug' => 'sabores-de-caaguazu/platos-tipicos',
    'parent' => 'sabores-de-caaguazu',
    'title' => 'Platos típicos de Caaguazú',
    'excerpt' => 'Ryguasu chyryry, sopa paraguaya, chipa guasu, mbeju, vori vori: los platos imprescindibles.',
    'body' => '<h3>Ryguasu chyryry</h3><p>Pollo desmenuzado salteado con cebolla, locote y huevo revuelto. El sabor casero por excelencia.</p><h3>Sopa paraguaya</h3><p>No es sopa: es un pan denso de harina de maíz, queso paraguay y cebolla. Acompaña asados y pucheros.</p><h3>Chipa guasu</h3><p>Torta de maíz fresco con queso, leche y huevo. Servida tibia, cortada en cuadrados.</p><h3>Mbeju</h3><p>Tortilla de almidón de mandioca con queso y grasa. Crujiente por fuera, suave por dentro.</p><h3>Vori vori</h3><p>Sopa con bolitas de harina de maíz y queso, en caldo de gallina. Plato de invierno.</p><h3>Mandi\'o chyryry</h3><p>Mandioca cocida y salteada con cebolla y grasa. La guarnición universal.</p>',
  ),
  'donde-comer' => 
  array (
    'old_slug' => 'sabores-de-caaguazu/donde-comer',
    'parent' => 'sabores-de-caaguazu',
    'title' => 'Dónde comer en Caaguazú',
    'excerpt' => 'Lista de locales recomendados — desde puestos del mercado hasta restaurantes familiares.',
    'body' => '<p>Mercado</p><h3>Comedores del Mercado de Abasto</h3><p>Mejor desayuno-almuerzo de la ciudad. Empanadas, chipa, sopa paraguaya recién hecha.</p>$<p>Tradicional</p><h3>Comedor La Casona</h3><p>Cocina paraguaya clásica en porciones generosas. Imperdible el vori vori en invierno.</p>$$<p>Asador</p><h3>Parrilla Ruta 7</h3><p>Asado a la estaca, mandioca y sopa paraguaya. Sobre la Ruta 7, recomendado para grupos.</p>$$<p>Casero</p><h3>Doña Mirta</h3><p>Almuerzos caseros de lunes a viernes. Ryguasu chyryry los miércoles.</p>$<h2>Directorio de locales</h2>[caaguazu_locales tipo="restaurante"]',
  ),
  'mate-y-terere' => 
  array (
    'old_slug' => 'sabores-de-caaguazu/mate-y-terere',
    'parent' => 'sabores-de-caaguazu',
    'title' => 'Mate y tereré — La cultura de la ronda en Caaguazú',
    'excerpt' => 'Tereré al mediodía, mate al amanecer. La ronda es el ritual social más persistente del Paraguay.',
    'body' => '<h2>El [gn]tereré[/gn]</h2><p>Yerba mate bien tupida en la guampa, agua bien fría con hielo en el termo, y el añadido caaguaceño: hierbas frescas machacadas en mortero — menta, cedrón, kokũ, burrito. Cada familia tiene su combinación. Cada combinación es una declaración de pertenencia.</p><h2>El mate</h2><p>El mate aparece de madrugada, antes que el sol. Caliente, amargo, compartido. En invierno se prolonga toda la mañana. La guampa pasa de mano en mano siguiendo el sentido de las agujas del reloj.</p><h2>Reglas no escritas</h2><ul><li>Nunca se rechaza el primer convite — es una bienvenida.</li><li>«Gracias» significa «no quiero más». Decílo solo cuando termines.</li><li>No se mueve la bombilla con la mano.</li><li>El cebador es el último en tomar.</li></ul>',
  ),
  'vivir-caaguazu' => 
  array (
    'old_slug' => 'vivir-caaguazu',
    'parent' => 'turismo',
    'title' => 'Vivir Caaguazú — Cultura, festividades y vida cotidiana',
    'excerpt' => 'El guaraní en la calle, las fiestas patronales, las plazas. Cómo se vive Caaguazú cuando nadie está mirando.',
    'body' => '<p>Caaguazú no se entiende sin el guaraní en la conversación, sin la siesta sagrada, sin la peña del sábado. Esta sección es para acercarte a esos detalles.</p><a href="#tourism-link:vivir-caaguazu/guarani-en-nuestra-ciudad#">Guaraní →</a><a href="#tourism-link:vivir-caaguazu/festividades#">Festividades →</a><a href="#tourism-link:vivir-caaguazu/galeria#">Galería →</a>',
  ),
  'festividades' => 
  array (
    'old_slug' => 'vivir-caaguazu/festividades',
    'parent' => 'vivir-caaguazu',
    'title' => 'Festividades y calendario — Caaguazú',
    'excerpt' => 'Fiesta Patronal del 8 de diciembre, aniversario fundacional del 8 de mayo, peñas y tereré en la plaza.',
    'body' => '8 DIC<h3>Fiesta Patronal Inmaculada Concepción</h3><p>Procesión, misa en Ykua La Patria, festivales musicales y peñas folklóricas.</p>8 MAY<h3>Aniversario de Caaguazú</h3><p>Desfiles estudiantiles, festivales artísticos y conmemoración fundacional (181 años en 2026).</p>TODO EL AÑO<h3>Tereré en la plaza</h3><p>Cualquier tarde, cualquier plaza. La cita social que une la ciudad bajo cualquier sombra.</p>',
  ),
  'guarani-en-nuestra-ciudad' => 
  array (
    'old_slug' => 'vivir-caaguazu/guarani-en-nuestra-ciudad',
    'parent' => 'vivir-caaguazu',
    'title' => 'Guaraní en nuestra ciudad — Glosario caaguaceño',
    'excerpt' => 'Términos guaraní que vas a escuchar en Caaguazú: significado, pronunciación, contexto.',
    'body' => '<dl class="glossary"><div class="glossary-item"><dt>Ka\'a Guasu<span class="ipa">/kaˈʔa guaˈsu/</span></dt><dd>monte grande / selva grande <span class="ctx">El nombre original del territorio de Caaguazú.</span></dd></div><div class="glossary-item"><dt>Ykua<span class="ipa">/ɨˈkua/</span></dt><dd>manantial / fuente de agua <span class="ctx">Como en Ykua La Patria, el manantial fundacional.</span></dd></div><div class="glossary-item"><dt>Ryguasu chyryry<span class="ipa">/rɨguaˈsu tʃɨrɨˈrɨ/</span></dt><dd>gallina frita tradicional <span class="ctx">Plato emblema de Caaguazú.</span></dd></div><div class="glossary-item"><dt>Mandi\'o<span class="ipa">/manˈdiʔo/</span></dt><dd>mandioca / yuca</dd></div><div class="glossary-item"><dt>Tereré<span class="ipa">/tereˈre/</span></dt><dd>yerba mate fría <span class="ctx">El ritual social paraguayo por excelencia.</span></dd></div><div class="glossary-item"><dt>Poha ñana<span class="ipa">/poˈha ɲaˈna/</span></dt><dd>plantas medicinales</dd></div><div class="glossary-item"><dt>Techapyrã<span class="ipa">/tetʃapɨˈɾã/</span></dt><dd>mucho por ver</dd></div><div class="glossary-item"><dt>Sopa paraguaya<span class="ipa">/ˈsopa paɾaˈɣwaja/</span></dt><dd>pastel de maíz (no es sopa)</dd></div><div class="glossary-item"><dt>Vori vori<span class="ipa">/ˈvori ˈvori/</span></dt><dd>sopa con bolitas de maíz</dd></div><div class="glossary-item"><dt>Kesu<span class="ipa">/keˈsu/</span></dt><dd>queso fresco paraguayo</dd></div></dl>',
  ),
  'galeria' => 
  array (
    'old_slug' => 'vivir-caaguazu/galeria',
    'parent' => 'vivir-caaguazu',
    'title' => 'Galería — Caaguazú en imágenes',
    'excerpt' => 'Fotografías de la Capital de la Madera: oficios, paisaje, fiestas, gente.',
    'body' => '<div class="gallery-grid"><img src="https://images.unsplash.com/photo-1448375240586-882707db888b?w=1200&auto=format&fit=crop&q=80" alt="Bosque al amanecer" loading="lazy"><img src="https://images.unsplash.com/photo-1504148455328-c376907d081c?w=1200&auto=format&fit=crop&q=80" alt="Manos trabajando madera" loading="lazy"><img src="https://images.unsplash.com/photo-1437482078695-73f5ca6c96e2?w=1200&auto=format&fit=crop&q=80" alt="Río en el bosque" loading="lazy"><img src="https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?w=1200&auto=format&fit=crop&q=80" alt="Troncos apilados" loading="lazy"><img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=1200&auto=format&fit=crop&q=80" alt="Productos de mercado" loading="lazy"><img src="https://images.unsplash.com/photo-1548407260-da850faa41e3?w=1200&auto=format&fit=crop&q=80" alt="Iglesia al atardecer" loading="lazy"><img src="https://images.unsplash.com/photo-1426604966848-d7adac402bff?w=1200&auto=format&fit=crop&q=80" alt="Parque verde" loading="lazy"><img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1200&auto=format&fit=crop&q=80" alt="Dosel del bosque" loading="lazy"></div>',
  ),
  'planifica-tu-visita' => 
  array (
    'old_slug' => 'planifica-tu-visita',
    'parent' => 'turismo',
    'title' => 'Planificá tu visita — Caaguazú',
    'excerpt' => 'Cómo llegar, dónde alojarte, mejor época del año y mapa interactivo para visitar Caaguazú.',
    'body' => '<p>Caaguazú está a 180 km al este de Asunción y a 145 km al oeste de Ciudad del Este, sobre la Ruta 7 — la ruta más transitada del país.</p><a href="#tourism-link:planifica-tu-visita/como-llegar#">Cómo llegar →</a><a href="#tourism-link:planifica-tu-visita/donde-alojarte#">Dónde alojarte →</a><a href="#tourism-link:planifica-tu-visita/mejor-epoca#">Mejor época →</a><a href="#tourism-link:planifica-tu-visita/mapa-interactivo#">Mapa →</a>',
  ),
  'como-llegar' => 
  array (
    'old_slug' => 'planifica-tu-visita/como-llegar',
    'parent' => 'planifica-tu-visita',
    'title' => 'Cómo llegar a Caaguazú',
    'excerpt' => 'En auto o en bus desde Asunción y Ciudad del Este, sobre la Ruta 7.',
    'body' => '<h2>Desde Asunción</h2><ul><li><strong>Distancia:</strong> 180 km al este</li><li><strong>En auto:</strong> 2 h 30 min por Ruta 2 + Ruta 7</li><li><strong>En bus:</strong> Cualquier servicio Asunción ↔ Ciudad del Este, parada en Caaguazú. Frecuencia: cada 30–60 min.</li></ul><h2>Desde Ciudad del Este</h2><ul><li><strong>Distancia:</strong> 145 km al oeste</li><li><strong>En auto:</strong> 2 h por Ruta 7</li><li><strong>En bus:</strong> Mismos servicios troncales, frecuencia similar.</li></ul><h2>Una vez en la ciudad</h2><p>El centro se recorre a pie en 20 minutos. Para Techapyrã o la Ruta de la Madera conviene taxi o vehículo propio.</p>',
  ),
  'donde-alojarte' => 
  array (
    'old_slug' => 'planifica-tu-visita/donde-alojarte',
    'parent' => 'planifica-tu-visita',
    'title' => 'Dónde alojarte en Caaguazú',
    'excerpt' => 'Hoteles, hospedajes y opciones para todos los presupuestos en Caaguazú y alrededores.',
    'body' => '<p>Hotel</p><h3>Hotel Caaguazú Centro</h3><p>Sobre la avenida principal. Habitaciones standard, desayuno incluido.</p>$$<p>Hospedaje</p><h3>Hospedaje Ykua</h3><p>Familiar, a 4 cuadras del centro. Recomendado para viajeros solos.</p>$<p>Apart</p><h3>Apart Ruta 7</h3><p>Departamentos equipados sobre la ruta, ideal para estadías largas.</p>$$<p>Posada</p><h3>Posada del Bosque</h3><p>A 8 km de la ciudad, entornos verdes, ideal para familias.</p>$$$<p>Listado orientativo. Confirmá disponibilidad y tarifas directamente con cada establecimiento.</p><h2>Directorio de locales</h2>[caaguazu_locales tipo="hotel"]',
  ),
  'mejor-epoca' => 
  array (
    'old_slug' => 'planifica-tu-visita/mejor-epoca',
    'parent' => 'planifica-tu-visita',
    'title' => 'Mejor época para visitar Caaguazú',
    'excerpt' => 'Clima subtropical, estaciones marcadas y los meses ideales para disfrutar Caaguazú.',
    'body' => '<table><thead><tr><th>Temporada</th><th>Clima</th><th>Recomendación</th></tr></thead><tbody><tr><td>Verano (dic–feb)</td><td>Caluroso y húmedo (28–38 °C). Tormentas vespertinas frecuentes.</td><td>Empezá temprano, siesta larga, tereré obligado.</td></tr><tr><td>Otoño (mar–may)</td><td>Templado y estable (18–28 °C). La mejor época.</td><td>Ideal para Ruta de la Madera, Techapyrã y caminatas.</td></tr><tr><td>Invierno (jun–ago)</td><td>Fresco a frío (8–22 °C). Mañanas húmedas, tardes soleadas.</td><td>Llevá abrigo. Vori vori y mate al amanecer.</td></tr><tr><td>Primavera (sep–nov)</td><td>Tibio (18–30 °C). Floración del lapacho.</td><td>Septiembre — el lapacho en flor pinta la ciudad de rosa.</td></tr></tbody></table><p><strong>Veredicto:</strong> abril–mayo y septiembre–octubre son las ventanas óptimas.</p>',
  ),
  'mapa-interactivo' => 
  array (
    'old_slug' => 'planifica-tu-visita/mapa-interactivo',
    'parent' => 'planifica-tu-visita',
    'title' => 'Mapa de Caaguazú',
    'excerpt' => 'Mapa interactivo de la ciudad: atracciones, hoteles y servicios.',
    'body' => '<h2>Locales y negocios</h2>[caaguazu_mapa alto="480px"]<h2>Puntos históricos</h2>[caaguazu_mapa_puntos]<p class="map-credit">Mapa base: OpenStreetMap contributors.</p><ul><li>Ykua La Patria — centro</li><li>Iglesia Inmaculada Concepción — plaza central</li><li>Mercado de Abasto — a 4 cuadras del centro</li><li>Techapyrã — a 1 km del centro</li><li>Ruta de la Madera — Ruta 7, km 175–185</li></ul>',
  ),
  'contacto' => 
  array (
    'old_slug' => 'contacto',
    'parent' => 'turismo',
    'title' => 'Contacto — Turismo Caaguazú',
    'excerpt' => 'Contactá a la Secretaría de Turismo de la Municipalidad de Caaguazú.',
    'body' => '<h2>Secretaría de Turismo · Municipalidad de Caaguazú</h2><ul><li><strong>Dirección:</strong> Palacio Municipal, centro de Caaguazú</li><li><strong>Email:</strong><a>turismo@caaguazu.net</a></li><li><strong>Teléfono:</strong> +595 522 432 000</li><li><strong>Horario:</strong> Lunes a viernes 7:30–13:30</li></ul><a href="#tourism-link:home#">← Volver al inicio</a>',
  ),
);
}
