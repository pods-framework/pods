/* eslint-disable react/prop-types */
import React from 'react';
import { STORE_KEY_EDIT_POD, uiConstants } from 'pods-dfv/src/admin/edit-pod/store/constants';

/* WordPress dependencies */
// noinspection JSUnresolvedVariable
const { __ } = wp.i18n;
const { withSelect } = wp.data;

export const SaveStatusMessage = withSelect( ( select ) => {
	return {
		saveStatus: select( STORE_KEY_EDIT_POD ).getSaveStatus()
	};
} )
( ( props ) => {
	switch ( props.saveStatus ) {
		case uiConstants.saveStatuses.SAVING:
			return (
				<div id="message" className="notice notice-warning">
					<p><b>{__( 'Saving Pod...', 'pods' )}</b></p>
				</div>
			);

		case uiConstants.saveStatuses.SAVE_SUCCESS:
			return (
				<div id="message" className="updated fade">
					<p>
						<strong>{__( 'Success!', 'pods' )}</strong>
						{'\u00A0' /* &nbsp; */}
						{__( 'Pod saved successfully.', 'pods' )}
					</p>
				</div>
			);

		case uiConstants.saveStatuses.SAVE_ERROR:
			return (
				<div id="message" className="notice error">
					<p><b>{__( 'Save Error', 'pods' )}</b></p>
				</div>
			);

		default:
			return null;
	}
} );
