import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import FieldListItem from './field-list-item';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

export const DraggableFieldListItem = ( props ) => {
	const {
		storeKey,
		podType,
		podName,
		podID,
		podLabel,
		field,
		groupName,
		groupLabel,
		groupID,
		hasMoved,
		cloneField,
	} = props;

	const { id } = field;

	const {
		attributes,
		listeners,
		setNodeRef,
		transform,
		transition,
		isDragging,
	} = useSortable( { id: id.toString() } );

	const style = {
		transform: CSS.Translate.toString( transform ),
		transition,
	};

	return (
		<FieldListItem
			storeKey={ storeKey }
			podType={ podType }
			podName={ podName }
			key={ field.id }
			podID={ podID }
			podLabel={ podLabel }
			groupLabel={ groupLabel }
			field={ field }
			groupName={ groupName }
			groupID={ groupID }
			cloneField={ cloneField }
			hasMoved={ hasMoved }
			isDragging={ isDragging }
			style={ style }
			draggableAttributes={ attributes }
			draggableListeners={ listeners }
			draggableSetNodeRef={ setNodeRef }
		/>
	);
};

DraggableFieldListItem.propTypes = {
	storeKey: PropTypes.string.isRequired,
	podType: PropTypes.string.isRequired,
	podName: PropTypes.string.isRequired,
	podID: PropTypes.number.isRequired,
	podLabel: PropTypes.string.isRequired,
	field: FIELD_PROP_TYPE_SHAPE,
	groupName: PropTypes.string.isRequired,
	groupLabel: PropTypes.string.isRequired,
	groupID: PropTypes.number.isRequired,
	hasMoved: PropTypes.bool.isRequired,

	cloneField: PropTypes.func.isRequired,
};

export default DraggableFieldListItem;
