/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import './style.pcss';

const Placeholder = ( { children, className } ) => (
	<div className={ classNames( 'tribe-editor__placeholder', className ) }>
		{ children }
	</div>
);

Placeholder.propTypes = {
	children: PropTypes.node.isRequired,
};

export default Placeholder;
