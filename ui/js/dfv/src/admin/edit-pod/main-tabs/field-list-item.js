import React, { useState, useEffect } from 'react';
import { omit } from 'lodash';
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Dashicon } from '@wordpress/components';
import { sprintf, __ } from '@wordpress/i18n';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import SettingsModal from './settings-modal';

import { SAVE_STATUSES, DELETE_STATUSES } from 'dfv/src/store/constants';

import {
	GROUP_PROP_TYPE_SHAPE,
	FIELD_PROP_TYPE_SHAPE,
} from 'dfv/src/config/prop-types';

import { toBool } from 'dfv/src/helpers/booleans';

import './field-list-item.scss';

const ENTER_KEY = 13;

export const FieldListItem = ( props ) => {
	const {
		storeKey,
		podType,
		podName,
		podID,
		podLabel,
		field,
		saveStatus,
		deleteStatus,
		saveMessage,
		typeObject,
		relatedObject,
		editFieldPod,
		saveField,
		resetFieldSaveStatus,
		groupName,
		groupLabel,
		groupID,
		hasMoved,
		cloneField,
		deleteField,
		removeFieldFromGroup,
		isDragging,
		isOverlay,
		style = {},
		draggableAttributes = {},
		draggableListeners = {},
		draggableSetNodeRef = () => {},
	} = props;

	const {
		id,
		name,
		label,
		type,
	} = field;

	const required = ( field.required && '0' !== field.required ) ? true : false;

	const isDeleting = DELETE_STATUSES.DELETING === deleteStatus;
	const hasDeleteFailed = DELETE_STATUSES.DELETE_ERROR === deleteStatus;

	const [ showEditFieldSettings, setShowEditFieldSettings ] = useState( false );

	const handleKeyPress = ( event ) => {
		if ( event.charCode === ENTER_KEY ) {
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

		if ( ! resetFieldSaveStatus ) {
			return;
		}

		resetFieldSaveStatus( name );
	};

	const onEditFieldSave = ( updatedOptions = {} ) => ( event ) => {
		event.stopPropagation();

		if ( ! saveField ) {
			return;
		}

		saveField(
			podID,
			groupID,
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
			deleteField();
		}
	};

	useEffect( () => {
		// Close the Field Settings modal if we finished saving.
		if ( SAVE_STATUSES.SAVE_SUCCESS === saveStatus ) {
			setShowEditFieldSettings( false );
		}
	}, [ saveStatus ] );

	useEffect( () => {
		if ( ! removeFieldFromGroup ) {
			return;
		}

		// After the field deletion is finished, remove the field from its group.
		if ( DELETE_STATUSES.DELETE_SUCCESS === deleteStatus ) {
			removeFieldFromGroup();
		}
	}, [ deleteStatus ] );

	const classes = classnames(
		'pods-field_wrapper',
		isDragging && 'pods-field_wrapper--dragging',
		isOverlay && 'pods-field_wrapper--overlay',
		hasMoved && 'pods-field_wrapper--unsaved',
		isDeleting && 'pods-field_wrapper--deleting',
		hasDeleteFailed && 'pods-field_wrapper--errored',
	);

	const isRepeatable = toBool( field?.repeatable );

	return (
		<div
			ref={ draggableSetNodeRef }
			className="pods-field_outer-wrapper"
			style={ style || {} }
		>
			<div className={ classes }>
				<div
					className="pods-field pods-field_handle"
					aria-label="drag"
					// eslint-disable-next-line react/jsx-props-no-spreading
					{ ...draggableListeners }
					// eslint-disable-next-line react/jsx-props-no-spreading
					{ ...draggableAttributes }
				>
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

					{ hasDeleteFailed ? (
						<div className="pods-field_controls-container__error">
							{ __( 'Delete failed. Try again?', 'pods' ) }
						</div>
					) : null }

					<div className="pods-field_controls-container">
						<button
							className="pods-field_button pods-field_edit"
							onClick={ onEditFieldClick }
						>
							{ __( 'Edit', 'pods' ) }
						</button>

						{ cloneField ? (
							<>
								|
								<button
									className="pods-field_button pods-field_duplicate"
									onClick={ ( e ) => {
										if ( ! typeObject ) {
											return;
										}

										e.stopPropagation();

										cloneField( typeObject.type );
									} }
								>
									{ __( 'Duplicate', 'pods' ) }
								</button>
							</>
						) : null }

						{ deleteField ? (
							<>
								|
								<button
									className="pods-field_button pods-field_delete"
									onClick={ onDeleteFieldClick }
								>
									{ __( 'Delete', 'pods' ) }
								</button>
							</>
						) : null }
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
					{ isRepeatable && (
						<span className="pods-field_repeatable"> ({ __( 'repeatable', 'pods' ) })</span>
					) }
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

			{ ( showEditFieldSettings && editFieldPod ) ? (
				<SettingsModal
					storeKey={ storeKey }
					podType={ podType }
					podName={ podName }
					optionsPod={ editFieldPod }
					selectedOptions={ field }
					title={ sprintf(
						// @todo Zack: Make these into elements we can style the parent pod / group label differently.
						/* translators: %1$s: Pod Label, %2$s Group Label, %3$s Field Label */
						__( '%1$s > %2$s > %3$s > Edit Field', 'pods' ),
						podLabel,
						groupLabel,
						label
					) }
					isSaving={ saveStatus === SAVE_STATUSES.SAVING }
					hasSaveError={ saveStatus === SAVE_STATUSES.SAVE_ERROR }
					errorMessage={
						saveMessage ||
						__( 'There was an error saving the field, please try again.', 'pods' )
					}
					saveButtonText={ __( 'Save Field', 'pods' ) }
					cancelEditing={ onEditFieldCancel }
					save={ onEditFieldSave }
				/>
			) : null }
		</div>
	);
};

FieldListItem.propTypes = {
	storeKey: PropTypes.string.isRequired,
	podType: PropTypes.string.isRequired,
	podName: PropTypes.string.isRequired,
	podID: PropTypes.number.isRequired,
	podLabel: PropTypes.string.isRequired,
	field: FIELD_PROP_TYPE_SHAPE,
	groupName: PropTypes.string.isRequired,
	groupLabel: PropTypes.string.isRequired,
	groupID: PropTypes.number.isRequired,
	hasMoved: PropTypes.bool,

	// Props from withSelect:
	editFieldPod: PropTypes.object,
	relatedObject: PropTypes.object,
	typeObject: PropTypes.object.isRequired,
	saveStatus: PropTypes.string,
	deleteStatus: PropTypes.string,
	saveMessage: PropTypes.string,

	// Props from withDispatch:
	saveField: PropTypes.func,
	resetFieldSaveStatus: PropTypes.func,
	cloneField: PropTypes.func,
	deleteField: PropTypes.func,
	removeFieldFromGroup: PropTypes.func,

	// Props from the DraggableFieldItem wrapper:
	isDragging: PropTypes.bool,
	style: PropTypes.object,
	draggableAttributes: PropTypes.object,
	draggableListeners: PropTypes.object,
	draggableSetNodeRef: PropTypes.func,

	// From FieldGroups (as drag overlay):
	isOverlay: PropTypes.bool,
};

const ConnectedFieldListItem = compose( [
	withSelect( ( select, ownProps ) => {
		const {
			field = {},
			storeKey,
		} = ownProps;

		const storeSelect = select( storeKey );

		// Look up the relatedObject, to find the key for it, we may have to combine
		// pick_object and pick_val
		let relatedObject;

		if ( 'pick' === field.type && field.pick_object ) {
			const key = field.pick_val
				? `${ field.pick_object }-${ field.pick_val }`
				: field.pick_object;

			relatedObject = storeSelect.getFieldRelatedObjects()[ key ];
		}

		// @todo This may be a temporary way to add the "Conditional Logic" screen
		// to the Field options.
		// @todo only add the Conditional Logic group for specific field types.
		const editFieldPod = {
			...(storeSelect.getGlobalFieldOptions()),
			groups: [
				...(storeSelect.getGlobalFieldOptions()?.groups || []),
				{
					id: '',
					label: __( 'Conditional Logic', 'pods' ),
					name: 'conditional-logic-group',
					fields: [
						{
							id: '',
							group: 'group/pod/_pods_field/conditional-logic',
							label: __( 'Enable Conditional Logic', 'pods' ),
							name: 'enable-conditional-logic',
							parent: 'pod/_pods_field',
							type: 'boolean',
						},
						{
							id: '',
							group: 'group/pod/_pods_field/conditional-logic',
							label: __( 'Conditional Logic', 'pods' ),
							name: 'conditional-logic',
							parent: 'pod/_pods_field',
							type: 'conditional-logic',
						},
					],
				},
			]
		};

		return {
			editFieldPod: editFieldPod,
			relatedObject,
			typeObject: storeSelect.getFieldTypeObject( field.type ),
			saveStatus: storeSelect.getFieldSaveStatus( field.name ),
			deleteStatus: storeSelect.getFieldDeleteStatus( field.name ),
			saveMessage: storeSelect.getFieldSaveMessage( field.name ),
		};
	} ),
	withDispatch( ( dispatch, ownProps ) => {
		const {
			storeKey,
			field: {
				id: fieldID,
				name: fieldName,
			},
			groupID,
		} = ownProps;

		const storeDispatch = dispatch( storeKey );

		return {
			deleteField: () => storeDispatch.deleteField( fieldID, fieldName ),
			removeFieldFromGroup: () => storeDispatch.removeGroupField( groupID, fieldID ),
			resetFieldSaveStatus: storeDispatch.resetFieldSaveStatus,
			saveField: storeDispatch.saveField,
		};
	} ),
] )( FieldListItem );

export default ConnectedFieldListItem;
