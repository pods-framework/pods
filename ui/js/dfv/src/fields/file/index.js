/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * Pods components
 */
import FileFull from './file-full';
import FileReadOnly from './file-read-only';

/**
 * Other Pods dependencies
 */
import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const File = ( props ) => {
	const { fieldConfig } = props;

	const {
		read_only: readOnly,
	} = fieldConfig;

	// The read-only version of the field is a full React component,
	// which will eventually be used to build out the React version of the
	// File field. If its not set to read-only, then we use the old Marionette
	// version of the field.
	if ( toBool( readOnly ) ) {
		return <FileReadOnly { ...props } />;
	}

	return (
		<FileFull { ...props } />
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
