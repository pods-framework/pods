import React from 'react';
import ReactQuill from 'react-quill';
import PropTypes from 'prop-types';

import TinyMCE from './tinymce';

import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import 'react-quill/dist/quill.snow.css';
import './wysiwyg.scss';

const QUILL_TOOLBAR_OPTIONS = [
	[ 'bold', 'italic', 'underline', 'strike' ],
	[ 'blockquote', 'code-block' ],

	[ { header: 1 }, { header: 2 } ],
	[ { list: 'ordered' }, { list: 'bullet' } ],
	[ { script: 'sub' }, { script: 'super' } ],
	[ { indent: '-1' }, { indent: '+1' } ],

	[ { header: [ 1, 2, 3, 4, 5, 6, false ] } ],

	[ { color: [] }, { background: [] } ],
	[ { align: [] } ],

	[ 'clean' ],
];

const Wysiwyg = ( props ) => {
	const {
		fieldConfig = {},
		setValue,
		value,
	} = props;

	const {
		htmlAttr: htmlAttributes = {},
		name,
		wysiwyg_editor: editor = 'tinymce',
		wysiwyg_editor_height: editorHeight = 400,
		wysiwyg_media_buttons: mediaButtons,
	} = fieldConfig;

	if ( 'quill' === editor || 'cleditor' === editor ) {
		// The "theme" option supports: snow (CLEditor-like) | bubble (simple barebones WYSIWYG).
		return (
			<>
				<ReactQuill
					value={ value || '' }
					onChange={ setValue }
					theme="snow"
					modules={ {
						toolbar: QUILL_TOOLBAR_OPTIONS,
					} }
				/>

				<input
					type="hidden"
					value={ value || '' }
					name={ htmlAttributes.name || name }
				/>
			</>
		);
	}

	return (
		<TinyMCE
			name={ htmlAttributes.name || name }
			value={ value }
			setValue={ setValue }
			editorHeight={ parseInt( editorHeight, 10 ) }
			mediaButtons={ toBool( mediaButtons ) }
		/>
	);
};

Wysiwyg.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Wysiwyg;
