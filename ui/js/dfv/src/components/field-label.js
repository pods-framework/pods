import React from 'react';
import PropTypes from 'prop-types';

import sanitizeHtml from 'sanitize-html';
import { removep } from '@wordpress/autop';

import HelpTooltip from 'dfv/src/components/help-tooltip';

import { richTextNoLinks } from '../../../blocks/src/config/html';

import './field-label.scss';

const FieldLabel = ( {
	label,
	required,
	htmlFor,
	helpTextString,
	helpLink,
} ) => (
	<div className={ `pods-field-label pods-field-label-${ name }` }>
		<label
			className="pods-field-label__label"
			htmlFor={ htmlFor }
		>
			<span
				dangerouslySetInnerHTML={ {
					__html: removep( sanitizeHtml( label, richTextNoLinks ) ),
				} }
			/>
			{ required && ( <span className="pods-field-label__required">{ '\u00A0' /* &nbsp; */ }*</span> ) }
		</label>

		{ ( helpTextString && helpLink ) && (
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

FieldLabel.defaultProps = {
	required: false,
	helpTextString: null,
	helpLink: null,
};

FieldLabel.propTypes = {
	label: PropTypes.string.isRequired,
	htmlFor: PropTypes.string.isRequired,
	helpTextString: PropTypes.string,
	helpLink: PropTypes.string,
};

export default FieldLabel;
