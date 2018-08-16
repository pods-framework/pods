import React from 'react';
import ReactDOM from 'react-dom';

export function reactRenderer ( FieldClass, element, data ) {
	const Field = React.createFactory( FieldClass );

	ReactDOM.render(
		Field( data ),
		element
	);
}
