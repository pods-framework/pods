import React from 'react';
import sanitizeHtml from 'sanitize-html';

import { removep } from '@wordpress/autop';

import { richTextNoLinks } from '../../../../blocks/src/config/html';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

export const HTMLField = ( { fieldConfig } ) => {
	const {
		name,
		html_content: content,
	} = fieldConfig;

	return (
		<div
			className={ `pods-form-ui-html pods-form-ui-html-${ name }` }
			dangerouslySetInnerHTML={ {
				__html: removep( sanitizeHtml( content, richTextNoLinks ) ),
			} }
		>
		</div>
	);
};

HTMLField.propTypes = FIELD_COMPONENT_BASE_PROPS;

export default HTMLField;
