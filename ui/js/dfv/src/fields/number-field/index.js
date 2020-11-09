import React, { useState } from 'react';
import PropTypes from 'prop-types';

import formatNumericString from 'dfv/src/helpers/formatNumericString';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './number-field.scss';

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
		number_html5: html5,
		number_max: max,
		number_max_length: maxLength,
		number_min: min,
		number_placeholder: placeholder,
		number_step: step,
	} = fieldConfig;

	const [ currentValue, setCurrentValue ] = useState( value );

	const handleBlur = ( event ) => {
		const formattedValue = formatNumericString(
			event.target.value,
			decimals,
			format,
			softFormat
		);

		setValue( formattedValue || '' );
	};

	const handleChange = ( event ) => setCurrentValue( event.target.value );

	if ( 'slider' === type ) {
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
					min={ parseInt( min, 10 ) || undefined }
					max={ parseInt( max, 10 ) || undefined }
					step={ parseFloat( step ) || undefined }
				/>

				<div className="pods-slider-field-display">
					{ formatNumericString( value, decimals, format, softFormat ) }
				</div>
			</div>
		);
	}

	const integerMaxLength = parseInt( maxLength, 10 );
	const processedMaxLength = ( -1 !== integerMaxLength && ! isNaN( integerMaxLength ) )
		? integerMaxLength
		: undefined;

	return (
		<input
			type={ html5 ? 'number' : 'text' }
			name={ htmlAttributes.name }
			id={ htmlAttributes.id }
			placeholder={ placeholder }
			maxLength={ processedMaxLength }
			value={ currentValue }
			step={ html5 ? 'any' : undefined }
			readOnly={ !! readOnly }
			onChange={ handleChange }
			onBlur={ handleBlur }
		/>
	);
};

NumberField.defaultProps = {
	value: '',
};

NumberField.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default NumberField;
