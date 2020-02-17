import React from 'react';

// WordPress dependencies
const { withSelect, withDispatch } = wp.data;
const { compose } = wp.compose;

// Pods dependencies
import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';
import { DynamicTabContent } from './dynamic-tab-content';
import { FieldGroups } from './field-groups';

const StoreSubscribe = compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );
		return {
			activeTab: storeSelect.getActiveTab(),
			tabOptions: storeSelect.getTabOptions( storeSelect.getActiveTab() ),
			groups: storeSelect.getGroups(),
			getOptionValue: storeSelect.getOptionValue,
			getGroupFields: storeSelect.getGroupFields,
			groupList: storeSelect.getGroupList(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );
		return {
			setOptionValue: storeDispatch.setOptionValue,
			setGroupList: storeDispatch.setGroupList,
			moveGroup: storeDispatch.moveGroup,
		};
	} )
] );

/**
 * ActiveTabContent
 *
 * Display the content for the active tab, manage-fields is treated special
 */
export const ActiveTabContent = StoreSubscribe ( ( props ) => {
	let Component;

	if ( 'manage-fields' === props.activeTab ) {
		Component = (
			<FieldGroups
				groups={props.groups}
				getGroupFields={props.getGroupFields}
				groupList={props.groupList}
				setGroupList={props.setGroupList}
				moveGroup={props.moveGroup}
			/>
		);
	} else {
		Component = (
			<DynamicTabContent
				tabOptions={props.tabOptions}
				getOptionValue={props.getOptionValue}
				setOptionValue={props.setOptionValue}
			/>
		);
	}

	return (
		<div
			id='post-body-content'
			className='pods-nav-tab-group pods-manage-field'
		>
			{Component}
		</div>
	);
} );
