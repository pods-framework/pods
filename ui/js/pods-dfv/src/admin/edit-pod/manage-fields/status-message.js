/* eslint-disable react/prop-types */
import React from 'react';
const { __ } = wp.i18n;

export const PodsDFVEditPodStatusMessage = ( props ) => {

	if ( props.isSaving ) {
		return (
			<div id="message" className="notice notice-warning">
				<p><b>{__( 'Saving Pod...', 'pods' )}</b></p>
			</div>
		);
	} else if ( props.saved ) {
		return (
			<div id="message" className="updated fade">
				<p>
					<strong>{__( 'Success!', 'pods' )}</strong>
					{'\u00A0' /* &nbsp; */}
					{__( 'Pod saved successfully.', 'pods' )}
				</p>
			</div>
		);
	}

	return null;
};
