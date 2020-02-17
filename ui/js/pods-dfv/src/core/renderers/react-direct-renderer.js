import React from 'react';
import ReactDOM from 'react-dom';
import { initStore } from 'pods-dfv/src/admin/edit-pod/store/store';

export function reactDirectRenderer ( FieldClass, element, props ) {
	const Field = React.createFactory( FieldClass );

	// Todo:
	//
	//  1: Need a generic way to initialize the stores.  This can't be inside
	// the component or it will revert to initial values on every render.
	// Kludged here for now to continue prototyping.
	//
	// 2: initialState (in initStore) will not arrive until WP 5.2.
	//
	initStore( props );

	ReactDOM.render(
		Field( props ),
		element
	);
}
