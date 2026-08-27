<?php
/**
 * Comprobaciones de la integración con el CEAD.
 *
 * POR QUÉ EXISTE
 *
 * Cuando el acceso desde el CEAD no funciona, lo único que se ve es una
 * pantalla que dice «no pudimos verificar tu acceso» — y del otro lado hay
 * cinco piezas que tienen que estar bien a la vez: dos constantes en
 * `wp-config.php`, dos plugins activos, una regla de reescritura, el panel de
 * promotores registrado en el sistema de cuentas, y un endpoint del otro sitio
 * que puede no existir todavía. Adivinar cuál de las cinco falló, mirando un
 * mensaje que a propósito no da detalles, es el peor uso posible del tiempo de
 * quien administra esto.
 *
 * Cada comprobación devuelve tres cosas: qué se miró, cómo salió, y —cuando
 * sale mal— qué hacer. Sin ese tercer campo un diagnóstico es un lamento.
 *
 * Los tres estados son `ok`, `aviso` (funciona, pero hay algo que mirar) y
 * `falla` (esto no puede andar así).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class CEADSSO_Validacion {

	/** Transient del aviso de wp-admin. Ver roles_sin_mapear(). */
	const CACHE_SIN_MAPEAR = 'ceadsso_roles_sin_mapear';

	/**
	 * Todas las comprobaciones que no salen a la red.
	 *
	 * @return array[] { clave, titulo, estado, detalle, arreglo }
	 */
	public static function comprobar() {
		return array(
			self::dependencias(),
			self::constantes(),
			self::ruta(),
			self::panel(),
			self::mapa_de_roles(),
			self::rechazos_recientes(),
		);
	}

	private static function item( $clave, $titulo, $estado, $detalle, $arreglo = '' ) {
		return compact( 'clave', 'titulo', 'estado', 'detalle', 'arreglo' );
	}

	/* --------------------------------------------------------------------- */

	private static function dependencias() {
		$faltan = array();
		if ( ! function_exists( 'caaguazu_account_grant' ) ) { $faltan[] = 'Caaguazú Cuentas'; }
		if ( ! function_exists( 'promotur_url' ) )           { $faltan[] = 'Caaguazú Portal'; }

		if ( $faltan ) {
			return self::item(
				'dependencias',
				__( 'Los dos plugins de los que depende', 'caaguazu-sso-cead' ),
				'falla',
				sprintf(
					/* translators: %s = lista de plugins */
					__( 'Falta activar: %s.', 'caaguazu-sso-cead' ),
					implode( ', ', $faltan )
				),
				__( 'Activalos desde Plugins. Sin ellos, /acceso-cead responde 503.', 'caaguazu-sso-cead' )
			);
		}
		return self::item( 'dependencias', __( 'Los dos plugins de los que depende', 'caaguazu-sso-cead' ), 'ok', __( 'Caaguazú Cuentas y Caaguazú Portal están activos.', 'caaguazu-sso-cead' ) );
	}

	private static function constantes() {
		$secret = defined( 'CEAD_TUR_SSO_SECRET' ) ? (string) CEAD_TUR_SSO_SECRET : '';
		$url    = defined( 'CEAD_TUR_SSO_URL' ) ? (string) CEAD_TUR_SSO_URL : '';

		if ( '' === $secret || '' === $url ) {
			return self::item(
				'constantes',
				__( 'La configuración de wp-config.php', 'caaguazu-sso-cead' ),
				'falla',
				__( 'Falta CEAD_TUR_SSO_SECRET y/o CEAD_TUR_SSO_URL.', 'caaguazu-sso-cead' ),
				__( 'Agregalas a wp-config.php. El secreto tiene que ser idéntico al del sitio del CEAD, y la URL es la de su endpoint de canje.', 'caaguazu-sso-cead' )
			);
		}

		// Un secreto corto no falla el canje, lo debilita: se firma igual, y
		// nadie se entera hasta que alguien lo prueba a la fuerza.
		if ( strlen( $secret ) < 32 ) {
			return self::item(
				'constantes',
				__( 'La configuración de wp-config.php', 'caaguazu-sso-cead' ),
				'aviso',
				__( 'Las dos constantes están, pero el secreto compartido es corto.', 'caaguazu-sso-cead' ),
				__( 'Usá 64 caracteres hexadecimales. Un secreto corto firma igual y se adivina antes.', 'caaguazu-sso-cead' )
			);
		}

		if ( 0 !== strpos( $url, 'https://' ) ) {
			return self::item(
				'constantes',
				__( 'La configuración de wp-config.php', 'caaguazu-sso-cead' ),
				'aviso',
				__( 'Las dos constantes están, pero la URL del CEAD no es https.', 'caaguazu-sso-cead' ),
				__( 'Por ahí viajan el email y el nombre de cada persona. Tiene que ser https.', 'caaguazu-sso-cead' )
			);
		}

		return self::item(
			'constantes',
			__( 'La configuración de wp-config.php', 'caaguazu-sso-cead' ),
			'ok',
			sprintf(
				/* translators: %s = URL del endpoint del CEAD */
				__( 'Secreto cargado y endpoint del CEAD en %s.', 'caaguazu-sso-cead' ),
				$url
			)
		);
	}

	private static function ruta() {
		$reglas = get_option( 'rewrite_rules' );
		if ( is_array( $reglas ) && isset( $reglas['^acceso-cead/?$'] ) ) {
			return self::item( 'ruta', __( 'La dirección /acceso-cead', 'caaguazu-sso-cead' ), 'ok', __( 'La regla de reescritura está guardada.', 'caaguazu-sso-cead' ) );
		}
		return self::item(
			'ruta',
			__( 'La dirección /acceso-cead', 'caaguazu-sso-cead' ),
			'falla',
			__( 'La regla de reescritura no está: la URL de acceso va a caer en un 404.', 'caaguazu-sso-cead' ),
			__( 'Entrá a Ajustes → Enlaces permanentes y guardá (sin cambiar nada). Eso la regenera.', 'caaguazu-sso-cead' )
		);
	}

	/**
	 * El panel `promotor` registrado en el sistema de cuentas.
	 *
	 * Esta es la comprobación que más importa y la que menos se ve: el grant
	 * que este plugin otorga guarda un snapshot de las capabilities del rol, y
	 * ese snapshot lo pide al registro de paneles. Si el panel no está
	 * registrado en el momento del canje, el grant se guarda vacío: la persona
	 * entra «con permiso» y el panel le da 403 en la primera pantalla, sin que
	 * nada haya fallado visiblemente.
	 */
	private static function panel() {
		if ( ! class_exists( 'Caaguazu_Cuentas_Panels' ) ) {
			return self::item(
				'panel',
				__( 'El panel de promotores, en el sistema de cuentas', 'caaguazu-sso-cead' ),
				'falla',
				__( 'El sistema de cuentas no está cargado.', 'caaguazu-sso-cead' ),
				__( 'Activá Caaguazú Cuentas.', 'caaguazu-sso-cead' )
			);
		}

		$panel = Caaguazu_Cuentas_Panels::instance()->panel( 'promotor' );
		if ( ! $panel || empty( $panel['roles'] ) ) {
			return self::item(
				'panel',
				__( 'El panel de promotores, en el sistema de cuentas', 'caaguazu-sso-cead' ),
				'falla',
				__( 'El panel «promotor» no está registrado, o no declara ningún rol.', 'caaguazu-sso-cead' ),
				__( 'Lo registra Caaguazú Portal al arrancar. Comprobá que esté activo y sin errores.', 'caaguazu-sso-cead' )
			);
		}

		return self::item(
			'panel',
			__( 'El panel de promotores, en el sistema de cuentas', 'caaguazu-sso-cead' ),
			'ok',
			sprintf(
				/* translators: %s = lista de roles */
				__( 'Registrado, con estos roles: %s.', 'caaguazu-sso-cead' ),
				implode( ', ', array_keys( $panel['roles'] ) )
			)
		);
	}

	/**
	 * Cada destino del mapa de roles tiene que existir de verdad en el panel.
	 */
	private static function mapa_de_roles() {
		$mapa    = CEADSSO_Roles::mapa();
		$validos = CEADSSO_Roles::roles_del_panel();
		$rotos   = array();

		foreach ( $mapa as $origen => $destino ) {
			if ( ! isset( $validos[ $destino ] ) ) {
				$rotos[] = $origen . ' → ' . $destino;
			}
		}

		if ( $rotos ) {
			return self::item(
				'mapa',
				__( 'El mapa de roles', 'caaguazu-sso-cead' ),
				'falla',
				sprintf(
					/* translators: %s = entradas rotas del mapa */
					__( 'Estas entradas apuntan a un rol que el panel no tiene: %s.', 'caaguazu-sso-cead' ),
					implode( ', ', $rotos )
				),
				__( 'Corregilas en la tabla de abajo. Un grant con un rol inexistente deja a la persona sin ninguna capability.', 'caaguazu-sso-cead' )
			);
		}

		return self::item(
			'mapa',
			__( 'El mapa de roles', 'caaguazu-sso-cead' ),
			'ok',
			sprintf(
				/* translators: %d = cuántas equivalencias hay cargadas */
				_n( '%d equivalencia cargada, y todas apuntan a un rol que existe.', '%d equivalencias cargadas, y todas apuntan a un rol que existe.', count( $mapa ), 'caaguazu-sso-cead' ),
				count( $mapa )
			)
		);
	}

	/**
	 * Roles que llegaron y se rechazaron: el síntoma que se ve en la práctica.
	 */
	private static function rechazos_recientes() {
		$sin_mapear = self::roles_sin_mapear();

		if ( ! $sin_mapear ) {
			return self::item( 'rechazos', __( 'Roles rechazados últimamente', 'caaguazu-sso-cead' ), 'ok', __( 'Ninguno.', 'caaguazu-sso-cead' ) );
		}

		return self::item(
			'rechazos',
			__( 'Roles rechazados últimamente', 'caaguazu-sso-cead' ),
			'aviso',
			sprintf(
				/* translators: %s = lista de roles */
				__( 'El CEAD mandó estos roles y no supimos qué hacer con ellos: %s.', 'caaguazu-sso-cead' ),
				implode( ', ', $sin_mapear )
			),
			__( 'Agregalos a la tabla de abajo con el rol del portal que les corresponda. La próxima vez que esa persona entre, pasa.', 'caaguazu-sso-cead' )
		);
	}

	/**
	 * Los roles que aparecen rechazados en el log y que hoy siguen sin mapear.
	 *
	 * Se filtra contra el mapa actual porque un rol que ya se mapeó no es un
	 * pendiente: mostrarlo sería pedir dos veces el mismo trabajo.
	 *
	 * @return string[] roles crudos, como llegaron
	 */
	public static function roles_sin_mapear() {
		// El aviso de wp-admin llama a esto en cada pantalla del escritorio, y
		// esto lee 200 filas del log. Se cachea diez minutos: un rol nuevo que
		// aparece tarda a lo sumo eso en avisarse, y guardar el mapa borra el
		// caché — así la pantalla que lo arregla ve el efecto al instante.
		$cache = get_transient( self::CACHE_SIN_MAPEAR );
		if ( is_array( $cache ) ) {
			return $cache;
		}

		$out = array();
		foreach ( CEADSSO_Log::recent( 200 ) as $fila ) {
			if ( 'rechazado' !== $fila['resultado'] || 'rol_desconocido' !== $fila['motivo'] ) {
				continue;
			}
			$rol = (string) $fila['rol_cead'];
			if ( '' === $rol || CEADSSO_Roles::resolver( $rol ) ) {
				continue;
			}
			$out[ CEADSSO_Roles::normalizar( $rol ) ] = $rol;
		}

		$out = array_values( $out );
		set_transient( self::CACHE_SIN_MAPEAR, $out, 10 * MINUTE_IN_SECONDS );
		return $out;
	}

	/** Se llama al guardar el mapa de roles. */
	public static function olvidar_cache() {
		delete_transient( self::CACHE_SIN_MAPEAR );
	}

	/* --------------------------------------------------------------------- */

	/**
	 * La prueba que sí sale a la red: ¿existe el endpoint del CEAD?
	 *
	 * Se manda un código deliberadamente inválido. Lo que se está comprobando
	 * no es que el canje funcione —para eso hace falta un código real, de un
	 * solo uso— sino que del otro lado haya alguien atendiendo: un 404
	 * `rest_no_route` significa que el plugin emisor no está instalado, y eso
	 * es indistinguible de «tu enlace venció» para quien intenta entrar.
	 *
	 * No corre sola al abrir la pantalla: es un pedido a otro servidor, y esas
	 * cosas se hacen cuando alguien las pide.
	 *
	 * @return array { estado, detalle, arreglo }
	 */
	public static function probar_endpoint() {
		if ( ! CEADSSO_Redeem::configured() ) {
			return self::item(
				'endpoint',
				__( 'El endpoint del CEAD', 'caaguazu-sso-cead' ),
				'falla',
				__( 'No se puede probar: faltan las constantes de wp-config.php.', 'caaguazu-sso-cead' ),
				__( 'Cargalas primero.', 'caaguazu-sso-cead' )
			);
		}

		// 64 ceros: tiene la forma que el contrato pide y no puede ser un
		// código real, así que no se quema el canje de nadie.
		$code = str_repeat( '0', 64 );
		$ts   = time();
		$sig  = hash_hmac( 'sha256', $code . '|' . $ts, CEAD_TUR_SSO_SECRET );

		$respuesta = wp_remote_post( CEAD_TUR_SSO_URL, array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array( 'code' => $code, 'ts' => $ts, 'sig' => $sig ) ),
			'timeout' => 10,
		) );

		if ( is_wp_error( $respuesta ) ) {
			return self::item(
				'endpoint',
				__( 'El endpoint del CEAD', 'caaguazu-sso-cead' ),
				'falla',
				sprintf(
					/* translators: %s = mensaje de error de red */
					__( 'No se pudo llegar: %s', 'caaguazu-sso-cead' ),
					$respuesta->get_error_message()
				),
				__( 'Comprobá la URL, el certificado del sitio del CEAD y que este servidor pueda salir a internet.', 'caaguazu-sso-cead' )
			);
		}

		$codigo = (int) wp_remote_retrieve_response_code( $respuesta );
		$cuerpo = json_decode( wp_remote_retrieve_body( $respuesta ), true );
		$cuerpo = is_array( $cuerpo ) ? $cuerpo : array();

		if ( 404 === $codigo || ( isset( $cuerpo['code'] ) && 'rest_no_route' === $cuerpo['code'] ) ) {
			return self::item(
				'endpoint',
				__( 'El endpoint del CEAD', 'caaguazu-sso-cead' ),
				'falla',
				__( 'El sitio del CEAD responde, pero ese endpoint no existe (404).', 'caaguazu-sso-cead' ),
				__( 'La mitad emisora todavía no está instalada del lado del colegio. Hasta que lo esté, el acceso de un clic no puede funcionar por más que de este lado esté todo bien.', 'caaguazu-sso-cead' )
			);
		}

		// Un código inválido debe rebotar con un error del contrato. Que
		// rebote es exactamente lo que se quería comprobar: hay alguien
		// atendiendo, entiende el formato y verifica la firma.
		$clave = isset( $cuerpo['error'] ) ? (string) $cuerpo['error'] : '';
		if ( in_array( $clave, array( 'code_invalido', 'code_vencido', 'code_usado' ), true ) ) {
			return self::item(
				'endpoint',
				__( 'El endpoint del CEAD', 'caaguazu-sso-cead' ),
				'ok',
				__( 'Contesta y entiende el formato: rechazó el código de prueba como corresponde. El secreto compartido coincide.', 'caaguazu-sso-cead' )
			);
		}

		if ( 'firma_invalida' === $clave || 'desfase_horario' === $clave ) {
			return self::item(
				'endpoint',
				__( 'El endpoint del CEAD', 'caaguazu-sso-cead' ),
				'falla',
				__( 'Contesta, pero rechaza nuestra firma.', 'caaguazu-sso-cead' ),
				__( 'El secreto compartido no es el mismo en los dos sitios, o los relojes están desincronizados.', 'caaguazu-sso-cead' )
			);
		}

		return self::item(
			'endpoint',
			__( 'El endpoint del CEAD', 'caaguazu-sso-cead' ),
			'aviso',
			sprintf(
				/* translators: 1: código HTTP, 2: clave de error devuelta */
				__( 'Contesta con HTTP %1$d y una respuesta que no reconocemos (%2$s).', 'caaguazu-sso-cead' ),
				$codigo,
				$clave ? $clave : __( 'sin clave de error', 'caaguazu-sso-cead' )
			),
			__( 'Comparalo con el contrato: el canje tiene que devolver { ok: true, … } o { error: "…" }.', 'caaguazu-sso-cead' )
		);
	}
}
