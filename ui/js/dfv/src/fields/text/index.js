import React from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const Text = ( props ) => {
	const {
		fieldConfig = {},
		value,
	} = props;

	const {
		text_max_length: maxLength,
		text_placeholder: placeholder,
	} = fieldConfig;

	return (
		<BaseInput
			{ ...props }
			type="text"
			value={ value || '' }
			maxLength={ maxLength ? parseInt( maxLength, 10 ) : undefined }
			placeholder={ placeholder }
		/>
	);
};

Text.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Text;
