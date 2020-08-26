import React, { useState, useEffect } from 'react';
import * as PropTypes from 'prop-types';
import classnames from 'classnames';
import { omit } from 'lodash';

// WordPress dependencies
import { sprintf, __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import {
	STORE_KEY_EDIT_POD,
	SAVE_STATUSES,
} from 'dfv/src/admin/edit-pod/store/constants';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/prop-types';

// Internal dependencies
import SettingsModal from './settings-modal';
import FieldListItem from './field-list-item';

import './manage-fields.scss';
import './field-list.scss';

const FieldList = ( {
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
	setGroupFields,
} ) => {
	const [ showAddFieldModal, setShowAddFieldModal ] = useState( false );
	const [ newFieldOptions, setNewFieldOptions ] = useState( {} );
	const [ addedFieldName, setAddedFieldName ] = useState( null );

	const handleAddField = ( options = {} ) => ( event ) => {
		event.stopPropagation();

		setAddedFieldName( options.name );
		setNewFieldOptions( {} );

		saveField(
			podID,
			groupName,
			options.name,
			options.name,
			options.label,
			options.type,
			omit( options, [ 'name', 'label', 'id', 'field_type' ] )
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

		setShowAddFieldModal( true );
	};

	const findField = ( id ) => {
		return {
			field: fields.find( ( item ) => item.id === id ),
			index: fields.findIndex( ( item ) => item.id === id ),
		};
	};

	const moveField = ( id, atIndex ) => {
		const { field, index } = findField( id );

		const remainingItems = fields.filter( ( item, itemIndex ) => index !== itemIndex );

		const reorderedItems = [
			...remainingItems.slice( 0, atIndex ),
			field,
			...remainingItems.slice( atIndex ),
		];

		setGroupFields( groupName, reorderedItems );
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
		'pods-manage-fields',
		{ 'no-fields': isEmpty }
	);

	return (
		<div className={ classes }>
			{ showAddFieldModal && (
				<SettingsModal
					optionsPod={ editFieldPod }
					selectedOptions={ newFieldOptions }
					title={ sprintf(
						/* translators: %1$s: Pod Label, %2$s Group Label */
						__( '%1$s > %2$s > Add Field', 'pods' ),
						podLabel,
						groupLabel,
					) }
					hasSaveError={
						fieldSaveStatuses[ addedFieldName ] === SAVE_STATUSES.SAVE_ERROR ||
						false
					}
					saveButtonText={ __( 'Save New Field', 'pods' ) }
					errorMessage={
						fieldSaveMessages[ addedFieldName ] ||
						__( 'There was an error saving the field, please try again.', 'pods' )
					}
					cancelEditing={ () => {
						setShowAddFieldModal( false );
						setAddedFieldName( null );
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

					<div className="pods-field_wrapper-items">
						{ fields.map( ( field, index ) => {
							return (
								<FieldListItem
									key={ field.id }
									podID={ podID }
									podLabel={ podLabel }
									groupLabel={ groupLabel }
									field={ field }
									index={ index }
									moveField={ moveField }
									groupName={ groupName }
									groupID={ groupID }
									cloneField={ handleCloneField( field ) }
								/>
							);
						} ) }
					</div>

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
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		return {
			editFieldPod: storeSelect.getGlobalFieldOptions(),
			fieldSaveStatuses: storeSelect.getFieldSaveStatuses(),
			fieldSaveMessages: storeSelect.getFieldSaveMessages(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			setGroupFields: storeDispatch.setGroupFields,
			saveField: storeDispatch.saveField,
		};
	} ),
] )( FieldList );

