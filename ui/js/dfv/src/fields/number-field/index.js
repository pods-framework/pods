import React, { useState, useEffect } from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import {
	parseFloatWithPodsFormat,
	formatNumberWithPodsFormat,
} from 'dfv/src/helpers/formatNumberWithPodsFormat';

import { numberValidator } from 'dfv/src/helpers/validators';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './number-field.scss';

const NumberField = ( {
	addValidationRules,
	fieldConfig,
	value,
	setValue,
	setHasBlurred,
} ) => {
	const {
		htmlAttr: htmlAttributes = {},
		name,
		readonly: readOnly,
		number_decimals: decimalMaxLength = 'auto',
		number_format: format,
		number_format_soft: softFormat,
		number_format_type: type,
		number_html5: html5,
		number_max: max,
		number_max_length: digitMaxLength,
		number_min: min,
		number_placeholder: placeholder,
		number_step: step,
	} = fieldConfig;

	// The actual value from the store could be either a float or
	// a formatted string, so be able to handle either one, but keep
	// a formatted version available locally.
	const [ formattedValue, setFormattedValue ] = useState(
		formatNumberWithPodsFormat( value, format, softFormat )
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
			softFormat
		);

		setFormattedValue( newFormattedValue );
	};

	const handleBlur = () => {
		setHasBlurred();
		reformatFormattedValue();
	};

	if ( 'slider' === type ) {
		return (
			<div>
				<input
					type="range"
					id={ htmlAttributes.id || `pods-form-ui-${ name }` }
					name={ htmlAttributes.name || name }
					className={ classnames( 'pods-form-ui-number-range', htmlAttributes.class ) }
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
		<input
			type={ html5 ? 'number' : 'text' }
			name={ htmlAttributes.name }
			id={ htmlAttributes.id || `pods-form-ui-${ name }` }
			data-name-clean={ htmlAttributes.name_clean }
			className={ classnames( 'pods-form-ui-field pods-form-ui-field-type-number', htmlAttributes.class ) }
			placeholder={ placeholder }
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
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ),
};

export default NumberField;
