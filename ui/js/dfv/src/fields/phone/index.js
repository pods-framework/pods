import React from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';
import { toBool } from 'dfv/src/helpers/booleans';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const Phone = ( props ) => {
	const { fieldConfig = {} } = props;

	const {
		phone_max_length: maxLength,
		phone_placeholder: placeholder = fieldConfig.placeholder,
		phone_html5: html5,
	} = fieldConfig;

	return (
		<BaseInput
			{ ...props }
			fieldConfig={ fieldConfig }
			type={ true === toBool( html5 ) ? 'tel' : 'text' }
			maxLength={ 0 < parseInt( maxLength, 10 ) ? parseInt( maxLength, 10 ) : undefined }
			placeholder={ placeholder }
		/>
	);
};

Phone.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Phone;
