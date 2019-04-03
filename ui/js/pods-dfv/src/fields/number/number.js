import React from 'react';
import { PodsDFVBaseInput } from 'pods-dfv/src/components/base-input';

export const PodsDFVNumber = ( props ) => {

	return (
		<PodsDFVBaseInput
			type="text"
			{...props}
		/>
	);
};
