import React from 'react';
import * as PropTypes from 'prop-types';

import { Tooltip, Dashicon } from '@wordpress/components';

const HelpTooltip = ( { helpText } ) => (
	<Tooltip text={ helpText }>
		<span>
			<Dashicon icon="editor-help" />
		</span>
	</Tooltip>
);

HelpTooltip.propTypes = {
	helpText: PropTypes.string.isRequired,
};

export default HelpTooltip;
