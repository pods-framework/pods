/**
 * External Dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import Input from '@moderntribe/common/elements/input/element';

const NumberInput = ( {
	className,
	max,
	min,
	onChange,
	step,
	...rest
} ) => (
	<Input
		className={ classNames( 'tribe-editor__input--number', className ) }
		max={ max }
		min={ min }
		onChange={ onChange }
		step={ step }
		type="number"
		{ ...rest }
	/>
);

NumberInput.propTypes = {
	className: PropTypes.string,
	max: PropTypes.number,
	min: PropTypes.number,
	onChange: PropTypes.func,
	step: PropTypes.number,
};

export default NumberInput;
