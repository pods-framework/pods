import React from 'react';
import { debounce } from 'lodash';
import PropTypes from 'prop-types';

import numberFormatValue from 'dfv/src/helpers/numberFormatValue';
import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './currency.scss';

const Currency = ( {
	fieldConfig,
	value,
	setValue,
} ) => {
	const {
		htmlAttr: htmlAttributes = {},
		readonly: readOnly,
		currency_decimals: decimals,
		// currency_decimal_handling: decimalHandling,
		currency_format: format,
		currency_format_type: type,
		currency_html5: html5,
		currency_max: max,
		currency_max_length: maxLength,
		currency_min: min,
		currency_placeholder: placeholder,
		currency_step: step,
	} = fieldConfig;

	const handleChange = ( event ) => debounce( () => {
		setValue(
			numberFormatValue( event.target.value, decimals, format )
		);
	}, 1000 );

	if ( 'slider' === type ) {
		return (
			<div>
				<input
					type="range"
					className="pods-currency-field-slider-input"
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
					{ numberFormatValue( value, decimals, format ) }
				</div>
			</div>
		);
	}

	// @todo Pull in the currency sign from window.podsDFVConfig.currencies
	return (
		<div className="pods-currency-container">
			<code className="pods-currency-sign">$</code>
			<input
				type={ html5 ? 'number' : 'text' }
				className="pods-currency-input"
				name={ htmlAttributes.name }
				id={ htmlAttributes.id }
				placeholder={ placeholder }
				maxLength={ -1 !== parseInt( maxLength, 10 ) ? maxLength : undefined }
				step={ html5 ? 'any' : undefined }
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
