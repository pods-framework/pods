import React from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';
import sanitizeSlug from 'dfv/src/helpers/sanitizeSlug';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const Slug = ( props ) => {
	const {
		fieldConfig = {},
		setValue,
	} = props;

	const {
		slug_placeholder: placeholder,
		slug_separator: separator,
	} = fieldConfig;

	// Intercept the setValue call to force the slug formatting.
	const forceSlugFormatting = ( newValue ) => {
		setValue( sanitizeSlug( newValue, separator ) );
	};

	return (
		<BaseInput
			{ ...props }
			type="text"
			placeholder={ placeholder }
			setValue={ forceSlugFormatting }
		/>
	);
};

Slug.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Slug;
