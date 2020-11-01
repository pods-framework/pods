import React from 'react';

import './wysiwyg.scss';

const Wysiwyg = ( props ) => {
	const {
		fieldConfig = {},
		onBlur,
		onChange,
		setValue,
		value,
	} = props;

	const {
		htmlAttr = {},
		wysiwyg_allow_shortcode: allowShortcode,
		wysiwyg_allowed_html_tags: allowedHtmlTags,
		wysiwyg_convert_chars: convertChars,
		wysiwyg_editor: editor,
		wysiwyg_editor_height: editorHeight,
		wysiwyg_media_buttons: mediaButtons,
		wysiwyg_oembed: oembed,
		wysiwyg_wpautop: autoP,
		wysiwyg_wptexturize: texturize,
		read_only: readOnly = false,
	} = fieldConfig;

	// Default implementation if onChange is omitted from props
	const handleChange = ( event ) => setValue( event.target.value );

	return (
		<textarea
			value={ value }
			name={ htmlAttr.name }
			id={ htmlAttr.id }
			className="pods-form-ui-field pods-form-ui-field-type-paragraph"
			maxLength={ -1 !== parseInt( maxLength, 10 ) ? maxLength : undefined }
			placeholder={ placeholder }
			onChange={ onChange || handleChange }
			onBlur={ onBlur }
			readOnly={ readOnly }
		>
			{ value }
		</textarea>
	);
};

export default Wysiwyg;
