import React from 'react';
import BaseInput from 'dfv/src/fields/base-input';

// @todo this may be an incomplete field component
// @todo add tests?
const Text = ( props ) => {
	return (
		<BaseInput
			type="text"
			{ ...props }
		/>
	);
};

export default Text;
