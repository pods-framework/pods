import React from 'react';
import PropTypes from 'prop-types';

import Pick from '../pick';
import {
	toBool,
	toNumericBool,
} from 'dfv/src/helpers/booleans';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

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

	// Typecast values.
	const numericBooleanValue = toNumericBool( value );
	const setBooleanValue = ( newValue ) => setValue( toBool( newValue ) );

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
			} }
			value={ '1' === numericBooleanValue ? '1' : undefined }
			setValue={ setBooleanValue }
			data={ options }
		/>
	);
};

Boolean.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Boolean;
