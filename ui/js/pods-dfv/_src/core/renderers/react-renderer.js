import React from 'react';
import ReactDOM from 'react-dom';
import { PodsDFVFieldContainer } from 'pods-dfv/_src/core/pods-dfv-field-container';

export function reactRenderer ( FieldClass, element, props ) {
	const Field = React.createFactory( FieldClass );

	ReactDOM.render(
		<PodsDFVFieldContainer
			fieldComponent={ Field }
			{ ...props }
		/>,
		element
	);
}
