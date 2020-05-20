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
				<FieldGroups />
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
	getPodOption: PropTypes.func.isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		const activeTab = storeSelect.getActiveTab();

		return {
			activeTab,
			activeTabOptions: storeSelect.getGlobalPodGroupFields( activeTab ),
			getPodOption: storeSelect.getPodOption,
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setOptionValue: dispatch( STORE_KEY_EDIT_POD ).setOptionValue,
		};
	} ),
] )( ActiveTabContent );
