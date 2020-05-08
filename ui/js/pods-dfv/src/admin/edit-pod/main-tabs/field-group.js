import React, {
	forwardRef,
	useEffect,
	useImperativeHandle,
	useRef,
} from 'react';
import * as PropTypes from 'prop-types';
import { flow, max, map } from 'lodash';
import { getEmptyImage } from 'react-dnd-html5-backend';

import dragSource from './group-drag-source';
import dropTarget from './group-drop-target';
import { FieldGroupSettings } from './field-group-settings';
import { FieldList } from 'pods-dfv/src/admin/edit-pod/main-tabs/field-list';
import update from 'immutability-helper';

const { useState } = React;
const { Dashicon } = wp.components;
const { __ } = wp.i18n;

// eslint-disable-next-line react/display-name
const FieldGroup = forwardRef( ( props, ref ) => {
	const {
		connectDragSource,
		connectDropTarget,
		connectDragPreview,
		isDragging,
	} = props;
	const {
		groupName,
		getGroupFields,
		fields,
		groupFieldList,
		randomString,
		setGroupFields,
		addGroupField,
		setFields,
	} = props;
	const [ expanded, setExpanded ] = useState( false );
	const [ showSettings, setShowSettings ] = useState( false );
	const wrapperRef = useRef( ref );
	const dragHandleRef = useRef( ref );
	const [ originalFields, setOriginalFields ] = useState( fields );

	useEffect( () => {
		if ( connectDragPreview ) {
			// Use empty image as a drag preview so browsers don't draw it,
			// we use our custom drag layer instead.
			connectDragPreview( getEmptyImage(), {
				// IE fallback: specify that we'd rather screenshot the node
				// when it already knows it's being dragged so we can hide it with CSS.
				captureDraggingState: true,
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

	const addField = ( groupName, type = 'text' ) => {
		const str = randomString( 6 );
		const fieldName = 'field_' + str;
		const fields = getGroupFields( groupName );

		let maxPosition = max( map( fields, ( f ) => f.position ) );

		if ( ! maxPosition ) {
			maxPosition = 0;
		}

		const field = {
			id: str,
			name: fieldName,
			label: 'Field ' + str,
			position: maxPosition + 1,
			type,
			required: false,
		};

		addGroupField( groupName, field );
	};

	const cloneField = ( groupName, type ) => {
		addField( groupName, type );
	};

	const deleteField = ( groupName, fieldName ) => {
		const fields = getGroupFields( groupName );
		const newFields = fields.filter( function( obj ) {
			return obj.name != fieldName;
		} );

		setGroupFields( groupName, newFields );

		// var fields = getGroupFields(groupName);
		// var index = fields.indexOf(fieldName);

		// if (index !== -1) {
		// 	// fields.splice(index, 1);
		// 	delete fields[fieldName];
		// 	// setFields(originalFields)
		// 	setGroupFields(groupName, fields);
		// }
	};

	const moveField = ( groupName, field, dragIndex, hoverIndex, item ) => {
		if ( groupName === item.groupName ) {
			const fields = getGroupFields( item.groupName );
			const movedItem = fields.find(
				( itm, index ) => index === hoverIndex
			);
			const remainingItems = fields.filter(
				( itm, index ) => index !== hoverIndex
			);

			const reorderedItems = [
				...remainingItems.slice( 0, dragIndex ),
				movedItem,
				...remainingItems.slice( dragIndex ),
			];

			setGroupFields( groupName, reorderedItems );
		} else {
			// console.log(item)
			// let oldGroupFields = groupFieldList[item.groupName]
			// console.log(oldGroupFields)
			// var movedFieldIndex = oldGroupFields.indexOf(item.fieldName)
			// setGroupFields(groupName, update(groupFieldList[groupName], {
			// 	$splice: [
			// 		[dragIndex, 1],
			// 		[hoverIndex, 0, item.fieldName],
			// 	],
			// }))
		}
	};

	return (
		<div
			className="pods-field-group-wrapper"
			ref={ wrapperRef }
			style={ { opacity: isDragging ? 0 : 1 } }
		>
			<div
				className="pods-field-group_title"
				onClick={ () => setExpanded( !expanded ) }
			>
				<div>
					<div
						ref={ dragHandleRef }
						className="pods-field-group_handle"
						style={ { cursor: isDragging ? 'ns-resize' : null } }
					>
						<Dashicon icon="menu" />
					</div>
					<div className="pods-field-group_name">{ groupName }</div>
				</div>

				<div>
					{ expanded && (
						<div
							className="pods-field-group_add_field_link"
							onClick={ ( e ) => {
								e.stopPropagation();
								addField( groupName );
							} }
						>
							{ __( 'Add Field', 'pods' ) }
						</div>
					) }

					<div
						className="pods-field-group_manage_link"
						onClick={ ( e ) => setExpanded( !expanded ) }
					>
						{ __( 'Manage Fields', 'pods' ) }
					</div>
					<div
						className="pods-field-group_edit"
						onClick={ ( e ) => onEditGroupClick( e ) }
					>
						{ __( 'Edit Group', 'pods' ) }
					</div>
					<div className="pods-field-group_manage">
						<div className="pods-field-group_toggle">
							<Dashicon
								icon={ expanded ? 'arrow-up' : 'arrow-down' }
							/>
						</div>
					</div>
				</div>

				{ showSettings && (
					<FieldGroupSettings
						groupName={ groupName }
						show={ setShowSettings }
					/>
				) }
			</div>

			{ expanded && !isDragging && (
				<FieldList
					fields={ getGroupFields( groupName ) }
					setGroupFields={ setGroupFields }
					moveField={ moveField }
					groupName={ groupName }
					cloneField={ cloneField }
					deleteField={ deleteField }
				/>
			) }
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
