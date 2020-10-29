import React from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';
import { toBool } from 'dfv/src/helpers/booleans';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const Phone = ( props ) => {
	const { fieldConfig = {} } = props;

	const {
		phone_max_length: maxLength,
		phone_placeholder: placeholder,
		phone_html5: html5,
	} = fieldConfig;

	return (
		<BaseInput
			{ ...props }
			fieldConfig={ fieldConfig }
			type={ true === toBool( html5 ) ? 'tel' : 'text' }
			maxLength={ maxLength ? parseInt( maxLength, 10 ) : undefined }
			placeholder={ placeholder }
		/>
	);
};

Phone.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Phone;
