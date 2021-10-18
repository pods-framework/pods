import React, { useState, useEffect } from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import {
	parseFloatWithPodsFormat,
	formatNumberWithPodsFormat,
} from 'dfv/src/helpers/formatNumberWithPodsFormat';

import { numberValidator } from 'dfv/src/helpers/validators';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './currency.scss';

const Currency = ( {
	addValidationRules,
	fieldConfig,
	value,
	setValue,
	setHasBlurred,
} ) => {
	const {
		htmlAttr: htmlAttributes = {},
		readonly: readOnly,
		currency_decimal_handling: decimalHandling = 'none',
		currency_decimals: decimalMaxLength,
		currency_format: format,
		currency_format_sign: formatSign,
		currency_format_type: type = 'number',
		currency_html5: html5,
		currency_max: max,
		currency_max_length: digitMaxLength,
		currency_min: min,
		currency_placeholder: placeholder,
		currency_step: step,
	} = fieldConfig;

	// The actual value from the store could be either a float or
	// a formatted string, so be able to handle either one, but keep
	// a formatted version available locally.
	const [ formattedValue, setFormattedValue ] = useState(
		formatNumberWithPodsFormat( value, format, decimalHandling === 'remove' )
	);

	useEffect( () => {
		const numberValidationRule = {
			rule: numberValidator( digitMaxLength, decimalMaxLength, format ),
			condition: () => true,
		};

		addValidationRules( [ numberValidationRule ] );
	}, [] );

	const handleChange = ( event ) => {
		setValue( parseFloatWithPodsFormat( event.target.value, format ) );
		setFormattedValue( event.target.value );
	};

	const reformatFormattedValue = () => {
		const newFormattedValue = formatNumberWithPodsFormat(
			value,
			format,
			decimalHandling === 'remove'
		);

		setFormattedValue( newFormattedValue );
	};

	const handleBlur = () => {
		setHasBlurred();

		reformatFormattedValue();
	};

	const formatSignSymbol = window?.podsAdminConfig?.currencies[ formatSign ]?.sign || '$';

	if ( 'slider' === type ) {
		return (
			<div>
				<input
					type="range"
					id={ htmlAttributes.id }
					name={ htmlAttributes.name }
					className={ classnames( 'pods-currency-field-slider-input', htmlAttributes.class ) }
					placeholder={ placeholder }
					value={ value || min || 0 }
					readOnly={ !! readOnly }
					onChange={ handleChange }
					onBlur={ handleBlur }
					min={ parseInt( min, 10 ) || undefined }
					max={ parseInt( max, 10 ) || undefined }
					step={ parseFloat( step ) || undefined }
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
				id={ htmlAttributes.id }
				name={ htmlAttributes.name }
				data-name-clean={ htmlAttributes.name_clean }
				className={ classnames( 'pods-currency-input', htmlAttributes.class ) }
				placeholder={ placeholder }
				step={ html5 ? 'any' : undefined }
				min={ html5 ? ( parseInt( min, 10 ) || undefined ) : undefined }
				max={ html5 ? ( parseInt( max, 10 ) || undefined ) : undefined }
				value={ formattedValue }
				readOnly={ !! readOnly }
				onChange={ handleChange }
				onBlur={ handleBlur }
			/>
		</div>
	);
};

Currency.defaultProps = {
	value: '',
};

Currency.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ),
};

export default Currency;
