import React from 'react';

export const PodsDFVBaseInput = ( props ) => {

	// Default implementation if onChange is omitted from props
	function handleChange ( event ) {
		props.setValue( event.target.value );
	}

	return (
		<input
			type={ props.type }
			name={ props.htmlAttr.name }
			id={ props.htmlAttr.id }
			className={ props.htmlAttr.class }
			data-name-clean={ props.htmlAttr.name_clean }
			placeholder={ props.fieldConfig.text_placeholder }
			maxLength={ props.fieldConfig.text_max_length }
			value={ props.value}
			onChange={ props.onChange || handleChange }
			readOnly={ !!props.fieldConfig.readonly }
		/>
	);
};
