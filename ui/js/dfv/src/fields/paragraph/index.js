import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import { toBool } from 'dfv/src/helpers/booleans';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './paragraph.scss';

const Paragraph = ( {
	fieldConfig = {},
	onBlur,
	onChange,
	setValue,
	value,
	setHasBlurred,
} ) => {
	const {
		htmlAttr: htmlAttributes = {},
		name,
		paragraph_max_length: maxLength,
		paragraph_placeholder: placeholder = fieldConfig.placeholder,
		read_only: readOnly,
	} = fieldConfig;

	// Default implementation if onChange is omitted from props
	const handleChange = ( event ) => setValue( event.target.value );

	const handleBlur = ( event ) => {
		if ( onBlur ) {
			onBlur( event );
		}

		setHasBlurred();
	};

	return (
		<textarea
			value={ value || '' }
			id={ htmlAttributes.id || `pods-form-ui-${ name }` }
			name={ htmlAttributes.name || name }
			className={ classnames( 'pods-form-ui-field pods-form-ui-field-type-paragraph', htmlAttributes.class ) }
			maxLength={ 0 < parseInt( maxLength, 10 ) ? parseInt( maxLength, 10 ) : undefined }
			placeholder={ placeholder }
			onChange={ onChange || handleChange }
			onBlur={ handleBlur }
			readOnly={ toBool( readOnly ) }
		>
			{ value }
		</textarea>
	);
};

Paragraph.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Paragraph;
