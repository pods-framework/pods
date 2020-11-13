import React from 'react';
import { omit } from 'lodash';
import ReactDOM from 'react-dom';

function reactRenderer( component, element, props ) {
	const FieldComponent = component;

	// eslint-disable-next-line no-console
	console.log( element, props );

	const fieldProps = {
		...props.data,
		fieldConfig: omit(
			props.data?.fieldConfig || {},
			[ '_field_object', 'output_options', 'item_id' ]
		),
	};

	ReactDOM.render(
		<FieldComponent
			{ ...fieldProps }
		/>,
		element
	);
}

export default reactRenderer;
