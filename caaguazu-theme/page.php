<?php
/**
 * Plantilla de página estándar — toma el lugar del stub.php original.
 *
 * Si la página no tiene contenido en el editor, muestra el mismo
 * "en construcción" del sitio original.
 *
 * @package Caaguazu
 */

get_header();

while ( have_posts() ) :
	the_post();

	$slug      = get_post_field( 'post_name', get_the_ID() );
	$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'caaguazu-hero' );

	// Hero defaults heredados del stub original. El admin los sobrescribe poniendo featured image y contenido.
	$stub_heros = array(
		'sobre-caaguazu' => array( 'Sobre Caaguazú', 'Información general del departamento',
			'Datos sobre geografía, historia y población del departamento de Caaguazú.',
			'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5d/Municipalidad_de_Caaguaz%C3%BA_Paraguay_-_panoramio.jpg/1280px-Municipalidad_de_Caaguaz%C3%BA_Paraguay_-_panoramio.jpg' ),
		'servicios'      => array( 'Servicios', 'Trámites y servicios',
			'Información sobre trámites y servicios disponibles para ciudadanos, empresas y visitantes.',
			'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1400&q=80' ),
		'noticias'       => array( 'Noticias', 'Noticias del departamento',
			'Comunicados y publicaciones oficiales de Caaguazú.',
			'https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=1400&q=80' ),
		'ecosistema'     => array( 'Ecosistema', 'Sub-portales del departamento',
			'Acceso a los sub-portales especializados de Caaguazú, cada uno con contenido propio dentro de una misma identidad institucional.',
			'https://upload.wikimedia.org/wikipedia/commons/thumb/0/04/Plaza_principal_de_Caaguaz%C3%BA.jpg/1280px-Plaza_principal_de_Caaguaz%C3%BA.jpg' ),
		'contacto'       => array( 'Contacto', 'Canales de contacto',
			'Canales de contacto oficiales para ciudadanos, prensa, empresas y otros organismos.',
			'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1400&q=80' ),
	);

	// Páginas de la sección Turismo (migradas, ver inc/tourism-seeder.php) no tienen
	// entrada fija en $stub_heros -- son demasiadas para hardcodear una por una --
	// así que el hero se arma con los datos reales de la propia página.
	$ancestors      = get_post_ancestors( get_the_ID() );
	$is_tourism     = caaguazu_is_tourism_context();
	$is_tourism_hub = caaguazu_is_tourism_hub();

	$hero    = isset( $stub_heros[ $slug] ) ? $stub_heros[ $slug ] : array(
		$is_tourism ? __( 'Turismo', 'caaguazu' ) : __( 'Página', 'caaguazu' ),
		get_the_title(),
		get_the_excerpt(),
		'',
	);
	$eyebrow = $hero[0];
	$title   = $hero[1];
	$sub     = $hero[2];
	$img     = $thumb_url ? $thumb_url : $hero[3];
	$label   = get_the_title();
?>

<?php if ( ! $is_tourism_hub ) : ?>
	<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
			<?php foreach ( array_reverse( $ancestors ) as $ancestor_id ) : ?>
				<li>›</li>
				<li><a href="<?php echo esc_url( get_permalink( $ancestor_id ) ); ?>"><?php echo esc_html( get_the_title( $ancestor_id ) ); ?></a></li>
			<?php endforeach; ?>
			<li>›</li>
			<li aria-current="page"><?php echo esc_html( $label ); ?></li>
		</ol>
	</nav>
<?php endif; ?>

<?php if ( $is_tourism_hub ) : ?>
	<section class="tourism-hero-full">
		<div class="img">
			<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/Carpenter_in_his_workshop.jpg/1280px-Carpenter_in_his_workshop.jpg" alt="" loading="eager">
		</div>
		<div class="container inner">
			<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( $sub ) : ?>
				<p class="lead"><?php echo esc_html( $sub ); ?></p>
			<?php endif; ?>
		</div>
	</section>
<?php elseif ( $is_tourism ) : ?>
	<section class="container page-hero tourism-hero">
		<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<h1><?php echo esc_html( $title ); ?></h1>
	</section>
<?php else : ?>
	<section class="container page-hero">
		<div class="grid">
			<div>
				<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<h1><?php echo esc_html( $title ); ?></h1>
				<?php if ( $sub ) : ?>
					<p class="sub"><?php echo esc_html( $sub ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $img ) : ?>
				<div class="img"><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy"></div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php
$content = trim( wp_strip_all_tags( get_the_content() ) );
if ( $content ) : ?>
	<div class="container page-content">
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>
<?php else : ?>
	<div class="wip">
		<p class="eyebrow"><?php esc_html_e( 'En construcción', 'caaguazu' ); ?></p>
		<p><?php esc_html_e( 'Esta sección está siendo desarrollada. Pronto encontrarás aquí el contenido completo.', 'caaguazu' ); ?></p>
	</div>
<?php endif; ?>

<?php
endwhile;

get_footer();
