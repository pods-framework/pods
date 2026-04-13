import React from 'react';
import PropTypes from 'prop-types';

import sanitizeHtml from 'sanitize-html';
import { removep } from '@wordpress/autop';

import { richTextNoLinks } from '../../../blocks/src/config/html';

import './field-description.scss';

const FieldDescription = ( { description } ) => (
	<p
		className="pods-field-description"
		dangerouslySetInnerHTML={ {
			__html: removep( sanitizeHtml( description, richTextNoLinks ) ),
		} }
	/>
);

FieldDescription.propTypes = {
	description: PropTypes.string.isRequired,
};

export default FieldDescription;
