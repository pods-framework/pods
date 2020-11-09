import React, { useState } from 'react';
import PropTypes from 'prop-types';

import {
	parseFloatWithPodsFormat,
	formatNumberWithPodsFormat,
} from 'dfv/src/helpers/formatNumberWithPodsFormat';

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
		currency_decimal_handling: decimalHandling = 'none',
		currency_decimals: decimals,
		currency_format: format,
		currency_format_sign: formatSign,
		currency_format_type: type = 'number',
		currency_html5: html5,
		currency_max: max,
		currency_max_length: maxLength,
		currency_min: min,
		currency_placeholder: placeholder,
		currency_step: step,
	} = fieldConfig;

	// The actual value from the store could be either a float or
	// a formatted string, so be able to handle either one, but keep
	// a formatted version available locally.
	const [ formattedValue, setFormattedValue ] = useState(
		formatNumberWithPodsFormat( value, decimals, format, decimalHandling === 'remove' )
	);

	const handleChange = ( event ) => {
		setValue( parseFloatWithPodsFormat( event.target.value, format ) );
		setFormattedValue( event.target.value );
	};

	const reformatFormattedValue = () => {
		const newFormattedValue = formatNumberWithPodsFormat(
			value,
			decimals,
			format,
			decimalHandling === 'remove'
		);

		setFormattedValue( newFormattedValue );
	};

	const formatSignSymbol = window?.podsAdminConfig?.currencies[ formatSign ]?.sign || '$';

	if ( 'slider' === type ) {
		return (
			<div>
				<input
					type="range"
					className="pods-currency-field-slider-input"
					name={ htmlAttributes.name }
					id={ htmlAttributes.id }
					placeholder={ placeholder }
					value={ value || min || 0 }
					readOnly={ !! readOnly }
					onChange={ handleChange }
					onBlur={ reformatFormattedValue }
					min={ min }
					max={ max }
					step={ step }
				/>

				<div className="pods-slider-field-display">
					{ formattedValue }
				</div>
			</div>
		);
	}

	return (
		<div className="pods-currency-container">
			<code className="pods-currency-sign">
				{ formatSignSymbol }
			</code>
			<input
				type={ html5 ? 'number' : 'text' }
				className="pods-currency-input"
				name={ htmlAttributes.name }
				id={ htmlAttributes.id }
				placeholder={ placeholder }
				maxLength={ -1 !== parseInt( maxLength, 10 ) ? maxLength : undefined }
				step={ html5 ? 'any' : undefined }
				value={ formattedValue }
				readOnly={ !! readOnly }
				onChange={ handleChange }
				onBlur={ reformatFormattedValue }
			/>
		</div>
	);
};

Currency.defaultProps = {
	value: '',
};

Currency.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ),
};

export default Currency;
