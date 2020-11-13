import React from 'react';
import ReactDOM from 'react-dom';
import { initStore } from 'dfv/src/admin/edit-pod/store/store';

function reactDirectRenderer( component, element, props ) {
	initStore( props );

	const FieldComponent = component;

	console.log( element, props );

	ReactDOM.render(
		<FieldComponent { ...props } />,
		element
	);
}

export default reactDirectRenderer;
