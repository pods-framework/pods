import React from 'react';

// Pods dependencies
import withDragDropContext from './with-drag-drop-context';
import { STORE_KEY_EDIT_POD } from './store/constants';
import { handleSubmit } from './handle-submit';
import { SaveStatusMessage } from './save-status-message';
import { EditPodName } from './edit-pod-name';
import { PodsNavTab } from 'pods-dfv/src/components/tabs/pods-nav-tab';
import { ActiveTabContent } from './main-tabs/active-tab-content';
import { Postbox } from './postbox';

// WordPress dependencies
const { withSelect, withDispatch } = wp.data;
const { compose } = wp.compose;

const StoreSubscribe = compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );
		return {
			tabs: storeSelect.getTabs(),
			activeTab: storeSelect.getActiveTab(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );
		return {
			setActiveTab: storeDispatch.setActiveTab,
		};
	} )
] );

const EditPod = StoreSubscribe( ( props ) => {

//--! Todo: debugging only
	window.select = wp.data.select( 'pods/edit-pod' );
	window.dispatch = wp.data.dispatch( 'pods/edit-pod' );
//--! Todo: debugging only

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

export const PodsDFVEditPod = withDragDropContext( EditPod );
