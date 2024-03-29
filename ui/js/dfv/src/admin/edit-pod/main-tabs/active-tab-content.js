import React from 'react';
import PropTypes from 'prop-types';

// WordPress dependencies
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';

// Pods dependencies
import DynamicTabContent from './dynamic-tab-content';
import FieldGroups from './field-groups';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './active-tab-content.scss';

// Display the content for the active tab, manage-fields is treated special
const ActiveTabContent = ( {
	storeKey,
	activeTab,
	activeTabFields,
	allPodFields,
	allPodValues,
	setOptionValue,
} ) => {
	const isManageFieldsTabActive = 'manage-fields' === activeTab;

	return (
		<div
			id="post-body-content"
			className="pods-nav-tab-group pods-edit-pod-manage-field"
		>
			{ isManageFieldsTabActive ? (
				<FieldGroups storeKey={ storeKey } />
			) : (
				<DynamicTabContent
					storeKey={ storeKey }
					tabOptions={ activeTabFields }
					allPodFields={ allPodFields }
					allPodValues={ allPodValues }
					setOptionValue={ setOptionValue }
				/>
			) }
		</div>
	);
};

ActiveTabContent.propTypes = {
	/**
	 * Redux store key.
	 */
	storeKey: PropTypes.string.isRequired,

	/**
	 * Slug for the active tab.
	 */
	activeTab: PropTypes.string.isRequired,

	/**
	 * Array of fields belonging to the active tab.
	 */
	activeTabFields: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,

	/**
	 * Array of field configs for the whole Pod.
	 */
	allPodFields: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,

	/**
	 * All values for the Pod.
	 */
	allPodValues: PropTypes.object.isRequired,

	/**
	 * Function to update the field's value on change.
	 */
	setOptionValue: PropTypes.func.isRequired,
};

export default compose( [
	withSelect( ( select, ownProps ) => {
		const { storeKey } = ownProps;

		const storeSelect = select( storeKey );

		const activeTab = storeSelect.getActiveTab();

		return {
			activeTab,
			activeTabFields: storeSelect.getGlobalPodGroupFields( activeTab ),
			allPodFields: storeSelect.getGlobalPodFieldsFromAllGroups(),
			allPodValues: storeSelect.getPodOptions(),
		};
	} ),
	withDispatch( ( dispatch, ownProps ) => {
		const { storeKey } = ownProps;

		return {
			setOptionValue: dispatch( storeKey ).setOptionValue,
		};
	} ),
] )( ActiveTabContent );
