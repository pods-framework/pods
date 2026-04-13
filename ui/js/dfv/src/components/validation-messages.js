import React from 'react';
import PropTypes from 'prop-types';

import { Notice } from '@wordpress/components';

const ValidationMessages = ( { messages } ) => {
	if ( ! messages.length ) {
		return null;
	}

	return (
		<div className="pods-validation-messages" data-testid="validation-messages">
			{ messages.map( ( message, index ) => (
				<Notice
					key={ `message-${ index }` }
					status="error"
					isDismissible={ false }
					politeness="polite"
					data-testid="validation-message"
				>
					{ message }
				</Notice>
			) ) }
		</div>
	);
};

ValidationMessages.propTypes = {
	messages: PropTypes.arrayOf( PropTypes.string ).isRequired,
};

export default ValidationMessages;
