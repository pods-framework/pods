import React from 'react';
import HelpTooltip from 'dfv/src/components/help-tooltip';

const Heading = ( props ) => {
	const shouldShowHelpText = props.helpText && ( 'help' !== props.helpText );

	// It's possible to get an array of strings for the help text, but it
	// will usually be a string.
	const helpTextString = Array.isArray( props.helpText ) ? props.helpText.join( '\n' ) : props.helpText;

	return (
		<h3 className={ `pods-form-ui-heading pods-form-ui-heading-${ props.name }` }>
			{ props.label }
			{ shouldShowHelpText && ( <HelpTooltip helpText={ helpTextString } /> ) }
		</h3>
	);
};

export default Heading;
