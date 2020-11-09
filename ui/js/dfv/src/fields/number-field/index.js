import React, { useState } from 'react';
import PropTypes from 'prop-types';

import {
	parseFloatWithPodsFormat,
	formatNumberWithPodsFormat,
} from 'dfv/src/helpers/formatNumberWithPodsFormat';

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

	// The actual value from the store could be either a float or
	// a formatted string, so be able to handle either one, but keep
	// a formatted version available locally.
	const [ formattedValue, setFormattedValue ] = useState(
		formatNumberWithPodsFormat( value, decimals, format, softFormat )
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
			softFormat
		);

		setFormattedValue( newFormattedValue );
	};

	if ( 'slider' === type ) {
		return (
			<div>
				<input
					type="range"
					className="pods-number-field-slider-input"
					name={ htmlAttributes.name }
					id={ htmlAttributes.id }
					placeholder={ placeholder }
					value={ value || min || 0 }
					readOnly={ !! readOnly }
					onChange={ handleChange }
					onBlur={ reformatFormattedValue }
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
			value={ formattedValue }
			step={ html5 ? 'any' : undefined }
			readOnly={ !! readOnly }
			onChange={ handleChange }
			onBlur={ reformatFormattedValue }
		/>
	);
};

NumberField.defaultProps = {
	value: '',
};

NumberField.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ),
};

export default NumberField;
