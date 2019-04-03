import React from 'react';
import { PodsDFVBaseInput } from 'pods-dfv/src/components/base-input';

export const PodsDFVNumber = ( props ) => {
	return (
		<PodsDFVBaseInput
			type="number"
			min={props.fieldConfig.number_min}
			max={props.fieldConfig.number_max}
			{...props}
		/>
	);
};
