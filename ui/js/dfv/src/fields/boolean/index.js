import React from 'react';
import PropTypes from 'prop-types';

import Pick from '../pick';

import { toNumericBool } from 'dfv/src/helpers/booleans';
import {
	FIELD_PROP_TYPE_SHAPE,
	BOOLEAN_ALL_TYPES_OR_EMPTY,
} from 'dfv/src/config/prop-types';

const Boolean = ( {
	fieldConfig = {},
	setValue,
	value,
} ) => {
	const {
		boolean_format_type: formatType = 'checkbox', // 'checkbox', 'radio', or 'dropdown'
		boolean_no_label: noLabel = 'No',
		boolean_yes_label: yesLabel = 'Yes',
	} = fieldConfig;

	const options = [ { value: '1', label: yesLabel } ];

	if ( 'checkbox' !== formatType ) {
		options.push( { value: '0', label: noLabel } );
	}

	return (
		<Pick
			fieldConfig={ {
				...fieldConfig,
				pick_format_type: 'single',
				pick_format_single: formatType,
				data: options,
			} }
			value={ toNumericBool( value ) }
			setValue={ setValue }
		/>
	);
};

Boolean.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: BOOLEAN_ALL_TYPES_OR_EMPTY,
};

export default Boolean;
