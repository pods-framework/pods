import React, { useEffect, useRef } from 'react';
import PropTypes from 'prop-types';
import Quill from 'quill';

import TinyMCE from './tinymce';

import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import 'quill/dist/quill.snow.css';
import './wysiwyg.scss';

const QUILL_TOOLBAR_OPTIONS = [
	[ 'bold', 'italic', 'underline', 'strike' ],
	[ 'blockquote', 'code-block', 'link' ],

	[ { header: 1 }, { header: 2 } ],
	[ { list: 'ordered' }, { list: 'bullet' } ],
	[ { script: 'sub' }, { script: 'super' } ],
	[ { indent: '-1' }, { indent: '+1' } ],

	[ { header: [ 1, 2, 3, 4, 5, 6, false ] } ],

	[ { color: [] }, { background: [] } ],
	[ { align: [] } ],

	[ 'clean' ],
];

// Lightweight React wrapper around Quill 2.x, replacing the abandoned
// react-quill (which pinned the vulnerable quill 1.3.7).
const QuillEditor = ( {
	value = '',
	onChange,
	onBlur,
	readOnly = false,
} ) => {
	const containerRef = useRef( null );
	const quillRef = useRef( null );

	// Hold the latest callbacks so the init effect can stay mount-only
	// without capturing stale references.
	const onChangeRef = useRef( onChange );
	const onBlurRef = useRef( onBlur );

	useEffect( () => {
		onChangeRef.current = onChange;
		onBlurRef.current = onBlur;
	} );

	useEffect( () => {
		const container = containerRef.current;

		if ( ! container ) {
			return undefined;
		}

		// Quill inserts the toolbar as a sibling of its editor node, so give
		// it a dedicated child element that React itself does not manage.
		const editorNode = container.appendChild(
			container.ownerDocument.createElement( 'div' )
		);

		const quill = new Quill( editorNode, {
			theme: 'snow',
			readOnly,
			modules: {
				toolbar: QUILL_TOOLBAR_OPTIONS,
			},
		} );

		quillRef.current = quill;

		if ( value ) {
			// Run the incoming HTML through Quill's own parser rather than
			// assigning innerHTML directly.
			quill.setContents(
				quill.clipboard.convert( { html: value } ),
				'silent'
			);
		}

		quill.on( 'text-change', ( delta, oldDelta, source ) => {
			// Only update state for user-initiated changes to prevent
			// infinite loops on mount/programmatic updates.
			if ( 'user' === source ) {
				onChangeRef.current( quill.root.innerHTML );
			}
		} );

		quill.on( 'selection-change', ( range, oldRange ) => {
			// A null range after having one means the editor lost focus.
			if ( null === range && null !== oldRange ) {
				onBlurRef.current();
			}
		} );

		return () => {
			quill.off( 'text-change' );
			quill.off( 'selection-change' );
			quillRef.current = null;
			container.innerHTML = '';
		};
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	// Reflect external value changes without clobbering active editing.
	useEffect( () => {
		const quill = quillRef.current;

		if ( ! quill || quill.hasFocus() ) {
			return;
		}

		const nextHTML = value || '';

		if ( nextHTML !== quill.root.innerHTML ) {
			quill.setContents(
				quill.clipboard.convert( { html: nextHTML } ),
				'silent'
			);
		}
	}, [ value ] );

	// Reflect readOnly changes without reinitializing the editor.
	useEffect( () => {
		if ( quillRef.current ) {
			quillRef.current.enable( ! readOnly );
		}
	}, [ readOnly ] );

	return <div ref={ containerRef } />;
};

QuillEditor.propTypes = {
	value: PropTypes.string,
	onChange: PropTypes.func.isRequired,
	onBlur: PropTypes.func.isRequired,
	readOnly: PropTypes.bool,
};

const Wysiwyg = ( props ) => {
	const {
		fieldConfig = {},
		setValue,
		value,
		setHasBlurred,
	} = props;

	const {
		htmlAttr: htmlAttributes = {},
		name,
		wysiwyg_editor: editor = 'tinymce',
		wysiwyg_editor_height: editorHeight = 200,
		wysiwyg_media_buttons: mediaButtons = false,
		wysiwyg_delay_init: delayInit = false,
		wysiwyg_wpautop: wpautop = true,
		wysiwyg_default_editor: defaultEditor = 'tinymce',
		read_only: readOnly,
	} = fieldConfig;

	if ( 'quill' === editor || 'cleditor' === editor ) {
		// The "theme" option supports: snow (CLEditor-like) | bubble (simple barebones WYSIWYG).
		return (
			<>
				<QuillEditor
					value={ value || '' }
					onBlur={ () => setHasBlurred() }
					onChange={ ( content ) => setValue( content ) }
					readOnly={ toBool( readOnly ) }
				/>

				<input
					type="hidden"
					value={ value || '' }
					name={ htmlAttributes.name || name }
				/>
			</>
		);
	}

	// If it's readonly and would normally be a TinyMCE field, we don't load the TinyMCE editor.
	if ( toBool( readOnly ) ) {
		return (
			<textarea
				name={ htmlAttributes.name || name }
				value={ value || '' }
				readOnly={ toBool( readOnly ) }
			>
				{ value }
			</textarea>
		);
	}

	return (
		<TinyMCE
			htmlAttributes={ htmlAttributes }
			name={ name }
			value={ value || '' }
			setValue={ setValue }
			editorHeight={ parseInt( editorHeight, 10 ) }
			delayInit={ toBool( delayInit ) }
			mediaButtons={ toBool( mediaButtons ) }
			wpautop={ toBool( wpautop ) }
			defaultEditor={ defaultEditor }
			onBlur={ () => setHasBlurred() }
		/>
	);
};

Wysiwyg.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Wysiwyg;
