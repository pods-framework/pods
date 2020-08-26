import React, { useRef, useState, useEffect } from 'react';
import { useDrag, useDrop } from 'react-dnd';
import { omit } from 'lodash';
import classnames from 'classnames';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import { Dashicon } from '@wordpress/components';
import { sprintf, __ } from '@wordpress/i18n';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';

// Internal dependencies
import SettingsModal from './settings-modal';

import {
	STORE_KEY_EDIT_POD,
	SAVE_STATUSES,
} from 'dfv/src/admin/edit-pod/store/constants';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/prop-types';

import './field-list-item.scss';

const ENTER_KEY = 13;

export const FieldListItem = ( props ) => {
	const {
		podID,
		podLabel,
		field,
		saveStatus,
		saveMessage,
		podSaveStatus,
		index,
		typeObject,
		relatedObject,
		editFieldPod,
		saveField,
		resetFieldSaveStatus,
		moveField,
		groupName,
		groupLabel,
		groupID,
		cloneField,
		deleteField,
	} = props;

	const {
		id,
		name,
		label,
		type,
	} = field;

	const required = ( field.required && '0' !== field.required ) ? true : false;

	const [ showEditFieldSettings, setShowEditFieldSettings ] = useState( false );
	const [ hasMoved, setHasMoved ] = useState( false );

	const handleKeyPress = ( event ) => {
		if ( event.keyCode === ENTER_KEY ) {
			event.stopPropagation();
			setShowEditFieldSettings( true );
		}
	};

	const onEditFieldClick = ( event ) => {
		event.stopPropagation();
		setShowEditFieldSettings( true );
	};

	const onEditFieldCancel = ( event ) => {
		event.stopPropagation();
		setShowEditFieldSettings( false );
		resetFieldSaveStatus( name );
	};

	const onEditFieldSave = ( updatedOptions = {} ) => ( event ) => {
		event.stopPropagation();

		saveField(
			podID,
			groupName,
			name,
			updatedOptions.name || name,
			updatedOptions.label || label || name,
			updatedOptions.type || type,
			omit( updatedOptions, [ 'name', 'label', 'id', 'group' ] ),
			id,
		);
	};

	const onDeleteFieldClick = ( event ) => {
		event.stopPropagation();

		// eslint-disable-next-line no-alert
		const confirmation = confirm(
			// eslint-disable-next-line @wordpress/i18n-no-collapsible-whitespace
			__( 'You are about to permanently delete this Field. Make sure you have recent backups just in case. Are you sure you would like to delete this Field?\n\nClick ‘OK’ to continue, or ‘Cancel’ to make no changes.', 'pods' )
		);

		if ( confirmation ) {
			deleteField( groupID, id );
		}
	};

	const fieldRef = useRef( null );

	const [ { isDragging }, drag, preview ] = useDrag( {
		item: {
			...field,
			type: 'field-list-item',
		},
		collect: ( monitor ) => ( {
			isDragging: monitor.isDragging(),
		} ),
		end: () => setHasMoved( true ),
	} );

	const [ , drop ] = useDrop( {
		accept: 'field-list-item',
		hover( item, monitor ) {
			if ( ! fieldRef.current ) {
				return;
			}

			const dragIndex = item.index;
			const hoverIndex = index;

			// Don't replace items with themselves
			if ( dragIndex === hoverIndex ) {
				return;
			}
			// Determine rectangle on screen
			const hoverBoundingRect = fieldRef.current.getBoundingClientRect();

			// Get vertical middle
			const hoverMiddleY =
			( hoverBoundingRect.bottom - hoverBoundingRect.top ) / 2;

			// Determine mouse position
			const clientOffset = monitor.getClientOffset();

			// Get pixels to the top
			const hoverClientY = clientOffset.y - hoverBoundingRect.top;

			// Only perform the move when the mouse has crossed half of the items height
			// When dragging downwards, only move when the cursor is below 50%
			// When dragging upwards, only move when the cursor is above 50%
			// Dragging downwards
			if ( dragIndex < hoverIndex && hoverClientY < hoverMiddleY ) {
				return;
			}

			// Dragging upwards
			if ( dragIndex > hoverIndex && hoverClientY > hoverMiddleY ) {
				return;
			}

			// Time to actually perform the action
			moveField( item.id, hoverIndex );

			// Note: we're mutating the monitor item here!
			// Generally it's better to avoid mutations,
			// but it's good here for the sake of performance
			// to avoid expensive index searches.
			item.index = hoverIndex;
		},
	} );

	useEffect( () => {
		// Close the Field Settings modal if we finished saving.
		if ( SAVE_STATUSES.SAVE_SUCCESS === saveStatus ) {
			setShowEditFieldSettings( false );
		}
	}, [ saveStatus ] );

	useEffect( () => {
		// Reset the "unsaved" indicator after the pod has been saved.
		if ( SAVE_STATUSES.SAVE_SUCCESS === podSaveStatus ) {
			setHasMoved( false );
		}
	}, [ podSaveStatus ] );

	const classes = classnames(
		'pods-field_wrapper',
		{ 'pods-field_wrapper--unsaved': hasMoved }
	);

	return (
		<div className="pods-field_outer-wrapper" ref={ drop }>
			<div
				className={ classes }
				ref={ drag( preview( fieldRef ) ) }
				style={ { opacity: isDragging ? 0.4 : 1 } }
			>
				{ showEditFieldSettings && (
					<SettingsModal
						optionsPod={ editFieldPod }
						selectedOptions={ field }
						title={ sprintf(
							/* translators: %1$s: Pod Label, %2$s Group Label, %3$s Field Label */
							__( '%1$s > %2$s > %3$s > Edit Field', 'pods' ),
							podLabel,
							groupLabel,
							label
						) }
						hasSaveError={ saveStatus === SAVE_STATUSES.SAVE_ERROR }
						errorMessage={
							saveMessage ||
							__( 'There was an error saving the field, please try again.', 'pods' )
						}
						saveButtonText={ __( 'Save Field', 'pods' ) }
						cancelEditing={ onEditFieldCancel }
						save={ onEditFieldSave }
					/>
				) }

				<div className="pods-field pods-field_handle" ref={ drag }>
					<Dashicon icon="menu" />
				</div>

				<div className="pods-field pods-field_label">
					<span
						className="pods-field_label__link"
						tabIndex={ 0 }
						role="button"
						onClick={ onEditFieldClick }
						onKeyPress={ handleKeyPress }
					>
						{ label }
						{ required && ( <span className="pods-field_required">&nbsp;*</span> ) }
					</span>

					<div className="pods-field_id"> [id = { id }]</div>

					<div className="pods-field_controls-container">
						<button
							className="pods-field_button pods-field_edit"
							onClick={ onEditFieldClick }
						>
							{ __( 'Edit', 'pods' ) }
						</button>

						<button
							className="pods-field_button pods-field_duplicate"
							onClick={ ( e ) => {
								e.stopPropagation();
								cloneField( typeObject.type );
							} }
						>
							{ __( 'Duplicate', 'pods' ) }
						</button>

						<button
							className="pods-field_button pods-field_delete"
							onClick={ onDeleteFieldClick }
						>
							{ __( 'Delete', 'pods' ) }
						</button>
					</div>
				</div>

				<div
					tabIndex={ 0 }
					role="button"
					className="pods-field pods-field_name"
					onClick={ onEditFieldClick }
					onKeyPress={ handleKeyPress }
				>
					{ name }
				</div>

				<div className="pods-field pods-field_type">
					{ typeObject?.label }
					{ typeObject?.type && (
						<div className="pods-field_id"> [type = { typeObject.type }]</div>
					) }
					{ relatedObject?.label && (
						<div className="pods-field_related_object">
							&raquo; { relatedObject.label }
							<div className="pods-field_id"> [object = { relatedObject.name }]</div>
						</div>
					) }
				</div>
			</div>
		</div>
	);
};

