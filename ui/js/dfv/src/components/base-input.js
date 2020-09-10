/* eslint-disable react/prop-types */
import React from 'react';

export const PodsDFVBaseInput = ( props ) => {
	// Default implementation if onChange is omitted from props
	function handleChange( event ) {
		props.setValue( event.target.value );
	}

	// noinspection JSUnresolvedVariable
	return (
		<input
			type={ props.type }
			name={ props.htmlAttr.name }
			id={ props.htmlAttr.id }
			className={ props.className }
			data-name-clean={ props.htmlAttr.name_clean }
			placeholder={ props.fieldConfig.text_placeholder }
			maxLength={ props.fieldConfig.text_max_length }
			value={ props.value }
			readOnly={ !! props.fieldConfig.readonly }
			onChange={ props.onChange || handleChange }
			onBlur={ props.onBlur }
			min={ props.min }
			max={ props.max }
		/>
	);
};
