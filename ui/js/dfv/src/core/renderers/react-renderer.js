import React from 'react';
import ReactDOM from 'react-dom';
import FieldContainer from 'dfv/src/components/field-container';

function reactRenderer( component, element, props ) {
	const FieldComponent = component;

	const Field = React.createFactory( FieldComponent );

	// eslint-disable-next-line no-console
	console.log( 'reactRenderer', FieldComponent, element, props );

	ReactDOM.render(
		<FieldContainer
			fieldComponent={ Field }
			{ ...props.data }
		/>,
		element
	);
}

export default reactRenderer;
