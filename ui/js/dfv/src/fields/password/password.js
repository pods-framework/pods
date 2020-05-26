import React from 'react';
import { PodsDFVBaseInput } from 'dfv/src/components/base-input';

export const PodsDFVPassword = ( props ) => {

	return (
		<PodsDFVBaseInput
			type="password"
			{...props}
		/>
	);
};
