import React from 'react';

import { __ } from '@wordpress/i18n';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

import { STORE_KEY_EDIT_POD, uiConstants } from 'pods-dfv/src/admin/edit-pod/store/constants';

export const SaveStatusMessage = ( { saveStatus, saveMessage } ) => {
	switch ( saveStatus ) {
		case uiConstants.saveStatuses.SAVING:
			return (
				<div id="message" className="notice notice-warning">
					<p><b>{ __( 'Saving Pod…', 'pods' ) }</b></p>
				</div>
			);

		case uiConstants.saveStatuses.SAVE_SUCCESS:
			return (
				<div id="message" className="updated fade">
					<p>
						<strong>{ __( 'Success!', 'pods' ) }</strong>
						{ '\u00A0' /* &nbsp; */ }
						{ __( 'Pod saved successfully.', 'pods' ) }
					</p>
				</div>
			);

		case uiConstants.saveStatuses.SAVE_ERROR:
			return (
				<div id="message" className="notice error">
					<p><b>{ !! saveMessage ? saveMessage : __( 'Save Error', 'pods' ) }</b></p>
				</div>
			);

		default:
			return null;
	}
};

export default compose( [
	withSelect( ( select ) => {
		return {
			saveStatus: select( STORE_KEY_EDIT_POD ).getSaveStatus(),
			saveMessage: select( STORE_KEY_EDIT_POD ).getSaveMessage(),
		};
	} ),
] )( SaveStatusMessage );
