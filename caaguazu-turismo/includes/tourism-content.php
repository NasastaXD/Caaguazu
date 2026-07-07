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
    'excerpt' => 'Información turística de Caaguazú: historia, oficio maderero, gastronomía y cultura guaraní.',
    'body' => '<div class="stats-grid stats-grid--compact tourism-stats">
<div class="stat"><span class="stat-num">181</span><span class="stat-label">años de historia</span></div>
<div class="stat"><span class="stat-num">90</span><span class="stat-label">aserraderos activos</span></div>
<div class="stat"><span class="stat-num">5.000</span><span class="stat-label">carpinterías</span></div>
</div>
<p>El nombre Caaguazú proviene del guaraní [gn]Ka\'a Guasu[/gn], que significa monte grande. La ciudad fue fundada en 1845 junto a un manantial rodeado de bosque denso. La actividad maderera, presente desde los orígenes del poblado, define hasta hoy la identidad productiva del departamento.</p>
<h2>Secciones</h2>
[promotur_destacados]
<div class="eco-grid">
<a class="eco-card" href="#tourism-link:la-capital-de-la-madera#"><div class="body"><span class="eco-tag">Historia y oficio</span><h3>La Capital de la Madera</h3><p class="desc">Historia, especies madereras y artesanos del departamento.</p><span class="arrow">Ver sección</span></div></a>
<a class="eco-card" href="#tourism-link:que-hacer#"><div class="body"><span class="eco-tag">Atractivos</span><h3>Qué hacer</h3><p class="desc">Ykua La Patria, el parque Techapyrã, la Iglesia Inmaculada Concepción y el Mercado de Abasto.</p><span class="arrow">Ver sección</span></div></a>
<a class="eco-card" href="#tourism-link:platos-tipicos#"><div class="body"><span class="eco-tag">Gastronomía</span><h3>Sabores de Caaguazú</h3><p class="desc">Platos típicos, dónde comer y la cultura del mate y tereré.</p><span class="arrow">Ver sección</span></div></a>
<a class="eco-card" href="#tourism-link:festividades#"><div class="body"><span class="eco-tag">Cultura</span><h3>Vivir Caaguazú</h3><p class="desc">Festividades, galería de imágenes y glosario de guaraní.</p><span class="arrow">Ver sección</span></div></a>
<a class="eco-card" href="#tourism-link:como-llegar#"><div class="body"><span class="eco-tag">Info práctica</span><h3>Planificá tu visita</h3><p class="desc">Cómo llegar, dónde alojarte y la mejor época para venir.</p><span class="arrow">Ver sección</span></div></a>
<a class="eco-card" href="#tourism-link:contacto#"><div class="body"><span class="eco-tag">Turismo</span><h3>Contacto</h3><p class="desc">Secretaría de Turismo de la Municipalidad de Caaguazú.</p><span class="arrow">Ver sección</span></div></a>
</div>',
  ),
  'la-capital-de-la-madera' =>
  array (
    'old_slug' => 'la-capital-de-la-madera',
    'parent' => 'turismo',
    'title' => 'La Capital de la Madera — Caaguazú',
    'excerpt' => 'Historia, oficio maderero, artesanos y especies madereras de Caaguazú.',
    'body' => '<h2>Orígenes</h2><p>Once familias se establecieron junto a un manantial en 1845. Los primeros trabajadores madereros de la zona —rolleros y hacheros— operaban sobre la Picada de 7 Leguas, entonces la única vía de acceso a la región, descrita por cronistas de la época como <em>«un túnel serpenteante abovedado por entretejidas ramas de perenne verdor»</em>.</p><h2>Evolución del sector</h2><p>Hacia 1970, Caaguazú se consolidó como el primer productor maderero del país. En la década de 1990, la deforestación intensiva del lapacho provocó una crisis en el sector. La actividad se recuperó mediante la reforestación planificada con eucalipto, lo que permitió diversificar la producción sin abandonar el oficio maderero.</p><h2>Situación actual</h2><p>El departamento cuenta hoy con 90 aserraderos y 5.000 carpinterías, que dan sustento a unas 10.000 familias. En muchos talleres el trabajo se organiza por núcleo familiar: los hombres arman las piezas, las mujeres realizan el pintado y barnizado, y los hijos participan en la venta en los puestos sobre la Ruta 7.</p><h2>Cronología</h2><ol><li>1706 — Merced Real<p>Las tierras conocidas como Ka\'a Guasú son adjudicadas a Don Cristóbal Villalba mediante Merced Real de 60 leguas.</p></li><li>1844 — Guardia del Empalado<p>Carlos Antonio López ordena establecer una guardia militar defensiva en la región ante incursiones bandeirantes.</p></li><li>1845 — Fundación oficial<p>8 de mayo. Once familias guaireñas se asientan junto al manantial Ykua La Patria. Se funda Caaguazú.</p></li><li>1864-70 — Guerra de la Triple Alianza<p>El manantial de Ykua La Patria abastece de agua a los soldados paraguayos durante el conflicto.</p></li><li>1882 — Municipio<p>Caaguazú es formalmente establecida como municipio de la República.</p></li><li>1965 — Iglesia Inmaculada Concepción<p>Se construye el templo principal, con 60 m² de murales del artista Jorge Aguirre.</p></li><li>≈1970 — Capital de la Madera<p>Caaguazú se consolida como el primer productor y exportador maderero del Paraguay.</p></li><li>1978 — Forestal Caaguazú S.A.<p>Se funda la primera empresa moderna de gestión forestal del distrito.</p></li><li>≈1990 — Crisis maderera<p>La deforestación intensiva del lapacho original obliga a reorganizar el modelo productivo.</p></li><li>1993 — Asociación de Madereros<p>Se formaliza el gremio que agrupa a aserraderos y carpinterías del distrito.</p></li><li>≈2000 — Reforestación<p>Se reactiva la producción mediante el cultivo planificado de eucalipto, en ciclos de 7 a 10 años.</p></li><li>2026 — Actualidad<p>181 años de fundación. 98.200 habitantes. 90 aserraderos. 5.000 carpinterías. 10.000 familias vinculadas al sector.</p></li></ol><h2>Especies madereras</h2><div class="info-grid">'
      . '<div class="info-card"><h3>Lapacho</h3><span class="meta">Construcción, muebles finos</span><p>Madera muy dura y resistente a la intemperie. Fue históricamente la más utilizada en Caaguazú; hoy es escasa debido a la deforestación del siglo XX.</p></div>'
      . '<div class="info-card"><h3>Cedro</h3><span class="meta">Mueblería, imaginería religiosa</span><p>Madera de trabajo sencillo y aroma característico. Es la especie más utilizada por el artesano tradicional.</p></div>'
      . '<div class="info-card"><h3>Curupay</h3><span class="meta">Estructural, vigas</span><p>Resistencia mecánica alta. Se utiliza en reemplazo del lapacho cuando la demanda supera la disponibilidad.</p></div>'
      . '<div class="info-card"><h3>Petereby</h3><span class="meta">Carpintería tradicional</span><p>Veteado distintivo y trabajabilidad media. Se emplea en muebles domésticos y puertas.</p></div>'
      . '<div class="info-card"><h3>Eucalipto</h3><span class="meta">Reforestación, paletas, embalaje</span><p>Especie utilizada en la reforestación planificada. Su ciclo de 7 a 10 años sostiene la producción maderera desde el año 2000.</p></div>'
      . '</div><blockquote>«En la ciudad de Caaguazú, el 80 % de la población tiene vinculación con la madera.»</blockquote><div class="cta-row"><a class="btn btn-primary" href="#tourism-link:la-capital-de-la-madera/artesanos#">Ver artesanos →</a><a class="btn btn-outline" href="#tourism-link:la-capital-de-la-madera/la-ruta-de-la-madera#">Ver la Ruta de la Madera →</a></div>',
  ),
  'historia' =>
  array (
    'old_slug' => 'la-capital-de-la-madera/historia',
    'parent' => 'la-capital-de-la-madera',
    'title' => 'Historia de Caaguazú — 1845 hasta hoy',
    'excerpt' => 'Desde la Merced Real de 1706 hasta la actualidad: fundación, guerra, oficio maderero y desarrollo del departamento.',
    'body' => '<h2>Antes de la fundación</h2><p>En 1706 la Corona Española entregó, mediante Merced Real, 60 leguas de la región conocida como Ka\'a Guasú a Don Cristóbal Villalba. El acceso a la zona se realizaba por la Picada de 7 Leguas, un camino a través de bosque denso descrito por los cronistas de la época.</p><h2>8 de mayo de 1845</h2><p>Once familias guaireñas se asentaron junto a un manantial. Lo llamaron Ykua La Patria. Ese mismo manantial abasteció de agua a los soldados paraguayos durante la Guerra de la Triple Alianza, dos décadas después.</p><h2>Desarrollo del oficio maderero</h2><p>Durante el siglo XX se instalaron los primeros aserraderos y se consolidó la actividad de rolleros y hacheros. Hacia 1970, Caaguazú era el primer productor maderero del país. La crisis del lapacho en la década de 1990 llevó a reorganizar el sector: hoy, la producción de eucalipto sostiene una cadena de 90 aserraderos y 5.000 carpinterías.</p><p><a href="#tourism-link:la-capital-de-la-madera#">← Volver a la sección</a></p><div class="cta-row"><a class="btn btn-primary" href="#tourism-link:que-hacer/ykua-la-patria#">Ver Ykua La Patria →</a></div>',
  ),
  'la-ruta-de-la-madera' =>
  array (
    'old_slug' => 'la-capital-de-la-madera/la-ruta-de-la-madera',
    'parent' => 'la-capital-de-la-madera',
    'title' => 'La Ruta de la Madera — Caaguazú',
    'excerpt' => 'Sobre la Ruta 7, talleres abiertos donde la madera se trabaja, se exhibe y se vende.',
    'body' => '<h2>Qué se puede ver</h2><p>Aserraderos en funcionamiento, carpinteros ensamblando muebles y puestos familiares que venden juguetes de madera. Sillas, mesas, roperos, parquets y puertas talladas se exhiben y se venden directamente en los talleres.</p><h2>Cómo recorrerla</h2><ul><li>Tramo principal: entre los km 175 y 185 de la Ruta 7.</li><li>Horario recomendado: 9 a 17 h, días hábiles.</li><li>Acceso en auto propio o en cualquier bus que cubra el trayecto Asunción–Ciudad del Este (solicitar parada en Caaguazú).</li><li>Los precios suelen ser negociables. Se recomienda pago en efectivo, en guaraníes.</li></ul><div class="cta-row"><a class="btn btn-primary" href="#tourism-link:planifica-tu-visita/como-llegar#">Cómo llegar →</a><a class="btn btn-outline" href="#tourism-link:la-capital-de-la-madera/artesanos#">Ver artesanos →</a></div>',
  ),
  'artesania-y-oficios' =>
  array (
    'old_slug' => 'la-capital-de-la-madera/artesania-y-oficios',
    'parent' => 'la-capital-de-la-madera',
    'title' => 'Artesanía y oficios — Caaguazú',
    'excerpt' => 'Carpinteros, parqueteros, talladores y jugueteros: los oficios madereros del departamento.',
    'body' => '<h2>Organización del trabajo</h2><p>En Caaguazú, la carpintería suele desarrollarse en talleres familiares anexos a la vivienda, donde las tareas se distribuyen entre generaciones. Habitualmente, los hombres ensamblan y cepillan; las mujeres pintan y barnizan; los hijos atienden los puestos de venta sobre la Ruta 7.</p><h2>Especialidades</h2><ul><li><strong>Mueblería tradicional</strong> — roperos, mesas y aparadores en cedro y petereby.</li><li><strong>Parquet</strong> — pisos de madera instalados en obras de alcance nacional, como el Polideportivo Olimpia.</li><li><strong>Juguetes</strong> — autitos, trompos y caballos de madera, vendidos en los puestos de la ruta.</li><li><strong>Imaginería religiosa</strong> — figuras talladas en cedro para parroquias del departamento.</li></ul><div class="cta-row"><a class="btn btn-primary" href="#tourism-link:la-capital-de-la-madera/artesanos#">Ver artesanos →</a><a class="btn btn-outline" href="#tourism-link:la-capital-de-la-madera/la-ruta-de-la-madera#">Ver la Ruta de la Madera →</a></div>',
  ),
  'artesanos' =>
  array (
    'old_slug' => 'la-capital-de-la-madera/artesanos',
    'parent' => 'la-capital-de-la-madera',
    'title' => 'Artesanos de Caaguazú',
    'excerpt' => 'Perfiles de carpinteros, parqueteros y talladores del departamento.',
    'body' => '<p>Directorio de carpinteros, parqueteros, pintoras y jugueteros que se dedican al oficio maderero en Caaguazú.</p>'
      . '<div class="eco-grid"><a class="eco-card" href="' . esc_url( get_post_type_archive_link( 'caaguazu_artisan' ) ) . '"><div class="body"><span class="eco-tag">Directorio</span><h3>Ver perfiles de artesanos</h3><p class="desc">Nombre, oficio, zona y una frase de cada artesano — un directorio actualizable, no una lista fija.</p><span class="arrow">Ver directorio</span></div></a></div>',
  ),
  'que-hacer' =>
  array (
    'old_slug' => 'que-hacer',
    'parent' => 'turismo',
    'title' => 'Qué hacer en Caaguazú',
    'excerpt' => 'Ykua La Patria, la Ruta de la Madera, el parque Techapyrã, la Iglesia Inmaculada Concepción y el Mercado de Abasto.',
    'body' => '<div class="eco-grid">'
      . '<a class="eco-card" href="#tourism-link:que-hacer/ykua-la-patria#"><div class="img"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/94/Ycua_La_Patria_(Caaguaz%C3%BA).jpg/1280px-Ycua_La_Patria_(Caaguaz%C3%BA).jpg" alt="Monolito y manantial de Ykua La Patria" loading="lazy"></div><div class="body"><span class="eco-tag">Sitio fundacional</span><h3>Ykua La Patria</h3><p class="desc">El manantial donde se fundó la ciudad. Parque histórico con monolito conmemorativo de 1845.</p><span class="arrow">Ver más</span></div></a>'
      . '<a class="eco-card" href="#tourism-link:la-capital-de-la-madera/la-ruta-de-la-madera#"><div class="img"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/Carpenter_in_his_workshop.jpg/1280px-Carpenter_in_his_workshop.jpg" alt="Carpintero trabajando la madera en su taller" loading="lazy"></div><div class="body"><span class="eco-tag">Recorrido</span><h3>La Ruta de la Madera</h3><p class="desc">Talleres abiertos sobre la Ruta 7, con carpinteros trabajando y muebles y juguetes a la venta.</p><span class="arrow">Ver más</span></div></a>'
      . '<a class="eco-card" href="#tourism-link:que-hacer/patrimonio-religioso#"><div class="img"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8b/Iglesia_de_la_Inmaculada_Concepci%C3%B3n_(Caaguaz%C3%BA).jpg/1280px-Iglesia_de_la_Inmaculada_Concepci%C3%B3n_(Caaguaz%C3%BA).jpg" alt="Fachada de la Iglesia Inmaculada Concepción de Caaguazú, con sus murales" loading="lazy"></div><div class="body"><span class="eco-tag">Patrimonio religioso</span><h3>Inmaculada Concepción</h3><p class="desc">60 m² de murales del artista Jorge Aguirre. Iluminación nocturna e misas patronales el 8 de diciembre.</p><span class="arrow">Ver más</span></div></a>'
      . '<a class="eco-card" href="#tourism-link:que-hacer/mercado-municipal#"><div class="img"><img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=1200&auto=format&fit=crop&q=80" alt="Puesto de mercado con productos frescos" loading="lazy"></div><div class="body"><span class="eco-tag">Vida local</span><h3>Mercado de Abasto</h3><p class="desc">Centro comercial de la ciudad. Productos frescos, comida tradicional y hierbas medicinales.</p><span class="arrow">Ver más</span></div></a>'
      . '<a class="eco-card" href="#tourism-link:que-hacer/parques-y-naturaleza#"><div class="img"><img src="https://images.unsplash.com/photo-1519331379826-f10be5486c6f?w=1200&auto=format&fit=crop&q=80" alt="Sendero arbolado en un parque urbano" loading="lazy"></div><div class="body"><span class="eco-tag">Familiar</span><h3>Parque Techapyrã</h3><p class="desc">Único parque del país con reproducciones de dinosaurios a escala real. 5 hectáreas a 1 km del centro.</p><span class="arrow">Ver más</span></div></a>'
      . '</div>',
  ),
  'ykua-la-patria' =>
  array (
    'old_slug' => 'que-hacer/ykua-la-patria',
    'parent' => 'que-hacer',
    'title' => 'Ykua La Patria — Manantial fundacional de Caaguazú',
    'excerpt' => 'Sitio histórico y parque urbano donde se fundó la ciudad en 1845.',
    'body' => '<h2>Descripción</h2><p>Parque histórico y manantial natural en el centro de Caaguazú. Conserva el monolito conmemorativo de la fundación (1845) y un mirador hacia el manantial que dio origen a la ciudad.</p><h2>Datos prácticos</h2><ul><li><strong>Dirección:</strong> Centro de Caaguazú · acceso libre</li><li><strong>Horario:</strong> Parque abierto todo el día</li><li><strong>Entrada:</strong> Gratuita</li><li><strong>Accesibilidad:</strong> Senderos planos, aptos para sillas de ruedas en la zona principal</li></ul><h2>Contexto histórico</h2><p>Durante la Guerra de la Triple Alianza (1864–1870), los soldados paraguayos en marcha hacia el frente se abastecieron de agua en este manantial, lo que le dio al sitio un valor simbólico adicional al de su origen fundacional.</p><p><a href="#tourism-link:que-hacer#">← Otros atractivos</a></p><div class="cta-row"><a class="btn btn-primary" href="#tourism-link:la-capital-de-la-madera/historia#">Ver historia →</a></div>',
  ),
  'patrimonio-religioso' =>
  array (
    'old_slug' => 'que-hacer/patrimonio-religioso',
    'parent' => 'que-hacer',
    'title' => 'Patrimonio religioso — Iglesia Inmaculada Concepción',
    'excerpt' => 'Templo principal de Caaguazú: 60 m² de murales de Jorge Aguirre e iluminación nocturna.',
    'body' => '<h2>El templo</h2><p>Inaugurada en 1965, la Iglesia Inmaculada Concepción es el templo principal de Caaguazú. Sus paredes interiores contienen 60 m² de murales del artista paraguayo Jorge Aguirre, uno de los conjuntos pictóricos religiosos más significativos del interior del país.</p><h2>Cuándo visitarla</h2><ul><li><strong>Misas regulares:</strong> domingos a las 7, 10 y 19 h</li><li><strong>Fiesta patronal:</strong> 8 de diciembre — procesión, novenas y festivales</li><li><strong>Iluminación:</strong> todas las noches, después de la puesta del sol</li></ul>',
  ),
  'mercado-municipal' =>
  array (
    'old_slug' => 'que-hacer/mercado-municipal',
    'parent' => 'que-hacer',
    'title' => 'Mercado de Abasto — Caaguazú',
    'excerpt' => 'Mercado central: productos frescos, comida típica, hierbas medicinales y artesanía menor.',
    'body' => '<h2>Qué se puede encontrar</h2><ul><li><strong>Productos frescos:</strong> mandioca, batata, zapallo, choclo, frutas de estación.</li><li><strong>Comida tradicional:</strong> empanadas, chipa guasu, mbeju, sopa paraguaya.</li><li><strong>Hierbas medicinales ([gn]poha ñana[/gn]):</strong> ofrecidas por vendedoras especializadas en saberes tradicionales guaraníes.</li><li><strong>Artesanía menor:</strong> guampas, bombillas, cestos.</li></ul><h2>Cuándo ir</h2><p>El horario de mayor actividad es entre las 6 y las 10 h. Se recomienda llevar billetes de baja denominación y bolsa propia.</p>',
  ),
  'parques-y-naturaleza' =>
  array (
    'old_slug' => 'que-hacer/parques-y-naturaleza',
    'parent' => 'que-hacer',
    'title' => 'Parques y naturaleza — Caaguazú',
    'excerpt' => 'Parque Techapyrã, único en el país con dinosaurios a escala real, y espacios verdes de la ciudad.',
    'body' => '<h2>Techapyrã</h2><p>Único parque del Paraguay con reproducciones de dinosaurios a escala real. Ocupa cinco hectáreas a un kilómetro del centro de Caaguazú y es utilizado por familias, grupos escolares y visitantes.</p><h2>Datos prácticos</h2><ul><li><strong>Ubicación:</strong> a 1 km del centro de Caaguazú</li><li><strong>Horario:</strong> martes a domingo, 9 a 18 h</li><li><strong>Recomendación de horario:</strong> mañanas, evitando el mediodía en verano</li><li><strong>Sugerencias:</strong> llevar agua, repelente y gorra</li></ul>',
  ),
  'platos-tipicos' =>
  array (
    'old_slug' => 'sabores-de-caaguazu/platos-tipicos',
    'parent' => 'turismo',
    'title' => 'Platos típicos de Caaguazú',
    'excerpt' => 'Ryguasu chyryry, sopa paraguaya, chipa guasu, mbeju y vori vori: platos representativos de la región.',
    'body' => '<div class="info-grid">'
      . '<div class="info-card"><h3>Ryguasu chyryry</h3><span class="meta">Plato casero</span><p>Pollo desmenuzado salteado con cebolla, locote y huevo revuelto. Es uno de los platos caseros más habituales.</p></div>'
      . '<div class="info-card"><h3>Sopa paraguaya</h3><span class="meta">Acompañamiento</span><p>Pese al nombre, no es una sopa: es un pan denso de harina de maíz, queso paraguayo y cebolla. Se sirve como acompañamiento de asados y pucheros.</p></div>'
      . '<div class="info-card"><h3>Chipa guasu</h3><span class="meta">Torta de maíz</span><p>Torta de maíz fresco con queso, leche y huevo, servida tibia y cortada en cuadrados.</p></div>'
      . '<div class="info-card"><h3>Mbeju</h3><span class="meta">Almidón de mandioca</span><p>Tortilla de almidón de mandioca con queso y grasa, crujiente por fuera y suave por dentro.</p></div>'
      . '<div class="info-card"><h3>Vori vori</h3><span class="meta">Plato de invierno</span><p>Sopa con bolitas de harina de maíz y queso, en caldo de gallina. Plato habitual en invierno.</p></div>'
      . '<div class="info-card"><h3>Mandi\'o chyryry</h3><span class="meta">Guarnición</span><p>Mandioca cocida y salteada con cebolla y grasa. Guarnición frecuente en las comidas.</p></div>'
      . '</div><div class="cta-row"><a class="btn btn-primary" href="#tourism-link:sabores-de-caaguazu/donde-comer#">Ver dónde comer →</a><a class="btn btn-outline" href="#tourism-link:sabores-de-caaguazu/mate-y-terere#">Ver mate y tereré →</a></div>',
  ),
  'donde-comer' =>
  array (
    'old_slug' => 'sabores-de-caaguazu/donde-comer',
    'parent' => 'turismo',
    'title' => 'Dónde comer en Caaguazú',
    'excerpt' => 'Locales recomendados, desde puestos del mercado hasta restaurantes familiares.',
    'body' => '<div class="info-grid">'
      . '<div class="info-card"><h3>Comedores del Mercado de Abasto</h3><span class="meta">Mercado · $</span><p>Desayunos y almuerzos con empanadas, chipa y sopa paraguaya recién hechos.</p></div>'
      . '<div class="info-card"><h3>Comedor La Casona</h3><span class="meta">Cocina tradicional · $$</span><p>Cocina paraguaya en porciones generosas. Recomendado el vori vori en invierno.</p></div>'
      . '<div class="info-card"><h3>Parrilla Ruta 7</h3><span class="meta">Parrilla · $$</span><p>Asado a la estaca, mandioca y sopa paraguaya. Ubicado sobre la Ruta 7; recomendado para grupos.</p></div>'
      . '<div class="info-card"><h3>Doña Mirta</h3><span class="meta">Comida casera · $</span><p>Almuerzos caseros de lunes a viernes. Ryguasu chyryry los miércoles.</p></div>'
      . '</div><h2>Directorio de locales</h2>[caaguazu_locales tipo="restaurante"]',
  ),
  'mate-y-terere' =>
  array (
    'old_slug' => 'sabores-de-caaguazu/mate-y-terere',
    'parent' => 'turismo',
    'title' => 'Mate y tereré en Caaguazú',
    'excerpt' => 'El tereré al mediodía y el mate al amanecer, dos costumbres cotidianas del departamento.',
    'body' => '<h2>El [gn]tereré[/gn]</h2><p>Se prepara con yerba mate en la guampa y agua fría con hielo en el termo. En Caaguazú es habitual agregar hierbas frescas machacadas —menta, cedrón, kokũ, burrito—, según la preferencia de cada familia.</p><h2>El mate</h2><p>El mate se toma temprano por la mañana, caliente y compartido. En invierno su consumo suele extenderse durante toda la mañana. La guampa se comparte entre los presentes, en un orden establecido.</p><h2>Costumbres habituales</h2><ul><li>No se suele rechazar el primer convite.</li><li>Decir «gracias» indica que no se desea repetir.</li><li>No se mueve la bombilla con la mano.</li><li>El cebador es, por costumbre, el último en tomar.</li></ul>',
  ),
  'festividades' =>
  array (
    'old_slug' => 'vivir-caaguazu/festividades',
    'parent' => 'turismo',
    'title' => 'Festividades y calendario — Caaguazú',
    'excerpt' => 'Fiesta patronal del 8 de diciembre, aniversario fundacional del 8 de mayo y encuentros comunitarios.',
    'body' => '<div class="info-grid">'
      . '<div class="info-card"><h3>Fiesta Patronal Inmaculada Concepción</h3><span class="meta">8 de diciembre</span><p>Procesión, misa en Ykua La Patria, festivales musicales y peñas folklóricas.</p></div>'
      . '<div class="info-card"><h3>Aniversario de Caaguazú</h3><span class="meta">8 de mayo</span><p>Desfiles estudiantiles, festivales artísticos y acto conmemorativo de la fundación (181 años en 2026).</p></div>'
      . '<div class="info-card"><h3>Tereré en la plaza</h3><span class="meta">Todo el año</span><p>Encuentro social habitual en las plazas de la ciudad, sin fecha fija.</p></div>'
      . '</div><div class="cta-row"><a class="btn btn-primary" href="#tourism-link:vivir-caaguazu/guarani-en-nuestra-ciudad#">Ver glosario de guaraní →</a><a class="btn btn-outline" href="#tourism-link:vivir-caaguazu/galeria#">Ver galería →</a></div>',
  ),
  'guarani-en-nuestra-ciudad' =>
  array (
    'old_slug' => 'vivir-caaguazu/guarani-en-nuestra-ciudad',
    'parent' => 'turismo',
    'title' => 'Guaraní en Caaguazú — Glosario',
    'excerpt' => 'Términos guaraní de uso frecuente en Caaguazú: significado, pronunciación y contexto.',
    'body' => '<dl class="glossary"><div class="glossary-item"><dt>Ka\'a Guasu<span class="ipa">/kaˈʔa guaˈsu/</span></dt><dd>monte grande / selva grande <span class="ctx">Nombre original del territorio de Caaguazú.</span></dd></div><div class="glossary-item"><dt>Ykua<span class="ipa">/ɨˈkua/</span></dt><dd>manantial / fuente de agua <span class="ctx">Como en Ykua La Patria, el manantial fundacional.</span></dd></div><div class="glossary-item"><dt>Ryguasu chyryry<span class="ipa">/rɨguaˈsu tʃɨrɨˈrɨ/</span></dt><dd>gallina frita tradicional <span class="ctx">Plato representativo de Caaguazú.</span></dd></div><div class="glossary-item"><dt>Mandi\'o<span class="ipa">/manˈdiʔo/</span></dt><dd>mandioca / yuca</dd></div><div class="glossary-item"><dt>Tereré<span class="ipa">/tereˈre/</span></dt><dd>yerba mate fría <span class="ctx">Costumbre social habitual en Paraguay.</span></dd></div><div class="glossary-item"><dt>Poha ñana<span class="ipa">/poˈha ɲaˈna/</span></dt><dd>plantas medicinales</dd></div><div class="glossary-item"><dt>Techapyrã<span class="ipa">/tetʃapɨˈɾã/</span></dt><dd>mucho por ver</dd></div><div class="glossary-item"><dt>Sopa paraguaya<span class="ipa">/ˈsopa paɾaˈɣwaja/</span></dt><dd>pastel de maíz (pese al nombre, no es una sopa)</dd></div><div class="glossary-item"><dt>Vori vori<span class="ipa">/ˈvori ˈvori/</span></dt><dd>sopa con bolitas de maíz</dd></div><div class="glossary-item"><dt>Kesu<span class="ipa">/keˈsu/</span></dt><dd>queso fresco paraguayo</dd></div></dl>',
  ),
  'galeria' =>
  array (
    'old_slug' => 'vivir-caaguazu/galeria',
    'parent' => 'turismo',
    'title' => 'Galería — Caaguazú en imágenes',
    'excerpt' => 'Fotografías de Caaguazú: oficios, paisaje, fiestas y vida cotidiana.',
    'body' => '<div class="gallery-grid">'
      . '<img src="https://images.unsplash.com/photo-1448375240586-882707db888b?w=1200&auto=format&fit=crop&q=80" alt="Bosque al amanecer" loading="lazy">'
      . '<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/Carpenter_in_his_workshop.jpg/1280px-Carpenter_in_his_workshop.jpg" alt="Carpintero trabajando la madera en su taller" loading="lazy">'
      . '<img src="https://images.unsplash.com/photo-1437482078695-73f5ca6c96e2?w=1200&auto=format&fit=crop&q=80" alt="Río en el bosque" loading="lazy">'
      . '<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5d/Municipalidad_de_Caaguaz%C3%BA_Paraguay_-_panoramio.jpg/1280px-Municipalidad_de_Caaguaz%C3%BA_Paraguay_-_panoramio.jpg" alt="Municipalidad de Caaguazú" loading="lazy">'
      . '<img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=1200&auto=format&fit=crop&q=80" alt="Productos de mercado" loading="lazy">'
      . '<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8b/Iglesia_de_la_Inmaculada_Concepci%C3%B3n_(Caaguaz%C3%BA).jpg/1280px-Iglesia_de_la_Inmaculada_Concepci%C3%B3n_(Caaguaz%C3%BA).jpg" alt="Iglesia Inmaculada Concepción de Caaguazú" loading="lazy">'
      . '<img src="https://images.unsplash.com/photo-1519331379826-f10be5486c6f?w=1200&auto=format&fit=crop&q=80" alt="Sendero arbolado en un parque urbano" loading="lazy">'
      . '<img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1200&auto=format&fit=crop&q=80" alt="Dosel del bosque" loading="lazy">'
      . '</div>',
  ),
  'como-llegar' =>
  array (
    'old_slug' => 'planifica-tu-visita/como-llegar',
    'parent' => 'turismo',
    'title' => 'Cómo llegar a Caaguazú',
    'excerpt' => 'En auto o en bus desde Asunción y Ciudad del Este, sobre la Ruta 7.',
    'body' => '<h2>Desde Asunción</h2><ul><li><strong>Distancia:</strong> 180 km al este</li><li><strong>En auto:</strong> 2 h 30 min por Ruta 2 y Ruta 7</li><li><strong>En bus:</strong> cualquier servicio Asunción–Ciudad del Este realiza parada en Caaguazú. Frecuencia aproximada: cada 30–60 min.</li></ul><h2>Desde Ciudad del Este</h2><ul><li><strong>Distancia:</strong> 145 km al oeste</li><li><strong>En auto:</strong> 2 h por Ruta 7</li><li><strong>En bus:</strong> mismos servicios, frecuencia similar.</li></ul><h2>Dentro de la ciudad</h2><p>El centro se recorre a pie en aproximadamente 20 minutos. Para llegar a Techapyrã o a la Ruta de la Madera se recomienda taxi o vehículo propio.</p><div class="cta-row"><a class="btn btn-primary" href="#tourism-link:planifica-tu-visita/donde-alojarte#">Ver dónde alojarte →</a><a class="btn btn-outline" href="#tourism-link:planifica-tu-visita/mejor-epoca#">Ver mejor época →</a><a class="btn btn-outline" href="#tourism-link:planifica-tu-visita/mapa-interactivo#">Ver mapa →</a></div>',
  ),
  'donde-alojarte' =>
  array (
    'old_slug' => 'planifica-tu-visita/donde-alojarte',
    'parent' => 'turismo',
    'title' => 'Dónde alojarte en Caaguazú',
    'excerpt' => 'Hoteles y hospedajes disponibles en Caaguazú y alrededores.',
    'body' => '<div class="info-grid">'
      . '<div class="info-card"><h3>Hotel Caaguazú Centro</h3><span class="meta">Hotel · $$</span><p>Sobre la avenida principal. Habitaciones estándar, desayuno incluido.</p></div>'
      . '<div class="info-card"><h3>Hospedaje Ykua</h3><span class="meta">Hospedaje · $</span><p>A 4 cuadras del centro. Recomendado para viajeros que viajan solos.</p></div>'
      . '<div class="info-card"><h3>Apart Ruta 7</h3><span class="meta">Apart · $$</span><p>Departamentos equipados sobre la ruta, adecuados para estadías prolongadas.</p></div>'
      . '<div class="info-card"><h3>Posada del Bosque</h3><span class="meta">Posada · $$$</span><p>A 8 km de la ciudad, en entorno natural. Adecuado para familias.</p></div>'
      . '</div><p>Listado orientativo. Se recomienda confirmar disponibilidad y tarifas directamente con cada establecimiento.</p><h2>Directorio de locales</h2>[caaguazu_locales tipo="hotel"]',
  ),
  'mejor-epoca' =>
  array (
    'old_slug' => 'planifica-tu-visita/mejor-epoca',
    'parent' => 'turismo',
    'title' => 'Mejor época para visitar Caaguazú',
    'excerpt' => 'Clima subtropical y recomendaciones según la época del año.',
    'body' => '<table><thead><tr><th>Temporada</th><th>Clima</th><th>Recomendación</th></tr></thead><tbody><tr><td>Verano (dic–feb)</td><td>Caluroso y húmedo (28–38 °C). Tormentas vespertinas frecuentes.</td><td>Programar actividades temprano; llevar tereré.</td></tr><tr><td>Otoño (mar–may)</td><td>Templado y estable (18–28 °C). Considerada la mejor época para visitar.</td><td>Adecuado para la Ruta de la Madera, Techapyrã y caminatas.</td></tr><tr><td>Invierno (jun–ago)</td><td>Fresco a frío (8–22 °C). Mañanas húmedas, tardes soleadas.</td><td>Llevar abrigo.</td></tr><tr><td>Primavera (sep–nov)</td><td>Templado (18–30 °C). Floración del lapacho.</td><td>Septiembre: floración del lapacho en la ciudad.</td></tr></tbody></table><p><strong>Recomendación general:</strong> abril–mayo y septiembre–octubre son los períodos más adecuados para la visita.</p>',
  ),
  'mapa-interactivo' =>
  array (
    'old_slug' => 'planifica-tu-visita/mapa-interactivo',
    'parent' => 'turismo',
    'title' => 'Mapa de Caaguazú',
    'excerpt' => 'Mapa interactivo de la ciudad: atracciones, hoteles y servicios.',
    'body' => '<h2>Locales y negocios</h2>[caaguazu_mapa alto="480px"]<h2>Puntos históricos</h2>[caaguazu_mapa_puntos]<p class="map-credit">Mapa base: OpenStreetMap contributors.</p><ul><li>Ykua La Patria — centro</li><li>Iglesia Inmaculada Concepción — plaza central</li><li>Mercado de Abasto — a 4 cuadras del centro</li><li>Techapyrã — a 1 km del centro</li><li>Ruta de la Madera — Ruta 7, km 175–185</li></ul>',
  ),
  'contacto' =>
  array (
    'old_slug' => 'contacto',
    'parent' => 'turismo',
    'title' => 'Contacto — Turismo Caaguazú',
    'excerpt' => 'Datos de contacto de la Secretaría de Turismo de la Municipalidad de Caaguazú.',
    'body' => '<h2>Secretaría de Turismo · Municipalidad de Caaguazú</h2><ul><li><strong>Dirección:</strong> Palacio Municipal, centro de Caaguazú</li><li><strong>Email:</strong><a>turismo@caaguazu.net</a></li><li><strong>Teléfono:</strong> +595 522 432 000</li><li><strong>Horario:</strong> lunes a viernes, 7:30 a 13:30</li></ul><a href="#tourism-link:home#">← Volver al inicio</a>',
  ),
);
}
