import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const BaseInput = ( props ) => {
	const {
		fieldConfig = {},
		maxLength,
		placeholder,
		onBlur,
		onChange,
		setValue,
		type,
		value,
	} = props;

	const {
		htmlAttr: htmlAttributes = {},
		readonly: readOnly,
		name,
	} = fieldConfig;

	// Default implementation if onChange is omitted from props
	const handleChange = ( event ) => setValue( event.target.value );

	return (
		<input
			type={ type }
			id={ htmlAttributes.id || `pods-form-ui-${ name }` }
			name={ htmlAttributes.name || name }
			// eslint-disable-next-line camelcase
			data-name-clean={ htmlAttributes.name_clean }
			className={ classnames( props.className, htmlAttributes.class ) }
			placeholder={ placeholder }
			maxLength={ -1 !== maxLength ? maxLength : undefined }
			value={ type !== 'checkbox' ? value : undefined }
			checked={ type === 'checkbox' ? toBool( value ) : undefined }
			readOnly={ !! readOnly }
			onChange={ onChange || handleChange }
			onBlur={ onBlur }
		/>
	);
};

BaseInput.propTypes = {
	className: PropTypes.string,
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	onBlur: PropTypes.func,
	onChange: PropTypes.func,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	setValue: PropTypes.func.isRequired,
	type: PropTypes.string.isRequired,
	maxLength: PropTypes.number,
	placeholder: PropTypes.string,
};

export default BaseInput;
