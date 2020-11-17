import React from 'react';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';

// Pods dependencies
import { STORE_KEY_EDIT_POD } from 'dfv/src/admin/edit-pod/store/constants';
import DynamicTabContent from './dynamic-tab-content';
import FieldGroups from './field-groups';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

// Display the content for the active tab, manage-fields is treated special
const ActiveTabContent = ( {
	podType,
	podName,
	activeTab,
	activeTabOptions,
	activeTabOptionValues,
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
					optionValues={ activeTabOptionValues }
					setOptionValue={ setOptionValue }
					podType={ podType }
					podName={ podName }
				/>
			) }
		</div>
	);
};

ActiveTabContent.propTypes = {
	activeTab: PropTypes.string.isRequired,
	activeTabOptions: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,
	activeTabOptionValues: PropTypes.object.isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		const activeTab = storeSelect.getActiveTab();

		return {
			podType: storeSelect.getPodOption( 'type' ),
			podName: storeSelect.getPodOption( 'name' ),
			activeTab,
			activeTabOptions: storeSelect.getGlobalPodGroupFields( activeTab ),
			activeTabOptionValues: storeSelect.getPodOptions(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setOptionValue: dispatch( STORE_KEY_EDIT_POD ).setOptionValue,
		};
	} ),
] )( ActiveTabContent );
