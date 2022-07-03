import React from 'react';
import HelpTooltip from 'dfv/src/components/help-tooltip';
import HeadingTag from './heading-tag';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './heading.scss';

const Heading = ( props ) => {
	const {
		fieldConfig: {
			heading_tag: headingTag = 'h3',
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
		<HeadingTag type={ headingTag } className={ `pods-form-ui-heading pods-form-ui-heading-${ name }` }>
			{ label }
			{ shouldShowHelpText && (
				<HelpTooltip
					helpText={ helpTextString }
					helpLink={ helpLink }
				/> ) }
		</HeadingTag>
	);
};

Heading.propTypes = FIELD_COMPONENT_BASE_PROPS;

export default Heading;
