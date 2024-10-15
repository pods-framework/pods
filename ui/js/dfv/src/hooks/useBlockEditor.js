
const useBlockEditor = () => {
	const editorSelect = wp.data?.select( 'core/editor' );
	const editorDispatch = wp.data?.dispatch( 'core/editor' );
	const notices = wp.data?.dispatch( 'core/notices' );

	// @todo Use hook instead of savePost override once stable.
	if ( ! window.PodsBlockEditor && editorDispatch && editorDispatch.hasOwnProperty( 'savePost' ) ) {
		// First init.
		window.PodsBlockEditor = {
			// Store original.
			savePost: editorDispatch.savePost,
			messages: {},
			callbacks: {},
		};

		// Override the current editor savePost function.
		editorDispatch.savePost = async ( options ) => {
			options = options || {};

			const pbe = window.PodsBlockEditor;

			if ( ! Object.values( pbe.messages ).length ) {
				// eslint-disable-next-line no-undef
				return pbe.savePost.apply( this, arguments );
			}

			return new Promise( function( resolve, reject ) {
				// Bail early if is autosave or preview.
				if ( options.isAutosave || options.isPreview ) {
					return resolve( 'Validation ignored (autosave).' );
				}
				for ( const fieldName in pbe.messages ) {
					if ( pbe.messages.hasOwnProperty( fieldName ) ) {
						pbe.messages[ fieldName ].forEach( function( message ) {
							notices.createErrorNotice( 'Pods: ' + message, { id: fieldName, isDismissible: true } );
						} );
					}
					editorDispatch?.lockPostSaving( fieldName );
				}
				for ( const fieldCallback in pbe.callbacks ) {
					if ( pbe.callbacks.hasOwnProperty( fieldCallback ) && 'function' === typeof pbe.callbacks[ fieldCallback ] ) {
						pbe.callbacks[ fieldCallback ]();
					}
				}
				return reject( 'Pods validation failed' );
			} );
		};
	}

	return {
		data: wp.data,
		select: editorSelect,
		dispatch: editorDispatch,
		notices,
		lockPostSaving: ( name, messages, callback ) => {
			// @todo Use hook instead of savePost override once stable.
			//wp.hooks.addFilter( 'editor.__unstablePreSavePost', 'editor', filter );

			const pbe = window.PodsBlockEditor;
			if ( messages.length ) {
				pbe.messages[ name ] = messages;
				pbe.callbacks[ name ] = callback;
			}
		},
		unlockPostSaving: ( name ) => {
			// @todo Use hook instead of savePost override once stable.
			//wp.hooks.removeFilter( 'editor.__unstablePreSavePost', 'editor', filter );

			delete window.PodsBlockEditor.messages[ name ];
			delete window.PodsBlockEditor.callbacks[ name ];
			editorDispatch?.unlockPostSaving( name );
		},
	};
};

export default useBlockEditor;
