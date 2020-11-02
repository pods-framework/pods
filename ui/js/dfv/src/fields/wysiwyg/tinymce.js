import React, { useEffect, useRef } from 'react';
import { debounce } from 'lodash';
import PropTypes from 'prop-types';

import { F10, isKeyboardEvent } from '@wordpress/keycodes';

const TinyMCE = ( {
	name,
	value,
	setValue,
	editorHeight,
	mediaButtons,
} ) => {
	const fieldId = `pods-form-ui-${ name }`;

	const didMount = useRef( false );

	useEffect( () => {
		if ( ! didMount.current ) {
			return;
		}

		const editor = window.tinymce.get( fieldId );
		const currentContent = editor?.getContent();

		if ( currentContent !== value ) {
			editor.setContent( value || '' );
		}
	}, [ value ] );

	useEffect( () => {
		const { baseURL, suffix } = window.wpEditorL10n.tinymce;

		didMount.current = true;

		window.tinymce.EditorManager.overrideDefaults( {
			base_url: baseURL,
			suffix,
		} );

		function onSetup( editor ) {
			if ( value ) {
				editor.on( 'loadContent', () => editor.setContent( value ) );
			}

			const debouncedOnChange = debounce( () => {
				const newValue = editor.getContent();

				if ( newValue !== editor._lastChange ) {
					editor._lastChange = value;
					setValue( newValue );
				}
			}, 250 );
			editor.on( 'Paste Change input Undo Redo', debouncedOnChange );

			// We need to cancel the debounce call because when we remove
			// the editor (onUnmount) this callback is executed in
			// another tick. This results in setting the content to empty.
			editor.on( 'remove', debouncedOnChange.cancel );

			editor.on( 'keydown', ( event ) => {
				if ( isKeyboardEvent.primary( event, 'z' ) ) {
					// Prevent the gutenberg undo kicking in so TinyMCE undo stack works as expected
					event.stopPropagation();
				}

				const { altKey } = event;
				/*
				 * Prevent Mousetrap from kicking in: TinyMCE already uses its own
				 * `alt+f10` shortcut to focus its toolbar.
				 */
				if ( altKey && event.keyCode === F10 ) {
					event.stopPropagation();
				}
			} );
		}

		function initialize() {
			const { settings } = window.wpEditorL10n.tinymce;

			window.wp.oldEditor.initialize( fieldId, {
				tinymce: {
					...settings,
					content_css: false,
					setup: onSetup,
					height: editorHeight,
				},
				mediaButtons,
				quicktags: true,
			} );
		}

		function onReadyStateChange() {
			if ( document.readyState === 'complete' ) {
				initialize();
			}
		}

		if ( document.readyState === 'complete' ) {
			initialize();
		} else {
			document.addEventListener( 'readystatechange', onReadyStateChange );
		}

		return () => {
			document.removeEventListener(
				'readystatechange',
				onReadyStateChange
			);
			wp.oldEditor.remove( fieldId );
		};
	}, [ mediaButtons ] );

	return (
		<div
			id={ `wp-${ fieldId }-container` }
			className="wp-editor-container pods-tinymce-editor-container"
		>
			<textarea
				className="wp-editor-area"
				name={ name }
				id={ fieldId }
				onChange={ ( event ) => setValue( event.target.value ) }
			>
				{ value }
			</textarea>
		</div>
	);
};

TinyMCE.propTypes = {
	name: PropTypes.string.isRequired,
};

export default TinyMCE;
