import React from 'react';
import * as PropTypes from 'prop-types';

const { Tooltip, Dashicon } = wp.components;

export const HelpTooltip = ( props ) => {
	return (
		<Tooltip text={ props.helpText }>
			<span>
				<Dashicon icon="editor-help" />
			</span>
		</Tooltip>
	);
};

HelpTooltip.propTypes = {
	helpText: PropTypes.string.isRequired,
};
