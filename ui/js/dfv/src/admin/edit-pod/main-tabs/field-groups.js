import React, { useState, useEffect } from 'react';
import * as PropTypes from 'prop-types';
import { omit } from 'lodash';

// WordPress dependencies
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { sprintf, __ } from '@wordpress/i18n';

import SettingsModal from './settings-modal';
import {
	STORE_KEY_EDIT_POD,
	SAVE_STATUSES,
} from 'dfv/src/admin/edit-pod/store/constants';
import GroupDragLayer from './group-drag-layer';
import FieldGroup from './field-group';

import { GROUP_PROP_TYPE_SHAPE } from 'dfv/src/prop-types';

import './field-groups.scss';

const FieldGroups = ( {
	podID,
	podName,
	podSaveStatus,
	groups,
	saveGroup,
	deleteAndRemoveGroup,
	moveGroup,
	groupSaveStatuses,
	editGroupPod,
} ) => {
	const [ showAddGroupModal, setShowAddGroupModal ] = useState( false );
	const [ addedGroupName, setAddedGroupName ] = useState( null );

	// If there's only one group, expand that group initially.
	const [ expandedGroups, setExpandedGroups ] = useState(
		1 === groups.length ? { [ groups[ 0 ].name ]: true } : {}
	);

	const [ groupsMovedSinceLastSave, setGroupsMovedSinceLastSave ] = useState( {} );

	const handleAddGroup = ( options = {} ) => ( event ) => {
		event.stopPropagation();

		setAddedGroupName( options.name );

		saveGroup(
			podID,
			options.name,
			options.name,
			options.label,
			omit( options, [ 'name', 'label', 'id' ] )
		);
	};

	const toggleExpandGroup = ( groupName ) => ( event ) => {
		event.stopPropagation();
		setExpandedGroups( {
			...expandedGroups,
			[ groupName ]: expandedGroups[ groupName ] ? false : true,
		} );
	};

	const handleGroupMove = ( oldIndex, newIndex ) => {
		moveGroup( oldIndex, newIndex );
	};

	const handleGroupDrop = () => {
		// Mark all groups as being edited
		setGroupsMovedSinceLastSave(
			groups.reduce( ( accumulator, current ) => {
				return {
					...accumulator,
					[ current.name ]: true,
				};
			}, {} )
		);
	};

	// After the pod has been saved, reset the list of groups
	// that haven't been saved.
	useEffect( () => {
		if ( podSaveStatus === SAVE_STATUSES.SAVE_SUCCESS ) {
			setGroupsMovedSinceLastSave( {} );
		}
	}, [ podSaveStatus ] );

	// After a new group has successfully been added, close
	// the modal.
	useEffect( () => {
		if (
			!! addedGroupName &&
			groupSaveStatuses[ addedGroupName ] === SAVE_STATUSES.SAVE_SUCCESS
		) {
			setShowAddGroupModal( false );
		}
	}, [ addedGroupName, setShowAddGroupModal, groupSaveStatuses ] );

	return (
		<div className="field-groups">
			{ showAddGroupModal && (
				<SettingsModal
					optionsPod={ editGroupPod }
					selectedOptions={ {} }
					title={ sprintf(
						/* translators: %1$s: Pod Label */
						__( '%1$s > Add Group', 'pods' ),
						podName,
					) }
					hasSaveError={ groupSaveStatuses[ addedGroupName ] === SAVE_STATUSES.SAVE_ERROR || false }
					saveButtonText={ __( 'Save New Group', 'pods' ) }
					errorMessage={ __( 'There was an error saving the group, please try again.', 'pods' ) }
					cancelEditing={ () => setShowAddGroupModal( false ) }
					save={ handleAddGroup }
				/>
			) }

			<div className="pods-button-group_container">
				<button
					className="pods-button-group_add-new"
					onClick={ () => setShowAddGroupModal( true ) }
				>
					{ __( '+ Add New Group', 'pods' ) }
				</button>
			</div>

			{ groups.map( ( group, index ) => {
				const hasMoved = !! groupsMovedSinceLastSave[ group.name ];

				return (
					<FieldGroup
						key={ group.name }
						podID={ podID }
						podName={ podName }
						group={ group }
						index={ index }
						editGroupPod={ editGroupPod }
						deleteGroup={ deleteAndRemoveGroup }
						moveGroup={ handleGroupMove }
						handleGroupDrop={ handleGroupDrop }
						saveStatus={ groupSaveStatuses[ group.name ] }
						saveGroup={ saveGroup }
						isExpanded={ expandedGroups[ group.name ] || false }
						toggleExpanded={ toggleExpandGroup( group.name ) }
						hasMoved={ hasMoved }
					/>
				);
			} ) }

			<GroupDragLayer />

			<div className="pods-button-group_container">
				<button
					className="pods-button-group_add-new"
					onClick={ () => setShowAddGroupModal( true ) }
				>
					{ __( '+ Add New Group', 'pods' ) }
				</button>
			</div>
		</div>
	);
};

FieldGroups.propTypes = {
	podID: PropTypes.number.isRequired,
	podName: PropTypes.string.isRequired,
	podSaveStatus: PropTypes.string.isRequired,
	groups: PropTypes.arrayOf( GROUP_PROP_TYPE_SHAPE ).isRequired,
	deleteAndRemoveGroup: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,
	editGroupPod: PropTypes.object.isRequired,
	groupSaveStatuses: PropTypes.object.isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		return {
			podID: storeSelect.getPodID(),
			podName: storeSelect.getPodName(),
			podSaveStatus: storeSelect.getSaveStatus(),
			groups: storeSelect.getGroups(),
			editGroupPod: storeSelect.getGlobalGroupOptions(),
			groupSaveStatuses: storeSelect.getGroupSaveStatuses(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			saveGroup: storeDispatch.saveGroup,
			deleteAndRemoveGroup: ( groupID ) => {
				storeDispatch.deleteGroup( groupID );
				storeDispatch.removeGroup( groupID );
			},
			moveGroup: storeDispatch.moveGroup,
		};
	} ),
] )( FieldGroups );
