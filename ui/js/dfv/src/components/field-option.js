import React from 'react';
import * as PropTypes from 'prop-types';
import sanitizeHtml from 'sanitize-html';

import { removep } from '@wordpress/autop';

import HelpTooltip from 'dfv/src/components/help-tooltip';
import { richText } from '../../../blocks/src/config/html';

const PodsFieldOption = ( {
	fieldType,
	name,
	value,
	label,
	onChange,
	helpText,
	description,
} ) => {
	const toBool = ( stringOrNumber ) => {
		// Force any strings to numeric first
		return !! ( +stringOrNumber );
	};

	const shouldShowHelpText = helpText && ( 'help' !== helpText );

	return (
		<div className="pods-field-option">
			<label
				className={ `pods-form-ui-label pods-form-ui-label-${ name }` }
				htmlFor={ name }>
				{ label }
				{ shouldShowHelpText && ( <HelpTooltip helpText={ helpText } /> ) }
			</label>
			{ 'boolean' === fieldType ? (
				<input
					type="checkbox"
					id={ name }
					name={ name }
					checked={ toBool( value ) }
					onChange={ onChange }
					aria-label={ shouldShowHelpText && helpText }
				/>
			) : (
				<input
					type="text"
					id={ name }
					name={ name }
					value={ value }
					onChange={ onChange }
					aria-label={ shouldShowHelpText && helpText }
				/>
			)
			}
			{ !! description && (
				<p
					className="description"
					dangerouslySetInnerHTML={ removep( sanitizeHtml( description, richText ) ) }
				/>
			) }
		</div>
	);
};

PodsFieldOption.propTypes = {
	description: PropTypes.string,
	fieldType: PropTypes.string.isRequired,
	helpText: PropTypes.string,
	label: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	value: PropTypes.any.isRequired,
};

export default PodsFieldOption;
