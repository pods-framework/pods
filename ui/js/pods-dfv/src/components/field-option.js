import React from 'react';
import PropTypes from 'prop-types';

// Pods dependencies
import { PodsParameterizedLabel } from 'pods-dfv/src/components/parameterized-label';

export const PodsFieldOption = ( props ) => {
	const { name, value, labelFormat, labelParam, labelParamDefault, onChange } = props;

	return (
		<div className='pods-field-option'>
			<PodsParameterizedLabel
				className={`pods-form-ui-label pods-form-ui-label-${name}`}
				htmlFor={name}
				labelFormat={labelFormat}
				labelParam={labelParam}
				labelParamDefault={labelParamDefault}
			/>
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
	labelFormat: PropTypes.string.isRequired,
	labelParam: PropTypes.string,
	labelParamDefault: PropTypes.string,
	onChange: PropTypes.func.isRequired,
	// Todo: implement default with controlled inputs, can't just use defaultValue
	default: PropTypes.any,
};
