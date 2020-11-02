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
		name,
		wysiwyg_editor: editor = 'tinymce',
		wysiwyg_editor_height: editorHeight = 400,
		wysiwyg_media_buttons: mediaButtons,
		// wysiwyg_allow_shortcode: allowShortcode,
		// wysiwyg_allowed_html_tags: allowedHtmlTags,
		// wysiwyg_convert_chars: convertChars,
		// wysiwyg_oembed: oembed,
		// wysiwyg_wpautop: autoP,
		// wysiwyg_wptexturize: texturize,
	} = fieldConfig;

	if ( 'quill' === editor || 'cleditor' === editor ) {
		return (
			<ReactQuill
				value={ value || '' }
				onChange={ setValue }
				theme="snow"
				modules={ {
					toolbar: QUILL_TOOLBAR_OPTIONS,
				} }
			/>
		);
	}

	return (
		<TinyMCE
			name={ name }
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
