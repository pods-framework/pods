import React from 'react';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';

// Pods dependencies
import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';
import DynamicTabContent from './dynamic-tab-content';
import FieldGroups from './field-groups';

// Display the content for the active tab, manage-fields is treated special
const ActiveTabContent = ( {
	activeTab,
	activeTabOptions,
	groups,
	getGroupFields,
	groupList,
	setGroupList,
	addGroup,
	deleteGroup,
	moveGroup,
	groupFieldList,
	setGroupFields,
	addGroupField,
	setFields,
	fields,
	getPodOption,
	setOptionValue,
} ) => {
	const isManageFieldsTabActive = 'manage-fields' === activeTab;

	return (
		<div
			id="post-body-content"
			className="pods-nav-tab-group pods-manage-field"
		>
			{ isManageFieldsTabActive ? (
				<FieldGroups
					groups={ groups }
					getGroupFields={ getGroupFields }
					groupList={ groupList }
					setGroupList={ setGroupList }
					addGroup={ addGroup }
					deleteGroup={ deleteGroup }
					moveGroup={ moveGroup }
					groupFieldList={ groupFieldList }
					setGroupFields={ setGroupFields }
					addGroupField={ addGroupField }
					setFields={ setFields }
					fields={ fields }
				/>
			) : (
				<DynamicTabContent
					tabOptions={ activeTabOptions }
					getOptionValue={ getPodOption }
					setOptionValue={ setOptionValue }
				/>
			) }
		</div>
	);
};

ActiveTabContent.propTypes = {
	activeTab: PropTypes.string.isRequired,
	activeTabOptions: PropTypes.array,
	groups: PropTypes.arrayOf( PropTypes.object ).isRequired,
	getPodOption: PropTypes.func.isRequired,
	getGroupFields: PropTypes.func.isRequired,
	groupList: PropTypes.arrayOf( PropTypes.number ).isRequired,
	groupFieldList: PropTypes.object.isRequired,
	fields: PropTypes.arrayOf( PropTypes.object ).isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		const activeTab = storeSelect.getActiveTab();

		return {
			activeTab,
			activeTabOptions: storeSelect.getGlobalPodGroupFields( activeTab ),
			groups: storeSelect.getGroups(),
			getPodOption: storeSelect.getPodOption,
			getGroupFields: storeSelect.getGroupFields,
			groupList: storeSelect.getGroupList(),
			groupFieldList: storeSelect.groupFieldList(),
			fields: [],
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			setOptionValue: storeDispatch.setOptionValue,
			setGroupList: storeDispatch.setGroupList,
			addGroup: storeDispatch.addGroup,
			deleteGroup: storeDispatch.deleteGroup,
			setGroupFields: storeDispatch.setGroupFields,
			addGroupField: storeDispatch.addGroupField,
			setFields: storeDispatch.setFields,
			moveGroup: storeDispatch.moveGroup,
		};
	} ),
] )( ActiveTabContent );
