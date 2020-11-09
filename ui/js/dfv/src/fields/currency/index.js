import React from 'react';
import PropTypes from 'prop-types';

import { formatNumberWithPodsFormat } from 'dfv/src/helpers/formatNumberWithPodsFormat';

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

	const handleBlur = ( event ) => {
		const formattedValue = formatNumberWithPodsFormat(
			event.target.value,
			decimals,
			format,
			decimalHandling === 'remove'
		);

		setValue( formattedValue || '' );
	};

	const handleChange = ( event ) => setValue( event.target.value );

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
					value={ parseFloat( value ) || min || 0 }
					readOnly={ !! readOnly }
					onChange={ handleChange }
					min={ min }
					max={ max }
					step={ step }
				/>

				<div className="pods-slider-field-display">
					{ formatNumberWithPodsFormat( value, decimals, format, decimalHandling === 'remove' ) }
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
				value={ value || '' }
				readOnly={ !! readOnly }
				onChange={ handleChange }
				onBlur={ handleBlur }
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
