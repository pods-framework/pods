import React from 'react';

import Pick from '../pick';

import { toNumericBool } from 'dfv/src/helpers/booleans';
import {
	BOOLEAN_ALL_TYPES_OR_EMPTY,
	FIELD_COMPONENT_BASE_PROPS,
} from 'dfv/src/config/prop-types';

const Boolean = ( props ) => {
	const {
		fieldConfig = {},
		setValue,
		value,
	} = props;

	const {
		boolean_format_type: formatType = 'checkbox', // 'checkbox', 'radio', or 'dropdown'
		boolean_no_label: noLabel = 'No',
		boolean_yes_label: yesLabel = 'Yes',
	} = fieldConfig;

	// Convert "Yes"/"No" strings to "1" or "0"
	let formattedValue = value;

	if ( 'Yes' === value ) {
		formattedValue = '1';
	} else if ( 'No' === value ) {
		formattedValue = '0';
	}

	formattedValue = toNumericBool( formattedValue );

	// Set up options to pass to Pick component
	const options = [ { value: '1', label: yesLabel } ];

	if ( 'checkbox' !== formatType ) {
		options.push( { value: '0', label: noLabel } );
	}

	return (
		<Pick
			{ ...props }
			fieldConfig={ {
				...fieldConfig,
				pick_format_type: 'single',
				pick_format_single: formatType,
				data: options,
			} }
			value={ formattedValue }
			setValue={ setValue }
		/>
	);
};

Boolean.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: BOOLEAN_ALL_TYPES_OR_EMPTY,
};

export default Boolean;
