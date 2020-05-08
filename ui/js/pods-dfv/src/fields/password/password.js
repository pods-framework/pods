import React from 'react';
import { PodsDFVBaseInput } from 'pods-dfv/src/components/base-input';

export const PodsDFVPassword = ( props ) => {

	return (
		<PodsDFVBaseInput
			type="password"
			{...props}
		/>
	);
};
