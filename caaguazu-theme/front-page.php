<?php
/**
 * Front page — Home de Caaguazú.
 *
 * Replica las secciones del home.php original: hero, identidad,
 * números, ecosistema, quiz y noticias.
 *
 * @package Caaguazu
 */

if ( caaguazu_maybe_render_builder_content() ) {
	return;
}

get_header();

$identity_defaults = caaguazu_identity_defaults();

/**
 * Panel de accesos destacados del hero — datos reales del sitio (no una
 * maqueta estática): próximo evento real de Agenda, si hay noticias
 * publicadas, y los dos ecosistemas siempre disponibles. Nada de esto se
 * hardcodea como "activo"/"próximamente" a mano — sale de una consulta.
 */
$hero_access = array();
if ( function_exists( 'caaguazu_upcoming_events' ) ) {
	$next = caaguazu_upcoming_events( 1 );
	$hero_access[] = array(
		'label'  => __( 'Agenda de la ciudad', 'caaguazu' ),
		'status' => $next->have_posts() ? get_the_date( 'j \d\e M', $next->posts[0]->ID ) : __( 'Sin eventos', 'caaguazu' ),
	);
	wp_reset_postdata();
}
$latest_news = new WP_Query( array( 'post_type' => 'post', 'category_name' => 'noticias', 'posts_per_page' => 1, 'no_found_rows' => true, 'fields' => 'ids' ) );
$hero_access[] = array(
	'label'  => __( 'Noticias locales', 'caaguazu' ),
	'status' => $latest_news->have_posts() ? __( 'Actualizado', 'caaguazu' ) : __( 'Próximamente', 'caaguazu' ),
);
$hero_access[] = array( 'label' => __( 'Turismo', 'caaguazu' ), 'status' => __( 'Disponible', 'caaguazu' ) );
$hero_access[] = array( 'label' => __( 'Educación', 'caaguazu' ), 'status' => __( 'Disponible', 'caaguazu' ) );
?>

<section class="hero-civic" aria-label="<?php esc_attr_e( 'Todo Caaguazú, en un solo lugar', 'caaguazu' ); ?>">
	<div class="container hero-civic-grid">
		<div class="hero-civic-copy reveal">
			<?php /* Mismo componente que header.php/drawer (misma clase .lang y
			   data-lang: el JS de assets/js/main.js sincroniza las tres copias
			   solo) — acá siempre visible, incluso en mobile, donde el header
			   lo esconde y lo manda al drawer. */ ?>
			<div class="lang hero-civic-lang" role="group" aria-label="<?php esc_attr_e( 'Idioma', 'caaguazu' ); ?>">
				<button class="on" data-lang="ES">ES</button>
				<button data-lang="GN">GN</button>
				<button data-lang="EN" disabled title="<?php esc_attr_e( 'Próximamente', 'caaguazu' ); ?>">EN</button>
			</div>
			<p class="eyebrow"><?php echo esc_html( caaguazu_opt( 'hero_eyebrow', __( 'Portal cívico de Caaguazú', 'caaguazu' ) ) ); ?></p>
			<h1><?php echo esc_html( caaguazu_opt( 'hero_title', __( 'Todo Caaguazú, en un solo lugar', 'caaguazu' ) ) ); ?></h1>
			<p class="sub"><?php echo esc_html( caaguazu_opt( 'hero_lead', __( 'Servicios, cultura, educación, turismo, noticias y participación ciudadana reunidos en una plataforma digital clara y accesible.', 'caaguazu' ) ) ); ?></p>
			<div class="hero-civic-cta">
				<a class="btn btn-primary" href="<?php echo esc_url( caaguazu_page_url( 'ecosistema' ) ); ?>"><?php esc_html_e( 'Explorar la ciudad', 'caaguazu' ); ?></a>
				<a class="btn btn-outline" href="#accesos-rapidos"><?php esc_html_e( 'Ver servicios', 'caaguazu' ); ?></a>
			</div>
		</div>
		<div class="hero-civic-panel reveal">
			<p class="hero-civic-panel-label"><?php esc_html_e( 'Accesos destacados', 'caaguazu' ); ?></p>
			<ul class="hero-civic-panel-list">
				<?php foreach ( $hero_access as $a ) : ?>
					<li><span><?php echo esc_html( $a['label'] ); ?></span><strong><?php echo esc_html( $a['status'] ); ?></strong></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>

