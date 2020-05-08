/* eslint-disable react/prop-types */
import React from 'react';

export const PodsDFVValidationMessage = ( props ) => {
	return <div className="notice notice-error">{ props.message }</div>;
};

export const PodsDFVValidationMessages = ( props ) => {
	return props.messages.map( ( thisMessage ) => {
		return (
			<PodsDFVValidationMessage
				key={ thisMessage }
				message={ thisMessage }
			/>
		);
	} );
};
