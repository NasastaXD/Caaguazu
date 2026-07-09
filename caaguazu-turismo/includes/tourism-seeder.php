<?php
/**
 * Siembra la sección Turismo (páginas reales, migradas del sitio de turismo
 * que se fusionó a este portal) al activar el plugin. Reimportable a mano
 * desde Apariencia → Caaguazú sin pisar páginas ya editadas.
 *
 * @package Caaguazu_Turismo
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function caaguazu_seed_tourism_on_activation() {
	if ( get_option( 'caaguazu_tourism_seeded' ) ) {
		return;
	}
	caaguazu_run_tourism_seed();
	update_option( 'caaguazu_tourism_seeded', 1 );
}

/**
 * `register_activation_hook` solo corre al ACTIVAR este plugin — un sitio
 * que ya lo tuviera activo antes de un cambio de contenido nunca vuelve a
 * dispararlo. Catch-up en `admin_init`, mismo patrón que
 * caaguazu-theme/inc/core-pages-seeder.php.
 */
add_action( 'admin_init', 'caaguazu_seed_tourism_on_activation' );

/**
 * Antes de v1.1.0, 'sabores-de-caaguazu', 'vivir-caaguazu' y
 * 'planifica-tu-visita' eran páginas puente reales (un párrafo + botones,
 * sin contenido propio) — se eliminaron de caaguazu_tourism_pages() porque
 * no aportaban nada que el propio hub de Turismo no mostrara ya. En sitios
 * que ya las tenían sembradas, esta rutina reubica a sus páginas hijas
 * directamente bajo 'turismo' y borra la página puente, que de otro modo
 * quedaría huérfana y duplicada respecto a las páginas nuevas que crea el
 * reseed normal.
 *
 * v2→v3: la v2 solo buscaba la página puente anidada bajo turismo/
 * ('turismo/sabores-de-caaguazu'). En sitios sembrados con versiones más
 * viejas todavía, esas tres páginas viven en la RAÍZ del sitio
 * ('sabores-de-caaguazu', sin 'turismo/' delante) — la v2 no las
 * encontraba, no hacía nada, y aun así marcaba el flag como completo, así
 * que esos sitios quedaban con las tres páginas puente huérfanas para
 * siempre (con su contenido viejo pre-migración) y ningún reintento las
 * iba a volver a tocar. v3 prueba las dos ubicaciones posibles antes de
 * decidir que una página puente no existe.
 */
function caaguazu_tourism_flatten_hierarchy() {
	if ( get_option( 'caaguazu_tourism_flattened_v3' ) ) {
		return;
	}

	$hub = get_page_by_path( 'turismo' );
	if ( ! $hub ) {
		update_option( 'caaguazu_tourism_flattened_v3', 1 );
		return;
	}

	$old_hubs = array(
		'sabores-de-caaguazu' => array( 'platos-tipicos', 'donde-comer', 'mate-y-terere' ),
		'vivir-caaguazu'      => array( 'festividades', 'guarani-en-nuestra-ciudad', 'galeria' ),
		'planifica-tu-visita' => array( 'como-llegar', 'donde-alojarte', 'mejor-epoca', 'mapa-interactivo' ),
	);

	foreach ( $old_hubs as $old_hub_slug => $children ) {
		// La página puente pudo quedar anidada bajo turismo/ (lo que ya
		// cubría la v2) o directo en la raíz del sitio (sembrados más
		// viejos, antes de que existiera esa jerarquía) — probar las dos.
		foreach ( array( 'turismo/' . $old_hub_slug, $old_hub_slug ) as $old_hub_path ) {
			foreach ( $children as $child_slug ) {
				$child = get_page_by_path( $old_hub_path . '/' . $child_slug );
				if ( $child && (int) $child->post_parent !== (int) $hub->ID ) {
					wp_update_post( array( 'ID' => $child->ID, 'post_parent' => $hub->ID ) );
				}
			}

			$old_hub = get_page_by_path( $old_hub_path );
			if ( $old_hub ) {
				wp_delete_post( $old_hub->ID, true );
			}
		}
	}

	update_option( 'caaguazu_tourism_flattened_v3', 1 );
}
add_action( 'admin_init', 'caaguazu_tourism_flatten_hierarchy' );

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
