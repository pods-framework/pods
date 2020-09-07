import React from 'react';
import PropTypes from 'prop-types';

import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const BaseInput = ( props ) => {
	const {
		fieldConfig = {},
		maxLength,
		onBlur,
		onChange,
		setValue,
		type,
		value,
	} = props;

	// Default implementation if onChange is omitted from props
	const handleChange = ( event ) => setValue( event.target.value );

	return (
		<input
			className={ props.className }
			type={ type }
			name={ fieldConfig.htmlAttr?.name }
			id={ fieldConfig.htmlAttr?.id }
			// eslint-disable-next-line camelcase
			data-name-clean={ fieldConfig.htmlAttr?.name_clean }
			placeholder={ fieldConfig.placeholder }
			maxLength={ maxLength }
			value={ type !== 'checkbox' ? value : undefined }
			checked={ type === 'checkbox' ? toBool( value ) : undefined }
			readOnly={ !! fieldConfig.readonly }
			onChange={ onChange || handleChange }
			onBlur={ onBlur }
			min={ fieldConfig.min }
			max={ fieldConfig.max }
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
};

export default BaseInput;
