import React from 'react';

import { toBool } from 'dfv/src/helpers/booleans';

import './paragraph.scss';

const Paragraph = ( props ) => {
	const {
		fieldConfig = {},
		onBlur,
		onChange,
		setValue,
		value,
	} = props;

	const {
		htmlAttr = {},
		paragraph_max_length: maxLength,
		paragraph_placeholder: placeholder,
		readonly: readOnly,
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
			readOnly={ toBool( readOnly ) }
		>
			{ value }
		</textarea>
	);
};

export default Paragraph;
