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

/* eslint-disable no-console */

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

	// Use an array of names for groups, but an array of IDs for fields:
	const [ groupsMovedSinceLastSave, setGroupsMovedSinceLastSave ] = useState( [] );
	const [ fieldsMovedSinceLastSave, setFieldsMovedSinceLastSave ] = useState( [] );

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

	// If there's only one group, expand that group initially.
	const [ expandedGroups, setExpandedGroups ] = useState(
		1 === groups.length ? { [ groups[ 0 ].name ]: true } : {}
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

		console.log( `handleFieldDragStart: ${ active.id }`, active );

		const newActiveField = allFields.find(
			( item ) => ( item.id.toString() === active.id.toString() ),
		);

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

		console.log( 'handleDragOver', active.id, over.id );

		// We only need to move items if we're going from one group to another.
		// (The containerId for a group's SortableContext is the same as the groupName.)
		const overGroupName = over.data?.current?.sortable?.containerId;
		const activeGroupName = active.data?.current?.sortable?.containerId;

		console.log(
			`- overGroupName: ${ overGroupName }`,
			` - activeGroupName: ${ activeGroupName }`
		);

		if ( overGroupName === activeGroupName ) {
			return;
		}

		const overGroup = groups.find(
			( group ) => group.name === overGroupName,
		);
		const activeGroup = groups.find(
			( group ) => group.name === activeGroupName,
		);

		// Shouldn't fail, but handle it just in case.
		if ( ! overGroup || ! activeGroup ) {
			return;
		}

		const overGroupFields = overGroup?.fields || [];
		const activeGroupFields = activeGroup?.fields || [];

		// @todo add a Droppable zone
		// It's simpler to handle adding one item to an empty list.
		if (
			over.id === 'TODO_DROPPABLE_ID' &&
			overGroupFields.length === 0
		) {
			const activeData = allFields.find(
				( item ) => ( item.id.toString() === active ),
			);

			if ( ! activeData ) {
				return;
			}

			setGroupFields( overGroupName, [ activeData ] );

			return;
		}

		const currentItems = [ ...overGroupFields ];

		// If the item has already been added, we don't
		// need to do anything.
		const doesListAlreadyIncludeActive = currentItems.findIndex(
			( item ) => item.id.toString() === active.id.toString(),
		) !== -1;

		if ( doesListAlreadyIncludeActive ) {
			console.log( '- returning, already in list' );
			return;
		}

		const activeData = allFields.find(
			( item ) => item.id.toString() === active.id.toString(),
		);

		if ( ! activeData ) {
			return;
		}

		console.log( '- activeData', activeData );

		const overIndex = currentItems.findIndex(
			( item ) => item.id.toString() === over.id.toString(),
		);

		const isBelowLastItem = overIndex === currentItems.length - 1 &&
			active.rect.current.translated &&
			active.rect.current.translated.offsetTop >
				over.rect.offsetTop + over.rect.height;

		const modifier = isBelowLastItem ? 1 : 0;

		const newIndex = overIndex >= 0
			? overIndex + modifier
			: currentItems.length + 1;

		const newOverGroupFields = [
			...currentItems.slice( 0, newIndex ),
			activeData,
			...currentItems.slice(
				newIndex,
				currentItems.length,
			),
		];

		const newActiveGroupFields = [ ...activeGroupFields ].filter(
			( field ) => field.id.toString() !== active.id.toString()
		);

		console.log( '-adding field to group' );

		// @todo should there be an action for moving a field from one group to another?
		setGroupFields( overGroupName, newOverGroupFields );
		setGroupFields( activeGroupName, newActiveGroupFields );
	};

	const handleDragEnd = ( event ) => {
		const { active, over } = event;

		// Don't sort anything if nothing changed or something is missing.
		if ( ! over?.id || active.id === over.id ) {
			console.log( 'handleDragEnd returning, active and over same', active, over );
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

		// @todo what happens when an the active item's group changes during the drag?

		const overGroupName = over.data?.current?.sortable?.containerId;
		const activeGroupName = active.data?.current?.sortable?.containerId;

		console.log( 'handleFieldDragEnd', active.id, over.id, overGroupName, activeGroupName );

		const overGroup = groups.find(
			( group ) => group.name === overGroupName,
		);

		const overGroupFields = overGroup?.fields || [];

		const oldIndex = overGroupFields.findIndex(
			( item ) => ( item.id.toString() === active.id ),
		);

		const newIndex = overGroupFields.findIndex(
			( item ) => ( item.id.toString() === over.id ),
		);

		// @todo use arraySwap?
		const reorderedItems = arrayMove( overGroupFields, oldIndex, newIndex );

		setActiveField( null );
		setClonedGroups( undefined );
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
							hasMoved={ false }
							isDragging={ true }
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
