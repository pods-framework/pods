/* eslint-disable react/prop-types */
import React from 'react';

export const PodsDFVParagraph = ( props ) => {

	// Default implementation if onChange is omitted from props
	function handleChange ( event ) {
		props.setValue( event.target.value );
	}

	return (
		<textarea
			value={ props.value }
			name={ props.htmlAttr.name }
			id={ props.htmlAttr.id }
			className={ props.className }
			data-name-clean={ props.htmlAttr.name_clean }
			data-label={ props.fieldConfig.label }
			onChange={ props.onChange || handleChange }
			onBlur={ props.onBlur }
		>
			{ props.value }
		</textarea>
	);
};
