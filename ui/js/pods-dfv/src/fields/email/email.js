/* eslint-disable react/prop-types */
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

	// noinspection JSUnresolvedVariable
	return (
		<PodsDFVBaseInput
			type={ '1' === props.fieldConfig.email_html5 ? 'email' : 'text' }
			{ ...props }
		/>
	);
};
