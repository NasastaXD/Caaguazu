<?php
/**
 * El archivo de traducción: bajarlo, y volver a subirlo traducido.
 *
 * POR QUÉ EXISTE
 *
 * Traducir una ficha campo por campo dentro del panel funciona, pero es lento
 * y no se puede delegar: quien traduce tiene que tener cuenta, entrar, y
 * copiar de a un cuadro por vez. Con un archivo, una nota entera se traduce
 * de una —a mano, con un traductor, o pegándoselo a un modelo de lenguaje— y
 * vuelve completa.
 *
 * EL ARCHIVO SE EXPLICA SOLO
 *
 * Es la decisión de diseño de acá, y es a propósito: el JSON lleva adentro
 * qué es cada campo, qué hay que traducir, qué NO hay que tocar y cómo
 * devolverlo. Sin eso, el archivo sólo sirve si quien lo recibe además recibe
 * las instrucciones por otro lado — y ese otro lado siempre se pierde. Así,
 * el archivo se le puede pasar a cualquiera (o a cualquier cosa) sin adjuntar
 * nada más.
 *
 * QUÉ PASA AL VOLVER
 *
 * El importador no confía en el archivo: comprueba el formato, que sea de
 * ESTA pieza de contenido, y descarta cualquier campo que no exista en el
 * modelo. Un archivo de otra ficha, de otro tipo o con campos inventados no
 * escribe nada — y lo dice, en vez de guardar a medias y dejar a alguien
 * buscando por qué la mitad quedó en castellano.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PROMOTUR_Traducciones_Archivo {

	/**
	 * Arma el contenido del archivo para una pieza.
	 *
	 * @param int      $post_id
	 * @param string[] $locales idiomas a incluir; vacío = todos
	 * @return array|WP_Error estructura lista para json_encode
	 */
	public static function exportar( $post_id, $locales = array() ) {
		$post_id = (int) $post_id;
		$post    = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error( 'no_existe', __( 'Ese contenido no existe.', 'caaguazu-portal' ) );
		}

		$tipo = PROMOTUR_Editorial::tipo_de( $post );
		if ( ! $tipo ) {
			return new WP_Error( 'tipo_desconocido', __( 'Ese contenido no es del panel.', 'caaguazu-portal' ) );
		}

		$campos = PROMOTUR_Traducciones::campos_de( $post_id );
		if ( ! $campos ) {
			return new WP_Error( 'sin_campos', __( 'Ese contenido no tiene nada para traducir todavía.', 'caaguazu-portal' ) );
		}

		$disponibles = array_keys( PROMOTUR_Traducciones::idiomas() );
		$locales     = array_values( array_intersect( (array) $locales, $disponibles ) );
		if ( ! $locales ) {
			$locales = $disponibles;
		}

		$lista = array();
		foreach ( $campos as $clave => $def ) {
			$original = PROMOTUR_Traducciones::original( $post_id, $clave, $def );
			if ( '' === trim( $original ) ) {
				continue; // no se manda a traducir lo que está vacío.
			}

			$fila = array(
				'clave'   => $clave,
				'que_es'  => isset( $def['que_es'] ) ? $def['que_es'] : '',
				'formato' => isset( $def['formato'] ) ? $def['formato'] : 'texto',
				PROMOTUR_Traducciones::ORIGEN => $original,
			);
			foreach ( $locales as $locale ) {
				$fila[ $locale ] = PROMOTUR_Traducciones::texto( $post_id, $clave, $locale );
			}
			$lista[] = $fila;
		}

		return array(
			'_formato'      => PROMOTUR_Traducciones::FORMATO,
			'_instrucciones' => self::instrucciones( $locales ),
			'contenido'     => array(
				'id'          => $post_id,
				'tipo'        => $tipo,
				'titulo'      => get_the_title( $post ),
				'sitio'       => home_url( '/' ),
				'exportado'   => gmdate( 'c' ),
				'modificado'  => (string) get_post_field( 'post_modified_gmt', $post_id ),
			),
			'idioma_original' => PROMOTUR_Traducciones::ORIGEN,
			'idiomas'         => $locales,
			'campos'          => $lista,
		);
	}

	/**
	 * Las instrucciones que viajan dentro del archivo.
	 *
	 * En castellano porque quien lo abre es del equipo. Están escritas para
	 * que las entienda igual una persona que un modelo de lenguaje, y dicen
	 * las cosas que efectivamente salen mal cuando no están dichas: que no se
	 * toque el castellano, que no se traduzcan los nombres propios, que los
	 * párrafos son párrafos, y que se devuelva el mismo archivo y no un texto
	 * suelto.
	 *
	 * @param string[] $locales
	 * @return array
	 */
	private static function instrucciones( $locales ) {
		$nombres = array();
		foreach ( $locales as $locale ) {
			$nombres[] = PROMOTUR_Traducciones::idioma_label( $locale ) . ' (' . $locale . ')';
		}

		return array(
			'que_es_esto' => 'Este archivo tiene los textos de una pieza de contenido del Portal de Promotores Turísticos de Caaguazú, para traducirlos.',
			'que_hacer'   => array(
				'1. Cada objeto de la lista «campos» es un texto. «que_es» dice qué es ese texto y dónde se lee.',
				'2. El texto original está en la clave «es» (castellano). NO se modifica: es el original.',
				'3. Escribí la traducción en la clave de cada idioma pedido: ' . implode( ', ', $nombres ) . '.',
				'4. Si una clave de idioma ya viene con texto, es una traducción anterior: revisala y corregila si hace falta.',
				'5. Devolvé este MISMO archivo, con la misma estructura y las mismas claves. No agregues ni saques campos.',
			),
			'reglas' => array(
				'Los nombres propios no se traducen: Caaguazú, Ykua La Patria, Yhú, Coronel Oviedo, los nombres de personas y de instituciones se dejan tal cual.',
				'Los montos en guaraníes se dejan igual: no se convierten a otra moneda ni se cambia el número.',
				'Los campos con formato «parrafos» pueden tener varios párrafos separados por un renglón en blanco: hay que conservar esa separación, párrafo por párrafo.',
				'Los campos con formato «texto» son de un solo renglón: no les agregues saltos de línea.',
				'No agregues HTML ni Markdown. El texto va plano, igual que el original.',
				'No agregues comentarios, notas del traductor ni explicaciones adentro del texto.',
				'Si un texto no se puede traducir, dejá la clave de ese idioma vacía. Vacío significa «sin traducir» y la app muestra el castellano, que es mejor que una traducción inventada.',
			),
			'donde_se_sube' => 'En el panel, dentro del editor de esta pieza, en el bloque «Idiomas»: «Subir traducciones».',
		);
	}

	/**
	 * Nombre del archivo. Lleva el tipo y el id para que veinte archivos en la
	 * carpeta de descargas se sigan distinguiendo.
	 *
	 * @param int $post_id
	 * @return string
	 */
	public static function nombre( $post_id ) {
		$tipo  = PROMOTUR_Editorial::tipo_de( $post_id );
		$slug  = sanitize_title( get_the_title( $post_id ) );
		$slug  = $slug ? $slug : 'sin-titulo';
		return sprintf( 'traduccion-%s-%d-%s.json', $tipo ? $tipo : 'contenido', (int) $post_id, $slug );
	}

	/* --------------------------------------------------------------------- */

	/**
	 * Lee un archivo traducido y lo guarda.
	 *
	 * @param int    $post_id el post al que se está subiendo
	 * @param string $json    contenido crudo del archivo
	 * @return array|WP_Error { idiomas: locale => cantidad, ignorados: string[] }
	 */
	public static function importar( $post_id, $json ) {
		$post_id = (int) $post_id;

		$datos = json_decode( (string) $json, true );
		if ( ! is_array( $datos ) ) {
			return new WP_Error(
				'json_invalido',
				__( 'Ese archivo no es un JSON válido. Si lo editaste a mano, fijate que no falte una coma o una comilla.', 'caaguazu-portal' )
			);
		}

		if ( ! isset( $datos['_formato'] ) || PROMOTUR_Traducciones::FORMATO !== $datos['_formato'] ) {
			return new WP_Error(
				'formato_desconocido',
				__( 'Ese archivo no es un archivo de traducción del panel. Bajá el archivo desde «Bajar para traducir» y trabajá sobre ese.', 'caaguazu-portal' )
			);
		}

		/*
		 * Que el archivo sea de ESTA pieza. Es la comprobación que evita el
		 * error caro: subir en una ficha el archivo de otra pisa textos que
		 * nadie revisó, y como cada campo se llama igual en las dos, el
		 * resultado se ve perfectamente normal hasta que alguien lee la app.
		 */
		$id_archivo = isset( $datos['contenido']['id'] ) ? (int) $datos['contenido']['id'] : 0;
		if ( $id_archivo && $id_archivo !== $post_id ) {
			return new WP_Error(
				'otro_contenido',
				sprintf(
					/* translators: %1$s = título del archivo, %2$d = id */
					__( 'Ese archivo es de otro contenido: «%1$s» (#%2$d). Abrí esa pieza y subilo ahí.', 'caaguazu-portal' ),
					isset( $datos['contenido']['titulo'] ) ? (string) $datos['contenido']['titulo'] : '#' . $id_archivo,
					$id_archivo
				)
			);
		}

		if ( empty( $datos['campos'] ) || ! is_array( $datos['campos'] ) ) {
			return new WP_Error( 'sin_campos', __( 'Ese archivo no trae ningún campo.', 'caaguazu-portal' ) );
		}

		$campos    = PROMOTUR_Traducciones::campos_de( $post_id );
		$idiomas   = array_keys( PROMOTUR_Traducciones::idiomas() );
		$por_idioma = array();
		$ignorados  = array();

		foreach ( $datos['campos'] as $fila ) {
			if ( ! is_array( $fila ) || empty( $fila['clave'] ) ) {
				continue;
			}
			$clave = (string) $fila['clave'];
			if ( ! isset( $campos[ $clave ] ) ) {
				$ignorados[] = $clave;
				continue;
			}
			foreach ( $idiomas as $locale ) {
				if ( ! isset( $fila[ $locale ] ) || ! is_string( $fila[ $locale ] ) ) {
					continue;
				}
				if ( '' === trim( $fila[ $locale ] ) ) {
					continue;
				}
				$por_idioma[ $locale ][ $clave ] = $fila[ $locale ];
			}
		}

		if ( ! $por_idioma ) {
			return new WP_Error(
				'nada_traducido',
				__( 'El archivo está bien, pero no trae ninguna traducción: las claves de idioma vinieron todas vacías.', 'caaguazu-portal' )
			);
		}

		/*
		 * Se guarda idioma por idioma y se FUSIONA con lo que ya había: un
		 * archivo exportado sólo para inglés no tiene por qué borrar el
		 * portugués que alguien cargó mientras tanto.
		 */
		$resultado = array();
		foreach ( $por_idioma as $locale => $valores ) {
			$previo = PROMOTUR_Traducciones::leer( $post_id, $locale );
			$resultado[ $locale ] = PROMOTUR_Traducciones::guardar(
				$post_id,
				$locale,
				array_merge( $previo, $valores )
			);
		}

		return array(
			'idiomas'   => $resultado,
			'ignorados' => array_values( array_unique( $ignorados ) ),
		);
	}
}
