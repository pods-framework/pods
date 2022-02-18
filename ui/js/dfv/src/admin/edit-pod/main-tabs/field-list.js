import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { omit } from 'lodash';
import {
	SortableContext,
	verticalListSortingStrategy,
} from '@dnd-kit/sortable';

/**
 * WordPress dependencies
 */
import { sprintf, __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { SAVE_STATUSES } from 'dfv/src/store/constants';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

/**
 * Internal dependencies
 */
import SettingsModal from './settings-modal';
import DraggableFieldListItem from './draggable-field-list-item';

import './field-list.scss';

const FieldList = ( {
	storeKey,
	podType,
	podName,
	podID,
	podLabel,
	groupName,
	groupLabel,
	groupID,
	fieldSaveStatuses,
	fieldSaveMessages,
	editFieldPod,
	saveField,
	fields,
	fieldsMovedSinceLastSave,
} ) => {
	const [ showAddFieldModal, setShowAddFieldModal ] = useState( false );
	const [ newFieldOptions, setNewFieldOptions ] = useState( {} );
	const [ newFieldIndex, setNewFieldIndex ] = useState( null );
	const [ addedFieldName, setAddedFieldName ] = useState( null );

	const handleAddField = ( options = {} ) => ( event ) => {
		event.stopPropagation();

		setAddedFieldName( options.name );
		setNewFieldOptions( {} );
		setNewFieldIndex( null );

		saveField(
			podID,
			groupID,
			groupName,
			options.name,
			options.name,
			options.label,
			options.type,
			omit( options, [ 'name', 'label', 'id', 'field_type' ] ),
			undefined,
			newFieldIndex
		);
	};

	const handleCloneField = ( field ) => () => {
		setNewFieldOptions(
			{
				...omit( field, [ 'id', 'group' ] ),
				/* translators: %1$s: Field Label */
				label: sprintf( __( '%1$s (Copy)', 'pods' ), field.label ),
				name: `${ field.name }_copy`,
			}
		);

		const originalFieldIndex = fields.findIndex(
			( searched ) => searched.name === field.name
		);

		setNewFieldIndex( originalFieldIndex + 1 );

		setShowAddFieldModal( true );
	};

	// Close the modal after a new field has been successfully added.
	useEffect( () => {
		if (
			!! addedFieldName &&
			fieldSaveStatuses[ addedFieldName ] === SAVE_STATUSES.SAVE_SUCCESS
		) {
			setShowAddFieldModal( false );
			setAddedFieldName( null );
			setNewFieldOptions( {} );
		}
	}, [ addedFieldName, setShowAddFieldModal, fieldSaveStatuses ] );

	const isEmpty = 0 === fields.length;

	const classes = classnames(
		'pods-field-list',
		isEmpty && 'pods-field-list--no-fields',
	);

	return (
		<div className={ classes }>
			{ showAddFieldModal && (
				<SettingsModal
					storeKey={ storeKey }
					podType={ podType }
					podName={ podName }
					optionsPod={ editFieldPod }
					selectedOptions={ newFieldOptions }
					title={ sprintf(
						// @todo Zack: Make these into elements we can style the parent pod / group label differently.
						/* translators: %1$s: Pod Label, %2$s Group Label */
						__( '%1$s > %2$s > Add Field', 'pods' ),
						podLabel,
						groupLabel,
					) }
					isSaving={ fieldSaveStatuses[ addedFieldName ] === SAVE_STATUSES.SAVING }
					hasSaveError={ fieldSaveStatuses[ addedFieldName ] === SAVE_STATUSES.SAVE_ERROR }
					saveButtonText={ __( 'Save New Field', 'pods' ) }
					errorMessage={
						fieldSaveMessages[ addedFieldName ] ||
						__( 'There was an error saving the field, please try again.', 'pods' )
					}
					cancelEditing={ () => {
						setShowAddFieldModal( false );
						setAddedFieldName( null );
						setNewFieldIndex( null );
						setNewFieldOptions( {} );
					} }
					save={ handleAddField }
				/>
			) }

			{ isEmpty ? (
				<>
					<p>{ __( 'There are no fields in this group.', 'pods' ) }</p>

					<Button
						isPrimary
						className="pods-field-group_add_field_link"
						onClick={ () => setShowAddFieldModal( true ) }
					>
						{ __( 'Add Field', 'pods' ) }
					</Button>
				</>
			) : (
				<>
					<Button
						isSecondary
						className="pods-field-group_add_field_link"
						onClick={ () => setShowAddFieldModal( true ) }
					>
						{ __( 'Add Field', 'pods' ) }
					</Button>

					<div className="pods-field_wrapper-labels">
						<div className="pods-field_wrapper-label">{ __( 'Label', 'pods' ) }</div>
						<div className="pods-field_wrapper-label_name">{ __( 'Name', 'pods' ) }</div>
						<div className="pods-field_wrapper-label_type">{ __( 'Type', 'pods' ) }</div>
					</div>

					<SortableContext
						id={ groupName }
						items={ fields.map( ( field ) => field.id.toString() ) }
						strategy={ verticalListSortingStrategy }
					>
						<div className="pods-field_wrapper-items">
							{ fields.map( ( field ) => {
								return (
									<DraggableFieldListItem
										storeKey={ storeKey }
										podType={ podType }
										podName={ podName }
										key={ field.id }
										podID={ podID }
										podLabel={ podLabel }
										groupLabel={ groupLabel }
										field={ field }
										groupName={ groupName }
										groupID={ groupID }
										cloneField={ handleCloneField( field ) }
										hasMoved={
											-1 !== fieldsMovedSinceLastSave.findIndex( ( id ) => id.toString() === field.id.toString() )
										}
									/>
								);
							} ) }
						</div>
					</SortableContext>

					<Button
						isSecondary
						className="pods-field-group_add_field_link"
						onClick={ () => setShowAddFieldModal( true ) }
					>
						{ __( 'Add Field', 'pods' ) }
					</Button>
				</>
			) }
		</div>
	);
};

FieldList.propTypes = {
	storeKey: PropTypes.string.isRequired,
	podType: PropTypes.string.isRequired,
	podName: PropTypes.string.isRequired,
	podLabel: PropTypes.string.isRequired,
	podID: PropTypes.number.isRequired,
	groupName: PropTypes.string.isRequired,
	groupLabel: PropTypes.string.isRequired,
	groupID: PropTypes.number.isRequired,
	fields: PropTypes.arrayOf(
		FIELD_PROP_TYPE_SHAPE
	).isRequired,
	fieldSaveStatuses: PropTypes.object.isRequired,
	fieldSaveMessages: PropTypes.object.isRequired,
	editFieldPod: PropTypes.object.isRequired,
	saveField: PropTypes.func.isRequired,
	fieldsMovedSinceLastSave: PropTypes.array.isRequired,
};

export default compose( [
	withSelect( ( select, ownProps ) => {
		const { storeKey } = ownProps;

		const storeSelect = select( storeKey );

		return {
			editFieldPod: storeSelect.getGlobalFieldOptions(),
			fieldSaveStatuses: storeSelect.getFieldSaveStatuses(),
			fieldSaveMessages: storeSelect.getFieldSaveMessages(),
		};
	} ),
	withDispatch( ( dispatch, ownProps ) => {
		const { storeKey } = ownProps;

		const storeDispatch = dispatch( storeKey );

		return {
			saveField: storeDispatch.saveField,
		};
	} ),
] )( FieldList );

