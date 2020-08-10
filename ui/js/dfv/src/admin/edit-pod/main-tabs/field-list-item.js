import React, { useRef, useState, useEffect } from 'react';
import { useDrag, useDrop } from 'react-dnd';
import { omit } from 'lodash';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import { Dashicon, Button } from '@wordpress/components';
import { sprintf, __ } from '@wordpress/i18n';

// Internal dependencies
import SettingsModal from './settings-modal';

import { SAVE_STATUSES } from 'dfv/src/admin/edit-pod/store/constants';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/prop-types';

const ENTER_KEY = 13;

export const FieldListItem = ( props, ref ) => {
	const {
		podID,
		podLabel,
		field,
		saveStatus,
		index,
		editFieldPod,
		saveField,
		moveField,
		groupName,
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

	const wref = useRef( ref );

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
	};

	const onEditFieldSave = ( updatedOptions = {} ) => ( event ) => {
		event.stopPropagation();

		saveField(
			podID,
			groupName,
			updatedOptions.name || name,
			updatedOptions.label || label || name,
			updatedOptions.field_type || type,
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

	const [ , drop ] = useDrop( {
		accept: 'field-list-item',
		hover( item, monitor ) {
			if ( ! wref.current ) {
				return;
			}
			const dragIndex = item.index;
			const hoverIndex = index;
			// Don't replace items with themselves
			if ( dragIndex === hoverIndex ) {
				return;
			}
			// Determine rectangle on screen
			const hoverBoundingRect = wref.current.getBoundingClientRect();
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
			// console.log("movefield")
			// Time to actually perform the action
			moveField( name, dragIndex, hoverIndex, item );
			// Note: we're mutating the monitor item here!
			// Generally it's better to avoid mutations,
			// but it's good here for the sake of performance
			// to avoid expensive index searches.
			item.index = hoverIndex;
		},
	} );

	const [ { isDragging }, drag ] = useDrag( {
		item: { type: 'field-list-item', id, index, groupName, name },
		collect: ( monitor ) => ( {
			isDragging: monitor.isDragging(),
		} ),
	} );

	drag( drop( wref ) );

	useEffect( () => {
		// Close the Field Settings modal if we finished saving.
		if ( SAVE_STATUSES.SAVE_SUCCESS === saveStatus ) {
			setShowEditFieldSettings( false );
		}
	}, [ saveStatus ] );

	return (
		<div className="pods-field_wrapper" ref={ wref }>

			{ showEditFieldSettings && (
				<SettingsModal
					optionsPod={ editFieldPod }
					selectedOptions={ field }
					title={ sprintf(
						/* translators: %1$s: Pod Label, %2$s Field Label */
						__( '%1$s > %3$s > Edit Field', 'pods' ),
						podLabel,
						label,
					) }
					hasSaveError={ saveStatus === SAVE_STATUSES.SAVE_ERROR }
					errorMessage={ __( 'There was an error saving the field, please try again.', 'pods' ) }
					saveButtonText={ __( 'Save Field', 'pods' ) }
					cancelEditing={ onEditFieldCancel }
					save={ onEditFieldSave }
				/>
			) }

			<div className="pods-field pods-field_handle">
				<Dashicon icon="menu" />
			</div>

			<div className="pods-field pods-field_label">
				<span
					tabIndex={ 0 }
					role="button"
					onClick={ onEditFieldClick }
					style={ { cursor: 'pointer' } }
					onKeyPress={ handleKeyPress }
				>
					{ label }
					{ required && ( <span className="pods-field_required">&nbsp;*</span> ) }
				</span>

				<div className="pods-field_id"> [id = { id }]</div>

				<div className="pods-field_controls-container">
					<Button
						className="pods-field_button pods-field_edit"
						isTertiary
						onClick={ onEditFieldClick }
					>
						{ __( 'Edit', 'pods' ) }
					</Button>

					<Button
						className="pods-field_button pods-field_duplicate"
						onClick={ ( e ) => {
							e.stopPropagation();
							cloneField( type );
						} }
						isTertiary
					>
						{ __( 'Duplicate', 'pods' ) }
					</Button>

					<Button
						className="pods-field_button pods-field_delete"
						onClick={ onDeleteFieldClick }
						isTertiary
					>
						{ __( 'Delete', 'pods' ) }
					</Button>
				</div>
			</div>

			<div className="pods-field pods-field_name">
				{ name }
			</div>

			<div className="pods-field pods-field_type">
				{ type }
				<div className="pods-field_id"> [type = [STILL NEED THIS]]</div>
			</div>
		</div>
	);
};

FieldListItem.propTypes = {
	podID: PropTypes.number.isRequired,
	podLabel: PropTypes.string.isRequired,
	field: FIELD_PROP_TYPE_SHAPE,
	saveStatus: PropTypes.string,
	// position: PropTypes.number.isRequired,
	index: PropTypes.number.isRequired,
	groupName: PropTypes.string.isRequired,
	groupLabel: PropTypes.string.isRequired,
	groupID: PropTypes.number.isRequired,

	editFieldPod: PropTypes.object.isRequired,

	saveField: PropTypes.func.isRequired,
	moveField: PropTypes.func.isRequired,
	cloneField: PropTypes.func.isRequired,
	deleteField: PropTypes.func.isRequired,
};

export default FieldListItem;
