import React, { useState, useEffect } from 'react';
import * as PropTypes from 'prop-types';

import GroupDragLayer from './group-drag-layer';
import FieldGroup from './field-group';
import './field-groups.scss';

export const FieldGroups = ( { groups, getGroupFields, groupList, setGroupList, addGroup, moveGroup, groupFieldList, setGroupFields, addGroupField, fields, setFields } ) => {
	const [ originalList, setOriginalList ] = useState( groupList );

	const [originalGroupFieldList, setOriginalGroupFieldList] = useState(groupFieldList)

	useEffect(() => {
		setOriginalGroupFieldList(groupFieldList)
	}, [groupFieldList])

	const handleBeginDrag = () => {
		// Take a snapshot of the list state when dragging begins
		setOriginalList( groupList );
	};

	const handleDragCancel = () => {
		// Items are re-ordered on the fly, be sure to reset on cancel
		setGroupList( originalList );
	};

	const handleAddGroup = (e) => {
		e.preventDefault();

		var str = randomString(6);
		var name = 'Group ' + str;
		addGroup(name)
	};

	const randomString = (length) => {
		var result = '';
		var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var charactersLength = characters.length;
		for (var i = 0; i < length; i++) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}

	return (
		<div className="field-groups">
			<div className="pods-button-group_container">
				<a href="#" className="button-primary" onClick={(e) => handleAddGroup(e)}>Add Group</a>
			</div>
			{groups.map( ( group, index ) => (
				<FieldGroup
					key={group.name}
					groupName={group.name}
					index={index}
					getGroupFields={getGroupFields}
					moveGroup={moveGroup}
					handleBeginDrag={handleBeginDrag}
					handleDragCancel={handleDragCancel}
					fields={fields}
					groupFieldList={groupFieldList}
					setGroupFields={setGroupFields}
					addGroupField={addGroupField}
					setFields={setFields}
					randomString={randomString}
				/>
			) )}
			<GroupDragLayer />
			<div className="pods-button-group_container">
				<a href="#" className="button-primary" onClick={(e) => handleAddGroup(e)}>Add Group</a>
			</div>
		</div>
	);
};

FieldGroups.propTypes = {
	groups: PropTypes.array.isRequired,
	getGroupFields: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,
	groupList: PropTypes.array.isRequired,
	setGroupList: PropTypes.func.isRequired,
};
