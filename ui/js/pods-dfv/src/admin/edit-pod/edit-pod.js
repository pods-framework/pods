import React from 'react';

// WordPress dependencies
const { withSelect, withDispatch } = wp.data;
const { compose } = wp.compose;

// Pods dependencies
import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';
import { handleSubmit } from 'pods-dfv/src/admin/edit-pod/handle-submit';
import { SaveStatusMessage } from 'pods-dfv/src/admin/edit-pod/save-status-message';
import { EditPodName } from 'pods-dfv/src/admin/edit-pod/edit-pod-name';
import { PodsNavTab } from 'pods-dfv/src/components/tabs/pods-nav-tab';
import { ActiveTabContent } from 'pods-dfv/src/admin/edit-pod/main-tabs/active-tab-content';
import { Postbox } from 'pods-dfv/src/admin/edit-pod/postbox';

export const PodsDFVEditPod = compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );
		return {
			state: storeSelect.getState(),
			tabs: storeSelect.getTabs(),
			activeTab: storeSelect.getActiveTab()
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );
		return {
			setSaveStatus: storeDispatch.setSaveStatus,
			setActiveTab: storeDispatch.setActiveTab,
		};
	} )
] )
( ( props ) => {
	//--! Todo: debugging only
	window.select = wp.data.select( 'pods/edit-pod' );
	window.dispatch = wp.data.dispatch( 'pods/edit-pod' );

	return (
		<form
			onSubmit={( e ) => handleSubmit( e, props )}>
			<div>
				<EditPodName />
				<SaveStatusMessage />
				<PodsNavTab
					tabs={props.tabs}
					activeTab={props.activeTab}
					setActiveTab={props.setActiveTab}
				/>
			</div>
			<div id='poststuff'>
				<div id='post-body' className='columns-2'>
					<ActiveTabContent />
					<Postbox />
				</div>
			</div>
		</form>
	);
} );
