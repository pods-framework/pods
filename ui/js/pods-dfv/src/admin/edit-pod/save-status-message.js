/* eslint-disable react/prop-types */
import React from 'react';
import { STORE_KEY, saveStatuses } from 'pods-dfv/src/admin/edit-pod/store/constants';

/* WordPress dependencies */
// noinspection JSUnresolvedVariable
const { __ } = wp.i18n;
const { withSelect } = wp.data;

export const SaveStatusMessage = withSelect( ( select ) => {
	return {
		saveStatus: select( STORE_KEY ).getSaveStatus()
	};
} )
( ( props ) => {
	switch ( props.saveStatus ) {
		case saveStatuses.SAVING:
			return (
				<div id="message" className="notice notice-warning">
					<p><b>{__( 'Saving Pod...', 'pods' )}</b></p>
				</div>
			);

		case saveStatuses.SAVE_SUCCESS:
			return (
				<div id="message" className="updated fade">
					<p>
						<strong>{__( 'Success!', 'pods' )}</strong>
						{'\u00A0' /* &nbsp; */}
						{__( 'Pod saved successfully.', 'pods' )}
					</p>
				</div>
			);

		case saveStatuses.SAVE_ERROR:
			return (
				<div id="message" className="notice error">
					<p><b>{__( 'Save Error', 'pods' )}</b></p>
				</div>
			);

		default:
			return null;
	}
} );
