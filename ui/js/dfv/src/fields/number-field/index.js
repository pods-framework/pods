/* eslint-disable react/prop-types */
import React from 'react';
import BaseInput from 'dfv/src/fields/base-input';
// import * as validationRules from 'dfv/src/validation/validation-rules';

// @todo this is an incomplete field component
// @todo add tests?
const NumberField = ( props ) => {
	// noinspection JSUnresolvedVariable
	/* Enable this only for slider
	props.validation.addRules( [
		{
			rule: validationRules.max( props.value, props.fieldConfig.number_max ),
			condition: true,
		},
		{
			rule: validationRules.min( props.value, props.fieldConfig.number_min ),
			condition: true,
		},
	] );*/

	// noinspection JSUnresolvedVariable
	return (
		<BaseInput
			type={ '1' === props.fieldConfig.number_html5 ? 'number' : 'text' }
			//min={props.fieldConfig.number_min} Enable this only for slider
			//max={props.fieldConfig.number_max} Enable this only for slider
			{ ...props }
		/>
	);
};

export default NumberField;
