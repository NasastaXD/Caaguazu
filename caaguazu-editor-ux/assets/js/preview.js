/**
 * Caaguazú Editor UX — modal de vista previa.
 *
 * Reutiliza el mismo mecanismo que el botón "Vista previa" nativo de
 * WordPress (autosave + getEditedPostPreviewLink), así nunca muestra
 * contenido desactualizado. Expone window.czuPreview.open()/close() para
 * que editor-plugin.js pueda abrirlo desde el panel/botón propio.
 *
 * Sin build step: JS plano contra los globals de wp-* (mismo enfoque que
 * el resto del sitio, que tampoco usa webpack).
 */
( function( wp ) {
	'use strict';

	if ( ! wp || ! wp.element || ! wp.data || ! wp.components ) {
		return;
	}

	var el          = wp.element.createElement;
	var useState     = wp.element.useState;
	var useEffect    = wp.element.useEffect;
	var __           = wp.i18n.__;
	var Modal        = wp.components.Modal;
	var Button       = wp.components.Button;
	var Spinner      = wp.components.Spinner;

	var mountNode = null;

	function ensureMountNode() {
		if ( ! mountNode ) {
			mountNode = document.createElement( 'div' );
			mountNode.id = 'czu-preview-root';
			document.body.appendChild( mountNode );
		}
		return mountNode;
	}

	function PreviewModal( props ) {
		var deviceState = useState( 'desktop' );
		var device      = deviceState[ 0 ];
		var setDevice   = deviceState[ 1 ];

		var linkState = useState( null );
		var link      = linkState[ 0 ];
		var setLink   = linkState[ 1 ];

		var loadingState = useState( true );
		var loading      = loadingState[ 0 ];
		var setLoading   = loadingState[ 1 ];

		useEffect( function() {
			var cancelled = false;
			setLoading( true );

			var editorSelect  = wp.data.select( 'core/editor' );
			var editorDispatch = wp.data.dispatch( 'core/editor' );

			function resolveLink() {
				if ( cancelled ) {
					return;
				}
				setLink( editorSelect.getEditedPostPreviewLink() || null );
				setLoading( false );
			}

			var canAutosave = editorSelect.isEditedPostSaveable && editorSelect.isEditedPostSaveable();
			var shouldAutosave = canAutosave && editorDispatch.autosave &&
				( editorSelect.isEditedPostDirty() || editorSelect.isEditedPostNew() );

			if ( shouldAutosave ) {
				var result = editorDispatch.autosave();
				if ( result && typeof result.then === 'function' ) {
					result.then( resolveLink ).catch( resolveLink );
				} else {
					resolveLink();
				}
			} else {
				resolveLink();
			}

			return function() {
				cancelled = true;
			};
		}, [] );

		return el(
			Modal,
			{
				title: __( 'Vista previa caaguazu.net', 'caaguazu-editor-ux' ),
				onRequestClose: props.onClose,
				className: 'czu-preview-modal',
				shouldCloseOnClickOutside: true,
			},
			el(
				'div',
				{ className: 'czu-preview-toolbar' },
				el( Button, {
					variant: 'desktop' === device ? 'primary' : 'secondary',
					'aria-pressed': 'desktop' === device,
					onClick: function() { setDevice( 'desktop' ); },
				}, __( 'Escritorio', 'caaguazu-editor-ux' ) ),
				el( Button, {
					variant: 'mobile' === device ? 'primary' : 'secondary',
					'aria-pressed': 'mobile' === device,
					onClick: function() { setDevice( 'mobile' ); },
				}, __( 'Celular', 'caaguazu-editor-ux' ) ),
				el( Button, {
					variant: 'tertiary',
					disabled: ! link,
					href: link || undefined,
					target: '_blank',
					rel: 'noopener noreferrer',
				}, __( 'Abrir en pestaña nueva', 'caaguazu-editor-ux' ) ),
				el( Button, {
					variant: 'tertiary',
					onClick: props.onClose,
				}, __( 'Cerrar', 'caaguazu-editor-ux' ) )
			),
			loading
				? el(
					'div',
					{ className: 'czu-preview-loading' },
					el( Spinner ),
					el( 'p', null, __( 'Preparando la vista previa…', 'caaguazu-editor-ux' ) )
				)
				: ( link
					? el(
						'div',
						{ className: 'czu-preview-frame-wrap czu-preview-frame-wrap--' + device },
						el( 'iframe', {
							src: link,
							title: __( 'Vista previa de la entrada', 'caaguazu-editor-ux' ),
							className: 'czu-preview-frame',
						} )
					)
					: el(
						'p',
						{ className: 'czu-preview-error' },
						__( 'Todavía no se pudo generar la vista previa. Guardá un borrador e intentá de nuevo.', 'caaguazu-editor-ux' )
					)
				)
		);
	}

	function App() {
		var openState = useState( false );
		var isOpen    = openState[ 0 ];
		var setIsOpen = openState[ 1 ];

		useEffect( function() {
			window.czuPreview = {
				open: function() { setIsOpen( true ); },
				close: function() { setIsOpen( false ); },
			};
			return function() {
				delete window.czuPreview;
			};
		}, [] );

		if ( ! isOpen ) {
			return null;
		}

		return el( PreviewModal, { onClose: function() { setIsOpen( false ); } } );
	}

	wp.domReady( function() {
		if ( wp.element.createRoot ) {
			wp.element.createRoot( ensureMountNode() ).render( el( App ) );
		} else {
			wp.element.render( el( App ), ensureMountNode() );
		}
	} );
} )( window.wp );
