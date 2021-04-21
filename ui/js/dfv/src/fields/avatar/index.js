import React from 'react';
import PropTypes from 'prop-types';

import File from '../file';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const Avatar = ( props ) => {
	const {
		fieldConfig = {},
	} = props;

	// Process the field config so that properties prefixed with "avatar_"
	// are changed to "file_".
	const fieldConfigEntries = Object.entries( fieldConfig ).map(
		( entry ) => {
			return [
				entry[ 0 ].startsWith( 'avatar_' ) ? 'file_' + entry[ 0 ].substr( 7 ) : entry[ 0 ],
				entry[ 1 ],
			];
		}
	);
	const fileFieldConfig = Object.fromEntries( fieldConfigEntries );

	return (
		<File
			{ ...props }
			fieldConfig={ fileFieldConfig }
		/>
	);
};

Avatar.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE.isRequired,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Avatar;
