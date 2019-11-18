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

const RadioInput = ( { checked, className, onChange, ...rest } ) => (
	<Input
		checked={ checked }
		className={ classNames( 'tribe-editor__input--radio', className ) }
		onChange={ onChange }
		type="radio"
		{ ...rest }
	/>
);

RadioInput.propTypes = {
	checked: PropTypes.bool,
	className: PropTypes.string,
	onChange: PropTypes.func,
};

export default RadioInput;
