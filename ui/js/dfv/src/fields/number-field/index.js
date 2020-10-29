import React from 'react';
import { debounce } from 'lodash';
import PropTypes from 'prop-types';

import numberFormatValue from 'dfv/src/helpers/numberFormatValue';
import { toBool } from 'dfv/src/helpers/booleans';
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

	const correctedDecimals = toBool( softFormat ) ? 'auto' : decimals;

	const handleChange = ( event ) => debounce( () => {
		setValue(
			numberFormatValue( event.target.value, correctedDecimals, format )
		);
	}, 1000 );

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
					min={ min }
					max={ max }
					step={ step }
				/>

				<div className="pods-slider-field-display">
					{ numberFormatValue( value, correctedDecimals, format ) }
				</div>
			</div>
		);
	}

	return (
		<input
			type={ html5 ? 'number' : 'text' }
			name={ htmlAttributes.name }
			id={ htmlAttributes.id }
			placeholder={ placeholder }
			maxLength={ -1 !== parseInt( maxLength, 10 ) ? maxLength : undefined }
			step={ html5 ? 'any' : undefined }
			value={ value }
			readOnly={ !! readOnly }
			onChange={ handleChange }
		/>
	);
};

NumberField.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default NumberField;
