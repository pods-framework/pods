import React from 'react';
import { PodsDFVBaseInput } from 'pods-dfv/src/components/base-input';
import { validationRules } from 'pods-dfv/src/validation/validation-rules';

export const PodsDFVEmail = ( props ) => {
	props.validation.addRules( [
		{
			rule: validationRules.emailFormat( props.value ),
			condition: true,
		},
	] );

	return (
		<PodsDFVBaseInput
			type={props.fieldConfig.email_html5 === '1' ? 'email' : 'text'}
			{...props}
		/>
	);
};
