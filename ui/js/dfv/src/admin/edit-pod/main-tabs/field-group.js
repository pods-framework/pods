import React, { forwardRef, useEffect, useImperativeHandle, useRef, useState } from 'react';
import * as PropTypes from 'prop-types';
import { flow, max, map, omit } from 'lodash';
import { getEmptyImage } from 'react-dnd-html5-backend';
import classnames from 'classnames';

import { Button, Dashicon } from '@wordpress/components';
import { sprintf, __ } from '@wordpress/i18n';

import dragSource from './group-drag-source';
import dropTarget from './group-drop-target';

import FieldGroupSettings from './field-group-settings';
import FieldList from 'dfv/src/admin/edit-pod/main-tabs/field-list';
import { GROUP_PROP_TYPE_SHAPE } from 'dfv/src/prop-types';

import { SAVE_STATUSES } from 'dfv/src/admin/edit-pod/store/constants';

const ENTER_KEY = 13;

const FieldGroup = forwardRef( ( props, ref ) => {
	const {
		connectDragSource,
		connectDropTarget,
		connectDragPreview,
		isDragging,
	} = props;

	const {
		podID,
		podName,
		group,
		isExpanded,
		hasMoved,
		saveStatus,
		randomString,
		deleteGroup,
		saveGroup,
		setGroupFields,
		addGroupField,
		toggleExpanded,
		editGroupPod,
	} = props;

	const {
		name: groupName,
		label: groupLabel,
		id: groupID,
		fields,
	} = group;

	const wrapperRef = useRef( ref );
	const dragHandleRef = useRef( ref );

	const [ isHovered, setIsHovered ] = useState( false );
	const [ showSettings, setShowSettings ] = useState( false );

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

	useEffect( () => {
		// Close the Group Settings modal if we finished saving.
		if ( SAVE_STATUSES.SAVE_SUCCESS === saveStatus ) {
			setShowSettings( false );
		}
	}, [ saveStatus ] );

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

	const onEditGroupCancel = () => {
		setShowSettings( false );
	};

	const onEditGroupSave = ( updatedOptions = {} ) => {
		saveGroup(
			podID,
			groupName,
			updatedOptions.name || groupName,
			updatedOptions.label || groupLabel || groupName,
			omit( updatedOptions, [ 'name', 'label', 'new_name', 'id' ] ),
			groupID,
		);
	};

	const onDeleteGroupClick = ( event ) => {
		event.stopPropagation();

		// eslint-disable-next-line no-alert
		const confirmation = confirm(
			// eslint-disable-next-line @wordpress/i18n-no-collapsible-whitespace
			__( 'You are about to permanently delete this Field Group and all of the Fields within it. Make sure you have recent backups just in case. Are you sure you would like to delete this Group?\n\nClick ‘OK’ to continue, or ‘Cancel’ to make no changes.', 'pods' )
		);

		if ( confirmation ) {
			deleteGroup( groupID );
		}
	};

	const addField = () => ( type = 'text' ) => {
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

	const cloneField = () => ( type ) => addField( group, type );

	const deleteField = () => ( fieldName ) => {
		const newFields = fields.filter( function( obj ) {
			return obj.name !== fieldName;
		} );

		setGroupFields( group, newFields );
	};

	const moveField = () => ( field, dragIndex, hoverIndex, item ) => {
		if ( group === item.groupName ) {
			const localFields = [ ...fields ];
			const movedItem = localFields.find( ( itm, index ) => index === hoverIndex );
			const remainingItems = localFields.filter( ( itm, index ) => index !== hoverIndex );

			const reorderedItems = [
				...remainingItems.slice( 0, dragIndex ),
				movedItem,
				...remainingItems.slice( dragIndex ),
			];

			setGroupFields( group, reorderedItems );
		}
	};

	const classes = classnames(
		'pods-field-group-wrapper',
		{
			'pods-unsaved-data': hasMoved,
		}
	);

	return (
		<div
			className={ classes }
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
					className="pods-field-group_name"
					ref={ dragHandleRef }
					style={ { cursor: isDragging ? 'ns-resize' : null } }
				>
					<div className="pods-field-group_handle">
						<Dashicon icon="menu" />
					</div>

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
						editGroupPod={ editGroupPod }
						groupOptions={ omit( group, [ 'fields' ] ) }
						title={ sprintf(
							/* translators: %1$s: Pod Label, %2$s Group Label */
							__( '%1$s > %2$s > Edit Group', 'pods' ),
							podName,
							groupLabel
						) }
						hasSaveError={ saveStatus === SAVE_STATUSES.SAVE_ERROR }
						cancelEditing={ onEditGroupCancel }
						save={ onEditGroupSave }
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
	podID: PropTypes.number.isRequired,
	podName: PropTypes.string.isRequired,
	group: GROUP_PROP_TYPE_SHAPE,

	index: PropTypes.number.isRequired,
	isExpanded: PropTypes.bool.isRequired,
	editGroupPod: PropTypes.object.isRequired,
	hasMoved: PropTypes.bool.isRequired,
	saveStatus: PropTypes.string,

	toggleExpanded: PropTypes.func.isRequired,
	deleteGroup: PropTypes.func.isRequired,
	saveGroup: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,
	handleGroupDrop: PropTypes.func.isRequired,

	// This comes from the drop target
	connectDropTarget: PropTypes.func.isRequired,

	// These come from the drag source
	connectDragSource: PropTypes.func.isRequired,
	connectDragPreview: PropTypes.func.isRequired,
	isDragging: PropTypes.bool.isRequired,
};

FieldGroup.displayName = 'FieldGroup';

export default flow( dropTarget, dragSource )( FieldGroup );
