import React from 'react';
import PropTypes from 'prop-types';

import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './currency.scss';

const formatValue = ( newValue ) => {
	return newValue;
};

const Currency = ( {
	fieldConfig,
	value,
	setValue,
} ) => {
	const {
		htmlAttr: htmlAttributes = {},
		readonly: readOnly,
		// currency_decimal_handling: decimalHandling,
		// currency_decimals: decimals,
		// currency_format: format,
		// currency_format_placement: formatPlacement,
		// currency_format_sign: formatSign,
		// currency_format_type: formatType,
		// currency_max: max,
		currency_max_length: maxLength,
		// currency_min: min,
		currency_placeholder: placeholder,
		// currency_step: step,
	} = fieldConfig;

	const handleChange = ( event ) => setValue(
		formatValue( event.target.value )
	);

	return (
		<div className="pods-currency-container">
			<code className="pods-currency-sign">$</code>
			<input
				type="text"
				className="pods-currency-input"
				name={ htmlAttributes.name }
				id={ htmlAttributes.id }
				placeholder={ placeholder }
				maxLength={ -1 !== parseInt( maxLength, 10 ) ? maxLength : undefined }
				value={ value }
				readOnly={ !! readOnly }
				onChange={ handleChange }
			/>
		</div>
	);
};

Currency.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Currency;
