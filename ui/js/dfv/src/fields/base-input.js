import React from 'react';
import PropTypes from 'prop-types';

const BaseInput = ( props ) => {
	// Default implementation if onChange is omitted from props
	const handleChange = ( event ) => {
		props.setValue( event.target.value );
	};

	return (
		<input
			type={ props.type }
			name={ props.htmlAttr?.name }
			id={ props.htmlAttr?.id }
			className={ props.className }
			// eslint-disable-next-line camelcase
			data-name-clean={ props.htmlAttr?.name_clean }
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

BaseInput.propTypes = {
	className: PropTypes.string,
	fieldConfig: PropTypes.shape( {
		readonly: PropTypes.bool,
		text_placeholder: PropTypes.string,
		text_max_length: PropTypes.string,
	} ),
	htmlAttr: PropTypes.shape( {
		name: PropTypes.string,
		name_clean: PropTypes.string,
		id: PropTypes.string,
	} ),
	min: PropTypes.number,
	max: PropTypes.number,
	onBlur: PropTypes.func,
	onChange: PropTypes.func,
	type: PropTypes.string.isRequired,
	// @todo something stricter than any
	value: PropTypes.any,
};

export default BaseInput;
