/**
 * Caaguazú Editor UX — panel de documento "caaguazu.net": checklist
 * editorial + campos opcionales (Fuente/referencia, Responsable del
 * contenido) + acceso a la vista previa (preview.js). También recorta un
 * par de paneles nativos que no aportan al flujo editorial de una nota.
 *
 * Sin build step: JS plano contra los globals de wp-*.
 */
( function( wp ) {
	'use strict';

	if ( ! wp || ! wp.plugins || ! wp.editPost || ! wp.data ) {
		return;
	}

	var el                         = wp.element.createElement;
	var Fragment                   = wp.element.Fragment;
	var useState                   = wp.element.useState;
	var __                         = wp.i18n.__;
	var registerPlugin             = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var PluginPostStatusInfo       = wp.editPost.PluginPostStatusInfo;
	var useSelect                  = wp.data.useSelect;
	var useDispatch                = wp.data.useDispatch;
	var TextControl                = wp.components.TextControl;
	var CheckboxControl            = wp.components.CheckboxControl;
	var Button                     = wp.components.Button;

	var SUPPORTED_POST_TYPE = ( window.czuEditorUX && window.czuEditorUX.supportedType ) || 'post';

	var CHECKLIST_ITEMS = [
		__( 'Título claro', 'caaguazu-editor-ux' ),
		__( 'Resumen corto agregado', 'caaguazu-editor-ux' ),
		__( 'Imagen de portada agregada', 'caaguazu-editor-ux' ),
		__( 'Categoría seleccionada', 'caaguazu-editor-ux' ),
		__( 'Fuente o referencia verificada', 'caaguazu-editor-ux' ),
		__( 'Contenido escrito o revisado por una persona', 'caaguazu-editor-ux' ),
	];

	/**
	 * Checklist puramente mental: no persiste en post meta (no hay campo
	 * para esto en el MVP), se reinicia al recargar. Es un recordatorio,
	 * no un dato — evita sobre-construir el panel.
	 */
	function EditorialChecklist() {
		var state    = useState( {} );
		var checked  = state[ 0 ];
		var setChecked = state[ 1 ];

		function toggle( index ) {
			var next = Object.assign( {}, checked );
			next[ index ] = ! next[ index ];
			setChecked( next );
		}

		return el(
			'ul',
			{ className: 'czu-checklist' },
			CHECKLIST_ITEMS.map( function( label, index ) {
				return el(
					'li',
					{ key: index },
					el( CheckboxControl, {
						label: label,
						checked: !! checked[ index ],
						onChange: function() { toggle( index ); },
					} )
				);
			} )
		);
	}

	function MetaFields() {
		var meta = useSelect( function( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );
		var editPost = useDispatch( 'core/editor' ).editPost;

		function updateMeta( key, value ) {
			var next = {};
			next[ key ] = value;
			editPost( { meta: next } );
		}

		return el(
			Fragment,
			null,
			el( TextControl, {
				label: __( 'Fuente / referencia', 'caaguazu-editor-ux' ),
				help: __( 'Opcional: de dónde salió la información (medio, institución, entrevista).', 'caaguazu-editor-ux' ),
				value: meta._czu_fuente_referencia || '',
				onChange: function( value ) { updateMeta( '_czu_fuente_referencia', value ); },
			} ),
			el( TextControl, {
				label: __( 'Responsable del contenido', 'caaguazu-editor-ux' ),
				help: __( 'Opcional: quién escribió o verificó esta nota.', 'caaguazu-editor-ux' ),
				value: meta._czu_responsable_contenido || '',
				onChange: function( value ) { updateMeta( '_czu_responsable_contenido', value ); },
			} )
		);
	}

	function openPreview() {
		if ( window.czuPreview && window.czuPreview.open ) {
			window.czuPreview.open();
		}
	}

	function useIsSupportedPostType() {
		return useSelect( function( select ) {
			return select( 'core/editor' ).getCurrentPostType() === SUPPORTED_POST_TYPE;
		}, [] );
	}

	function CaaguazuPanel() {
		if ( ! useIsSupportedPostType() ) {
			return null;
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'czu-panel',
				title: __( 'caaguazu.net', 'caaguazu-editor-ux' ),
				className: 'czu-panel',
			},
			el( 'p', { className: 'czu-panel-intro' }, __( 'Antes de publicar:', 'caaguazu-editor-ux' ) ),
			el( EditorialChecklist ),
			el(
				'p',
				{ className: 'czu-panel-note' },
				__( 'Escribí con tus palabras y verificá los datos con una fuente real: nada de contenido inventado o copiado.', 'caaguazu-editor-ux' )
			),
			el( 'hr', { className: 'czu-panel-sep' } ),
			el( MetaFields ),
			el( 'hr', { className: 'czu-panel-sep' } ),
			el( Button, {
				variant: 'secondary',
				className: 'czu-preview-button',
				onClick: openPreview,
			}, __( 'Vista previa caaguazu.net', 'caaguazu-editor-ux' ) )
		);
	}

	/**
	 * Botón "rápido" arriba, junto al resto del resumen del documento
	 * (Estado, Categoría, etc.) — visible sin tener que abrir nuestro panel.
	 */
	function QuickPreviewButton() {
		if ( ! useIsSupportedPostType() ) {
			return null;
		}

		return el(
			PluginPostStatusInfo,
			null,
			el( Button, {
				variant: 'secondary',
				className: 'czu-preview-button czu-preview-button--quick',
				onClick: openPreview,
			}, __( 'Vista previa caaguazu.net', 'caaguazu-editor-ux' ) )
		);
	}

	registerPlugin( 'caaguazu-editor-ux', {
		render: function() {
			return el( Fragment, null, el( QuickPreviewButton ), el( CaaguazuPanel ) );
		},
	} );

	// Limpieza de paneles nativos que no aportan al flujo editorial de una
	// nota (comentarios, etiquetas, formato de entrada). Envuelto en
	// try/catch: si una versión futura de WP cambia estos nombres, el
	// editor sigue funcionando igual, sólo no se ocultan esos paneles.
	wp.domReady( function() {
		try {
			var editPostDispatch = wp.data.dispatch( 'core/edit-post' );
			if ( editPostDispatch && editPostDispatch.removeEditorPanel ) {
				editPostDispatch.removeEditorPanel( 'discussion-panel' );
				editPostDispatch.removeEditorPanel( 'taxonomy-panel-post_tag' );
				editPostDispatch.removeEditorPanel( 'post-formats' );
			}

			var preferencesDispatch = wp.data.dispatch( 'core/preferences' );
			if ( preferencesDispatch && preferencesDispatch.set ) {
				preferencesDispatch.set( 'core/edit-post', 'welcomeGuide', false );
			}
		} catch ( e ) {
			// No-op: preferimos un editor funcional a un panel oculto.
		}
	} );
} )( window.wp );
