import React from 'react';
import PropTypes from 'prop-types';

import numberFormat from 'dfv/src/helpers/numberFormat';
import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './number-field.scss';

const formatValue = ( newValue, decimals, format ) => {
	if ( 'undefined' === typeof newValue ) {
		newValue = 0;
	}

	let thousands = '';
	let dot = '.';

	// @todo we need to localize a value from PHP to get the wp_locale values
	// as the defaults, something similar to:
	// https://github.com/WordPress/gutenberg/blob/9ced21b344695052db1b3fd10a865b205e5c418e/lib/compat.php#L264-L303
	//
	// thousands = wp_locale['number_format']['thousands_sep'];
	// dot       = wp_locale['number_format']['decimal_point'];

	switch ( format ) {
		case '9.999,99':
			thousands = '.';
			dot = ',';
			break;
		case '9,999.99':
			thousands = ',';
			dot = '.';
			break;
		case "9'999.99":
			thousands = '\'';
			dot = '.';
			break;
		case '9 999,99':
			thousands = ' ';
			dot = ',';
			break;
		case '9999.99':
			thousands = '';
			dot = '.';
			break;
		case '9999,99':
			thousands = '';
			dot = ',';
			break;
		case 'i18n':
			// fall through to default
		default:
			break;
	}

	return numberFormat( parseFloat( newValue ), decimals, dot, thousands );
};

const NumberField = ( {
	fieldConfig,
	value,
	setValue,
} ) => {
	const {
		htmlAttr: htmlAttributes = {},
		readonly: readOnly,
		number_decimals: decimals,
		number_format: format,
		number_format_soft: softFormat,
		number_format_type: type,
		number_max: max,
		number_max_length: maxLength,
		number_min: min,
		number_placeholder: placeholder,
		number_step: step,
	} = fieldConfig;

	const correctedDecimals = toBool( softFormat ) ? 0 : decimals;

	const handleChange = ( event ) => setValue(
		formatValue( event.target.value, correctedDecimals, format )
	);

	if ( 'number' === type ) {
		return (
			<input
				type="text"
				name={ htmlAttributes.name }
				id={ htmlAttributes.id }
				placeholder={ placeholder }
				maxLength={ -1 !== parseInt( maxLength, 10 ) ? maxLength : undefined }
				value={ value }
				readOnly={ !! readOnly }
				onChange={ handleChange }
			/>
		);
	}

	return (
		<div>
			<input
				type="range"
				className="pods-number-field-slider-input"
				name={ htmlAttributes.name }
				id={ htmlAttributes.id }
				placeholder={ placeholder }
				value={ parseFloat( value ) || min || 0 }
				readOnly={ !! readOnly }
				onChange={ handleChange }
				min={ min }
				max={ max }
				step={ step }
			/>

			<div className="pods-slider-field-display">
				{ formatValue( value, correctedDecimals, format ) }
			</div>
		</div>
	);
};

NumberField.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default NumberField;