FieldListItem.propTypes = {
	podID: PropTypes.number.isRequired,
	podLabel: PropTypes.string.isRequired,
	field: FIELD_PROP_TYPE_SHAPE,
	saveStatus: PropTypes.string,
	saveMessage: PropTypes.string,
	podSaveStatus: PropTypes.string.isRequired,
	index: PropTypes.number.isRequired,
	groupName: PropTypes.string.isRequired,
	groupLabel: PropTypes.string.isRequired,
	groupID: PropTypes.number.isRequired,
	typeObject: PropTypes.object.isRequired,
	relatedObject: PropTypes.object,
	editFieldPod: PropTypes.object.isRequired,

	saveField: PropTypes.func.isRequired,
	resetFieldSaveStatus: PropTypes.func.isRequired,
	moveField: PropTypes.func.isRequired,
	cloneField: PropTypes.func.isRequired,
	deleteField: PropTypes.func.isRequired,
};

export default compose( [
	withSelect( ( select, ownProps ) => {
		const {
			field,
		} = ownProps;

		const storeSelect = select( STORE_KEY_EDIT_POD );

		const relatedObjects = storeSelect.getFieldRelatedObjects();

		// eslint-disable-next-line camelcase
		const relatedObject = ( 'pick' === field?.type && field?.pick_object )
			? relatedObjects[ field.pick_object ]
			: null;

		return {
			editFieldPod: storeSelect.getGlobalFieldOptions(),
			relatedObject,
			typeObject: storeSelect.getFieldTypeObject( field.type ),
			podSaveStatus: storeSelect.getSaveStatus(),
			saveStatus: storeSelect.getFieldSaveStatus( field.name ),
			saveMessage: storeSelect.getFieldSaveMessage( field.name ),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			deleteAndRemoveField: ( groupID, fieldID ) => {
				storeDispatch.deleteField( fieldID );
				storeDispatch.removeGroupField( groupID, fieldID );
			},
			resetFieldSaveStatus: storeDispatch.resetFieldSaveStatus,
			saveField: storeDispatch.saveField,
		};
	} ),
] )( FieldListItem );
