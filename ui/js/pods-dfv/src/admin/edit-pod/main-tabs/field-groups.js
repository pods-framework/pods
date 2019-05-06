import React, { useState } from 'react';
import PropTypes from 'prop-types';

import DraggableGroup from './draggable-group';
import './field-groups.scss';

/**
 *
 */
export const FieldGroups = ( { groups, getGroupFields, reorderGroupItem } ) => {
	const [ dragInProgress, setDragInProgress ] = useState( false );

	const moveGroup = ( oldIndex, newIndex ) => {
		reorderGroupItem( oldIndex, newIndex );
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
					dragInProgress={dragInProgress}
					setDragInProgress={setDragInProgress}
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
	reorderGroupItem: PropTypes.func.isRequired,
};
