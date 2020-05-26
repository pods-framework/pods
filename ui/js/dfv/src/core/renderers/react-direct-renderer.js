import React from 'react';
import ReactDOM from 'react-dom';
import { initStore } from 'dfv/src/admin/edit-pod/store/store';

function reactDirectRenderer( FieldClass, element, props ) {
	const Field = React.createFactory( FieldClass );

	// Todo:
	//
	// Need a generic way to initialize the stores.  This can't be inside
	// the component or it will revert to initial values on every render.
	// Kludged here for now to continue prototyping.
	//
	initStore( props );

	ReactDOM.render(
		Field( props ),
		element
	);
}

export default reactDirectRenderer;
