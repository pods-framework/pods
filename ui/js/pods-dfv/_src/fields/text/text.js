import React from 'react';

export const PodsDFVText = ( props ) => {

	function onChanged ( event ) {
		props.setValue( event.target.value );
	}

	return (
		<input
			type="text"
			name={ props.htmlAttr.name }
			id={ props.htmlAttr.id }
			className={ props.htmlAttr.class }
			data-name-clean={ props.htmlAttr.name_clean }
			placeholder={ props.fieldConfig.text_placeholder }
			maxLength={ props.fieldConfig.text_max_length }
			value={ props.value }
			onChange={ onChanged }
			readOnly={ !!props.fieldConfig.readonly }
		/>
	);
};
