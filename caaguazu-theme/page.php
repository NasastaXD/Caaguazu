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
		'sobre-caaguazu' => array( 'Sobre Caaguazú', 'Un departamento entre bosques y oficios',
			'Tres siglos de historia productiva, una geografía marcada por el monte grande y una comunidad bilingüe que define la identidad caaguaceña.',
			'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1400&q=80' ),
		'servicios'      => array( 'Servicios', 'Trámites y servicios',
			'Acceso unificado a las gestiones del departamento. Ciudadanos, empresas y visitantes encuentran aquí su punto de entrada.',
			'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1400&q=80' ),
		'noticias'       => array( 'Noticias', 'Actualidad de Caaguazú',
			'Comunicados, publicaciones y cobertura de los hechos que dan forma al departamento.',
			'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=1400&q=80' ),
		'transparencia'  => array( 'Transparencia', 'Gobierno abierto',
			'Publicamos presupuesto, licitaciones y datos abiertos en formatos reutilizables. La transparencia es el cimiento del ecosistema.',
			'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1400&q=80' ),
		'ecosistema'     => array( 'Ecosistema', 'Un ecosistema, múltiples experiencias',
			'Caaguazu.net es el hub central de una red federada de sub-portales especializados. Cada uno con su voz, todos bajo la misma identidad.',
			'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=1400&q=80' ),
		'contacto'       => array( 'Contacto', 'Hablemos',
			'Canales oficiales para ciudadanos, prensa, empresas y otros gobiernos.',
			'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1400&q=80' ),
	);

	// Páginas de la sección Turismo (migradas, ver inc/tourism-seeder.php) no tienen
	// entrada fija en $stub_heros -- son demasiadas para hardcodear una por una --
	// así que el hero se arma con los datos reales de la propia página.
	$ancestors      = get_post_ancestors( get_the_ID() );
	$ancestor_slugs = array_map( function ( $id ) {
		return get_post_field( 'post_name', $id );
	}, $ancestors );
	$is_tourism     = 'turismo' === $slug || in_array( 'turismo', $ancestor_slugs, true );

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
