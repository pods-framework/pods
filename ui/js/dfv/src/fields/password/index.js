import React from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const Password = ( props ) => {
	const {
		fieldConfig = {},
		value,
	} = props;

	const {
		password_max_length: maxLength,
		password_placeholder: placeholder,
	} = fieldConfig;

	return (
		<BaseInput
			{ ...props }
			fieldConfig={ fieldConfig }
			type={ 'password' }
			value={ value || '' }
			maxLength={ maxLength ? parseInt( maxLength, 10 ) : undefined }
			placeholder={ placeholder }
			autoComplete={ 'new-password' }
		/>
	);
};

Password.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Password;
