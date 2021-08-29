import React from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';
import sanitizeSlug from 'dfv/src/helpers/sanitizeSlug';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const Slug = ( props ) => {
	const {
		fieldConfig = {},
		value,
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
			value={ value || '' }
			placeholder={ placeholder }
			setValue={ forceSlugFormatting }
		/>
	);
};

Slug.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Slug;
