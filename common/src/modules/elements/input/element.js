/**
 * External Dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import './style.pcss';

const Input = ( {
	className,
	type,
	...rest,
} ) => (
	<input
		className={ classNames( 'tribe-editor__input', className ) }
		type={ type }
		{ ...rest }
	/>
);

Input.propTypes = {
	className: PropTypes.string,
	type: PropTypes.string.isRequired,
};

export default Input;
