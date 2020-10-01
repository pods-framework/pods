import React from 'react';

const PodsDFVValidationMessage = ( { message } ) => (
	<div className="notice notice-error">{ message }</div>
);

const PodsDFVValidationMessages = ( { messages } ) => {
	return messages.map( ( thisMessage ) => (
		<PodsDFVValidationMessage
			key={ thisMessage }
			message={ thisMessage }
		/>
	) );
};

export default PodsDFVValidationMessages;
