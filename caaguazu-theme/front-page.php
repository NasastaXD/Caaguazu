<?php
/**
 * Front page — Home de Caaguazú.
 *
 * Replica las secciones del home.php original: hero, identidad,
 * números, ecosistema, quiz y noticias.
 *
 * @package Caaguazu
 */

get_header();

$identity_defaults = caaguazu_identity_defaults();

$hero_video  = caaguazu_opt( 'hero_video_url', 'https://videos.pexels.com/video-files/2491284/2491284-uhd_2560_1440_24fps.mp4' );
$hero_poster = caaguazu_opt_image( 'hero_poster', 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1920&q=80' );
?>

<section class="hero" aria-label="<?php esc_attr_e( 'Caaguazú, Capital de la Madera', 'caaguazu' ); ?>">
	<div class="hero-media" id="heroMedia">
		<?php if ( $hero_video ) : ?>
			<video id="heroVideo" autoplay muted loop playsinline preload="metadata"
				poster="<?php echo esc_url( $hero_poster ); ?>">
				<source src="<?php echo esc_url( $hero_video ); ?>" type="video/mp4">
			</video>
		<?php else : ?>
			<img src="<?php echo esc_url( $hero_poster ); ?>" alt="">
		<?php endif; ?>
	</div>
	<div class="hero-overlay"></div>
	<div class="hero-inner hero-fade">
		<p class="eyebrow"><?php echo esc_html( caaguazu_opt( 'hero_eyebrow', 'Departamento de Paraguay' ) ); ?></p>
		<h1><?php echo esc_html( caaguazu_opt( 'hero_title', 'Caaguazú' ) ); ?></h1>
		<p class="sub"><?php echo esc_html( caaguazu_opt( 'hero_sub', 'Capital de la Madera' ) ); ?></p>
		<p class="lead"><?php echo esc_html( caaguazu_opt( 'hero_lead', 'Portal oficial del departamento de Caaguazú. Información institucional, servicios, noticias y turismo en un mismo sitio.' ) ); ?></p>
	</div>
	<span class="scroll-hint" aria-hidden="true"></span>
	<?php if ( $hero_video ) : ?>
		<button class="video-toggle" id="videoToggle" aria-label="<?php esc_attr_e( 'Pausar video', 'caaguazu' ); ?>">❚❚</button>
	<?php endif; ?>
</section>

<?php caaguazu_render_quick_access(); ?>

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

<section class="stats-wrap" aria-label="<?php esc_attr_e( 'Caaguazú en números', 'caaguazu' ); ?>">
	<div class="container">
		<div class="weave-rule" aria-hidden="true"></div>
		<div class="stats-grid reveal">
			<?php foreach ( array(
				array( 'stat_0', 181,   __( 'años de historia', 'caaguazu' ),    'stats.years' ),
				array( 'stat_1', 90,    __( 'aserraderos activos', 'caaguazu' ), 'stats.sawmills' ),
				array( 'stat_2', 5000,  __( 'carpinterías', 'caaguazu' ),        'stats.workshops' ),
				array( 'stat_3', 10000, __( 'familias madereras', 'caaguazu' ),  'stats.families' ),
			) as $s ) :
				$stat_value = (int) caaguazu_opt( $s[0] . '_value', $s[1] );
				$stat_label = caaguazu_opt( $s[0] . '_label', $s[2] );
			?>
				<div class="stat">
					<span class="stat-num" data-count="<?php echo esc_attr( $stat_value ); ?>"><?php echo esc_html( number_format_i18n( $stat_value ) ); ?></span>
					<span class="stat-label"><?php caaguazu_i18n( $s[3], $stat_label ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php if ( function_exists( 'caaguazu_ecosystem_defaults' ) ) : $eco_defaults = caaguazu_ecosystem_defaults(); ?>
<section class="eco">
	<div class="container">
		<div class="section-head reveal">
			<p class="eyebrow"><?php esc_html_e( 'Ecosistema', 'caaguazu' ); ?></p>
			<h2><?php echo esc_html( caaguazu_opt( 'eco_section_title', 'Sub-portales del departamento' ) ); ?></h2>
			<p><?php echo esc_html( caaguazu_opt( 'eco_section_body', 'Caaguazu.net centraliza el acceso a los sub-portales especializados del departamento. Cada uno conserva su propio contenido dentro de una misma identidad institucional.' ) ); ?></p>
		</div>
		<div class="eco-grid">
			<?php for ( $i = 0; $i < 3; $i++ ) :
				$d     = $eco_defaults[ $i ];
				$tag   = caaguazu_opt( "eco_{$i}_tag",   $d['tag'] );
				$title = caaguazu_opt( "eco_{$i}_title", $d['title'] );
				$body  = caaguazu_opt( "eco_{$i}_body",  $d['body'] );
				$cta   = caaguazu_opt( "eco_{$i}_cta",   $d['cta'] );
				$url   = caaguazu_opt( "eco_{$i}_url",   $d['url'] );
				$img   = caaguazu_opt_image( "eco_{$i}_image", $d['image'] );
				$soon  = empty( $url );
				$tag_el = $soon ? 'div' : 'a';
				$attrs  = $soon ? '' : sprintf( 'href="%s" target="_blank" rel="noreferrer"', esc_url( $url ) );
			?>
				<<?php echo $tag_el; ?> class="eco-card reveal <?php echo $soon ? 'soon' : ''; ?>" <?php echo $attrs; ?>>
					<div class="img"><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy"></div>
					<div class="body">
						<span class="eco-tag"><?php echo esc_html( $tag ); ?></span>
						<h3><?php echo esc_html( $title ); ?></h3>
						<p class="desc"><?php echo esc_html( $body ); ?></p>
						<span class="arrow"><?php echo esc_html( $cta ); ?></span>
					</div>
				</<?php echo $tag_el; ?>>
			<?php endfor; ?>
		</div>
	</div>
</section>
<?php endif; ?>

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
		'primary_url'    => get_post_type_archive_link( 'caaguazu_news' ),
		'primary_label'  => caaguazu_i18n_pair( 'quiz.result.resident.primary', __( 'Ver noticias', 'caaguazu' ) ),
		'secondary_url'  => get_post_type_archive_link( 'caaguazu_event' ),
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
			<?php echo esc_html( date_i18n( 'j \d\e F', strtotime( $event_date ) ) ); ?>
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

<?php if ( post_type_exists( 'caaguazu_news' ) ) : ?>
<section class="container">
	<div class="news-head reveal">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Noticias', 'caaguazu' ); ?></p>
			<h2><?php esc_html_e( 'Últimas publicaciones', 'caaguazu' ); ?></h2>
		</div>
		<a class="arrow" href="<?php echo esc_url( get_post_type_archive_link( 'caaguazu_news' ) ); ?>"><?php esc_html_e( 'Ver todas', 'caaguazu' ); ?></a>
	</div>
	<div class="news-grid">
		<?php
		$news = new WP_Query( array(
			'post_type'      => 'caaguazu_news',
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
			// Sin noticias todavía — placeholder que coincide con el layout original.
			$placeholder_news = array(
				array( 'Desarrollo', 'Caaguazú lanza programa de reforestación con escuelas rurales',
					'La iniciativa involucra a más de 40 instituciones educativas en la plantación de especies nativas.',
					'12 de mayo, 2026', '4 min',
					'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=1200&q=80' ),
				array( 'Cultura', 'Festival de la Madera celebra su 15ª edición en Coronel Oviedo',
					'Tres días de exposiciones, talleres de carpintería tradicional y gastronomía local.',
					'8 de mayo, 2026', '3 min',
					'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=1200&q=80' ),
				array( 'Gobierno', 'Nuevas plataformas digitales simplifican trámites departamentales',
					'Más de 30 gestiones ya pueden realizarse en línea desde el portal de servicios.',
					'2 de mayo, 2026', '5 min',
					'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80' ),
			);
			foreach ( $placeholder_news as $n ) :
		?>
				<article class="news reveal">
					<div class="img"><img src="<?php echo esc_url( $n[5] ); ?>" alt="" loading="lazy"></div>
					<div class="body">
						<span class="cat"><?php echo esc_html( $n[0] ); ?></span>
						<h3><?php echo esc_html( $n[1] ); ?></h3>
						<p class="meta"><?php echo esc_html( $n[3] ); ?> · <?php echo esc_html( $n[4] ); ?> de lectura</p>
						<p class="ex"><?php echo esc_html( $n[2] ); ?></p>
						<a class="arrow" href="<?php echo esc_url( get_post_type_archive_link( 'caaguazu_news' ) ); ?>"><?php esc_html_e( 'Leer más', 'caaguazu' ); ?></a>
					</div>
				</article>
		<?php
			endforeach;
		endif;
		?>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
