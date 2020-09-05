import React from 'react';
import ReactDOM from 'react-dom';
import { initStore } from 'dfv/src/admin/edit-pod/store/store';

function reactDirectRenderer( component, element, props ) {
	// Todo:
	//
	// Need a generic way to initialize the stores.  This can't be inside
	// the component or it will revert to initial values on every render.
	// Kludged here for now to continue prototyping.
	//
	initStore( props );

	const FieldComponent = component;

	ReactDOM.render(
		<FieldComponent { ...props } />,
		element
	);
}

export default reactDirectRenderer;
