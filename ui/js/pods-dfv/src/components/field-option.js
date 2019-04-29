import React from 'react';
import PropTypes from 'prop-types';

export const PodsFieldOption = ( props ) => {
	const { name, value, label, onChange } = props;

	return (
		<div className='pods-field-option'>
			<label
				className={`pods-form-ui-label pods-form-ui-label-${name}`}
				htmlFor={name}>
				{label}
			</label>
			{ /* Todo: dynamic field type handling */ }
			<input
				type='text'
				id={name}
				name={name}
				value={value}
				onChange={onChange}
			/>
		</div>
	);
};

PodsFieldOption.propTypes = {
	name: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	label: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	// Todo: implement default with controlled inputs, can't just use defaultValue
	default: PropTypes.any,
};
