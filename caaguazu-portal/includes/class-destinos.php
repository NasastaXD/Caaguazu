<?php
/**
 * CPT "Destino" (ficha turística) + taxonomías + metadata + single público.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Destinos {

	private static $instance = null;
	const CPT = 'promotur_destino';

	/**
	 * Meta donde vive el dueño REAL de la ficha: el ID de cuenta del sistema
	 * de cuentas universal (caaguazu-cuentas). `post_author` deja de servir
	 * para esto — en toda ficha creada desde el panel apunta al usuario de
	 * servicio (caaguazu_service_user_id()), porque WordPress exige un autor
	 * válido pero ninguna persona del panel es ya un usuario de WordPress.
	 */
	const OWNER_META = '_caaguazu_owner';

	/** Sitio o evento. Ver el grupo «Qué es» de fields(). */
	const META_TIPO_ITEM = '_promotur_tipo_item';
	const META_INICIO    = '_promotur_evento_inicio';
	const META_FIN       = '_promotur_evento_fin';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_single' ) );
	}

	/**
	 * Carga el CSS del portal en la ficha pública (usa clases .promotur-*).
	 */
	public function enqueue_single() {
		if ( is_singular( self::CPT ) ) {
			wp_enqueue_style( 'promotur', promotur_asset( 'css/caaguazu-portal.css' ), array(), PROMOTUR_VERSION );
		}
	}

	/**
	 * Capabilities primitivas que genera el capability_type (para el admin).
	 *
	 * @return string[]
	 */
	public static function cpt_caps() {
		$s = 'promotur_destino';
		$p = 'promotur_destinos';
		return array(
			"edit_{$s}", "read_{$s}", "delete_{$s}",
			"edit_{$p}", "edit_others_{$p}", "publish_{$p}", "read_private_{$p}",
			"delete_{$p}", "delete_private_{$p}", "delete_published_{$p}", "delete_others_{$p}",
			"edit_private_{$p}", "edit_published_{$p}",
		);
	}

	public static function register_post_type() {
		$labels = array(
			'name'          => __( 'Destinos', 'caaguazu-portal' ),
			'singular_name' => __( 'Destino', 'caaguazu-portal' ),
			'add_new_item'  => __( 'Nuevo destino', 'caaguazu-portal' ),
			'edit_item'     => __( 'Editar destino', 'caaguazu-portal' ),
			'search_items'  => __( 'Buscar destinos', 'caaguazu-portal' ),
		);
		/*
		 * El destino dejó de ser una página web y pasó a ser un registro que
		 * consume la app: se publica en /wp-json/czu-app/v1/inventario, no en
		 * /destino/<slug>. Por eso el CPT ya no es públicamente consultable —
		 * si lo fuera, esa URL la dibujaría el theme del sitio, que hoy es una
		 * página de obra, y Google indexaría fichas que no se ven.
		 *
		 * Sigue editándose en wp-admin y en el panel, y sigue teniendo estados
		 * (publicado, borrador…), que es lo que la app mira para saber qué
		 * mostrar. El día que el sitio nuevo quiera fichas públicas, esto
		 * vuelve a `'public' => true` y se le escribe su plantilla.
		 */
		register_post_type( self::CPT, array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'exclude_from_search'=> true,
			// El panel es la única pantalla de edición: la nativa de wp-admin
			// (lista + editor de bloques) queda apagada.
			'show_ui'            => false,
			'show_in_rest'       => true,
			'has_archive'        => false,
			'rewrite'            => false,
			'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
			'capability_type' => array( 'promotur_destino', 'promotur_destinos' ),
			'map_meta_cap'    => true,
		) );
	}

	public static function register_taxonomies() {
		// Mismo criterio que el CPT: se editan y se sirven por la API de la
		// app, no tienen archivo web propio.
		$common = array(
			'hierarchical'       => true,
			'show_in_rest'       => true,
			'public'             => false,
			'publicly_queryable' => false,
			// Categorías, zonas y etiquetas se administran en Estructura, dentro
			// del panel: la pantalla nativa de wp-admin (edit-tags.php) queda
			// apagada, y con ella la columna que sólo esa pantalla mostraba.
			'show_ui'            => false,
			'show_admin_column'  => false,
			'rewrite'            => false,
		);
		register_taxonomy( 'promotur_categoria', self::CPT, array_merge( $common, array(
			'labels' => array( 'name' => __( 'Categorías', 'caaguazu-portal' ), 'singular_name' => __( 'Categoría', 'caaguazu-portal' ) ),
		) ) );
		register_taxonomy( 'promotur_zona', self::CPT, array_merge( $common, array(
			'labels' => array( 'name' => __( 'Zonas', 'caaguazu-portal' ), 'singular_name' => __( 'Zona', 'caaguazu-portal' ) ),
		) ) );
		register_taxonomy( 'promotur_etiqueta', self::CPT, array(
			'hierarchical' => false, 'show_in_rest' => true,
			'labels' => array( 'name' => __( 'Etiquetas', 'caaguazu-portal' ), 'singular_name' => __( 'Etiqueta', 'caaguazu-portal' ) ),
		) );
	}

	/**
	 * Definición de campos de la ficha (data model + base del editor y del checklist).
	 * 'req' => true marca los obligatorios que componen el checklist de mínimos.
	 * 'ayuda' es la línea que se lee debajo del campo, donde hace falta.
	 *
	 * @return array grupo => [ key => { label, type, req } ]
	 */
	public static function fields() {
		return array(
			/*
			 * QUÉ ES ESTA FICHA — sitio o evento.
			 *
			 * Va primero porque cambia el formulario: un evento pide fecha y
			 * hora, un sitio no. Y son la misma ficha a propósito: un evento
			 * ES un lugar con fechas. Tiene gancho, foto, ubicación, costo,
			 * horario, fuentes y flujo editorial exactamente igual; lo único
			 * que agrega es cuándo pasa.
			 *
			 * Antes los eventos vivían en un CPT aparte de la API
			 * (`promotur_evento`), que se cargaba desde wp-admin y tenía la
			 * mitad de los campos: sin gancho, sin galería, sin fuentes y sin
			 * pasar por revisión. Duplicar el modelo para agregarle dos fechas
			 * costaba mantener dos editores y dos checklists que se iban a
			 * separar con el tiempo.
			 */
			'que_es' => array(
				'label'  => __( 'Qué es', 'caaguazu-portal' ),
				'fields' => array(
					self::META_TIPO_ITEM => array(
						'label'   => __( 'Tipo', 'caaguazu-portal' ),
						'type'    => 'select',
						'req'     => true,
						'options' => array(
							'sitio'  => __( 'Sitio — está siempre', 'caaguazu-portal' ),
							'evento' => __( 'Evento — pasa en una fecha', 'caaguazu-portal' ),
						),
						'ayuda'   => __( 'Un evento es un lugar con fecha: la fiesta patronal, una feria, un festival. Todo lo demás se carga igual.', 'caaguazu-portal' ),
					),
					self::META_INICIO => array(
						'label' => __( 'Empieza', 'caaguazu-portal' ),
						'type'  => 'datetime',
						'req'   => true,
						'solo'  => 'evento',
						'ayuda' => __( 'Día y hora de inicio.', 'caaguazu-portal' ),
					),
					self::META_FIN => array(
						'label' => __( 'Termina', 'caaguazu-portal' ),
						'type'  => 'datetime',
						'req'   => false,
						'solo'  => 'evento',
						'ayuda' => __( 'Si dura un solo día, alcanza con la hora de cierre. Si es de varios días, poné el último.', 'caaguazu-portal' ),
					),
				),
			),
			'identidad' => array(
				'label'  => __( 'Identidad', 'caaguazu-portal' ),
				'fields' => array(
					'_promotur_gancho'        => array( 'label' => __( 'Gancho (una línea)', 'caaguazu-portal' ), 'type' => 'text', 'req' => true ),
					'_promotur_portada'       => array( 'label' => __( 'Foto de portada', 'caaguazu-portal' ), 'type' => 'image', 'req' => true ),
					'_promotur_credito_fotos' => array( 'label' => __( 'Crédito de las fotos', 'caaguazu-portal' ), 'type' => 'text', 'req' => true ),
					'_promotur_video'         => array( 'label' => __( 'Video (URL, opcional)', 'caaguazu-portal' ), 'type' => 'url', 'req' => false ),
				),
			),
			/*
			 * UBICACIÓN — el enlace de Google Maps es el modo por defecto, y las
			 * coordenadas quedaron de alternativa.
			 *
			 * El motivo no es de gusto: la app se apoya en Google Maps para
			 * llevar a la gente hasta el lugar, así que el enlace es el dato
			 * que de verdad se usa. Y del lado de quien carga la ficha, pegar
			 * un enlace que ya tiene en el teléfono es una operación que sale
			 * bien siempre; transcribir dos números con seis decimales, no.
			 *
			 * El pin del mapa se sigue necesitando, pero se saca del enlace
			 * cuando se puede (ver coords_desde_maps()). Los campos de latitud
			 * y longitud siguen ahí para los casos que ese parseo no cubre —un
			 * enlace corto de maps.app.goo.gl, un lugar sin ficha en Google— y
			 * para poder corregir el pin a mano.
			 */
			'ubicacion' => array(
				'label'  => __( 'Ubicación', 'caaguazu-portal' ),
				'fields' => array(
					'_promotur_maps' => array(
						'label' => __( 'Enlace de Google Maps', 'caaguazu-portal' ),
						'type'  => 'url',
						'req'   => false,
						'check' => true,
						'ayuda' => __( 'Buscá el lugar en Google Maps, tocá «Compartir» y pegá acá el enlace. De ahí sacamos el pin solos.', 'caaguazu-portal' ),
					),
					'_promotur_lat' => array(
						'label' => __( 'Latitud (alternativa al enlace)', 'caaguazu-portal' ),
						'type'  => 'coord',
						'req'   => false,
						'ayuda' => __( 'Sólo si el enlace no alcanza: un enlace corto, o un lugar que Google no tiene.', 'caaguazu-portal' ),
					),
					'_promotur_lng' => array(
						'label' => __( 'Longitud (alternativa al enlace)', 'caaguazu-portal' ),
						'type'  => 'coord',
						'req'   => false,
					),
					'_promotur_estado_camino' => array( 'label' => __( 'Estado del camino', 'caaguazu-portal' ), 'type' => 'select', 'req' => false, 'options' => array( 'asfalto' => 'Asfalto', 'ripio' => 'Ripio', 'tierra' => 'Tierra' ) ),
					'_promotur_accesibilidad' => array( 'label' => __( 'Accesibilidad', 'caaguazu-portal' ), 'type' => 'text', 'req' => false ),
				),
			),
			/*
			 * Se fueron de acá «cómo llegar», «temporada ideal», «duración
			 * sugerida», «servicios» y «referencia»: en la práctica se llenaban
			 * con frases genéricas que no ayudaban a decidir nada, y cada campo
			 * de relleno es un renglón más entre quien carga la ficha y los
			 * datos que la app sí usa. Cómo llegar lo resuelve el enlace de
			 * Google Maps mejor que un párrafo.
			 */
			'practicos' => array(
				'label'  => __( 'Datos prácticos', 'caaguazu-portal' ),
				'fields' => array(
					'_promotur_horario'   => array( 'label' => __( 'Horario y mejor momento para visitar', 'caaguazu-portal' ), 'type' => 'text', 'req' => true ),
					'_promotur_costo'     => array( 'label' => __( 'Costo / entrada', 'caaguazu-portal' ), 'type' => 'text', 'req' => true ),
					// Rango de precio como número, ADEMÁS del texto libre de
					// arriba y no en su reemplazo: la app necesita filtrar por
					// precio y pintar el indicador de la tarjeta, y eso no se
					// puede hacer interpretando frases escritas por distintos
					// promotores. El texto sigue diciendo lo que un número no
					// ("entrada libre, estacionamiento 5.000 Gs").
					'_promotur_rango_precio' => array(
						'label'   => __( 'Rango de precio', 'caaguazu-portal' ),
						'type'    => 'select',
						'req'     => false,
						'options' => array(
							''  => __( 'Sin especificar', 'caaguazu-portal' ),
							'0' => __( 'Gratis', 'caaguazu-portal' ),
							'1' => __( '$ — Muy barato', 'caaguazu-portal' ),
							'2' => __( '$$ — Barato', 'caaguazu-portal' ),
							'3' => __( '$$$ — Intermedio', 'caaguazu-portal' ),
							'4' => __( '$$$$ — Caro', 'caaguazu-portal' ),
						),
					),
					'_promotur_contacto'  => array( 'label' => __( 'Contacto del lugar', 'caaguazu-portal' ), 'type' => 'text', 'req' => false ),
				),
			),
			'editorial' => array(
				'label'  => __( 'Fuentes y referencias', 'caaguazu-portal' ),
				'fields' => array(
					'_promotur_fuentes' => array( 'label' => __( 'Fuentes / referencias', 'caaguazu-portal' ), 'type' => 'textarea', 'req' => false ),
				),
			),
		);
	}

	/**
	 * Campos que dejaron de existir en el modelo.
	 *
	 * Se listan —y no se borran de la base— porque el dato cargado por una
	 * persona no se tira sin que alguien lo decida: la ficha deja de pedirlos,
	 * de mostrarlos y de publicarlos, pero el valor sigue en su meta por si
	 * mañana hace falta recuperarlo. Sirve además de documentación de qué se
	 * podó, que si no se pierde en el historial de git.
	 *
	 * @return string[]
	 */
	public static function campos_retirados() {
		return array(
			'_promotur_como_llegar',
			'_promotur_referencia',
			'_promotur_temporada',
			'_promotur_servicios',
			'_promotur_duracion',
		);
	}

	/**
	 * Coordenadas de una ficha, o null si no tiene pin.
	 *
	 * @param int $post_id
	 * @return array|null { lat: float, lng: float }
	 */
	public static function coordenadas( $post_id ) {
		$lat = get_post_meta( $post_id, '_promotur_lat', true );
		$lng = get_post_meta( $post_id, '_promotur_lng', true );
		if ( '' === $lat || '' === $lng || null === $lat || null === $lng ) {
			return null;
		}
		return array( 'lat' => (float) $lat, 'lng' => (float) $lng );
	}

	/**
	 * Saca el par de coordenadas de un enlace de Google Maps.
	 *
	 * Google escribe el mismo punto de varias formas según de dónde salga el
	 * enlace, así que se prueban todas, de la más precisa a la más general:
	 *
	 *   !3d<lat>!4d<lng>   el punto exacto del lugar (enlace de «Compartir»)
	 *   @<lat>,<lng>,17z   el centro del mapa (enlace de la barra de dirección)
	 *   ?q= / query= / ll= / center=   el punto pedido
	 *   «-25.46, -56.01»   dos números pegados a mano, sin enlace
	 *
	 * `@` va DESPUÉS de `!3d!4d` a propósito: en un enlace de lugar los dos
	 * conviven, y el `@` es dónde estaba mirando la cámara, no dónde está el
	 * lugar. Tomar el primero que aparece pondría el pin corrido.
	 *
	 * Los enlaces cortos (`maps.app.goo.gl`, `goo.gl/maps`) no traen el punto:
	 * hay que seguir la redirección para saberlo, y eso es un pedido de red
	 * desde el servidor en medio de un guardado. No se hace: se devuelve null
	 * y quedan los campos de latitud/longitud para ese caso.
	 *
	 * @param string $url
	 * @return array|null { lat: float, lng: float }
	 */
	public static function coords_desde_maps( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return null;
		}

		$num     = '(-?\d{1,3}\.\d+)';
		$patrones = array(
			'/!3d' . $num . '!4d' . $num . '/',
			'/@' . $num . ',' . $num . '/',
			'/[?&](?:q|query|ll|center|daddr|destination)=' . $num . '(?:,|%2C)\s*' . $num . '/i',
			'/^' . $num . '\s*,\s*' . $num . '$/',
		);

		foreach ( $patrones as $patron ) {
			if ( ! preg_match( $patron, $url, $m ) ) {
				continue;
			}
			$lat = (float) $m[1];
			$lng = (float) $m[2];
			// Un enlace mal pegado puede dejar dos números que parsean pero no
			// son un punto de la Tierra; y si el punto cae fuera del planeta,
			// el pin miente en el mapa en vez de faltar.
			if ( $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
				continue;
			}
			return array( 'lat' => $lat, 'lng' => $lng );
		}

		return null;
	}

	/**
	 * Enlace de Google Maps de una ficha: el que cargó la persona, o uno
	 * armado con el pin si sólo hay coordenadas.
	 *
	 * La app abre siempre un enlace, así que resolver acá el caso "sólo hay
	 * coordenadas" le ahorra al cliente tener dos caminos para lo mismo.
	 *
	 * @param int $post_id
	 * @return string
	 */
	public static function maps_url( $post_id ) {
		$guardado = trim( (string) get_post_meta( $post_id, '_promotur_maps', true ) );
		if ( '' !== $guardado ) {
			return $guardado;
		}
		$coord = self::coordenadas( $post_id );
		if ( ! $coord ) {
			return '';
		}
		return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $coord['lat'] . ',' . $coord['lng'] );
	}

	/**
	 * ¿Tiene la ficha una ubicación cargada, por cualquiera de los dos
	 * caminos? Es el ítem del checklist que reemplazó a "latitud" y
	 * "longitud" sueltas.
	 *
	 * @param int $post_id
	 * @return bool
	 */
	public static function tiene_ubicacion( $post_id ) {
		return '' !== self::maps_url( $post_id );
	}

	/**
	 * ¿Esta ficha es un sitio o un evento?
	 *
	 * Sin el meta cargado es un sitio: es lo que eran todas las fichas antes de
	 * que el tipo existiera, y hacer que una ficha vieja se lea de golpe como
	 * un evento sin fecha sería inventarle un dato.
	 *
	 * @param int $post_id
	 * @return string 'sitio' | 'evento'
	 */
	public static function tipo_item( $post_id ) {
		return 'evento' === get_post_meta( $post_id, self::META_TIPO_ITEM, true ) ? 'evento' : 'sitio';
	}

	/**
	 * ¿Este campo aplica a esta ficha?
	 *
	 * Un campo con `solo => 'evento'` no se le pide a un sitio. Lo consulta el
	 * checklist: si no, «Empieza» quedaría como un mínimo sin cumplir en toda
	 * ficha que no sea un evento, y ninguna se podría enviar nunca.
	 *
	 * @param array $def
	 * @param int   $post_id
	 * @return bool
	 */
	public static function aplica_campo( $def, $post_id ) {
		if ( empty( $def['solo'] ) ) {
			return true;
		}
		return $def['solo'] === self::tipo_item( $post_id );
	}

	/**
	 * Ítems de checklist propios de la ficha, más allá de los campos `req`.
	 *
	 * @param int $post_id
	 * @return array[]
	 */
	public static function checklist_extra( $post_id ) {
		$content = $post_id ? get_post_field( 'post_content', $post_id ) : '';
		return array(
			array(
				'key'   => 'descripcion',
				'label' => __( 'Descripción', 'caaguazu-portal' ),
				'done'  => '' !== trim( wp_strip_all_tags( (string) $content ) ),
			),
			array(
				'key'   => '_promotur_maps',
				'label' => __( 'Ubicación (enlace de Google Maps o coordenadas)', 'caaguazu-portal' ),
				'done'  => $post_id ? self::tiene_ubicacion( $post_id ) : false,
			),
		);
	}

	/**
	 * Lista plana de todas las meta keys editables.
	 *
	 * @return array key => def
	 */
	public static function flat_fields() {
		$out = array();
		foreach ( self::fields() as $group ) {
			foreach ( $group['fields'] as $key => $def ) {
				$out[ $key ] = $def;
			}
		}
		return $out;
	}

	public function register_meta() {
		foreach ( self::flat_fields() as $key => $def ) {
			$type = in_array( $def['type'], array( 'coord' ), true ) ? 'number' : 'string';
			register_post_meta( self::CPT, $key, array(
				'type'          => $type,
				'single'        => true,
				'show_in_rest'  => false,
				'auth_callback' => function () { return caaguazu_account_can( 'promotor', 'promotur_edit_destino' ); },
			) );
		}
		// Meta de flujo editorial.
		foreach ( array( '_promotur_estado', '_promotur_revisor', '_promotur_galeria', '_promotur_destacado', '_promotur_verificado_en', '_promotur_articulos_rel', self::OWNER_META ) as $key ) {
			register_post_meta( self::CPT, $key, array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => false,
				'auth_callback' => function () { return caaguazu_account_can( 'promotor', 'promotur_edit_destino' ); },
			) );
		}
	}

	/**
	 * ID de cuenta dueña real de una ficha.
	 *
	 * Prioriza el meta propio (`OWNER_META`, seteado en toda ficha creada
	 * desde el cutover). Si no está (ficha creada ANTES del cutover, cuando
	 * post_author todavía era un usuario de WordPress real), resuelve vía el
	 * vínculo que dejó la migración (wp_user_id de la cuenta) — así el
	 * contenido viejo sigue reconociendo a su dueño sin necesidad de tocar
	 * cada fila a mano.
	 *
	 * @param int $post_id
	 * @return int ID de cuenta, o 0 si no se puede resolver.
	 */
	public static function owner_account_id( $post_id ) {
		$meta = (int) get_post_meta( $post_id, self::OWNER_META, true );
		if ( $meta > 0 ) {
			return $meta;
		}
		if ( ! function_exists( 'caaguazu_account_for_wp_user' ) ) {
			return 0;
		}
		$post_author = (int) get_post_field( 'post_author', $post_id );
		if ( $post_author <= 0 ) {
			return 0;
		}
		$account = caaguazu_account_for_wp_user( $post_author );
		return $account ? (int) $account['id'] : 0;
	}

	/**
	 * Marca el dueño real (cuenta) de una ficha nueva.
	 *
	 * @param int $post_id
	 * @param int $account_id
	 */
	public static function set_owner( $post_id, $account_id ) {
		update_post_meta( $post_id, self::OWNER_META, (int) $account_id );
	}

}
