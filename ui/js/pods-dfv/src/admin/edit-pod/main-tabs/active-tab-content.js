/* eslint-disable react/prop-types */
import React from 'react';

// WordPress dependencies
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;

// Pods dependencies
import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';
import { TabManageFields } from './tab-manage-fields';

export const ActiveTabContent = compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );
		return {
			activeTab: storeSelect.getActiveTab(),
			getOrderedTabOptions: storeSelect.getOrderedTabOptions
		};
	} ),
] )
( ( props ) => {
	const getActiveTabComponent = () => {
		if ( 'manage-fields' === props.activeTab ) {
			return ( <TabManageFields /> );
		} else {
			return (
				props.getOrderedTabOptions( props.activeTab ).map( option =>
					( <div key={option}>{`${option}`}</div> )
				)
			);
		}
	};

	return (
		<div id='post-body-content' className='pods-nav-tab-group'>
			{getActiveTabComponent()}
		</div>
	);
} );
