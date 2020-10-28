import React from 'react';
import sanitizeHtml from 'sanitize-html';

import { removep } from '@wordpress/autop';

import { richTextNoLinks } from '../../../../blocks/src/config/html';

export const HTMLField = ( props ) => {
	const {
		fieldConfig: {
			name,
			html_content: content,
		},
	} = props;

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

export default HTMLField;
