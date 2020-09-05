import React from 'react';
import BaseInput from 'dfv/src/fields/base-input';

const Text = ( props ) => {
	return (
		<BaseInput
			type="text"
			{ ...props }
		/>
	);
};

export default Text;
