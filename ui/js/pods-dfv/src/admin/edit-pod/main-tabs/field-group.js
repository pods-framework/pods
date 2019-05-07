import React, { forwardRef, useImperativeHandle, useRef } from 'react';
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
	const { groupName, getGroupFields } = props;
	const [ expanded, setExpanded ] = useState( false );
	const wrapperRef = useRef( ref );
	const groupTitleRef = useRef( ref );
	const dragHandleRef = useRef( ref );
	connectDropTarget( wrapperRef );
	connectDragPreview( groupTitleRef );
	connectDragSource( dragHandleRef );

	useImperativeHandle( ref, () => ( {
		getNode: () => wrapperRef.current,
	} ) );

	return (
		<div
			className="pods-field-group-wrapper"
			ref={wrapperRef}
			style={{ opacity: isDragging ? 0 : 1 }}>

			<div
				className="pods-field-group--title"
				ref={groupTitleRef}
				onClick={() => setExpanded( !expanded )}>

				<div ref={dragHandleRef} className="pods-field-group--handle">
					<Dashicon icon='menu' />
				</div>
				<div className="pods-field-group--name">{groupName}</div>
				<div className="pods-field-group--manage">
					<div className="pods-field-group--toggle">
						<Dashicon icon={expanded ? 'arrow-up' : 'arrow-down'} />
					</div>
				</div>
			</div>

			{expanded && !isDragging &&
			<FieldList fields={getGroupFields( groupName )} />}
		</div>
	);
} );

FieldGroup.propTypes = {
	groupName: PropTypes.string.isRequired,
	index: PropTypes.number.isRequired,
	getGroupFields: PropTypes.func.isRequired,
	handleBeginDrag: PropTypes.func.isRequired,
	handleDragCancel: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,

	// This comes from the drop target
	connectDropTarget: PropTypes.func.isRequired,

	// These come from the drag source
	connectDragSource: PropTypes.func.isRequired,
	connectDragPreview: PropTypes.func.isRequired,
	isDragging: PropTypes.bool.isRequired,
};

export default flow( dropTarget, dragSource )( FieldGroup );
