import React, { useState } from 'react';
import PropTypes from 'prop-types';

import DraggableGroup from './draggable-group';
import './field-groups.scss';

/**
 *
 */
export const FieldGroups = ( { groups, getGroupFields, groupList, setGroupList, moveGroup } ) => {
	const [ dragInProgress, setDragInProgress ] = useState( false );
	const [ originalList, setOriginalList ] = useState( groupList );

	const handleBeginDrag = () => {
		// Take a snapshot of the list state when dragging begins
		setOriginalList( groupList );
		setDragInProgress( true );
	};

	const handleEndDragCancel = () => {
		// Items are re-ordered on the fly, be sure to reset on cancel
		setGroupList( originalList );
		setDragInProgress( false );
	};
	const handleEndDrag = () => {
		setDragInProgress( false );
	};

	return (
		<div className="field-groups">
			{groups.map( ( group, index ) => (
				<DraggableGroup
					key={group.name}
					groupName={group.name}
					index={index}
					getGroupFields={getGroupFields}
					moveGroup={moveGroup}
					handleBeginDrag={handleBeginDrag}
					handleEndDrag={handleEndDrag}
					handleEndDragCancel={handleEndDragCancel}
					dragInProgress={dragInProgress}
				/>
			) )}
			<div className="pods-button-group--container">
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
