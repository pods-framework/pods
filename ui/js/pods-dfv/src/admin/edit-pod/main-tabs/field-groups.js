import React, { useState } from 'react';
import * as PropTypes from 'prop-types';

import GroupDragLayer from './group-drag-layer';
import FieldGroup from './field-group';
import './field-groups.scss';

export const FieldGroups = ( { groups, getGroupFields, groupList, setGroupList, moveGroup } ) => {
	const [ originalList, setOriginalList ] = useState( groupList );

	const handleBeginDrag = () => {
		// Take a snapshot of the list state when dragging begins
		setOriginalList( groupList );
	};

	const handleDragCancel = () => {
		// Items are re-ordered on the fly, be sure to reset on cancel
		setGroupList( originalList );
	};

	return (
		<div className="field-groups">
			{groups.map( ( group, index ) => (
				<FieldGroup
					key={group.name}
					groupName={group.name}
					index={index}
					getGroupFields={getGroupFields}
					moveGroup={moveGroup}
					handleBeginDrag={handleBeginDrag}
					handleDragCancel={handleDragCancel}
				/>
			) )}
			<GroupDragLayer />
			<div className="pods-button-group_container">
				<a href="#">Add Group</a> <a href="#">Add Field</a>
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
