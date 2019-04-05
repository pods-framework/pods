import React from 'react';
import { PodsDFVBaseInput } from 'pods-dfv/src/components/base-input';
import { validationRules } from 'pods-dfv/src/validation/validation-rules';

export const PodsDFVNumber = ( props ) => {
	props.validation.addRules( [
		{
			rule: validationRules.max(props.value, props.fieldConfig.number_max ),
			condition: true,
		},
		{
			rule: validationRules.min(props.value, props.fieldConfig.number_min ),
			condition: true,
		},
	] );

	return (
		<PodsDFVBaseInput
			type={props.fieldConfig.number_html5 === '1' ? 'number' : 'text'}
			min={props.fieldConfig.number_min}
			max={props.fieldConfig.number_max}
			{...props}
		/>
	);
};
