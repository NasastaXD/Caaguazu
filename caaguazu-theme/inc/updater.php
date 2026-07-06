<?php
/**
 * Auto-updater del theme vía GitHub Releases (repo público, sin token).
 *
 * El repo tiene el theme en una subcarpeta (caaguazu-theme/), así que el
 * update NO usa el zip de código fuente que arma GitHub para un tag (trae
 * todo el repo con la carpeta envolvente equivocada) — apunta al asset
 * caaguazu-theme.zip que el workflow .github/workflows/release.yml sube a
 * cada Release, empaquetado con bin/build-zip.sh.
 *
 * Cada Release se crea solo al mergear a main un bump de "Version:" en
 * style.css (ver ese workflow) — no hace falta taggear a mano.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Caaguazu_GitHub_Updater {

	const SLUG       = 'caaguazu-theme';
	const REPO       = 'NasastaXD/Caaguazu';
	const ASSET_NAME = 'caaguazu-theme.zip';
	const CACHE_KEY  = 'caaguazu_github_release';
	const CACHE_TTL  = 12 * HOUR_IN_SECONDS;

	public function __construct() {
		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_update' ) );
		add_filter( 'themes_api', array( $this, 'theme_info' ), 10, 3 );
		add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ), 10, 2 );
	}

	/**
	 * GET /repos/{repo}/releases/latest, cacheado 12h para no pegarle
	 * de más a la API pública de GitHub (60 req/hora sin token, por IP).
	 */
	private function get_latest_release() {
		$cached = get_transient( self::CACHE_KEY );
		if ( false !== $cached ) {
			return $cached;
		}

		$response = wp_remote_get(
			sprintf( 'https://api.github.com/repos/%s/releases/latest', self::REPO ),
			array(
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'Caaguazu-Theme-Updater',
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			// Sin releases todavía (404) u otro error de red: cachear "nada"
			// un rato corto para no reintentar en cada carga del admin.
			set_transient( self::CACHE_KEY, array(), 15 * MINUTE_IN_SECONDS );
			return array();
		}

		$release = json_decode( wp_remote_retrieve_body( $response ), true );
		$release = is_array( $release ) ? $release : array();
		set_transient( self::CACHE_KEY, $release, self::CACHE_TTL );
		return $release;
	}

	private function find_zip_asset( $release ) {
		foreach ( $release['assets'] ?? array() as $asset ) {
			if ( isset( $asset['name'], $asset['browser_download_url'] ) && self::ASSET_NAME === $asset['name'] ) {
				return $asset;
			}
		}
		return null;
	}

	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_latest_release();
		if ( empty( $release['tag_name'] ) ) {
			return $transient;
		}

		$remote_version  = ltrim( $release['tag_name'], 'v' );
		$current_version = isset( $transient->checked[ self::SLUG ] ) ? $transient->checked[ self::SLUG ] : '0';

		if ( version_compare( $remote_version, $current_version, '>' ) ) {
			$asset = $this->find_zip_asset( $release );
			if ( $asset ) {
				$transient->response[ self::SLUG ] = array(
					'theme'       => self::SLUG,
					'new_version' => $remote_version,
					'url'         => $release['html_url'],
					'package'     => $asset['browser_download_url'],
				);
			}
		}

		return $transient;
	}

	/**
	 * Alimenta el modal "Ver detalles de la versión X" con el changelog
	 * (body del Release en GitHub, generado por --generate-notes).
	 */
	public function theme_info( $result, $action, $args ) {
		if ( 'theme_information' !== $action || empty( $args->slug ) || self::SLUG !== $args->slug ) {
			return $result;
		}

		$release = $this->get_latest_release();
		if ( empty( $release['tag_name'] ) ) {
			return $result;
		}

		$asset = $this->find_zip_asset( $release );

		return (object) array(
			'name'          => 'Caaguazú',
			'slug'          => self::SLUG,
			'version'       => ltrim( $release['tag_name'], 'v' ),
			'sections'      => array(
				'changelog' => wpautop( wp_kses_post( $release['body'] ?? '' ) ),
			),
			'download_link' => $asset ? $asset['browser_download_url'] : '',
		);
	}

	public function clear_cache( $upgrader = null, $hook_extra = array() ) {
		delete_transient( self::CACHE_KEY );
	}
}

new Caaguazu_GitHub_Updater();
