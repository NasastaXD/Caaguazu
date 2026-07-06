<?php
/**
 * Siembra la sección Turismo (páginas reales, migradas del sitio de turismo
 * que se fusionó a este portal) al activar el theme. Reimportable a mano
 * desde Apariencia → Caaguazú sin pisar páginas ya editadas.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_switch_theme', 'caaguazu_seed_tourism_on_activation' );

function caaguazu_seed_tourism_on_activation() {
	if ( get_option( 'caaguazu_tourism_seeded' ) ) {
		return;
	}
	caaguazu_run_tourism_seed();
	update_option( 'caaguazu_tourism_seeded', 1 );
}

/**
 * Devuelve el path completo (padre/hijo/nieto) de un slug de turismo,
 * caminando la cadena de 'parent' definida en caaguazu_tourism_pages().
 */
function caaguazu_tourism_full_path( $wp_slug, $pages ) {
	$segments = array( $wp_slug );
	$cursor   = isset( $pages[ $wp_slug ]['parent'] ) ? $pages[ $wp_slug ]['parent'] : null;
	while ( $cursor ) {
		array_unshift( $segments, $cursor );
		$cursor = isset( $pages[ $cursor ]['parent'] ) ? $pages[ $cursor ]['parent'] : null;
	}
	return implode( '/', $segments );
}

function caaguazu_run_tourism_seed() {
	$pages      = caaguazu_tourism_pages();
	$slug_to_id = array();

	foreach ( $pages as $wp_slug => $data ) {
		$full_path = caaguazu_tourism_full_path( $wp_slug, $pages );
		$existing  = get_page_by_path( $full_path );
		if ( $existing ) {
			$slug_to_id[ $wp_slug ] = $existing->ID;
			continue;
		}

		$parent_id = 0;
		if ( ! empty( $data['parent'] ) && isset( $slug_to_id[ $data['parent'] ] ) ) {
			$parent_id = $slug_to_id[ $data['parent'] ];
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $data['title'],
			'post_name'    => $wp_slug,
			'post_excerpt' => $data['excerpt'],
			'post_content' => $data['body'],
			'post_parent'  => $parent_id,
		) );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			$slug_to_id[ $wp_slug ] = $post_id;
			update_post_meta( $post_id, '_caaguazu_tourism', 1 );
		}
	}

	caaguazu_resolve_tourism_links( $pages, $slug_to_id );
	flush_rewrite_rules();
}

/**
 * Reemplaza los tokens #tourism-link:slug-original# (dejados por la migración
 * de contenido) por el permalink real de la página ya insertada.
 */
function caaguazu_resolve_tourism_links( $pages, $slug_to_id ) {
	$hub_id     = isset( $slug_to_id['turismo'] ) ? $slug_to_id['turismo'] : 0;
	$old_to_new = array(
		''     => $hub_id,
		'home' => $hub_id,
	);
	foreach ( $pages as $wp_slug => $data ) {
		if ( isset( $slug_to_id[ $wp_slug ] ) ) {
			$old_to_new[ $data['old_slug'] ] = $slug_to_id[ $wp_slug ];
		}
	}

	foreach ( $slug_to_id as $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || false === strpos( $post->post_content, '#tourism-link:' ) ) {
			continue;
		}
		$new_content = preg_replace_callback(
			'/#tourism-link:([^#]*)#/',
			function ( $matches ) use ( $old_to_new ) {
				$target_id = isset( $old_to_new[ $matches[1] ] ) ? $old_to_new[ $matches[1] ] : 0;
				return $target_id ? get_permalink( $target_id ) : home_url( '/' );
			},
			$post->post_content
		);
		if ( $new_content !== $post->post_content ) {
			wp_update_post( array(
				'ID'           => $post_id,
				'post_content' => $new_content,
			) );
		}
	}
}

add_action( 'admin_menu', 'caaguazu_tourism_admin_menu' );

function caaguazu_tourism_admin_menu() {
	add_theme_page(
		__( 'Caaguazú', 'caaguazu' ),
		__( 'Caaguazú', 'caaguazu' ),
		'manage_options',
		'caaguazu-tourism',
		'caaguazu_tourism_admin_page'
	);
}

function caaguazu_tourism_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['caaguazu_reseed_tourism'] ) && check_admin_referer( 'caaguazu_reseed_tourism' ) ) {
		caaguazu_run_tourism_seed();
		update_option( 'caaguazu_tourism_seeded', 1 );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Contenido de turismo re-importado.', 'caaguazu' ) . '</p></div>';
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Caaguazú — Contenido', 'caaguazu' ); ?></h1>
		<p><?php esc_html_e( 'Re-importa las páginas de la sección Turismo sin desactivar el theme. Las páginas que ya existen (por slug/jerarquía) no se pisan.', 'caaguazu' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'caaguazu_reseed_tourism' ); ?>
			<button type="submit" name="caaguazu_reseed_tourism" value="1" class="button button-primary">
				<?php esc_html_e( 'Re-importar páginas de Turismo', 'caaguazu' ); ?>
			</button>
		</form>
	</div>
	<?php
}
