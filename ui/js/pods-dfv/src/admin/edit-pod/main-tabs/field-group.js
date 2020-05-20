import React, { forwardRef, useEffect, useImperativeHandle, useRef, useState } from 'react';
import * as PropTypes from 'prop-types';
import { flow, max, map } from 'lodash';
import { getEmptyImage } from 'react-dnd-html5-backend';

import { Button, Dashicon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import dragSource from './group-drag-source';
import dropTarget from './group-drop-target';
import FieldGroupSettings from './field-group-settings';
import FieldList from 'pods-dfv/src/admin/edit-pod/main-tabs/field-list';

const ENTER_KEY = 13;

const FieldGroup = forwardRef( ( props, ref ) => {
	const {
		connectDragSource,
		connectDropTarget,
		connectDragPreview,
		isDragging,
	} = props;

	const {
		podName,
		groupName,
		groupLabel,
		groupID,
		fields,
		groupFieldList,
		randomString,
		deleteGroup,
		setGroupFields,
		addGroupField,
		setFields,
		isExpanded,
		toggleExpanded,
		editGroupPod,
	} = props;

	const wrapperRef = useRef( ref );
	const dragHandleRef = useRef( ref );

	const [ isHovered, setIsHovered ] = useState( false );
	const [ showSettings, setShowSettings ] = useState( false );
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

	const handleKeyPress = ( event ) => {
		if ( event.keyCode === ENTER_KEY ) {
			toggleExpanded();
		}
	};

	const onEditGroupClick = ( event ) => {
		event.stopPropagation();
		setShowSettings( true );
	};

	const onDeleteGroupClick = ( event ) => {
		event.stopPropagation();

		// eslint-disable-next-line no-alert
		const confirmation = confirm(
			// eslint-disable-next-line @wordpress/i18n-no-collapsible-whitespace
			__( 'You are about to permanently delete this Field Group and all of the Fields within it. Make sure you have recent backups just in case. Are you sure you would like to delete this Group?\n\nClick ‘OK’ to continue, or ‘Cancel’ to make no changes.', 'pods' )
		);

		if ( confirmation ) {
			deleteGroup( groupName );
		}
	};

	const addField = ( group ) => ( type = 'text' ) => {
		const str = randomString( 6 );
		const fieldName = 'field_' + str;

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
			required: '0',
			group,
			object_type: 'field',
			parent: podName,
		};

		addGroupField( group, field );
	};

	const cloneField = ( group ) => ( type ) => {
		addField( group, type );
	};

	const deleteField = ( group ) => ( fieldName ) => {
		const newFields = fields.filter( function( obj ) {
			return obj.name !== fieldName;
		} );

		setGroupFields( group, newFields );
	};

	const moveField = ( groupName, field, dragIndex, hoverIndex, item ) => {
		if ( groupName === item.groupName ) {
			const localFields = [ ...fields ];
			const movedItem = localFields.find( ( itm, index ) => index === hoverIndex );
			const remainingItems = localFields.filter( ( itm, index ) => index !== hoverIndex );

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
			onMouseEnter={ () => setIsHovered( true ) }
			onMouseLeave={ () => setIsHovered( false ) }
		>
			<div
				tabIndex={ 0 }
				role="button"
				className="pods-field-group_title"
				onClick={ toggleExpanded }
				style={ { cursor: 'pointer' } }
				onKeyPress={ handleKeyPress }
			>
				<div
					ref={ dragHandleRef }
					className="pods-field-group_handle"
					style={ { cursor: isDragging ? 'ns-resize' : null } }
				>
					<Dashicon icon="menu" />
				</div>

				<div className="pods-field-group_name">
					{ groupLabel }
					{ !! groupID && (
						<span
							className="pods-field-group_name__id"
							style={ { opacity: isHovered ? 1 : 0 } }
						>
							{ `\u00A0 ID: ${ groupID }` }
						</span>
					) }
				</div>

				{ ! isExpanded && (
					<Button
						className="pods-field-group_manage_link"
						onClick={ toggleExpanded }
						isTertiary
						style={ { opacity: isHovered ? 1 : 0 } }
					>
						{ __( 'Manage Fields', 'pods' ) }
					</Button>
				) }

				<Button
					className="pods-field-group_edit"
					onClick={ ( event ) => onEditGroupClick( event ) }
					isTertiary
					style={ { opacity: isHovered ? 1 : 0 } }
				>
					{ __( 'Edit', 'pods' ) }
				</Button>

				<Button
					className="pods-field-group_delete"
					onClick={ onDeleteGroupClick }
					isTertiary
					style={ { opacity: isHovered ? 1 : 0 } }
				>
					{ __( 'Delete', 'pods' ) }
				</Button>

				<Button className="pods-field-group_manage">
					<Dashicon icon={ isExpanded ? 'arrow-up' : 'arrow-down' } />
				</Button>

				{ showSettings && (
					<FieldGroupSettings
						groupName={ groupName }
						show={ setShowSettings }
						editGroupPod={ editGroupPod }
					/>
				) }
			</div>

			{ isExpanded && ! isDragging && (
				<FieldList
					fields={ fields }
					setGroupFields={ setGroupFields }
					moveField={ moveField }
					groupName={ groupName }
					cloneField={ cloneField( groupName ) }
					deleteField={ deleteField( groupName ) }
					addField={ addField( groupName ) }
				/>
			) }
		</div>
	);
} );

FieldGroup.propTypes = {
	podName: PropTypes.string.isRequired,
	groupName: PropTypes.string.isRequired,
	groupLabel: PropTypes.string.isRequired,
	groupID: PropTypes.number,
	index: PropTypes.number.isRequired,
	isExpanded: PropTypes.bool.isRequired,
	editGroupPod: PropTypes.object.isRequired,

	toggleExpanded: PropTypes.func.isRequired,
	deleteGroup: PropTypes.func.isRequired,
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
