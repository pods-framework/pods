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

const Heading = ( { level, children, className } ) => {
	const HeadingLevel = `h${ level }`;
	const headingClassName = classNames(
		'tribe-editor__heading',
		`tribe-editor__heading--h${ level }`,
		className,
	);
	return (
		<HeadingLevel className={ headingClassName }>
			{ children }
		</HeadingLevel>
	);
};

Heading.propTypes = {
	children: PropTypes.node.isRequired,
	level: PropTypes.oneOf( [ 1, 2, 3, 4, 5, 6 ] ).isRequired,
};

export default Heading;
