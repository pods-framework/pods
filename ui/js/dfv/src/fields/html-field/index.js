import React from 'react';
import sanitizeHtml from 'sanitize-html';

import { removep, autop } from '@wordpress/autop';

import { richText } from '../../../../blocks/src/config/html';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

export const HTMLField = ( { fieldConfig } ) => {
	const {
		name,
		html_content: content,
		html_wpautop: htmlWPAutoP,
	} = fieldConfig;

	let safeHTML = sanitizeHtml( content, richText );

	if ( htmlWPAutoP ) {
		safeHTML = autop( safeHTML );
	} else {
		safeHTML = removep( safeHTML );
	}

	return (
		<div
			className={ `pods-form-ui-html pods-form-ui-html-${ name }` }
			dangerouslySetInnerHTML={ {
				__html: safeHTML,
			} }
		>
		</div>
	);
};

HTMLField.propTypes = FIELD_COMPONENT_BASE_PROPS;

export default HTMLField;
