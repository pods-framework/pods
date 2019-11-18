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
import './style.pcss';

const CheckboxInput = ( {
	checked,
	className,
	onChange,
	...rest
} ) => (
	<Input
		checked={ checked }
		className={ classNames( 'tribe-editor__input--checkbox', className ) }
		onChange={ onChange }
		type="checkbox"
		{ ...rest }
	/>
);

CheckboxInput.propTypes = {
	checked: PropTypes.bool,
	className: PropTypes.string,
	onChange: PropTypes.func,
};

export default CheckboxInput;
