import React from 'react';
import ReactDOM from 'react-dom';

export function reactDirectRenderer ( FieldClass, element, props ) {
	const Field = React.createFactory( FieldClass );

	ReactDOM.render(
		Field( props ),
		element
	);
}
