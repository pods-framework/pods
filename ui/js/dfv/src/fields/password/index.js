import React from 'react';
import BaseInput from 'dfv/src/fields/base-input';

const Password = ( props ) => {
	return (
		<BaseInput
			type="password"
			{ ...props }
		/>
	);
};

export default Password;