<div id="accesos-rapidos"><?php caaguazu_render_quick_access(); ?></div>

<?php
/**
 * "Necesito…" (V5, civic CMS): a diferencia de "Accesos rápidos" (destinos
 * por sección) esto está frased como tareas concretas — alguien entra a un
 * portal cívico con un objetivo, no con una categoría en mente. Reusa el
 * mismo componente visual que Accesos rápidos (`.quick-access`/`.qa-grid`/
 * `.qa-tile`) a propósito: mismo lenguaje visual, distinto criterio de
 * organización. Lista fija (no viene de un filtro de módulos): son verbos,
 * no secciones, así que no tiene sentido que cada plugin sume los suyos acá.
 */
$necesito_items = array(
	array( 'icon' => 'tool',     'label' => __( 'Buscar un servicio', 'caaguazu' ),          'url' => post_type_exists( 'servicio' ) ? get_post_type_archive_link( 'servicio' ) : home_url( '/?s=' ) ),
	array( 'icon' => 'calendar', 'label' => __( 'Ver eventos', 'caaguazu' ),                 'url' => caaguazu_category_url( 'agenda' ) ),
	array( 'icon' => 'building', 'label' => __( 'Encontrar una institución', 'caaguazu' ),   'url' => post_type_exists( 'institucion' ) ? get_post_type_archive_link( 'institucion' ) : caaguazu_page_url( 'contacto' ) ),
	array( 'icon' => 'pin',      'label' => __( 'Descubrir lugares', 'caaguazu' ),           'url' => post_type_exists( 'lugar' ) ? get_post_type_archive_link( 'lugar' ) : caaguazu_page_url( 'turismo' ) ),
	array( 'icon' => 'news',     'label' => __( 'Leer noticias', 'caaguazu' ),               'url' => caaguazu_category_url( 'noticias' ) ),
	array( 'icon' => 'mail',     'label' => __( 'Enviar información', 'caaguazu' ),          'url' => caaguazu_page_url( 'contacto' ) ),
	array( 'icon' => 'target',   'label' => __( 'Reportar información desactualizada', 'caaguazu' ), 'url' => caaguazu_page_url( 'contacto' ) ),
);
?>
<section class="quick-access necesito" aria-label="<?php esc_attr_e( 'Necesito…', 'caaguazu' ); ?>">
	<div class="container">
		<div class="section-head reveal">
			<p class="eyebrow"><?php esc_html_e( 'Portal cívico', 'caaguazu' ); ?></p>
			<h2><?php esc_html_e( 'Necesito…', 'caaguazu' ); ?></h2>
		</div>
		<div class="qa-grid">
			<?php foreach ( $necesito_items as $item ) : ?>
				<a class="qa-tile reveal" href="<?php echo esc_url( $item['url'] ); ?>">
					<span class="qa-ico" aria-hidden="true"><?php echo caaguazu_icon( $item['icon'] ); ?></span>
					<span class="qa-label"><?php echo esc_html( $item['label'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
// Tarjetas del Ecosistema — mismo cálculo y markup que la página `ecosistema`,
// compartidos vía inc/helpers.php (caaguazu_resolve_ecosystem_cards()/
// caaguazu_render_ecosystem_cards()) para no mantener dos copias.
caaguazu_render_ecosystem_cards( caaguazu_resolve_ecosystem_cards() );
?>

<?php caaguazu_render_turismo_carousel(); ?>

<section class="container identity">
	<?php for ( $i = 0; $i < 3; $i++ ) :
		$d       = $identity_defaults[ $i ];
		$eyebrow = caaguazu_opt( "identity_{$i}_eyebrow", $d['eyebrow'] );
		$title   = caaguazu_opt( "identity_{$i}_title",   $d['title'] );
		$body    = caaguazu_opt( "identity_{$i}_body",    $d['body'] );
		$img     = caaguazu_opt_image( "identity_{$i}_image", $d['image'] );
		$reverse = ( $i % 2 === 1 ) ? 'reverse' : '';
	?>
		<article class="move reveal <?php echo esc_attr( $reverse ); ?>">
			<div class="move-img"><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy"></div>
			<div>
				<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<h2><?php echo esc_html( $title ); ?></h2>
				<p><?php echo esc_html( $body ); ?></p>
				<a class="arrow" href="<?php echo esc_url( caaguazu_page_url( 'sobre-caaguazu' ) ); ?>"><?php esc_html_e( 'Conocer más', 'caaguazu' ); ?></a>
			</div>
		</article>
	<?php endfor; ?>
</section>

<?php
/**
 * "Caaguazú en números" pasó de estadísticas de la industria maderera
 * (inventadas, sin fuente citable) a datos operativos del portal mismo:
 * cuántas secciones hay activas, cuántos eventos reales hay agendados, y
 * cuándo se actualizó contenido por última vez, todos calculados en cada
 * carga — no tipeados a mano. Lo que todavía no existe (un directorio de
 * instituciones, más sub-portales) se etiqueta "En preparación"/
 * "Próximamente" en vez de inventarle un número.
 */
$stats_nav_sections = apply_filters( 'caaguazu_nav_items', array() );

$stats_upcoming_events = 0;
if ( function_exists( 'caaguazu_upcoming_events' ) ) {
	$upcoming_q            = caaguazu_upcoming_events( -1 );
	$stats_upcoming_events = $upcoming_q->found_posts;
	wp_reset_postdata();
}

$stats_last_modified = get_lastpostmodified( 'blog' );

$home_stats = array(
	array(
		'value'   => count( $stats_nav_sections ) + 2, // + Sobre Caaguazú y Contacto, siempre presentes.
		'label'   => __( 'secciones activas', 'caaguazu' ),
		'numeric' => true,
	),
	array(
		'value'   => $stats_upcoming_events,
		'label'   => __( 'eventos agendados', 'caaguazu' ),
		'numeric' => true,
	),
	array(
		'value'   => __( 'En preparación', 'caaguazu' ),
		'label'   => __( 'directorio de instituciones', 'caaguazu' ),
		'numeric' => false,
	),
	array(
		'value'   => __( 'Próximamente', 'caaguazu' ),
		'label'   => __( 'nuevos sub-portales', 'caaguazu' ),
		'numeric' => false,
	),
	array(
		'value'   => $stats_last_modified ? caaguazu_fecha_es( get_date_from_gmt( $stats_last_modified ), false ) : __( 'Sin novedades aún', 'caaguazu' ),
		'label'   => __( 'última actualización', 'caaguazu' ),
		'numeric' => false,
	),
);
?>
<section class="stats-wrap" aria-label="<?php esc_attr_e( 'Caaguazú en números', 'caaguazu' ); ?>">
	<div class="container">
		<div class="weave-rule" aria-hidden="true"></div>
		<div class="stats-grid reveal">
			<?php foreach ( $home_stats as $s ) : ?>
				<div class="stat">
					<?php if ( $s['numeric'] ) : ?>
						<span class="stat-num" data-count="<?php echo esc_attr( $s['value'] ); ?>"><?php echo esc_html( number_format_i18n( $s['value'] ) ); ?></span>
					<?php else : ?>
						<span class="stat-num stat-num-text"><?php echo esc_html( $s['value'] ); ?></span>
					<?php endif; ?>
					<span class="stat-label"><?php echo esc_html( $s['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="quiz-wrap">
	<div class="container">
		<div class="quiz reveal">
			<p class="eyebrow"><?php caaguazu_i18n( 'quiz.eyebrow', __( 'Guía rápida', 'caaguazu' ) ); ?></p>
			<h2><?php caaguazu_i18n( 'quiz.title', __( '¿Qué información buscás?', 'caaguazu' ) ); ?></h2>
			<p class="intro"><?php caaguazu_i18n( 'quiz.intro', __( 'Seleccioná una opción para ver los enlaces recomendados según tu perfil.', 'caaguazu' ) ); ?></p>
			<div class="quiz-opts">
				<?php foreach ( array(
					array( 'resident', '🏡', 'quiz.opt.resident', __( 'Soy residente', 'caaguazu' ) ),
					array( 'visitor',  '🌿', 'quiz.opt.visitor',  __( 'Voy de visita', 'caaguazu' ) ),
					array( 'investor', '📈', 'quiz.opt.investor', __( 'Busco invertir', 'caaguazu' ) ),
					array( 'student',  '📚', 'quiz.opt.student',  __( 'Soy estudiante', 'caaguazu' ) ),
					array( 'other',    '✨', 'quiz.opt.other',    __( 'Otro', 'caaguazu' ) ),
				) as $opt ) : ?>
					<button class="quiz-opt" type="button" data-key="<?php echo esc_attr( $opt[0] ); ?>">
						<span class="e"><?php echo esc_html( $opt[1] ); ?></span>
						<span><?php caaguazu_i18n( $opt[2], $opt[3] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="quiz-result" id="quizResult" hidden aria-live="polite"></div>
		</div>
	</div>
</section>

<?php
$quiz_map = array(
	'resident' => array(
		'title'          => caaguazu_i18n_pair( 'quiz.result.resident.title', __( 'Información para residentes', 'caaguazu' ) ),
		'primary_url'    => caaguazu_category_url( 'noticias' ),
		'primary_label'  => caaguazu_i18n_pair( 'quiz.result.resident.primary', __( 'Ver noticias', 'caaguazu' ) ),
		'secondary_url'  => caaguazu_category_url( 'agenda' ),
		'secondary_label'=> caaguazu_i18n_pair( 'quiz.result.resident.secondary', __( 'Ver agenda', 'caaguazu' ) ),
	),
	'visitor' => array(
		'title'          => caaguazu_i18n_pair( 'quiz.result.visitor.title', __( 'Información para visitantes', 'caaguazu' ) ),
		'primary_url'    => caaguazu_page_url( 'turismo' ),
		'primary_label'  => caaguazu_i18n_pair( 'quiz.result.visitor.primary', __( 'Ver sección de Turismo', 'caaguazu' ) ),
		'secondary_url'  => caaguazu_page_url( 'ecosistema' ),
		'secondary_label'=> caaguazu_i18n_pair( 'quiz.result.visitor.secondary', __( 'Ver ecosistema', 'caaguazu' ) ),
	),
	'investor' => array(
		'title'          => caaguazu_i18n_pair( 'quiz.result.investor.title', __( 'Información para inversores', 'caaguazu' ) ),
		'primary_url'    => caaguazu_page_url( 'ecosistema' ),
		'primary_label'  => caaguazu_i18n_pair( 'quiz.result.investor.primary', __( 'Ver ecosistema', 'caaguazu' ) ),
		'secondary_url'  => caaguazu_page_url( 'contacto' ),
		'secondary_label'=> caaguazu_i18n_pair( 'quiz.result.investor.secondary', __( 'Ir a contacto', 'caaguazu' ) ),
	),
	'student' => array(
		'title'          => caaguazu_i18n_pair( 'quiz.result.student.title', __( 'Información para estudiantes', 'caaguazu' ) ),
		'primary_url'    => caaguazu_page_url( 'ecosistema' ),
		'primary_label'  => caaguazu_i18n_pair( 'quiz.result.student.primary', __( 'Ver ecosistema', 'caaguazu' ) ),
		'secondary_url'  => caaguazu_page_url( 'contacto' ),
		'secondary_label'=> caaguazu_i18n_pair( 'quiz.result.student.secondary', __( 'Ir a contacto', 'caaguazu' ) ),
	),
	'other' => array(
		'title'          => caaguazu_i18n_pair( 'quiz.result.other.title', __( 'Otras consultas', 'caaguazu' ) ),
		'primary_url'    => caaguazu_page_url( 'contacto' ),
		'primary_label'  => caaguazu_i18n_pair( 'quiz.result.other.primary', __( 'Ir a contacto', 'caaguazu' ) ),
		'secondary_url'  => home_url( '/?s=' ),
		'secondary_label'=> caaguazu_i18n_pair( 'quiz.result.other.secondary', __( 'Buscar en el sitio', 'caaguazu' ) ),
	),
);
?>
<script>window.caaguazuQuizMap = <?php echo wp_json_encode( $quiz_map ); ?>;</script>

<?php
if ( function_exists( 'caaguazu_upcoming_events' ) ) :
	$next_event = caaguazu_upcoming_events( 1 );
	if ( $next_event->have_posts() ) :
		$next_event->the_post();
		$event_date     = get_post_meta( get_the_ID(), '_caaguazu_event_date', true );
		$event_location = get_post_meta( get_the_ID(), '_caaguazu_event_location', true );
?>
<section class="container">
	<a class="event-banner reveal" href="<?php the_permalink(); ?>">
		<span class="eyebrow"><?php esc_html_e( 'Próximo evento', 'caaguazu' ); ?></span>
		<h3><?php the_title(); ?></h3>
		<p class="meta">
			<?php echo esc_html( caaguazu_fecha_es( $event_date, false ) ); ?>
			<?php if ( $event_location ) : ?> · 📍 <?php echo esc_html( $event_location ); ?><?php endif; ?>
		</p>
		<span class="arrow"><?php esc_html_e( 'Ver en la agenda', 'caaguazu' ); ?></span>
	</a>
</section>
<?php
		wp_reset_postdata();
	endif;
endif;
?>

<?php if ( function_exists( 'caaguazu_news_primary_term' ) ) : ?>
<section class="container">
	<div class="news-head reveal">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Noticias', 'caaguazu' ); ?></p>
			<h2><?php esc_html_e( 'Últimas publicaciones', 'caaguazu' ); ?></h2>
		</div>
		<a class="arrow" href="<?php echo esc_url( caaguazu_category_url( 'noticias' ) ); ?>"><?php esc_html_e( 'Ver todas', 'caaguazu' ); ?></a>
	</div>
	<div class="news-grid">
		<?php
		$news = new WP_Query( array(
			'post_type'      => 'post',
			'category_name'  => 'noticias',
			'posts_per_page' => 3,
			'no_found_rows'  => true,
		) );

		if ( $news->have_posts() ) :
			while ( $news->have_posts() ) : $news->the_post();
				$cat   = caaguazu_news_primary_term( get_the_ID() );
				$mins  = (int) get_post_meta( get_the_ID(), '_caaguazu_read_minutes', true );
				$thumb = get_the_post_thumbnail_url( get_the_ID(), 'caaguazu-card' );
		?>
				<article class="news reveal">
					<div class="img">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
						<?php endif; ?>
					</div>
					<div class="body">
						<?php if ( $cat ) : ?><span class="cat"><?php echo esc_html( $cat ); ?></span><?php endif; ?>
						<h3><?php the_title(); ?></h3>
						<p class="meta">
							<?php echo esc_html( get_the_date() ); ?>
							<?php if ( $mins ) : ?> · <?php printf( esc_html__( '%d min de lectura', 'caaguazu' ), $mins ); ?><?php endif; ?>
						</p>
						<p class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p>
						<a class="arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leer más', 'caaguazu' ); ?></a>
					</div>
				</article>
		<?php
			endwhile;
			wp_reset_postdata();
		else :
			// Sin noticias todavía: estado vacío honesto, no contenido inventado.
			echo caaguazu_render_empty_state(
				__( 'Sin noticias todavía', 'caaguazu' ),
				__( 'Todavía no hay noticias publicadas.', 'caaguazu' ),
				__( 'Pronto se cargarán novedades verificadas de Caaguazú.', 'caaguazu' ),
				array( 'label' => __( 'Enviar información', 'caaguazu' ), 'url' => caaguazu_page_url( 'contacto' ) ),
				'wip-news'
			);
		endif;
			?>
	</div>
</section>
<?php endif; ?>

<?php if ( post_type_exists( 'servicio' ) ) : ?>
<section class="container">
	<div class="news-head reveal">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Servicios', 'caaguazu' ); ?></p>
			<h2><?php esc_html_e( 'Servicios destacados', 'caaguazu' ); ?></h2>
		</div>
		<a class="arrow" href="<?php echo esc_url( get_post_type_archive_link( 'servicio' ) ); ?>"><?php esc_html_e( 'Ver todos', 'caaguazu' ); ?></a>
	</div>
	<div class="aud-grid">
		<?php
		$servicios = new WP_Query( array( 'post_type' => 'servicio', 'posts_per_page' => 3, 'no_found_rows' => true ) );
		if ( $servicios->have_posts() ) :
			while ( $servicios->have_posts() ) : $servicios->the_post();
				$categoria = caaguazu_primary_term_name( get_the_ID(), 'categoria_servicio' );
		?>
				<a class="aud reveal" href="<?php the_permalink(); ?>">
					<span class="ico" aria-hidden="true"><?php echo caaguazu_icon( 'tool' ); ?></span>
					<h3><?php the_title(); ?></h3>
					<?php if ( $categoria ) : ?><p class="desc"><?php echo esc_html( $categoria ); ?></p><?php endif; ?>
					<span class="arrow"><?php esc_html_e( 'Ver servicio', 'caaguazu' ); ?></span>
				</a>
		<?php
			endwhile;
			wp_reset_postdata();
		else :
			echo caaguazu_render_empty_state(
				__( 'Sin servicios todavía', 'caaguazu' ),
				__( 'Todavía no hay servicios cargados.', 'caaguazu' ),
				__( 'Esta sección reunirá información útil y verificable para la ciudadanía.', 'caaguazu' )
			);
		endif;
		?>
	</div>
</section>
<?php endif; ?>

<?php if ( post_type_exists( 'institucion' ) ) : ?>
<section class="container">
	<div class="news-head reveal">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Directorio', 'caaguazu' ); ?></p>
			<h2><?php esc_html_e( 'Instituciones', 'caaguazu' ); ?></h2>
		</div>
		<a class="arrow" href="<?php echo esc_url( get_post_type_archive_link( 'institucion' ) ); ?>"><?php esc_html_e( 'Ver todas', 'caaguazu' ); ?></a>
	</div>
	<div class="aud-grid">
		<?php
		$instituciones = new WP_Query( array( 'post_type' => 'institucion', 'posts_per_page' => 3, 'no_found_rows' => true ) );
		if ( $instituciones->have_posts() ) :
			while ( $instituciones->have_posts() ) : $instituciones->the_post();
				$tipo = caaguazu_primary_term_name( get_the_ID(), 'tipo_institucion' );
		?>
				<a class="aud reveal" href="<?php the_permalink(); ?>">
					<span class="ico" aria-hidden="true"><?php echo caaguazu_icon( 'building' ); ?></span>
					<h3><?php the_title(); ?></h3>
					<?php if ( $tipo ) : ?><p class="desc"><?php echo esc_html( $tipo ); ?></p><?php endif; ?>
					<span class="arrow"><?php esc_html_e( 'Ver institución', 'caaguazu' ); ?></span>
				</a>
		<?php
			endwhile;
			wp_reset_postdata();
		else :
			echo caaguazu_render_empty_state(
				__( 'Sin instituciones todavía', 'caaguazu' ),
				__( 'Todavía no hay instituciones registradas.', 'caaguazu' ),
				__( 'Esta sección reunirá información verificada de instituciones locales.', 'caaguazu' )
			);
		endif;
		?>
	</div>
</section>
<?php endif; ?>

<?php if ( post_type_exists( 'lugar' ) ) : ?>
<section class="container">
	<div class="news-head reveal">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Descubrí Caaguazú', 'caaguazu' ); ?></p>
			<h2><?php esc_html_e( 'Lugares', 'caaguazu' ); ?></h2>
		</div>
		<a class="arrow" href="<?php echo esc_url( get_post_type_archive_link( 'lugar' ) ); ?>"><?php esc_html_e( 'Ver todos', 'caaguazu' ); ?></a>
	</div>
	<div class="news-grid">
		<?php
		$lugares = new WP_Query( array( 'post_type' => 'lugar', 'posts_per_page' => 3, 'no_found_rows' => true ) );
		if ( $lugares->have_posts() ) :
			while ( $lugares->have_posts() ) : $lugares->the_post();
				$tipo = caaguazu_primary_term_name( get_the_ID(), 'tipo_lugar' );
		?>
				<article class="news reveal">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="img"><?php the_post_thumbnail( 'caaguazu-card', array( 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
					<div class="body">
						<?php if ( $tipo ) : ?><span class="cat"><?php echo esc_html( $tipo ); ?></span><?php endif; ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
						<a class="arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Conocer lugar', 'caaguazu' ); ?></a>
					</div>
				</article>
		<?php
			endwhile;
			wp_reset_postdata();
		else :
			echo caaguazu_render_empty_state(
				__( 'Contenido en preparación', 'caaguazu' ),
				__( 'Contenido en preparación.', 'caaguazu' ),
				__( 'Pronto se incorporarán lugares, historias y referencias locales revisadas.', 'caaguazu' )
			);
		endif;
		?>
	</div>
</section>
<?php endif; ?>

<?php if ( post_type_exists( 'proyecto' ) ) : ?>
<section class="container">
	<div class="news-head reveal">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Ecosistema digital', 'caaguazu' ); ?></p>
			<h2><?php esc_html_e( 'Proyectos digitales', 'caaguazu' ); ?></h2>
		</div>
		<a class="arrow" href="<?php echo esc_url( get_post_type_archive_link( 'proyecto' ) ); ?>"><?php esc_html_e( 'Ver todos', 'caaguazu' ); ?></a>
	</div>
	<div class="eco-grid">
		<?php
		$proyectos = new WP_Query( array( 'post_type' => 'proyecto', 'posts_per_page' => 2, 'no_found_rows' => true ) );
		if ( $proyectos->have_posts() ) :
			while ( $proyectos->have_posts() ) : $proyectos->the_post();
				$area    = caaguazu_primary_term_name( get_the_ID(), 'area_proyecto' );
				$estado  = get_post_meta( get_the_ID(), '_caaguazu_proy_estado', true );
				$estados = caaguazu_proyecto_estado_values();
				$tag     = array_filter( array( $area, isset( $estados[ $estado ] ) ? $estados[ $estado ] : '' ) );
		?>
				<a class="eco-card reveal" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="img"><?php the_post_thumbnail( 'caaguazu-card', array( 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
					<div class="body">
						<?php if ( $tag ) : ?><span class="eco-tag"><?php echo esc_html( implode( ' · ', $tag ) ); ?></span><?php endif; ?>
						<h3><?php the_title(); ?></h3>
						<p class="desc"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						<span class="arrow"><?php esc_html_e( 'Ver proyecto', 'caaguazu' ); ?></span>
					</div>
				</a>
		<?php
			endwhile;
			wp_reset_postdata();
		else :
			echo caaguazu_render_empty_state(
				__( 'En preparación', 'caaguazu' ),
				__( 'Los proyectos digitales se irán incorporando progresivamente.', 'caaguazu' ),
				__( 'Esta sección reunirá iniciativas reales vinculadas a la ciudad.', 'caaguazu' )
			);
		endif;
		?>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
