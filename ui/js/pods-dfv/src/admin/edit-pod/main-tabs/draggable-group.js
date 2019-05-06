import React, { useImperativeHandle, useRef, forwardRef } from 'react';
import PropTypes from 'prop-types';
import { flow } from 'lodash';

import dragSource from './group-drag-source';
import dropTarget from './group-drop-target';
import FieldGroup from './field-group';

// eslint-disable-next-line react/display-name
const DraggableGroup = forwardRef( ( props, ref ) => {
	const { connectDragSource, connectDropTarget, isDragging } = props;

	const elementRef = useRef( ref );
	connectDragSource( elementRef );
	connectDropTarget( elementRef );

	useImperativeHandle( ref, () => ( {
		getNode: () => elementRef.current,
	} ) );

	return connectDragSource(
		<div ref={elementRef} style={{ opacity: isDragging ? 0 : 1 }}>
			<FieldGroup {...props} />
		</div>
	);
} );

DraggableGroup.propTypes = {
	index: PropTypes.number.isRequired,
	moveGroup: PropTypes.func.isRequired,
	connectDragSource: PropTypes.func.isRequired,
	connectDragPreview: PropTypes.func.isRequired,
	connectDropTarget: PropTypes.func.isRequired,
	isDragging: PropTypes.bool.isRequired,
	setDragInProgress: PropTypes.func.isRequired,
};

export default flow( dropTarget, dragSource )( DraggableGroup );
