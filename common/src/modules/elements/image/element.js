/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

const Image = ( {
	alt,
	className,
	src,
	...rest,
} ) => (
	<img
		src={ src }
		alt={ alt }
		className={ classNames( 'tribe-editor__image', className ) }
		{ ...rest }
	/>
);

Image.propTypes = {
	alt: PropTypes.string.isRequired,
	className: PropTypes.string,
	src: PropTypes.string.isRequired,
};

export default Image;
