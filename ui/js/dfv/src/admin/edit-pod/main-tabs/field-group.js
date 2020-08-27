import React, { forwardRef, useEffect, useImperativeHandle, useRef, useState } from 'react';
import * as PropTypes from 'prop-types';
import { flow, omit } from 'lodash';
import { getEmptyImage } from 'react-dnd-html5-backend';
import classnames from 'classnames';

import { Dashicon } from '@wordpress/components';
import { sprintf, __ } from '@wordpress/i18n';

import dragSource from './group-drag-source';
import dropTarget from './group-drop-target';

import SettingsModal from './settings-modal';
import FieldList from 'dfv/src/admin/edit-pod/main-tabs/field-list';
import { GROUP_PROP_TYPE_SHAPE } from 'dfv/src/prop-types';

import { SAVE_STATUSES } from 'dfv/src/admin/edit-pod/store/constants';

import './field-group.scss';

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
		podLabel,
		group,
		isExpanded,
		hasMoved,
		saveStatus,
		saveMessage,
		resetGroupSaveStatus,
		deleteGroup,
		saveGroup,
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
		if ( showSettings ) {
			return;
		}

		if ( event.keyCode === ENTER_KEY ) {
			toggleExpanded();
		}
	};

	const onEditGroupClick = ( event ) => {
		event.stopPropagation();
		setShowSettings( true );
	};

	const onEditGroupCancel = ( event ) => {
		event.stopPropagation();
		setShowSettings( false );
		resetGroupSaveStatus( groupName );
	};

	const onEditGroupSave = ( updatedOptions = {} ) => ( event ) => {
		event.stopPropagation();
		saveGroup(
			podID,
			groupName,
			updatedOptions.name || groupName,
			updatedOptions.label || groupLabel || groupName,
			omit( updatedOptions, [ 'name', 'label', 'id' ] ),
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

	const classes = classnames(
		'pods-field-group-wrapper',
		{ 'pods-unsaved-data': hasMoved }
	);

	return (
		<div
			className={ classes }
			ref={ wrapperRef }
			style={ { opacity: isDragging ? 0 : 1 } }
		>
			<div
				tabIndex={ 0 }
				role="button"
				className="pods-field-group_title"
				onClick={ ! showSettings ? toggleExpanded : undefined }
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
						<span className="pods-field-group_name__id">
							{ `\u00A0 [id = ${ groupID }]` }
						</span>
					) }
				</div>

				<div className="pods-field-group_buttons">
					{ ! isExpanded && (
						<>
							<button
								className="pods-field-group_button pods-field-group_manage_link"
								onClick={ toggleExpanded }
							>
								{ __( 'Manage Fields', 'pods' ) }
							</button>
							|
						</>
					) }

					<button
						className="pods-field-group_button pods-field-group_edit"
						onClick={ ( event ) => onEditGroupClick( event ) }
					>
						{ __( 'Edit', 'pods' ) }
					</button>
					|
					<button
						className="pods-field-group_button pods-field-group_delete"
						onClick={ onDeleteGroupClick }
					>
						{ __( 'Delete', 'pods' ) }
					</button>
				</div>

				<button
					className="pods-field-group_button pods-field-group_manage"
				>
					<Dashicon icon={ isExpanded ? 'arrow-up' : 'arrow-down' } />
				</button>

				{ showSettings && (
					<SettingsModal
						optionsPod={ editGroupPod }
						selectedOptions={ omit( group, [ 'fields' ] ) }
						title={ sprintf(
							/* translators: %1$s: Pod Label, %2$s Group Label */
							__( '%1$s > %2$s > Edit Group', 'pods' ),
							podLabel,
							groupLabel
						) }
						hasSaveError={ saveStatus === SAVE_STATUSES.SAVE_ERROR }
						errorMessage={
							saveMessage ||
							__( 'There was an error saving the group, please try again.', 'pods' )
						}
						saveButtonText={ __( 'Save Group', 'pods' ) }
						cancelEditing={ onEditGroupCancel }
						save={ onEditGroupSave }
					/>
				) }
			</div>

			{ isExpanded && ! isDragging && (
				<FieldList
					fields={ fields || [] }
					podID={ podID }
					podLabel={ podLabel }
					groupName={ groupName }
					groupID={ groupID }
					groupLabel={ groupLabel }
				/>
			) }
		</div>
	);
} );

FieldGroup.propTypes = {
	podID: PropTypes.number.isRequired,
	podLabel: PropTypes.string.isRequired,
	group: GROUP_PROP_TYPE_SHAPE,

	index: PropTypes.number.isRequired,
	isExpanded: PropTypes.bool.isRequired,
	editGroupPod: PropTypes.object.isRequired,
	hasMoved: PropTypes.bool.isRequired,
	saveStatus: PropTypes.string,
	saveMessage: PropTypes.string,

	toggleExpanded: PropTypes.func.isRequired,
	deleteGroup: PropTypes.func.isRequired,
	saveGroup: PropTypes.func.isRequired,
	resetGroupSaveStatus: PropTypes.func.isRequired,
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
