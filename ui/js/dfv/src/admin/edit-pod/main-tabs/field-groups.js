import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import { omit } from 'lodash';
import {
	DndContext,
	DragOverlay,
	closestCorners,
	KeyboardSensor,
	PointerSensor,
	useSensor,
	useSensors,
} from '@dnd-kit/core';
import { restrictToWindowEdges } from '@dnd-kit/modifiers';
import {
	arrayMove,
	SortableContext,
	sortableKeyboardCoordinates,
	verticalListSortingStrategy,
} from '@dnd-kit/sortable';

/**
 * WordPress dependencies
 */
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { sprintf, __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SettingsModal from './settings-modal';
import { SAVE_STATUSES } from 'dfv/src/store/constants';
import FieldGroup from './field-group';
import FieldListItem from './field-list-item';

import {
	GROUP_PROP_TYPE_SHAPE,
	FIELD_PROP_TYPE_SHAPE,
} from 'dfv/src/config/prop-types';

import './field-groups.scss';

const FieldGroups = ( {
	storeKey,
	podType,
	podName,
	podID,
	podLabel,
	podSaveStatus,
	groups,
	saveGroup,
	deleteGroup,
	removeGroupFromPod,
	setGroups,
	moveGroup,
	resetGroupSaveStatus,
	groupSaveStatuses,
	groupSaveMessages,
	groupDeleteStatuses,
	editGroupPod,
	allFields,
	setGroupFields,
} ) => {
	const [ showAddGroupModal, setShowAddGroupModal ] = useState( false );
	const [ addedGroupName, setAddedGroupName ] = useState( null );
	const [ activeField, setActiveField ] = useState( null );
	const [ clonedGroups, setClonedGroups ] = useState( null );

	// If there's only one group, expand that group initially.
	const [ expandedGroups, setExpandedGroups ] = useState(
		1 === groups.length ? { [ groups[ 0 ].name ]: true } : {}
	);

	// Use an array of names for groups, but an array of IDs for fields:
	const [ groupsMovedSinceLastSave, setGroupsMovedSinceLastSave ] = useState( [] );
	const [ fieldsMovedSinceLastSave, setFieldsMovedSinceLastSave ] = useState( [] );

	// During drag-and-drop operations, we need to find a specific group (or an array
	// of its fields) by name frequently.
	// We also need to find field information for a specific ID often.
	const findGroupFields = ( groupName ) => groups.find(
		( group ) => group.name === groupName,
	)?.fields || [];

	const findFieldData = ( fieldID ) => allFields.find(
		( field ) => field.id.toString() === fieldID.toString(),
	);

	const sensors = useSensors(
		useSensor( PointerSensor, {
			activationConstraint: {
				distance: 5,
			},
		} ),
		useSensor( KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates,
		} ),
	);

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

	const toggleExpandGroup = ( groupName ) => () => {
		setExpandedGroups( {
			...expandedGroups,
			[ groupName ]: expandedGroups[ groupName ] ? false : true,
		} );
	};

	const handleDragStart = ( event ) => {
		const { active } = event;

		// We only need to handle fields (not groups):
		if ( 'group' === active?.data?.current?.type ) {
			return;
		}

		const newActiveField = findFieldData( active.id );

		setActiveField( newActiveField );
		setClonedGroups( groups );
	};

	const handleDragOver = ( { active, over } ) => {
		if ( ! over || ! over.id ) {
			return;
		}

		// We only need to handle fields (not groups):
		if ( 'group' === active?.data?.current?.type ) {
			return;
		}

		// We only need to move items if we're going from one group to another.
		// (The containerId for a group's SortableContext is the same as the groupName.)
		// If we're dragging over an empty list, we get the ID passed to useDroppable
		// instead of useSortable.
		const overGroupName = 'empty-group' === over.data?.current?.type
			? over.id
			: over.data?.current?.sortable?.containerId;
		const activeGroupName = active.data?.current?.sortable?.containerId;

		// A field dragged within its original group gets moved during the dragEnd event,
		// not now.
		if ( overGroupName === activeGroupName ) {
			return;
		}

		const activeData = findFieldData( active.id );

		if ( ! activeData ) {
			return;
		}

		const overGroupFields = findGroupFields( overGroupName );

		// If the item has already been added, we don't
		// need to do anything.
		const doesListAlreadyIncludeActive = overGroupFields.findIndex(
			( item ) => item.id.toString() === active.id.toString(),
		) !== -1;

		if ( doesListAlreadyIncludeActive ) {
			return;
		}

		const overIndex = overGroupFields.findIndex(
			( item ) => item.id.toString() === over.id.toString(),
		);

		const isBelowLastItem = overIndex === overGroupFields.length - 1 &&
			active.rect.current.translated &&
			active.rect.current.translated.offsetTop >
				over.rect.offsetTop + over.rect.height;

		const modifier = isBelowLastItem ? 1 : 0;

		const newIndex = overIndex >= 0
			? overIndex + modifier
			: overGroupFields.length + 1;

		const newOverGroupFields = [
			...overGroupFields.slice( 0, newIndex ),
			activeData,
			...overGroupFields.slice(
				newIndex,
				overGroupFields.length,
			),
		];

		const activeGroupFields = findGroupFields( activeGroupName );

		const newActiveGroupFields = [ ...activeGroupFields ].filter(
			( field ) => field.id.toString() !== active.id.toString()
		);

		// @todo should there be an action for moving a field from one group to another?
		setGroupFields( overGroupName, newOverGroupFields );
		setGroupFields( activeGroupName, newActiveGroupFields );
	};

	const handleDragEnd = ( event ) => {
		const { active, over } = event;

		if ( ! over?.id ) {
			return;
		}

		// Handling the dragEnd for a Group is simpler, handle that and return early:
		if ( 'group' === active?.data?.current?.type ) {
			const oldIndex = groups.findIndex(
				( item ) => ( item.name === active.id ),
			);

			const newIndex = groups.findIndex(
				( item ) => ( item.name === over.id ),
			);

			moveGroup( oldIndex, newIndex );

			// Mark all groups as being edited.
			setGroupsMovedSinceLastSave( groups.map( ( group ) => group.name ) );

			return;
		}

		const overGroupName = over.data?.current?.sortable?.containerId;
		const overGroupFields = findGroupFields( overGroupName );

		const oldIndex = overGroupFields.findIndex(
			( item ) => ( item.id.toString() === active.id ),
		);

		const newIndex = overGroupFields.findIndex(
			( item ) => ( item.id.toString() === over.id ),
		);

		const reorderedItems = arrayMove( overGroupFields, oldIndex, newIndex );

		setActiveField( null );
		setClonedGroups( null );
		setGroupFields( overGroupName, reorderedItems );

		setFieldsMovedSinceLastSave( ( prevState ) => [
			...prevState,
			parseInt( active.id, 10 ),
		] );
	};

	const handleDragCancel = () => {
		if ( clonedGroups ) {
			setGroups( clonedGroups );
		}

		setActiveField( null );
		setClonedGroups( null );
	};

	// After the pod has been saved, reset the list of groups
	// that haven't been saved.
	useEffect( () => {
		if ( podSaveStatus === SAVE_STATUSES.SAVE_SUCCESS ) {
			setGroupsMovedSinceLastSave( [] );
			setFieldsMovedSinceLastSave( [] );
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
			setAddedGroupName( null );
		}
	}, [ addedGroupName, setShowAddGroupModal, groupSaveStatuses ] );

	return (
		<div className="field-groups">
			{ showAddGroupModal && (
				<SettingsModal
					storeKey={ storeKey }
					podType={ podType }
					podName={ podName }
					optionsPod={ editGroupPod }
					selectedOptions={ {} }
					title={ sprintf(
						// @todo Zack: Make these into elements we can style the parent pod differently.
						/* translators: %1$s: Pod Label */
						__( '%1$s > Add Group', 'pods' ),
						podLabel,
					) }
					isSaving={ groupSaveStatuses[ addedGroupName ] === SAVE_STATUSES.SAVING }
					hasSaveError={ groupSaveStatuses[ addedGroupName ] === SAVE_STATUSES.SAVE_ERROR }
					saveButtonText={ __( 'Save New Group', 'pods' ) }
					errorMessage={
						groupSaveMessages[ addedGroupName ] ||
						__( 'There was an error saving the group, please try again.', 'pods' )
					}
					cancelEditing={ () => {
						setShowAddGroupModal( false );
						setAddedGroupName( null );
					} }
					save={ handleAddGroup }
				/>
			) }

			<div className="pods-button-group_container">
				<button
					className="pods-button-group_add-new"
					onClick={ ( event ) => {
						event.target.blur();

						setShowAddGroupModal( true );
					} }
				>
					{ __( '+ Add New Group', 'pods' ) }
				</button>
			</div>

			<DndContext
				sensors={ sensors }
				collisionDetection={ closestCorners }
				onDragStart={ handleDragStart }
				onDragOver={ handleDragOver }
				onDragEnd={ handleDragEnd }
				onDragCancel={ handleDragCancel }
				modifiers={ [ restrictToWindowEdges ] }
			>
				<SortableContext
					items={ groups.map( ( group ) => group.name ) }
					strategy={ verticalListSortingStrategy }
				>
					<div>
						{ groups.map( ( group, index ) => {
							const hasMoved = groupsMovedSinceLastSave.includes( group.name );

							return (
								<FieldGroup
									storeKey={ storeKey }
									key={ group.name }
									podType={ podType }
									podName={ podName }
									podID={ podID }
									podLabel={ podLabel }
									group={ group }
									index={ index }
									editGroupPod={ editGroupPod }
									deleteGroup={ deleteGroup }
									removeGroupFromPod={ () => removeGroupFromPod( group.id ) }
									saveStatus={ groupSaveStatuses[ group.name ] }
									saveMessage={ groupSaveMessages[ group.name ] }
									deleteStatus={ groupDeleteStatuses[ group.name ] }
									saveGroup={ saveGroup }
									resetGroupSaveStatus={ resetGroupSaveStatus }
									isExpanded={ expandedGroups[ group.name ] || false }
									toggleExpanded={ toggleExpandGroup( group.name ) }
									hasMoved={ hasMoved }
									fieldsMovedSinceLastSave={ fieldsMovedSinceLastSave }
								/>
							);
						} ) }
					</div>
				</SortableContext>

				<DragOverlay>
					{ activeField ? (
						<FieldListItem
							storeKey={ storeKey }
							podType={ podType }
							podName={ podName }
							podID={ podID }
							podLabel={ podLabel }
							groupLabel={ '' }
							field={ activeField }
							groupName={ '' }
							groupID={ parseInt( activeField.group, 10 ) }
							cloneField={ undefined }
							isOverlay={ true }
						/>
					) : null }
				</DragOverlay>
			</DndContext>

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
	storeKey: PropTypes.string.isRequired,
	podType: PropTypes.string.isRequired,
	podName: PropTypes.string.isRequired,
	podID: PropTypes.number.isRequired,
	podLabel: PropTypes.string.isRequired,
	podSaveStatus: PropTypes.string.isRequired,
	groups: PropTypes.arrayOf( GROUP_PROP_TYPE_SHAPE ).isRequired,
	deleteGroup: PropTypes.func.isRequired,
	removeGroupFromPod: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,
	editGroupPod: PropTypes.object.isRequired,
	resetGroupSaveStatus: PropTypes.func.isRequired,
	groupSaveStatuses: PropTypes.object.isRequired,
	groupSaveMessages: PropTypes.object.isRequired,
	groupDeleteStatuses: PropTypes.object.isRequired,
	allFields: PropTypes.arrayOf(
		FIELD_PROP_TYPE_SHAPE
	).isRequired,
	setGroupFields: PropTypes.func.isRequired,
};

export default compose( [
	withSelect( ( select, ownProps ) => {
		const { storeKey } = ownProps;

		const storeSelect = select( storeKey );

		return {
			podType: storeSelect.getPodOption( 'type' ),
			podName: storeSelect.getPodOption( 'name' ),
			podID: storeSelect.getPodID(),
			podLabel: storeSelect.getPodOption( 'label' ),
			podSaveStatus: storeSelect.getSaveStatus(),
			groups: storeSelect.getGroups(),
			editGroupPod: storeSelect.getGlobalGroupOptions(),
			groupSaveStatuses: storeSelect.getGroupSaveStatuses(),
			groupSaveMessages: storeSelect.getGroupSaveMessages(),
			groupDeleteStatuses: storeSelect.getGroupDeleteStatuses(),
			allFields: storeSelect.getFieldsFromAllGroups(),
		};
	} ),
	withDispatch( ( dispatch, ownProps ) => {
		const { storeKey } = ownProps;

		const storeDispatch = dispatch( storeKey );

		return {
			saveGroup: storeDispatch.saveGroup,
			deleteGroup: storeDispatch.deleteGroup, // groupID, name
			removeGroupFromPod: storeDispatch.removeGroup, // groupID
			setGroups: storeDispatch.setGroups, // groups
			moveGroup: storeDispatch.moveGroup, // oldIndex, newIndex
			resetGroupSaveStatus: storeDispatch.resetGroupSaveStatus,
			setGroupFields: storeDispatch.setGroupFields,
		};
	} ),
] )( FieldGroups );
