import React from 'react';

export const PodsDFVHTML = ( props ) => {
	return (
		<div className={ `pods-form-ui-html pods-form-ui-html-${ props.name }` }>
			{ props.content }
		</div>
	);
};
