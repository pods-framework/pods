import React from 'react';
import PropTypes from 'prop-types';

import { Notice } from '@wordpress/components';

const ValidationMessages = ( { messages } ) => {
	if ( ! messages.length ) {
		return null;
	}

	return (
		<div className="pods-validation-messages">
			{ messages.map( ( message ) => (
				<Notice
					key="message"
					status="error"
					isDismissible={ false }
					politeness="polite"
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
