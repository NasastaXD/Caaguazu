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
	<p class="eyebrow" style="text-align:center"><?php esc_html_e( 'Buscar', 'caaguazu' ); ?></p>
	<h1 style="text-align:center;font-size:clamp(40px,6vw,64px);margin-top:16px"><?php esc_html_e( '¿Qué estás buscando?', 'caaguazu' ); ?></h1>

	<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<span style="margin-left:8px;color:var(--text-muted)">🔍</span>
		<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php esc_attr_e( 'Buscar páginas y noticias…', 'caaguazu' ); ?>"
			aria-label="<?php esc_attr_e( 'Búsqueda', 'caaguazu' ); ?>">
		<button type="submit"><?php esc_html_e( 'Buscar', 'caaguazu' ); ?></button>
	</form>

	<?php $current_type = caaguazu_search_post_type(); ?>
	<div class="filters">
		<?php
		$types = array(
			'any'           => array( 'search.chip.todos', __( 'Todos', 'caaguazu' ) ),
			'page'          => array( 'search.chip.paginas', __( 'Páginas', 'caaguazu' ) ),
			'caaguazu_news' => array( 'search.chip.noticias', __( 'Noticias', 'caaguazu' ) ),
		);
		foreach ( $types as $type => $i18n ) :
		?>
			<a class="chip <?php echo $current_type === $type ? 'on' : ''; ?>" href="<?php echo caaguazu_search_filter_url( $type ); ?>"><?php caaguazu_i18n( $i18n[0], $i18n[1] ); ?></a>
		<?php endforeach; ?>
	</div>

	<?php if ( 'caaguazu_news' === $current_type ) :
		$current_cat = caaguazu_search_news_cat();
		$cats        = get_terms( array( 'taxonomy' => 'caaguazu_news_cat', 'hide_empty' => true ) );
		if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) :
	?>
		<div class="filters">
			<a class="chip <?php echo ! $current_cat ? 'on' : ''; ?>" href="<?php echo caaguazu_search_filter_url( 'caaguazu_news' ); ?>"><?php esc_html_e( 'Todas las categorías', 'caaguazu' ); ?></a>
			<?php foreach ( $cats as $cat ) : ?>
				<a class="chip <?php echo $current_cat === $cat->slug ? 'on' : ''; ?>" href="<?php echo caaguazu_search_filter_url( 'caaguazu_news', $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></a>
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
							<span class="cat"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></span>
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
