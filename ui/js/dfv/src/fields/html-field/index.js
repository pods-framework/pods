import React from 'react';

// @todo this may be an incomplete field component
// @todo add tests?
export const HTMLField = ( props ) => {
	return (
		<div className={ `pods-form-ui-html pods-form-ui-html-${ props.name }` }>
			{ props.content }
		</div>
	);
};

export default HTMLField;
