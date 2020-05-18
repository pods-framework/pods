import React from 'react';
import * as PropTypes from 'prop-types';

import HelpTooltip from 'pods-dfv/src/components/help-tooltip';

const PodsFieldOption = ( props ) => {
	const { fieldType, name, value, label, onChange, helpText } = props;

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
		</div>
	);
};

PodsFieldOption.propTypes = {
	fieldType: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	label: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	helpText: PropTypes.string,
};

export default PodsFieldOption;
