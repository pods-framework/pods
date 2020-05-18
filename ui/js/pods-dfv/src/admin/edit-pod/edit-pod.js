import React from 'react';

// WordPress dependencies
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';

// Pods dependencies
import withDragDropContext from './with-drag-drop-context';
import { STORE_KEY_EDIT_POD } from './store/constants';
import SaveStatusMessage from './save-status-message';
import EditPodName from './edit-pod-name';
import PodsNavTab from 'pods-dfv/src/components/tabs/pods-nav-tab';
import ActiveTabContent from './main-tabs/active-tab-content';
import Postbox from './postbox';

const EditPod = ( {
	tabs,
	activeTab,
	setActiveTab,
} ) => {
	return (
		<div>
			<div>
				<EditPodName />
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
		</div>
	);
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		return {
			tabs: storeSelect.getGlobalGroups(),
			activeTab: storeSelect.getActiveTab(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );
		return {
			setActiveTab: storeDispatch.setActiveTab,
		};
	} ),
	withDragDropContext,
] )( EditPod );
