import React, { useState, useEffect } from 'react';
import * as PropTypes from 'prop-types';

import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import GroupDragLayer from './group-drag-layer';
import FieldGroup from './field-group';
import './field-groups.scss';

const FieldGroups = ( {
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
	fields,
	setFields,
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

			{ groups.map( ( group, index ) => (
				<FieldGroup
					key={ group.name }
					groupName={ group.name }
					groupLabel={ group.label }
					groupID={ group.id }
					index={ index }
					getGroupFields={ getGroupFields }
					deleteGroup={ deleteGroup }
					moveGroup={ moveGroup }
					handleBeginDrag={ handleBeginDrag }
					handleDragCancel={ handleDragCancel }
					fields={ fields }
					groupFieldList={ groupFieldList }
					setGroupFields={ setGroupFields }
					addGroupField={ addGroupField }
					setFields={ setFields }
					randomString={ randomString }
					isExpanded={ expandedGroups[ group.name ] || false }
					toggleExpanded={ createToggleExpandGroup( group.name ) }
				/>
			) ) }

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
	groups: PropTypes.array.isRequired,
	getGroupFields: PropTypes.func.isRequired,
	addGroup: PropTypes.func.isRequired,
	deleteGroup: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,
	groupList: PropTypes.arrayOf( PropTypes.number ).isRequired,
	setGroupList: PropTypes.func.isRequired,
};

export default FieldGroups;
