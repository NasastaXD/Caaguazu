<?php
/**
 * SEO/Open Graph básico: meta description, canonical, Open Graph y
 * Twitter Card. Sin plugin — solo lo esencial para que noticias y
 * páginas se vean bien al compartirse.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_seo_description() {
	if ( is_singular() ) {
		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return wp_strip_all_tags( $excerpt );
		}
		global $post;
		if ( $post ) {
			return wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '…' );
		}
	}
	return get_bloginfo( 'description' ) ?: __( 'Portal oficial del departamento de Caaguazú, Paraguay.', 'caaguazu' );
}

function caaguazu_seo_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$src = get_the_post_thumbnail_url( get_the_ID(), 'caaguazu-card' );
		if ( $src ) {
			return $src;
		}
	}
	if ( has_custom_logo() ) {
		$logo_id = get_theme_mod( 'custom_logo' );
		$src     = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		if ( $src ) {
			return $src;
		}
	}
	return caaguazu_opt_image( 'hero_poster', 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1920&q=80' );
}

function caaguazu_seo_title() {
	if ( is_singular() ) {
		return get_the_title() . ' — ' . get_bloginfo( 'name' );
	}
	if ( is_search() ) {
		return sprintf( __( 'Buscar "%s" — %s', 'caaguazu' ), get_search_query(), get_bloginfo( 'name' ) );
	}
	return get_bloginfo( 'name' ) . ' — ' . get_bloginfo( 'description' );
}

function caaguazu_seo_head() {
	if ( is_admin() ) {
		return;
	}

	$description = caaguazu_seo_description();
	$image       = caaguazu_seo_image();
	$title       = caaguazu_seo_title();
	if ( is_singular() ) {
		$url = get_permalink();
	} elseif ( is_front_page() ) {
		$url = home_url( '/' );
	} else {
		$url = home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) );
	}
	$type        = is_singular( 'caaguazu_news' ) ? 'article' : 'website';
	?>
	<meta name="description" content="<?php echo esc_attr( $description ); ?>">
	<link rel="canonical" href="<?php echo esc_url( $url ); ?>">
	<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
	<meta property="og:locale" content="es_PY">
	<meta property="og:type" content="<?php echo esc_attr( $type ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
	<?php if ( $image ) : ?>
		<meta property="og:image" content="<?php echo esc_url( $image ); ?>">
	<?php endif; ?>
	<?php if ( 'article' === $type ) : ?>
		<meta property="article:published_time" content="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
		<meta property="article:modified_time" content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">
	<?php endif; ?>
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
	<?php if ( $image ) : ?>
		<meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">
	<?php endif; ?>
	<?php
}
add_action( 'wp_head', 'caaguazu_seo_head', 1 );
