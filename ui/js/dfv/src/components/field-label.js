import React from 'react';
import PropTypes from 'prop-types';

import sanitizeHtml from 'sanitize-html';
import { removep } from '@wordpress/autop';

import HelpTooltip from 'dfv/src/components/help-tooltip';

import { richTextNoLinks } from '../../../blocks/src/config/html';

import './field-label.scss';

const FieldLabel = ( {
	name = '',
	label,
	required = false,
	htmlFor,
	helpTextString = null,
	helpLink = null,
} ) => (
	<div className={ `pods-field-label pods-field-label-${ name }` }>
		<label
			className="pods-field-label__label"
			htmlFor={ htmlFor }
			data-testid="field-label"
		>
			<span
				dangerouslySetInnerHTML={ {
					__html: removep( sanitizeHtml( label, richTextNoLinks ) ),
				} }
				data-testid="field-label-text"
			/>
			{ required && ( <span className="pods-field-label__required">{ '\u00A0' /* &nbsp; */ }*</span> ) }
		</label>

		{ helpTextString && (
			<span className="pods-field-label__tooltip-wrapper">
				{ '\u00A0' /* &nbsp; */ }
				<HelpTooltip
					helpText={ helpTextString }
					helpLink={ helpLink }
				/>
			</span>
		) }
	</div>
);

FieldLabel.propTypes = {
	name: PropTypes.string,
	label: PropTypes.string.isRequired,
	htmlFor: PropTypes.string.isRequired,
	helpTextString: PropTypes.string,
	helpLink: PropTypes.string,
};

export default FieldLabel;
