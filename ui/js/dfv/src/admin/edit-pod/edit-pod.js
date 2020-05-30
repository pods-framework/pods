import React from 'react';

// WordPress dependencies
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';

// Pods dependencies
import withDragDropContext from './with-drag-drop-context';
import { STORE_KEY_EDIT_POD } from './store/constants';
import SaveStatusMessage from './save-status-message';
import EditPodName from './edit-pod-name';
import PodsNavTab from 'dfv/src/components/pods-nav-tab';
import ActiveTabContent from './main-tabs/active-tab-content';
import Postbox from './postbox';

const EditPod = ( {
	tabs,
	activeTab,
	setActiveTab,
	podName,
	setPodName,
} ) => {
	return (
		<>
			<div className="pods-edit-pod-header">
				<EditPodName
					podName={ podName }
					setPodName={ setPodName }
				/>
				<SaveStatusMessage />
				<PodsNavTab
					tabs={ tabs }
					activeTab={ activeTab }
					setActiveTab={ setActiveTab }
				/>
			</div>
			<div id="poststuff">
				<div id="post-body" className="columns-2">
					<ActiveTabContent />
					<Postbox />
					<br className="clear" />
				</div>
			</div>
		</>
	);
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		return {
			tabs: storeSelect.getGlobalPodGroups(),
			activeTab: storeSelect.getActiveTab(),
			podName: storeSelect.getPodName(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			setActiveTab: storeDispatch.setActiveTab,
			setPodName: storeDispatch.setPodName,
		};
	} ),
	withDragDropContext,
] )( EditPod );
