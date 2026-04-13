import React from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { withSelect, withDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';

/**
 * Pods dependencies
 */
import SaveStatusMessage from './save-status-message';
import EditPodName from './edit-pod-name';
import PodsNavTab from 'dfv/src/components/pods-nav-tab';
import ActiveTabContent from './main-tabs/active-tab-content';
import Postbox from './postbox';

import './edit-pod.scss';

const EditPod = ( {
	tabs,
	activeTab,
	setActiveTab,
	podName,
	setPodName,
	isExtended,
	showFields,
	storeKey,
} ) => {
	return (
		<>
			<div className="pods-edit-pod-header">
				<EditPodName
					podName={ podName }
					setPodName={ setPodName }
					isEditable={ ! isExtended }
				/>
				<SaveStatusMessage storeKey={ storeKey } />
				<PodsNavTab
					tabs={
						showFields ? [
							{
								name: 'manage-fields',
								label: __( 'Fields', 'pods' ),
							},
							...tabs
						] : tabs
					}
					activeTab={ activeTab }
					setActiveTab={ setActiveTab }
				/>
			</div>
			<div id="poststuff">
				<div id="post-body" className="columns-2">
					<ActiveTabContent storeKey={ storeKey } />
					<Postbox storeKey={ storeKey } />
					<br className="clear" />
				</div>
			</div>
		</>
	);
};

EditPod.propTypes = {
	tabs: PropTypes.arrayOf( PropTypes.shape( {
		name: PropTypes.string.isRequired,
		label: PropTypes.string.isRequired,
	} ) ).isRequired,
	activeTab: PropTypes.string.isRequired,
	podName: PropTypes.string.isRequired,
	isExtended: PropTypes.bool.isRequired,
	showFields: PropTypes.bool.isRequired,
	storeKey: PropTypes.string.isRequired,
	setActiveTab: PropTypes.func.isRequired,
	setPodName: PropTypes.func.isRequired,
};

export default compose( [
	withSelect( ( select, ownProps ) => {
		const { storeKey } = ownProps;

		const storeSelect = select( storeKey );

		return {
			tabs: storeSelect.getGlobalPodGroups(),
			activeTab: storeSelect.getActiveTab(),
			podName: storeSelect.getPodName(),
			isExtended: !! storeSelect.getPodOption( 'object' ),
			showFields: storeSelect.getGlobalShowFields(),
		};
	} ),
	withDispatch( ( dispatch, ownProps ) => {
		const { storeKey } = ownProps;

		const storeDispatch = dispatch( storeKey );

		return {
			setActiveTab: storeDispatch.setActiveTab,
			setPodName: storeDispatch.setPodName,
		};
	} ),
] )( EditPod );
