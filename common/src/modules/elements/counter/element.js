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

const Counter = ( {
	className,
	count,
	label,
} ) => (
	<div className={ classNames(
		'tribe-editor__counter',
		className,
	) }>
		<span className="tribe-editor__counter__count">
			{ count }
		</span>
		<span className="tribe-editor__counter__label">
			{ label }
		</span>
	</div>
);

Counter.propTypes = {
	className: PropTypes.string,
	count: PropTypes.number,
	label: PropTypes.string,
};

export default Counter;
