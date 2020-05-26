import React from 'react';
import ReactDOM from 'react-dom';
import PodsDFVFieldContainer from 'dfv/src/components/field-container';

function reactRenderer( FieldClass, element, props ) {
	const Field = React.createFactory( FieldClass );

	ReactDOM.render(
		<PodsDFVFieldContainer
			fieldComponent={ Field }
			{ ...props }
		/>,
		element
	);
}

export default reactRenderer;
