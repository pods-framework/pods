/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * Pods components
 */
import FileList from './file-list';

/**
 * Other Pods dependencies
 */
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const File = ( props ) => {
	return (
		<FileList { ...props } />
	);
};

File.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.oneOfType( [
		PropTypes.arrayOf(
			PropTypes.oneOfType( [
				PropTypes.string,
				PropTypes.number,
			] )
		),
		PropTypes.string,
		PropTypes.number,
	] ),
};

export default File;
