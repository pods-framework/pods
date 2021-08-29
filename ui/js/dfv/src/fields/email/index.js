import React, { useEffect } from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';
import { emailValidator } from 'dfv/src/helpers/validators';
import { toBool } from 'dfv/src/helpers/booleans';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const Email = ( props ) => {
	const {
		addValidationRules,
		fieldConfig = {},
		value,
	} = props;

	const {
		email_max_length: maxLength,
		email_placeholder: placeholder,
		email_html5: html5,
	} = fieldConfig;

	useEffect( () => {
		const emailValidationRule = {
			rule: emailValidator(),
			condition: () => true,
		};

		addValidationRules( [ emailValidationRule ] );
	}, [] );

	return (
		<BaseInput
			{ ...props }
			fieldConfig={ fieldConfig }
			type={ true === toBool( html5 ) ? 'email' : 'text' }
			value={ value || '' }
			maxLength={ maxLength ? parseInt( maxLength, 10 ) : undefined }
			placeholder={ placeholder }
		/>
	);
};

Email.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Email;
