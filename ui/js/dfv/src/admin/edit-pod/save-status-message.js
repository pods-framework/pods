import React from 'react';

import { __ } from '@wordpress/i18n';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

import { SAVE_STATUSES } from 'dfv/src/store/constants';

export const SaveStatusMessage = ( { saveStatus, saveMessage } ) => {
	switch ( saveStatus ) {
		case SAVE_STATUSES.SAVING:
			return (
				<div id="message" className="notice notice-warning">
					<p><b>{ __( 'Saving Pod…', 'pods' ) }</b></p>
				</div>
			);

		case SAVE_STATUSES.SAVE_SUCCESS:
			return (
				<div id="message" className="updated fade">
					<p>
						<strong>{ __( 'Success!', 'pods' ) }</strong>
						{ '\u00A0' /* &nbsp; */ }
						{ __( 'Pod saved successfully.', 'pods' ) }
					</p>
				</div>
			);

		case SAVE_STATUSES.SAVE_ERROR:
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
	withSelect( ( select, ownProps ) => {
		const { storeKey } = ownProps;

		return {
			saveStatus: select( storeKey ).getSaveStatus(),
			saveMessage: select( storeKey ).getSaveMessage(),
		};
	} ),
] )( SaveStatusMessage );
