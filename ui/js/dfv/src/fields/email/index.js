import React, { useEffect } from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';
import { emailValidator } from 'dfv/src/helpers/validators';
import toBool from 'dfv/src/helpers/toBool';

const Email = ( props ) => {
	const {
		addValidationRules,
		fieldConfig = {},
	} = props;

	useEffect( () => {
		addValidationRules( [
			{
				rule: emailValidator(),
				condition: () => true,
			},
		] );
	}, [] );

	return (
		<BaseInput
			type={ true === toBool( fieldConfig.email_html5 ? 'email' : 'text' ) }
			{ ...props }
		/>
	);
};

Email.propTypes = {
	addValidationRules: PropTypes.func.isRequired,
	// @todo the shape of this object
	fieldConfig: PropTypes.object,
};

export default Email;
