import React, { useImperativeHandle, useRef, forwardRef } from 'react';
import * as PropTypes from 'prop-types';
import { flow } from 'lodash';

import dragSource from './group-drag-source';
import dropTarget from './group-drop-target';
import { FieldList } from 'pods-dfv/src/admin/edit-pod/main-tabs/field-list';

const { useState } = React;
const { Dashicon } = wp.components;

// eslint-disable-next-line react/display-name
const FieldGroup = forwardRef( ( props, ref ) => {
	const { connectDragSource, connectDropTarget, connectDragPreview, isDragging } = props;
	const { groupName, getGroupFields, dragInProgress } = props;
	const [ expanded, setExpanded ] = useState( false );
	const sourceRef = useRef( ref );
	const targetRef = useRef( ref );

	connectDragSource( sourceRef );
	connectDropTarget( targetRef );

	useImperativeHandle( ref, () => ( {
		getNode: () => sourceRef.current,
	} ) );

	return connectDragPreview(
		<div className="pods-field-group-wrapper" ref={targetRef} style={{ opacity: isDragging ? 0 : 1 }}>
			<div className="pods-field-group--title" onClick={() => setExpanded( !expanded )}>
				{connectDragSource(
					<div ref={sourceRef} className="pods-field-group--handle">
						<Dashicon icon='menu' />
					</div>
				)}
				<div className="pods-field-group--name">{groupName}</div>
				<div className="pods-field-group--manage">
					<div className="pods-field-group--toggle">
						<Dashicon icon={expanded ? 'arrow-up' : 'arrow-down'} />
					</div>
				</div>
			</div>

			{expanded && !dragInProgress &&
			<FieldList fields={getGroupFields( groupName )} />}
		</div>
	);
} );

FieldGroup.propTypes = {
	groupName: PropTypes.string.isRequired,
	index: PropTypes.number.isRequired,
	getGroupFields: PropTypes.func.isRequired,
	dragInProgress: PropTypes.bool.isRequired,
	handleBeginDrag: PropTypes.func.isRequired,
	handleEndDrag: PropTypes.func.isRequired,
	handleEndDragCancel: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,

	// This comes from the drop target
	connectDropTarget: PropTypes.func.isRequired,

	// These come from the drag source
	connectDragSource: PropTypes.func.isRequired,
	connectDragPreview: PropTypes.func.isRequired,
	isDragging: PropTypes.bool.isRequired,
};

export default flow( dropTarget, dragSource )( FieldGroup );
