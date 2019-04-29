import React from 'react';

// WordPress dependencies
const { withSelect, withDispatch } = wp.data;
const { compose } = wp.compose;

// Pods dependencies
import { STORE_KEY_EDIT_POD } from './store/constants';
import { handleSubmit } from './handle-submit';
import { SaveStatusMessage } from './save-status-message';
import { EditPodName } from './edit-pod-name';
import { PodsNavTab } from 'pods-dfv/src/components/tabs/pods-nav-tab';
import { ActiveTabContent } from './main-tabs/active-tab-content';
import { Postbox } from './postbox';

const StoreSubscribe = compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );
		return {
			state: storeSelect.getState(),
			tabs: storeSelect.getTabs(),
			activeTab: storeSelect.getActiveTab(),
			tabOptions: storeSelect.getTabOptions( storeSelect.getActiveTab() ),
			getOptionValue: storeSelect.getOptionValue,
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );
		return {
			setActiveTab: storeDispatch.setActiveTab,
			setOptionValue: storeDispatch.setOptionValue,
		};
	} )
] );

export const PodsDFVEditPod = StoreSubscribe( ( props ) => {
	const { activeTab, tabs, setActiveTab, tabOptions } = props;
	const { getOptionValue, setOptionValue } = props;

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
				<PodsNavTab tabs={tabs} activeTab={activeTab} setActiveTab={setActiveTab} />
			</div>
			<div id='poststuff'>
				<div id='post-body' className='columns-2'>
					<ActiveTabContent
						activeTab={activeTab}
						tabOptions={tabOptions}
						getOptionValue={getOptionValue}
						setOptionValue={setOptionValue}
					/>
					<Postbox />
				</div>
			</div>
		</form>
	);
} );
