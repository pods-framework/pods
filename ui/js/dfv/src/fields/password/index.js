import React from 'react';
import BaseInput from 'dfv/src/fields/base-input';

// @todo this is an incomplete field component
// @todo add tests?
const Password = ( props ) => {
	return (
		<BaseInput
			type="password"
			{ ...props }
		/>
	);
};

export default Password;
