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

const UrlInput = ( { checked, className, onChange, ...rest } ) => (
	<Input
		type="url"
		className={ classNames( 'tribe-editor__input--url', className ) }
		onChange={ onChange }
		{ ...rest }
	/>
);

UrlInput.propTypes = {
	className: PropTypes.string,
	onChange: PropTypes.func,
};

export default UrlInput;
