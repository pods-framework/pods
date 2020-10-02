import React from 'react';
import PropTypes from 'prop-types';

import { ColorPicker } from '@wordpress/components';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const Color = ( {
	setValue,
	value,
} ) => {
	return (
		<ColorPicker
			color={ value }
			onChangeComplete={ ( newValue ) => setValue( newValue.hex ) }
			disableAlpha
		/>
	);
};

Color.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Color;
