<?php
/**
 * De qué rol viene del CEAD a qué rol del panel de Promotores.
 *
 * POR QUÉ ESTO DEJÓ DE SER UNA CONSTANTE
 *
 * Hasta acá el mapa era una constante de dos entradas dentro de
 * `CEADSSO_Link`:
 *
 *     'alumno_turismo'  => 'promotur_mini'
 *     'docente_turismo' => 'promotur_promotor'
 *
 * y cualquier otra cosa se rechazaba con «Tu rol no está habilitado para
 * entrar al portal todavía». Eso funciona sólo si el CEAD manda exactamente
 * esos dos textos, y el CEAD es un WordPress: sus roles son roles de
 * WordPress. Lo que sale de ahí tiene la forma que le dio quien lo instaló —
 * `alumno`, `cead_alumno`, `estudiante`, `subscriber`, `Docente`— y ninguna de
 * esas cuatro es `alumno_turismo`. Del lado de acá la identidad no es de
 * WordPress: son cuentas del plugin propio, con roles `promotur_*`. Los dos
 * lados hablan de lo mismo con vocabularios distintos, y el puente entre los
 * dos vocabularios no puede estar congelado en una constante.
 *
 * Y el rechazo era además ciego: `CEADSSO_Log::record()` recibía los claims con
 * la clave `rol`, pero guarda la columna `rol_cead` — así que en el registro de
 * intentos el rol quedaba siempre en NULL. La pantalla de admin mostraba «—» y
 * no había forma de saber qué rol había llegado. Eso también se arregla acá:
 * el rol crudo se registra, se ve, y se puede mapear de un clic.
 *
 * CÓMO FUNCIONA AHORA
 *
 * Tres capas, de la más específica a la más general:
 *
 *   1. El mapa que un administrador editó (opción `caaguazu_sso_cead_roles`).
 *   2. El mapa base de abajo, que cubre el contrato original y las formas que
 *      un WordPress suele mandar.
 *   3. Normalización: minúsculas, sin acentos, sin el prefijo del colegio y
 *      sin el sufijo del curso. `Cead_Docente_Turismo` y `docente` caen las dos
 *      en `docente`.
 *
 * Lo que NO hace, y a propósito: inventar un permiso. Un rol que no cae en
 * ninguna de las tres capas se sigue rechazando. La diferencia es que ahora se
 * puede ver cuál era y resolverlo desde Herramientas → Acceso desde el CEAD,
 * en vez de tener que adivinarlo.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CEADSSO_Roles {

	/** Opción donde vive el mapa que edita un administrador. */
	const OPTION = 'caaguazu_sso_cead_roles';

	/**
	 * Mapa base: rol normalizado del CEAD → rol del panel `promotor`.
	 *
	 * Las dos primeras entradas son el contrato original. El resto son las
	 * formas que efectivamente manda un WordPress —el rol propio del curso, el
	 * rol nativo equivalente, y el nombre en castellano suelto—, porque el
	 * CEAD es uno y sus roles son suyos.
	 *
	 * Sólo hay dos destinos posibles, y ninguno es Promotor completo por
	 * accidente: quien enseña entra como Promotor, quien cursa entra como Mini
	 * Promotor. Un rol administrativo del colegio (`administrator`, `editor`)
	 * NO está acá: ser administrador del CEAD no es ser promotor turístico de
	 * Caaguazú, y confundir las dos cosas sería regalar el panel entero.
	 *
	 * @return array
	 */
	public static function base() {
		return array(
			// El contrato original, ya normalizado (pierde `_turismo`).
			'alumno'      => 'promotur_mini',
			'docente'     => 'promotur_promotor',
			// Las otras formas de decir lo mismo.
			'estudiante'  => 'promotur_mini',
			'cursante'    => 'promotur_mini',
			'subscriber'  => 'promotur_mini',
			'profesor'    => 'promotur_promotor',
			'instructor'  => 'promotur_promotor',
			'tutor'       => 'promotur_promotor',
			'coordinador' => 'promotur_promotor',
		);
	}

	/**
	 * Los roles del panel a los que se puede mapear, con su nombre legible.
	 *
	 * Se piden al panel y no se escriben acá: si mañana `caaguazu-portal`
	 * agrega o renombra un rol, esta pantalla lo refleja sola. Si el panel no
	 * está cargado (no debería pasar: es dependencia dura), se cae a los tres
	 * que existen hoy para que la pantalla siga sirviendo.
	 *
	 * @return array clave => etiqueta
	 */
	public static function roles_del_panel() {
		if ( class_exists( 'PROMOTUR_Roles' ) ) {
			$out = array();
			foreach ( PROMOTUR_Roles::roles() as $clave => $def ) {
				$out[ $clave ] = $def['label'];
			}
			return $out;
		}
		return array(
			'promotur_promotor'  => __( 'Promotor', 'caaguazu-sso-cead' ),
			'promotur_mini'      => __( 'Mini Promotor', 'caaguazu-sso-cead' ),
			'promotur_visitante' => __( 'Visitante', 'caaguazu-sso-cead' ),
		);
	}

	/**
	 * Normaliza un rol tal como lo manda el CEAD.
	 *
	 *   «Cead_Docente_Turismo» → «docente»
	 *   «Alumno del curso»     → «alumno_del_curso»
	 *   «DOCENTE»              → «docente»
	 *
	 * @param string $rol
	 * @return string
	 */
	public static function normalizar( $rol ) {
		$rol = (string) $rol;

		// Acentos fuera: «matrícula» y «matricula» son el mismo rol, y quien
		// escribe el rol del otro lado no tiene por qué saber cuál usamos acá.
		//
		// El reemplazo propio no es paranoia: sin él, la línea que limpia los
		// caracteres raros se lleva puesta la vocal acentuada entera y
		// «matrícula» queda en «matrcula», que no coincide con nada y falla en
		// silencio. remove_accents() está siempre en WordPress; esto cubre a
		// esta clase corriendo fuera de él (una prueba, un script).
		$rol = function_exists( 'remove_accents' )
			? remove_accents( $rol )
			: strtr(
				$rol,
				array(
					'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
					'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
					'ã' => 'a', 'ẽ' => 'e', 'ĩ' => 'i', 'õ' => 'o', 'ũ' => 'u', 'ỹ' => 'y', 'ÿ' => 'y',
					'Ã' => 'A', 'Ẽ' => 'E', 'Ĩ' => 'I', 'Õ' => 'O', 'Ũ' => 'U', 'Ỹ' => 'Y',
				)
			);
		$rol = strtolower( trim( $rol ) );
		$rol = preg_replace( '/[\s\-]+/', '_', $rol );
		$rol = preg_replace( '/[^a-z0-9_]/', '', $rol );
		$rol = trim( (string) $rol, '_' );

		// El prefijo del colegio y el sufijo del curso no distinguen roles:
		// los agrega quien instaló el plugin de allá, no la persona.
		$rol = preg_replace( '/^(cead|czu|caaguazu)_/', '', $rol );
		$rol = preg_replace( '/_(turismo|servicios_turisticos|st)$/', '', $rol );

		return (string) $rol;
	}

	/**
	 * El mapa efectivo: el base, pisado por el que editó un administrador.
	 *
	 * @return array rol normalizado del CEAD => rol del panel
	 */
	public static function mapa() {
		$guardado = get_option( self::OPTION, array() );
		$guardado = is_array( $guardado ) ? $guardado : array();

		$limpio = array();
		foreach ( $guardado as $origen => $destino ) {
			$origen  = self::normalizar( $origen );
			$destino = sanitize_key( $destino );
			if ( '' === $origen || '' === $destino ) {
				continue;
			}
			$limpio[ $origen ] = $destino;
		}

		/**
		 * Permite ajustar el mapa desde otro plugin (o desde wp-config).
		 *
		 * @param array $mapa rol normalizado del CEAD => rol del panel
		 */
		return apply_filters( 'caaguazu_sso_cead_mapa_roles', array_merge( self::base(), $limpio ) );
	}

	/**
	 * Guarda el mapa editado por un administrador.
	 *
	 * Sólo se guarda lo que difiere del mapa base: la opción es el conjunto de
	 * decisiones que alguien tomó, no una copia del archivo. Así, si mañana el
	 * mapa base cubre un caso más, esa entrada mejora sola en vez de quedar
	 * congelada en la base de datos.
	 *
	 * @param array $mapa rol del CEAD => rol del panel
	 * @return bool
	 */
	public static function guardar_mapa( array $mapa ) {
		$base   = self::base();
		$roles  = self::roles_del_panel();
		$limpio = array();

		foreach ( $mapa as $origen => $destino ) {
			$origen  = self::normalizar( $origen );
			$destino = sanitize_key( $destino );
			if ( '' === $origen || '' === $destino ) {
				continue;
			}
			// Un destino que no es un rol real del panel no se guarda: sería
			// un grant con un rol inexistente, o sea una cuenta sin ninguna
			// capability, entrando a un panel que le va a dar 403.
			if ( ! isset( $roles[ $destino ] ) ) {
				continue;
			}
			if ( isset( $base[ $origen ] ) && $base[ $origen ] === $destino ) {
				continue; // ya lo dice el mapa base.
			}
			$limpio[ $origen ] = $destino;
		}

		$guardado = update_option( self::OPTION, $limpio );
		// El aviso de wp-admin lee un caché: sin esto, el rol recién habilitado
		// seguiría figurando como pendiente hasta diez minutos después.
		if ( class_exists( 'CEADSSO_Validacion' ) ) {
			CEADSSO_Validacion::olvidar_cache();
		}
		return $guardado;
	}

	/**
	 * Resuelve el rol del panel para un rol del CEAD.
	 *
	 * @param string $rol_cead tal como llegó
	 * @return string|null rol del panel, o null si no se reconoce
	 */
	public static function resolver( $rol_cead ) {
		$mapa   = self::mapa();
		$normal = self::normalizar( $rol_cead );
		if ( '' === $normal ) {
			return null;
		}
		return isset( $mapa[ $normal ] ) ? $mapa[ $normal ] : null;
	}
}
