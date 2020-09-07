import React from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

// @todo this may be an incomplete field component
// @todo add tests
const Boolean = ( props ) => {
	const { setValue } = props;

	const handleChange = ( event ) => {
		setValue( event.target.checked );
	};

	return (
		<BaseInput
			type={ 'checkbox' }
			onChange={ handleChange }
			{ ...props }
		/>
	);
};

Boolean.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string.isRequired,
};

export default Boolean;
