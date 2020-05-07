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

const { deleteStatuses: DELETE_STATUSES } = uiConstants;

const Spinner = () => (
	<img src='/wp-admin/images/wpspin_light.gif' alt='' />
);

export const Postbox = ( { podID, isSaving, deleteStatus, setDeleteStatus } ) => {
	useEffect( () => {
		const deletePod = async () => {
			try {
				await apiFetch(
					{
						path: `/pods/v1/pods/${ podID }`,
						method: 'delete',
						parse: true,
					}
				);

				setDeleteStatus(
					uiConstants.deleteStatuses.DELETE_SUCCESS,
					podID
				);
			} catch ( error ) {
				setDeleteStatus(
					uiConstants.deleteStatuses.DELETE_ERROR,
					podID
				);
			}
		};

		// Try to delete the Pod if the status is set to DELETING.
		if ( deleteStatus === DELETE_STATUSES.DELETING ) {
			deletePod();
		}

		// Redirect if the Pod has successfully been deleted.
		if ( deleteStatus === DELETE_STATUSES.DELETE_SUCCESS ) {
			window.location.replace( '/wp-admin/admin.php?page=pods&deleted=1' );
		}
	}, [ podID, deleteStatus ] );

	return (
		<div id='postbox-container-1' className='postbox-container pods_floatmenu'>
			<div id='side-info-field' className='inner-sidebar'>
				<div id='side-sortables'>
					<div id='submitdiv' className='postbox pods-no-toggle'>
						<h3>
							<span>
								{__( 'Manage', 'pods' )}
								{'\u00A0' /* &nbsp; */}
								<small>
									(<a href='/wp-admin/admin.php?page=pods&amp;action=manage'>
										{__( '« Back to Manage', 'pods' )}
									</a>)
								</small>
							</span>
						</h3>
						<div className='inside'>
							<div className='submitbox' id='submitpost'>
								<div id='major-publishing-actions'>
									<div id='delete-action'>
										<button
											onClick={ () => {
												const confirm = window.confirm(
													__( 'Are you sure you want to delete this Pod? All fields and data will be removed.', 'pods' )
												);

												if ( confirm ) {
													setDeleteStatus( DELETE_STATUSES.DELETING, podID );
												}
											} }
											className="components-button editor-post-trash is-link"
										>
											{__( 'Delete Pod', 'pods' )}
										</button>
									</div>
									<div id='publishing-action'>
										{isSaving && <Spinner />}
										{'\u00A0' /* &nbsp; */}
										<button className='button-primary' type='submit'>
											{__( 'Save Pod', 'pods' )}
										</button>
									</div>
									<div className='clear'></div>
								</div>
							</div>
						</div>
					</div>
					<div className='pods-submittable-fields'>
						<div id='side-sortables' className='meta-box-sortables'></div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		return {
			isSaving: storeSelect.isSaving(),
			deleteStatus: storeSelect.getDeleteStatus(),
			podID: storeSelect.getPodID(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			setDeleteStatus: storeDispatch.setDeleteStatus,
		};
	} )
] )(Postbox);
