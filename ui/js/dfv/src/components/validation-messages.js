import React from 'react';
import PropTypes from 'prop-types';

const ValidationMessages = ( { messages } ) => {
	if ( ! messages.length ) {
		return null;
	}

	return (
		<div className="pods-validation-messages">
			{ messages.map( ( message ) => (
				<div
					className="notice notice-error"
					key={ message }
				>
					{ message }
				</div>
			) ) }
		</div>
	);
};

ValidationMessages.propTypes = {
	messages: PropTypes.arrayOf( PropTypes.string ).isRequired,
};

export default ValidationMessages;
