import React from 'react';
import classnames from 'classnames';

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
		htmlAttr: htmlAttributes = {},
		name,
		paragraph_max_length: maxLength,
		paragraph_placeholder: placeholder,
		readonly: readOnly,
	} = fieldConfig;

	// Default implementation if onChange is omitted from props
	const handleChange = ( event ) => setValue( event.target.value );

	return (
		<textarea
			value={ value }
			id={ htmlAttributes.id || `pods-form-ui-${ name }` }
			name={ htmlAttributes.name || name }
			className={ classnames( 'pods-form-ui-field pods-form-ui-field-type-paragraph', htmlAttributes.class ) }
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
