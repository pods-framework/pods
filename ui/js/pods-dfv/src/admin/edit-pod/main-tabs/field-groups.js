import React, { useState, useEffect } from 'react';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';
import GroupDragLayer from './group-drag-layer';
import FieldGroup from './field-group';
import './field-groups.scss';

const FieldGroups = ( {
	podName,
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
	editGroupPod,
} ) => {
	// If there's only one group, expand that group initially.
	const [ expandedGroups, setExpandedGroups ] = useState(
		1 === groups.length ? { [ groups[ 0 ].name ]: true } : {}
	);

	const [ originalList, setOriginalList ] = useState( groupList );
	const [ originalGroupFieldList, setOriginalGroupFieldList ] = useState( groupFieldList );

	useEffect( () => {
		setOriginalGroupFieldList( groupFieldList );
	}, [ groupFieldList ] );

	const handleBeginDrag = () => {
		// Take a snapshot of the list state when dragging begins
		setOriginalList( groupList );
	};

	const handleDragCancel = () => {
		// Items are re-ordered on the fly, be sure to reset on cancel
		setGroupList( originalList );
	};

	const handleAddGroup = ( e ) => {
		e.preventDefault();

		const str = randomString( 6 );
		const name = 'Group ' + str;
		addGroup( name );
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

	return (
		<div className="field-groups">
			<div className="pods-button-group_container">
				<Button
					isPrimary
					onClick={ ( e ) => handleAddGroup( e ) }
				>
					{ __( '+ Add New Group', 'pods' ) }
				</Button>
			</div>

			{ groups.map( ( group, index ) => {
				return (
					<FieldGroup
						key={ group.name }
						podName={ podName }
						groupName={ group.name }
						groupLabel={ group.label }
						groupID={ group.id }
						index={ index }
						fields={ getGroupFields( group.name ) }
						editGroupPod={ editGroupPod }
						deleteGroup={ deleteGroup }
						moveGroup={ moveGroup }
						handleBeginDrag={ handleBeginDrag }
						handleDragCancel={ handleDragCancel }
						groupFieldList={ groupFieldList }
						setGroupFields={ setGroupFields }
						addGroupField={ addGroupField }
						setFields={ setFields }
						randomString={ randomString }
						isExpanded={ expandedGroups[ group.name ] || false }
						toggleExpanded={ createToggleExpandGroup( group.name ) }
					/>
				);
			} ) }

			<GroupDragLayer />

			<div className="pods-button-group_container">
				<Button
					isPrimary
					onClick={ ( e ) => handleAddGroup( e ) }
				>
					{ __( '+ Add New Group', 'pods' ) }
				</Button>
			</div>
		</div>
	);
};

FieldGroups.propTypes = {
	podName: PropTypes.string.isRequired,
	// @todo make a group proptype shape
	groups: PropTypes.arrayOf( PropTypes.object ).isRequired,
	getGroupFields: PropTypes.func.isRequired,
	addGroup: PropTypes.func.isRequired,
	deleteGroup: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,
	groupList: PropTypes.arrayOf( PropTypes.number ).isRequired,
	setGroupList: PropTypes.func.isRequired,
	editGroupPod: PropTypes.object.isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		return {
			podName: storeSelect.getPodName(),
			groups: storeSelect.getGroups(),
			getGroupFields: storeSelect.getGroupFields,
			groupList: storeSelect.getGroupList(),
			groupFieldList: storeSelect.groupFieldList(),
			editGroupPod: storeSelect.getGlobalGroupOptions(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			setGroupList: storeDispatch.setGroupList,
			addGroup: storeDispatch.addGroup,
			deleteGroup: storeDispatch.deleteGroup,
			setGroupFields: storeDispatch.setGroupFields,
			addGroupField: storeDispatch.addGroupField,
			setFields: storeDispatch.setFields,
			moveGroup: storeDispatch.moveGroup,
		};
	} ),
] )( FieldGroups );
