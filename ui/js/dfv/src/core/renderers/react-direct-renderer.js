import React from 'react';
import ReactDOM from 'react-dom';
import { initEditPodStore } from 'dfv/src/admin/edit-pod/store/store';

function reactDirectRenderer( component, element, props ) {
	initEditPodStore( props.config || {} );

	const FieldComponent = component;

	console.log( 'react direct renderer: ', element, props );

	ReactDOM.render(
		<FieldComponent { ...props } />,
		element
	);
}

export default reactDirectRenderer;
