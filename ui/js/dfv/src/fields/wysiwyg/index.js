import React from 'react';
import PropTypes from 'prop-types';

import TinyMCE from './tinymce';
import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './wysiwyg.scss';

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

	// @todo support the "cleditor"
	if ( 'cleditor' === editor ) {
		return <textarea />;
	}

	return (
		<TinyMCE
			name={ name }
			value={ value }
			setValue={ setValue }
			editorHeight={ editorHeight }
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
