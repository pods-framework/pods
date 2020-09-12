import React from 'react';
import PropTypes from 'prop-types';

import MarionetteAdapter from 'dfv/src/fields/marionette-adapter';
import { File as FileView } from './file-upload';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

// @todo this may be an incomplete field component
// @todo add tests
const File = ( props ) => {
	const {
		fieldConfig: {
			data = [],
		},
		htmlAttr = {},
		setValue,
	} = props;

	return (
		<MarionetteAdapter
			View={ FileView }
			// setValue={ handleChange }
			{ ...props }
		/>
	);
};

File.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string.isRequired,
};

export default File;
