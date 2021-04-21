import React from 'react';
import HelpTooltip from 'dfv/src/components/help-tooltip';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const Heading = ( props ) => {
	const {
		fieldConfig: {
			helpText,
			label,
			name,
		},
	} = props;

	const shouldShowHelpText = helpText && ( 'help' !== helpText );

	// It's possible to get an array of strings for the help text, but it
	// will usually be a string.
	const helpTextString = Array.isArray( helpText ) ? helpText[ 0 ] : helpText;
	const helpLink = ( Array.isArray( helpText ) && !! helpText[ 1 ] )
		? helpText[ 1 ]
		: undefined;

	return (
		<h3 className={ `pods-form-ui-heading pods-form-ui-heading-${ name }` }>
			{ label }
			{ shouldShowHelpText && (
				<HelpTooltip
					helpText={ helpTextString }
					helpLink={ helpLink }
				/> ) }
		</h3>
	);
};

Heading.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
};

export default Heading;
