import React, { useState, useEffect } from 'react';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';

import {
	STORE_KEY_EDIT_POD,
	SAVE_STATUSES,
} from 'dfv/src/admin/edit-pod/store/constants';
import sanitizeSlug from 'dfv/src/helpers/sanitizeSlug';
import GroupDragLayer from './group-drag-layer';
import FieldGroup from './field-group';
import { GROUP_PROP_TYPE_SHAPE } from 'dfv/src/prop-types';

import './field-groups.scss';

const FieldGroups = ( {
	podID,
	podName,
	podSaveStatus,
	groups,
	addGroup,
	saveGroup,
	deleteAndRemoveGroup,
	moveGroup,
	groupFieldList,
	setGroupFields,
	addGroupField,
	setFields,
	editGroupPod,
} ) => {
	// If there's only one group, expand that group initially.
	const [ expandedGroups, setExpandedGroups ] = useState(
		1 === groups.length ? { [ groups[ 0 ].name ]: true } : {}
	);

	const [ groupsMovedSinceLastSave, setGroupsMovedSinceLastSave ] = useState( {} );

	const handleAddGroup = ( event ) => {
		event.preventDefault();

		const str = randomString( 6 );
		const label = 'Group ' + str;
		const name = sanitizeSlug( label );

		addGroup( name );

		saveGroup( {
			pod_id: podID.toString(),
			name,
			label: name, // @todo use a real label. But this will be moved anyway
			args: {},
		} );
	};

	const createToggleExpandGroup = ( groupName ) => () => {
		setExpandedGroups( {
			...expandedGroups,
			[ groupName ]: expandedGroups[ groupName ] ? false : true,
		} );
	};

	const randomString = ( length ) => {
		let result = '';
		const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		const charactersLength = characters.length;
		for ( let i = 0; i < length; i++ ) {
			result += characters.charAt( Math.floor( Math.random() * charactersLength ) );
		}
		return result;
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
		const hasSaved = podSaveStatus === SAVE_STATUSES.SAVE_SUCCESS;

		if ( hasSaved ) {
			setGroupsMovedSinceLastSave( {} );
		}
	}, [ podSaveStatus ] );

	return (
		<div className="field-groups">
			<div className="pods-button-group_container">
				<button
					className="pods-button-group_add-new"
					onClick={ ( e ) => handleAddGroup( e ) }
				>
					{ __( '+ Add New Group', 'pods' ) }
				</button>
			</div>

			{ groups.map( ( group, index ) => {
				const hasMoved = !! groupsMovedSinceLastSave[ group.name ];

				return (
					<FieldGroup
						key={ group.name }
						podName={ podName }
						group={ group }
						index={ index }
						editGroupPod={ editGroupPod }
						deleteGroup={ deleteAndRemoveGroup }
						moveGroup={ handleGroupMove }
						handleGroupDrop={ handleGroupDrop }
						groupFieldList={ groupFieldList }
						setGroupFields={ setGroupFields }
						addGroupField={ addGroupField }
						setFields={ setFields }
						randomString={ randomString }
						isExpanded={ expandedGroups[ group.name ] || false }
						toggleExpanded={ createToggleExpandGroup( group.name ) }
						hasMoved={ hasMoved }
					/>
				);
			} ) }

			<GroupDragLayer />

			<div className="pods-button-group_container">
				<button
					className="pods-button-group_add-new"
					onClick={ ( e ) => handleAddGroup( e ) }
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
	addGroup: PropTypes.func.isRequired,
	deleteAndRemoveGroup: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,
	editGroupPod: PropTypes.object.isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		return {
			podID: storeSelect.getPodID(),
			podName: storeSelect.getPodName(),
			podSaveStatus: storeSelect.getSaveStatus(),
			groups: storeSelect.getGroups(),
			groupFieldList: storeSelect.groupFieldList(),
			editGroupPod: storeSelect.getGlobalGroupOptions(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			addGroup: storeDispatch.addGroup,
			saveGroup: storeDispatch.saveGroup,
			deleteAndRemoveGroup: ( groupID ) => {
				storeDispatch.deleteGroup( groupID );
				storeDispatch.removeGroup( groupID );
			},
			setGroupFields: storeDispatch.setGroupFields,
			addGroupField: storeDispatch.addGroupField,
			setFields: storeDispatch.setFields,
			moveGroup: storeDispatch.moveGroup,
		};
	} ),
] )( FieldGroups );
