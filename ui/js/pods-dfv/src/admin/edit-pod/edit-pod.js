/* eslint-disable react/prop-types */
import React from 'react';

/* WordPress dependencies */
const { withSelect, withDispatch } = wp.data;
const { compose } = wp.compose;

import { handleSubmit } from 'pods-dfv/src/admin/edit-pod/handle-submit';
import { SaveStatusMessage } from 'pods-dfv/src/admin/edit-pod/save-status-message';
import { EditPodName } from 'pods-dfv/src/admin/edit-pod/edit-pod-name';
import { MainTabs } from 'pods-dfv/src/admin/edit-pod/main-tabs/main-tabs';
import { ActiveTabContent } from 'pods-dfv/src/admin/edit-pod/main-tabs/active-tab-content';
import { Postbox } from 'pods-dfv/src/admin/edit-pod/postbox';
import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';

export const PodsDFVEditPod = compose ( [
	withSelect( ( select ) => {
		return {
			state: select( STORE_KEY_EDIT_POD ).getState()
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setSaveStatus: dispatch( STORE_KEY_EDIT_POD ).setSaveStatus
		};
	} )
] )
( ( props ) => {
	return (
		<form
			className='pods-submittable pods-nav-tabbed'
			onSubmit={( e ) => handleSubmit( e, props )}>
			<div className='pods-submittable-fields'>
				<EditPodName />
				<SaveStatusMessage />
				<MainTabs />
			</div>
			<div id='poststuff'>
				<div id='post-body' className='meta-box-holder columns-2'>
					<ActiveTabContent />
					<Postbox />
				</div>
			</div>
		</form>
	);
} );
