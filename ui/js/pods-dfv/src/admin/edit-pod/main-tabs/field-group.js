import React, { forwardRef, useEffect, useImperativeHandle, useRef } from 'react';
import * as PropTypes from 'prop-types';
import { flow } from 'lodash';
import { getEmptyImage } from 'react-dnd-html5-backend';

import dragSource from './group-drag-source';
import dropTarget from './group-drop-target';
import { FieldGroupSettings } from './field-group-settings';
import { FieldList } from 'pods-dfv/src/admin/edit-pod/main-tabs/field-list';

const { useState } = React;
const { Dashicon } = wp.components;
const { __ } = wp.i18n;

// eslint-disable-next-line react/display-name
const FieldGroup = forwardRef( ( props, ref ) => {
	const { connectDragSource, connectDropTarget, connectDragPreview, isDragging } = props;
	const { groupName, getGroupFields } = props;
	const [ expanded, setExpanded ] = useState( false );
	const [ showSettings, setShowSettings ] = useState( false );
	const wrapperRef = useRef( ref );
	const dragHandleRef = useRef( ref );

	useEffect( () => {
		if ( connectDragPreview ) {
			// Use empty image as a drag preview so browsers don't draw it,
			// we use our custom drag layer instead.
			connectDragPreview( getEmptyImage(), {
				// IE fallback: specify that we'd rather screenshot the node
				// when it already knows it's being dragged so we can hide it with CSS.
				captureDraggingState: true
			} );
		}
	} );

	connectDropTarget( wrapperRef );
	connectDragSource( dragHandleRef );

	useImperativeHandle( ref, () => ( {
		getWrapperNode: () => wrapperRef.current,
		getHandleNode: () => dragHandleRef.current,
	} ) );

	const onEditGroupClick = ( e ) => {
		e.stopPropagation();
		setShowSettings( true );
	};

	return (
		<div
			className="pods-field-group-wrapper"
			ref={wrapperRef}
			style={{ opacity: isDragging ? 0 : 1 }}>

			<div className="pods-field-group_title"
				onClick={() => setExpanded( !expanded )}>

				<div ref={dragHandleRef} className="pods-field-group_handle" style={{ cursor: isDragging ? 'ns-resize' : null }}>
					<Dashicon icon='menu' />
				</div>
				<div className="pods-field-group_name">{groupName}</div>
				<div className="pods-field-group_edit" onClick={( e ) => onEditGroupClick( e )}>
					{__( 'Edit Group', 'pods')}
				</div>
				<div className="pods-field-group_manage">
					<div className="pods-field-group_toggle">
						<Dashicon icon={expanded ? 'arrow-up' : 'arrow-down'} />
					</div>
				</div>
				{showSettings && <FieldGroupSettings groupName={groupName} show={setShowSettings} /> }
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
