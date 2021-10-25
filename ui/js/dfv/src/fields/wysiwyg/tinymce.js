import React, { useEffect, useRef } from 'react';
import { debounce } from 'lodash';
import PropTypes from 'prop-types';

import { F10, isKeyboardEvent } from '@wordpress/keycodes';

// Based on the core Freeform block's edit component, see:
// https://github.com/WordPress/gutenberg/blob/trunk/packages/block-library/src/freeform/edit.js
const TinyMCE = ( {
	name,
	value,
	setValue,
	editorHeight,
	mediaButtons,
	defaultEditor,
	onBlur,
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

		window.tinymce.EditorManager.overrideDefaults( {
			base_url: baseURL,
			suffix,
		} );

		function onSetup( editor ) {
			if ( ! didMount.current ) {
				return;
			}

			if ( value ) {
				editor.on( 'loadContent', () => editor.setContent( value ) );
			}

			editor.on( 'blur', () => {
				setValue( editor.getContent() );

				onBlur();
			} );

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

			didMount.current = true;
		}

		function initialize() {
			let settings = window?.tinyMCEPreInit?.mceInit[ '_pods_dfv_' + name ] || null;

			if ( null === settings || 'undefined' === typeof settings ) {
				settings = window?.wpEditorL10n?.tinymce?.settings || window.wp.oldEditor.getDefaultSettings().tinymce;

				// Remove the media button from the TinyMCE toolbars if found.
				if ( ! mediaButtons ) {
					settings.toolbar1 = settings.toolbar1.replace( 'wp_add_media,', '' ).replace( ',wp_add_media', '' ).replace( 'wp_add_media', '' );
					settings.toolbar2 = settings.toolbar2.replace( 'wp_add_media,', '' ).replace( ',wp_add_media', '' ).replace( 'wp_add_media', '' );
					settings.toolbar3 = settings.toolbar3.replace( 'wp_add_media,', '' ).replace( ',wp_add_media', '' ).replace( 'wp_add_media', '' );
					settings.toolbar4 = settings.toolbar4.replace( 'wp_add_media,', '' ).replace( ',wp_add_media', '' ).replace( 'wp_add_media', '' );
				}
			}

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
	}, [ mediaButtons, editorHeight, defaultEditor ] );

	return (
		<div
			id={ `wp-${ fieldId }-container` }
			className="wp-editor-container pods-tinymce-editor-container"
		>
			<textarea
				className="wp-editor-area"
				id={ fieldId }
				value={ value || '' }
				onChange={ ( event ) => setValue( event.target.value ) }
				name={ name }
			/>
		</div>
	);
};

TinyMCE.propTypes = {
	name: PropTypes.string.isRequired,
	value: PropTypes.string,
	setValue: PropTypes.func.isRequired,
	editorHeight: PropTypes.number,
	mediaButtons: PropTypes.bool,
	onBlur: PropTypes.func.isRequired,
};

export default TinyMCE;
