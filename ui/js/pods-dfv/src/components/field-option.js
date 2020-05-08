import React from 'react';
import * as PropTypes from 'prop-types';

import { HelpTooltip } from 'pods-dfv/src/components/help-tooltip';

export const PodsFieldOption = ( props ) => {
	const { fieldType, name, value, label, onChange, helpText } = props;

	const toBool = ( stringOrNumber ) => {
		// Force any strings to numeric first
		return !!+stringOrNumber;
	};

	return (
		<div className="pods-field-option">
			<label
				className={ `pods-form-ui-label pods-form-ui-label-${ name }` }
				htmlFor={ name }
			>
				{ label }
				{ helpText && 'help' !== helpText && (
					<HelpTooltip helpText={ helpText } />
				) }
			</label>
			{ 'boolean' === fieldType ? (
				<input
					type="checkbox"
					id={ name }
					name={ name }
					checked={ toBool( value ) }
					onChange={ onChange }
				/>
			) : (
				<input
					type="text"
					id={ name }
					name={ name }
					value={ value }
					onChange={ onChange }
				/>
			) }
		</div>
	);
};

PodsFieldOption.propTypes = {
	fieldType: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	label: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	helpText: PropTypes.any,
};
