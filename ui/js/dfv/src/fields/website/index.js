import React from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';
import { toBool } from 'dfv/src/helpers/booleans';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const Website = ( props ) => {
	const { fieldConfig = {} } = props;

	const {
		website_max_length: maxLength,
		website_placeholder: placeholder,
		website_html5: html5,
	} = fieldConfig;

	return (
		<BaseInput
			fieldConfig={ fieldConfig }
			type={ true === toBool( html5 ) ? 'url' : 'text' }
			maxLength={ parseInt( maxLength, 10 ) }
			placeholder={ placeholder }
			{ ...props }
		/>
	);
};

Website.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Website;
