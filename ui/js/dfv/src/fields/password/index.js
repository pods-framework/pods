import React from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const Password = ( props ) => {
	const { fieldConfig = {} } = props;

	const {
		password_max_length: maxLength,
		password_placeholder: placeholder,
	} = fieldConfig;

	return (
		<BaseInput
			fieldConfig={ fieldConfig }
			type={ 'password' }
			maxLength={ parseInt( maxLength, 10 ) }
			placeholder={ placeholder }
			{ ...props }
		/>
	);
};

Password.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string.isRequired,
};

export default Password;
