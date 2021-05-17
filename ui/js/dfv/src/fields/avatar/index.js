import React from 'react';

import File from '../file';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

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

Avatar.propTypes = FIELD_COMPONENT_BASE_PROPS;

export default Avatar;
