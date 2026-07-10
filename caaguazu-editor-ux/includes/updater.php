<?php
/**
 * Auto-updater de plugin vía GitHub Releases — mismo repo y mismo release
 * que el theme (ver caaguazu-theme/inc/updater.php), pero comparando contra
 * el manifest.json que bin/build-zip.sh publica como asset: el tag del
 * release lleva la versión del THEME, no la de los plugins, así que
 * compararse contra el tag daría falsos updates. El manifest lista la
 * versión real de cada componente empaquetado.
 *
 * Cierra la clase de bugs "theme al día + plugin viejo" (el theme se
 * auto-actualiza desde 1.x; los plugins hasta ahora se reinstalaban a mano).
 *
 * La clase es genérica y este archivo vive COPIADO TAL CUAL en
 * caaguazu-modulos, caaguazu-turismo y caaguazu-editor-ux (plugins
 * independientes no pueden compartir archivos) — el guard class_exists hace
 * que el segundo en cargar reutilice la clase del primero. Cada plugin la
 * instancia con sus datos desde su bootstrap. Si se toca este archivo,
 * copiar el cambio a los gemelos.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Caaguazu_Component_Updater' ) ) :

class Caaguazu_Component_Updater {

	const REPO = 'NasastaXD/Caaguazu';
	// Compartidas entre el theme y los plugins: es el mismo release y el
	// mismo manifest — una sola pegada a la API para todos los componentes.
	const RELEASE_CACHE_KEY  = 'caaguazu_github_release';
	const MANIFEST_CACHE_KEY = 'caaguazu_release_manifest';
	const CACHE_TTL          = 12 * HOUR_IN_SECONDS;

	/** @var string p. ej. "caaguazu-editor-ux/caaguazu-editor-ux.php" */
	private $basename;

	/** @var string carpeta/slug del plugin, y nombre del zip ("{slug}.zip") y clave en el manifest. */
	private $slug;

	/** @var string versión instalada. */
	private $version;

	/** @var string nombre legible para el modal de detalles. */
	private $name;

	public function __construct( $plugin_file, $version, $slug, $name ) {
		$this->basename = plugin_basename( $plugin_file );
		$this->version  = $version;
		$this->slug     = $slug;
		$this->name     = $name;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
		add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ), 10, 2 );
		// El "Volver a comprobar" nativo (?force-check=1) — mismo fix que el theme.
		add_action( 'load-update-core.php', array( $this, 'maybe_force_check' ) );
	}

	public function maybe_force_check() {
		if ( ! empty( $_GET['force-check'] ) ) {
			$this->clear_cache();
		}
	}

	private function get_latest_release() {
		$cached = get_transient( self::RELEASE_CACHE_KEY );
		if ( false !== $cached ) {
			return $cached;
		}

		$response = wp_remote_get(
			sprintf( 'https://api.github.com/repos/%s/releases/latest', self::REPO ),
			array(
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'Caaguazu-Plugin-Updater',
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			// Sin releases (404) u otro error: cachear "nada" un rato corto.
			set_transient( self::RELEASE_CACHE_KEY, array(), 15 * MINUTE_IN_SECONDS );
			return array();
		}

		$release = json_decode( wp_remote_retrieve_body( $response ), true );
		$release = is_array( $release ) ? $release : array();
		set_transient( self::RELEASE_CACHE_KEY, $release, self::CACHE_TTL );
		return $release;
	}

	private function find_asset( $release, $name ) {
		foreach ( $release['assets'] ?? array() as $asset ) {
			if ( isset( $asset['name'], $asset['browser_download_url'] ) && $name === $asset['name'] ) {
				return $asset;
			}
		}
		return null;
	}

	/**
	 * manifest.json del último release: { "caaguazu-theme": "3.0.0",
	 * "caaguazu-modulos": "1.4.0", ... }. Releases anteriores a la 3.0 no lo
	 * traen — en ese caso no se ofrece update (mejor que ofrecer uno falso).
	 */
	private function get_manifest() {
		$cached = get_transient( self::MANIFEST_CACHE_KEY );
		if ( false !== $cached ) {
			return $cached;
		}

		$manifest = array();
		$release  = $this->get_latest_release();
		$asset    = $this->find_asset( $release, 'manifest.json' );
		if ( $asset ) {
			$response = wp_remote_get( $asset['browser_download_url'], array(
				'headers' => array( 'User-Agent' => 'Caaguazu-Plugin-Updater' ),
				'timeout' => 10,
			) );
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$decoded  = json_decode( wp_remote_retrieve_body( $response ), true );
				$manifest = is_array( $decoded ) ? $decoded : array();
			}
		}

		set_transient( self::MANIFEST_CACHE_KEY, $manifest, $manifest ? self::CACHE_TTL : 15 * MINUTE_IN_SECONDS );
		return $manifest;
	}

	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$manifest = $this->get_manifest();
		if ( empty( $manifest[ $this->slug ] ) ) {
			return $transient;
		}

		$remote_version = $manifest[ $this->slug ];
		if ( ! version_compare( $remote_version, $this->version, '>' ) ) {
			return $transient;
		}

		$release = $this->get_latest_release();
		$asset   = $this->find_asset( $release, $this->slug . '.zip' );
		if ( ! $asset ) {
			return $transient;
		}

		$transient->response[ $this->basename ] = (object) array(
			'slug'        => $this->slug,
			'plugin'      => $this->basename,
			'new_version' => $remote_version,
			'url'         => isset( $release['html_url'] ) ? $release['html_url'] : '',
			'package'     => $asset['browser_download_url'],
		);

		return $transient;
	}

	/**
	 * Modal "Ver detalles de la versión X": changelog = body del release.
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || $this->slug !== $args->slug ) {
			return $result;
		}

		$manifest = $this->get_manifest();
		$release  = $this->get_latest_release();
		if ( empty( $manifest[ $this->slug ] ) || empty( $release['tag_name'] ) ) {
			return $result;
		}

		$asset = $this->find_asset( $release, $this->slug . '.zip' );

		return (object) array(
			'name'          => $this->name,
			'slug'          => $this->slug,
			'version'       => $manifest[ $this->slug ],
			'sections'      => array(
				'changelog' => wpautop( wp_kses_post( isset( $release['body'] ) ? $release['body'] : '' ) ),
			),
			'download_link' => $asset ? $asset['browser_download_url'] : '',
		);
	}

	public function clear_cache( $upgrader = null, $hook_extra = array() ) {
		delete_transient( self::RELEASE_CACHE_KEY );
		delete_transient( self::MANIFEST_CACHE_KEY );
	}
}

endif;
