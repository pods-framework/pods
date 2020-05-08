import React from 'react';
import ReactDOM from 'react-dom';
import { PodsDFVFieldContainer } from 'pods-dfv/src/components/field-container';

export function reactRenderer ( FieldClass, element, props ) {
	const Field = React.createFactory( FieldClass );

	ReactDOM.render(
		<PodsDFVFieldContainer fieldComponent={ Field } { ...props } />,
		element
	);
}
