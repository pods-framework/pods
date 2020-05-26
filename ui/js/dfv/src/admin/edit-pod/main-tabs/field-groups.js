import React, { useState, useEffect } from 'react';
import * as PropTypes from 'prop-types';

import GroupDragLayer from './group-drag-layer';
import FieldGroup from './field-group';
import './field-groups.scss';

const FieldGroups = ( {
	groups,
	getGroupFields,
	groupList,
	setGroupList,
	addGroup,
	moveGroup,
	groupFieldList,
	setGroupFields,
	addGroupField,
	fields,
	setFields,
} ) => {
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
				<button className="button-primary" onClick={ ( e ) => handleAddGroup( e ) }>Add Group</button>
			</div>
			{ groups.map( ( group, index ) => (
				<FieldGroup
					key={ group.name }
					groupName={ group.name }
					groupLabel={ group.label }
					groupID={ group.id }
					index={ index }
					getGroupFields={ getGroupFields }
					moveGroup={ moveGroup }
					handleBeginDrag={ handleBeginDrag }
					handleDragCancel={ handleDragCancel }
					fields={ fields }
					groupFieldList={ groupFieldList }
					setGroupFields={ setGroupFields }
					addGroupField={ addGroupField }
					setFields={ setFields }
					randomString={ randomString }
				/>
			) ) }
			<GroupDragLayer />
			<div className="pods-button-group_container">
				<button className="button-primary" onClick={ ( e ) => handleAddGroup( e ) }>Add Group</button>
			</div>
		</div>
	);
};

FieldGroups.propTypes = {
	groups: PropTypes.array.isRequired,
	getGroupFields: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,
	groupList: PropTypes.arrayOf( PropTypes.number ).isRequired,
	setGroupList: PropTypes.func.isRequired,
};

export default FieldGroups;
