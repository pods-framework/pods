import React from 'react';
import * as PropTypes from 'prop-types';

import { Tooltip, Dashicon } from '@wordpress/components';

const HelpTooltip = ( { helpText } ) => (
	<Tooltip
		text={ helpText }
		position="right"
	>
		<div
			style={ {
				display: 'inline-block',
				verticalAlign: 'bottom',
			} }
		>
			<Dashicon icon="editor-help" />
		</div>
	</Tooltip>
);

HelpTooltip.propTypes = {
	helpText: PropTypes.string.isRequired,
};

export default HelpTooltip;
