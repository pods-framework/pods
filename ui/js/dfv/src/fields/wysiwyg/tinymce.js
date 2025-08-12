/**
 * External dependencies
 */
import React, { useState, useEffect, useRef } from 'react';
import { debounce } from 'lodash';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { F10, isKeyboardEvent } from '@wordpress/keycodes';
import {Button} from "@wordpress/components";
import {__} from "@wordpress/i18n";

// Based on the core Freeform block's edit component, see:
// https://github.com/WordPress/gutenberg/blob/trunk/packages/block-library/src/freeform/edit.js
const TinyMCE = ( {
	htmlAttributes,
	name,
	value,
	setValue,
	editorHeight,
	mediaButtons,
	delayInit,
	wpautop,
	defaultEditor,
	onBlur,
} ) => {
	const fieldId = htmlAttributes.id || `pods-form-ui-${ name }`;

	const didMountRef = useRef( false );

	const [ willDelayInit, setDelayInit ] = useState( delayInit );

	let willDelayInitNow = willDelayInit;

	const disableDelayInit = () => {
		if (document.readyState !== 'complete') {
			return;
		}

		setDelayInit(false);
		willDelayInitNow = false;

		initialize();
	}

	const onReadyStateChange = () => {
		if ( document.readyState === 'complete' ) {
			initialize();
		}
	}

	const destroyEditor = () => {
		const editor = window.tinymce.get( fieldId );
		const textarea = document.getElementById( fieldId );

		document.removeEventListener(
			'readystatechange',
			onReadyStateChange
		);
		window.wp.oldEditor.remove( fieldId );
        if (textarea) {
		    textarea.removeAttribute( 'style' );
        }
		didMountRef.current = false;
	}

	const reInit = () => {
		destroyEditor();
		initialize();
	}

	function onSetup( editor ) {
		if ( ! didMountRef.current ) {
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

		didMountRef.current = true;
	}

	function initialize() {
		if ( willDelayInitNow ) {
			return;
		}

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

		// Remove fullscreen button from the TinyMCE toolbar.
		settings.toolbar1 = settings.toolbar1.replace( 'fullscreen,', '' ).replace( ',fullscreen', '' ).replace( 'fullscreen', '' );
		settings.toolbar2 = settings.toolbar2.replace( 'fullscreen,', '' ).replace( ',fullscreen', '' ).replace( 'fullscreen', '' );
		settings.toolbar3 = settings.toolbar3.replace( 'fullscreen,', '' ).replace( ',fullscreen', '' ).replace( 'fullscreen', '' );
		settings.toolbar4 = settings.toolbar4.replace( 'fullscreen,', '' ).replace( ',fullscreen', '' ).replace( 'fullscreen', '' );

		const tinymceSettings = {
			...settings,
			content_css: false,
			setup: onSetup,
			wpautop,
		};

		if ( editorHeight ) {
			tinymceSettings.height = editorHeight;
		}

		window.wp.oldEditor.initialize( fieldId, {
			tinymce: tinymceSettings,
			mediaButtons,
			quicktags: true,
		} );
	}

	useEffect( () => {
		if ( ! didMountRef.current ) {
			return;
		}

		const editor = window.tinymce.get( fieldId );

		if ( ! editor ) {
			return;
		}

		const currentContent = editor.getContent();

		if ( currentContent !== value ) {
			editor.setContent( value || '' );
		}
	}, [ value ] );

	useEffect( () => {
		const { baseURL, suffix } = window?.wpEditorL10n?.tinymce || { baseURL: '', suffix: '' };

		//didMountRef.current = true;

		if ( '' !== baseURL || '' !== suffix ) {
			window.tinymce.EditorManager.overrideDefaults({
				base_url: baseURL,
				suffix,
			});
		}

		if ( document.readyState === 'complete' ) {
			initialize();
		} else {
			document.addEventListener( 'readystatechange', onReadyStateChange );
		}

		return destroyEditor;
	}, [] );

	return (
		<div
			id={ `wp-${ fieldId }-container` }
			className="wp-editor-container pods-tinymce-editor-container"
		>
			{ willDelayInit && (
				<Button
					onClick={disableDelayInit}
					isSecondary
					aria-label={__('Initialize the TinyMCE editor to begin editing content', 'pods')}
				>
					{__('Initialize this editor', 'pods')}
				</Button>
			) }

			<textarea
				className="wp-editor-area"
				style={willDelayInit ? { display: 'none' } : {}}
				id={ fieldId }
				value={ value || '' }
				onChange={ ( event ) => setValue( event.target.value ) }
				name={ htmlAttributes.name || name }
			/>

			{ ! willDelayInit && (
				<p className="pods-tinymce-reinit">
					<Button
						onClick={reInit}
						isTertiary
						isSmall
						aria-label={__('Reload the editor instance in case of a compatibility issue', 'pods')}
					>
						{__('Reload editor', 'pods')}
					</Button>
				</p>
			) }
		</div>
	);
};

TinyMCE.propTypes = {
	htmlAttributes: PropTypes.shape( {
		id: PropTypes.string,
		class: PropTypes.string,
		name: PropTypes.string,
	} ),
	name: PropTypes.string.isRequired,
	value: PropTypes.string,
	setValue: PropTypes.func.isRequired,
	editorHeight: PropTypes.number,
	mediaButtons: PropTypes.bool,
	delayInit: PropTypes.bool,
	wpautop: PropTypes.bool,
	onBlur: PropTypes.func.isRequired,
};

export default TinyMCE;
