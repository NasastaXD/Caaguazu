<?php
/**
 * Resultados de búsqueda. Reusa la UI del pages/buscar.php original.
 *
 * @package Caaguazu
 */

get_header(); ?>

<nav class="container breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'caaguazu' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'caaguazu' ); ?></a></li>
		<li>›</li>
		<li aria-current="page"><?php esc_html_e( 'Buscar', 'caaguazu' ); ?></li>
	</ol>
</nav>

<div class="container search-wrap">
	<div style="text-align:center"><p class="eyebrow"><?php esc_html_e( 'Buscar', 'caaguazu' ); ?></p></div>
	<h1 style="text-align:center;font-size:clamp(40px,6vw,64px);margin-top:16px"><?php esc_html_e( '¿Qué estás buscando?', 'caaguazu' ); ?></h1>

	<div class="search-form-wrap">
		<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" autocomplete="off">
			<span style="margin-left:8px;color:var(--text-muted)">🔍</span>
			<input type="search" name="s" id="caaguazu-search-input" value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php esc_attr_e( 'Buscar en todo el sitio…', 'caaguazu' ); ?>"
				aria-label="<?php esc_attr_e( 'Búsqueda', 'caaguazu' ); ?>"
				role="combobox" aria-expanded="false" aria-controls="caaguazu-search-suggest" aria-autocomplete="list">
			<button type="submit"><?php esc_html_e( 'Buscar', 'caaguazu' ); ?></button>
		</form>
		<ul class="search-suggest" id="caaguazu-search-suggest" role="listbox" hidden></ul>
	</div>

	<?php $current_type = caaguazu_search_type(); ?>
	<div class="filters">
		<?php
		$types = array(
			'any'      => array( 'search.chip.todos', __( 'Todos', 'caaguazu' ) ),
			'page'     => array( 'search.chip.paginas', __( 'Páginas', 'caaguazu' ) ),
			'noticias' => array( 'search.chip.noticias', __( 'Noticias', 'caaguazu' ) ),
			'agenda'   => array( 'search.chip.eventos', __( 'Eventos', 'caaguazu' ) ),
		);
		if ( post_type_exists( 'institucion' ) ) {
			$types['institucion'] = array( 'search.chip.instituciones', __( 'Instituciones', 'caaguazu' ) );
		}
		if ( post_type_exists( 'lugar' ) ) {
			$types['lugar'] = array( 'search.chip.lugares', __( 'Lugares', 'caaguazu' ) );
		}
		if ( post_type_exists( 'servicio' ) ) {
			$types['servicio'] = array( 'search.chip.servicios', __( 'Servicios', 'caaguazu' ) );
		}
		if ( post_type_exists( 'proyecto' ) ) {
			$types['proyecto'] = array( 'search.chip.proyectos', __( 'Proyectos', 'caaguazu' ) );
		}
		foreach ( $types as $type => $i18n ) :
		?>
			<a class="chip <?php echo $current_type === $type ? 'on' : ''; ?>" href="<?php echo caaguazu_search_filter_url( $type ); ?>"><?php caaguazu_i18n( $i18n[0], $i18n[1] ); ?></a>
		<?php endforeach; ?>
	</div>

	<?php if ( 'noticias' === $current_type ) :
		$current_cat  = caaguazu_search_news_cat();
		$noticias_cat = get_category_by_slug( 'noticias' );
		$cats         = $noticias_cat ? get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true, 'parent' => $noticias_cat->term_id ) ) : array();
		if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) :
	?>
		<div class="filters">
			<a class="chip <?php echo ! $current_cat ? 'on' : ''; ?>" href="<?php echo caaguazu_search_filter_url( 'noticias' ); ?>"><?php esc_html_e( 'Todas las categorías', 'caaguazu' ); ?></a>
			<?php foreach ( $cats as $cat ) : ?>
				<a class="chip <?php echo $current_cat === $cat->slug ? 'on' : ''; ?>" href="<?php echo caaguazu_search_filter_url( 'noticias', $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; endif; ?>

	<?php if ( get_search_query() ) : ?>
		<?php if ( have_posts() ) : ?>
			<div class="news-grid" style="margin-top:48px">
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="news">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="img"><?php the_post_thumbnail( 'caaguazu-card', array( 'loading' => 'lazy' ) ); ?></div>
						<?php endif; ?>
						<div class="body">
							<?php
							$family_labels = array(
								'noticias'  => __( 'Noticias', 'caaguazu' ),
								'agenda'    => __( 'Agenda', 'caaguazu' ),
								'educacion' => __( 'Educación', 'caaguazu' ),
							);
							$result_family = 'post' === get_post_type() ? caaguazu_post_category_family( get_the_ID() ) : null;
							$badge         = $result_family && isset( $family_labels[ $result_family ] )
								? $family_labels[ $result_family ]
								: get_post_type_object( get_post_type() )->labels->singular_name;
							?>
							<span class="cat"><?php echo esc_html( $badge ); ?></span>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p>
							<a class="arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Ver', 'caaguazu' ); ?></a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<div class="search-empty"><?php printf( esc_html__( 'No hay resultados para "%s".', 'caaguazu' ), esc_html( get_search_query() ) ); ?></div>
		<?php endif; ?>
	<?php else : ?>
		<div class="search-empty"><?php esc_html_e( 'Escribí algo para empezar a buscar en el sitio.', 'caaguazu' ); ?></div>
	<?php endif; ?>
</div>

<?php get_footer();
