/* eslint-disable react/prop-types */
import React from 'react';

import apiFetch from '@wordpress/api-fetch';
import { withSelect, withDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';

import {
	STORE_KEY_EDIT_POD,
	uiConstants,
} from 'pods-dfv/src/admin/edit-pod/store/constants';

const {
	saveStatuses: SAVE_STATUSES,
	deleteStatuses: DELETE_STATUSES,
} = uiConstants;

// Helper functions
const savePod = async ( podID, podName, options, groups, fields, setSaveStatus, setOptionsValues ) => {
	const optionKeys = Object.keys( options );

	const data = {
		name: podName,
		label: options.label || '',
		args: {
			...options,
			// @todo Re-enable `fields` and `groups` once Scott updates
			// the API endpoint to accept fields and groups:
			// groups,
			// fields,
		},
	};

	// The label doesn't need to be repeated in 'args'.
	if ( data.args.label ) {
		delete data.args.label;
	}

	try {
		const result = await apiFetch(
			{
				path: `/pods/v1/pods/${ podID }`,
				method: 'post',
				parse: true,
				body: JSON.stringify( data ),
			}
		);

		// Re-update our options in case any of them changed server-side.
		const updatedOptions = {};
		optionKeys.forEach( ( key ) => {
			updatedOptions[ key ] = result.pod[ key ] || null;
		} );

		setOptionsValues( updatedOptions );

		setSaveStatus( SAVE_STATUSES.SAVE_SUCCESS );
	} catch ( error ) {
		setSaveStatus( SAVE_STATUSES.SAVE_ERROR, error );
	}
};

const deletePod = async ( podID, setDeleteStatus ) => {
	try {
		await apiFetch(
			{
				path: `/pods/v1/pods/${ podID }`,
				method: 'delete',
				parse: true,
			}
		);

		setDeleteStatus( DELETE_STATUSES.DELETE_SUCCESS );
	} catch ( error ) {
		setDeleteStatus( DELETE_STATUSES.DELETE_ERROR );
	}
};

// Helper components
const Spinner = () => (
	<img src="/wp-admin/images/wpspin_light.gif" alt="" />
);

export const Postbox = ( {
	podID,
	podName,
	options,
	groups,
	fields,
	saveStatus,
	deleteStatus,
	setSaveStatus,
	setDeleteStatus,
	setOptionsValues,
} ) => {
	const isSaving = saveStatus === SAVE_STATUSES.SAVING;

	useEffect( () => {
		// Try to delete the Pod if the status is set to DELETING.
		if ( saveStatus === SAVE_STATUSES.SAVING ) {
			savePod( podID, podName, options, groups, fields, setSaveStatus, setOptionsValues );
		}

		// Try to delete the Pod if the status is set to DELETING.
		if ( deleteStatus === DELETE_STATUSES.DELETING ) {
			deletePod( podID, setDeleteStatus );
		}

		// Redirect if the Pod has successfully been deleted.
		if ( deleteStatus === DELETE_STATUSES.DELETE_SUCCESS ) {
			window.location.replace( '/wp-admin/admin.php?page=pods&deleted=1' );
		}
	}, [ podID, deleteStatus, saveStatus, podName, options, groups, fields ] );

	return (
		<div id="postbox-container-1" className="postbox-container pods_floatmenu">
			<div id="side-info-field" className="inner-sidebar">
				<div id="side-sortables">
					<div id="submitdiv" className="postbox pods-no-toggle">
						<h3>
							<span>
								{ __( 'Manage', 'pods' ) }
								{ '\u00A0' /* &nbsp; */ }
								<small>
									(<a href="/wp-admin/admin.php?page=pods&amp;action=manage">
										{ __( '« Back to Manage', 'pods' ) }
									</a>)
								</small>
							</span>
						</h3>
						<div className="inside">
							<div className="submitbox" id="submitpost">
								<div id="major-publishing-actions">
									<div id="delete-action">
										<button
											onClick={ () => {
												const confirm = window.confirm(
													__( 'Are you sure you want to delete this Pod? All fields and data will be removed.', 'pods' )
												);

												if ( confirm ) {
													setDeleteStatus( DELETE_STATUSES.DELETING );
												}
											} }
											className="components-button editor-post-trash is-link"
										>
											{ __( 'Delete Pod', 'pods' ) }
										</button>
									</div>
									<div id="publishing-action">
										{ isSaving && <Spinner /> }
										{ '\u00A0' /* &nbsp; */ }
										<button
											className="button-primary"
											type="submit"
											disabled={ isSaving }
											onClick={ () => {
												setSaveStatus( SAVE_STATUSES.SAVING );
											} }
										>
											{ __( 'Save Pod', 'pods' ) }
										</button>
									</div>
									<div className="clear"></div>
								</div>
							</div>
						</div>
					</div>
					<div className="pods-submittable-fields">
						<div id="side-sortables" className="meta-box-sortables"></div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		// Reduce 'options' down to key/values.
		const allOptionData = storeSelect.getOptions();
		const optionEntries = Object.entries( allOptionData );
		const options = {};

		optionEntries.forEach( ( [ key, value ] ) => {
			options[ key ] = value.value || null;
		} );

		// Reduce groups to their IDs.
		const groups = storeSelect.getGroups().map( ( group ) => group.name );

		// Reduce fields to their IDs grouped by Group.
		const fields = {};

		storeSelect.getGroups().forEach( ( group ) => {
			fields[ group.name ] = group.fields.map( ( field ) => field.id );
		} );

		return {
			saveStatus: storeSelect.getSaveStatus(),
			deleteStatus: storeSelect.getDeleteStatus(),
			podID: storeSelect.getPodID(),
			podName: storeSelect.getPodName(),
			options,
			groups,
			fields,
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			setDeleteStatus: storeDispatch.setDeleteStatus,
			setSaveStatus: storeDispatch.setSaveStatus,
			setOptionsValues: storeDispatch.setOptionsValues,
		};
	} ),
] )( Postbox );
