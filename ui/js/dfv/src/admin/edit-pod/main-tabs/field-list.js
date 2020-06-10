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

import SettingsModal from './settings-modal';
import FieldListItem from './field-list-item';

import './manage-fields.scss';
import './field-list.scss';

const FieldList = ( props ) => {
	const {
		podID,
		podName,
		groupName,
		groupLabel,
		groupID,
		fieldSaveStatuses,
		editFieldPod,
		saveField,
		deleteAndRemoveField,
		fields,
		setGroupFields,
	} = props;

	const [ showAddFieldModal, setShowAddFieldModal ] = useState( false );
	const [ addedFieldName, setAddedFieldName ] = useState( null );

	const handleAddField = ( options = {} ) => ( event ) => {
		event.stopPropagation();

		console.log( 'handleAddField', options );

		setAddedFieldName( options.name );

		// @todo we're not getting Default values when this happens.
		saveField(
			podID,
			groupName,
			options.name,
			options.label,
			options.field_type,
			omit( options, [ 'name', 'label', 'id', 'field_type' ] )
		);
	};

	// @todo
	const handleCloneField = () => {};

	const moveField = () => ( field, dragIndex, hoverIndex, item ) => {
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
		}
	};

	// Close the modal after a new field has been successfully added.
	useEffect( () => {
		if (
			!! addedFieldName &&
			fieldSaveStatuses[ addedFieldName ] === SAVE_STATUSES.SAVE_SUCCESS
		) {
			setShowAddFieldModal( false );
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
					selectedOptions={ {} }
					title={ sprintf(
						/* translators: %1$s: Pod Label, %2$s Group Label */
						__( '%1$s > %2$s > Add Group', 'pods' ),
						podName,
						groupLabel,
					) }
					hasSaveError={ fieldSaveStatuses[ addedFieldName ] === SAVE_STATUSES.SAVE_ERROR || false }
					errorMessage={ __( 'There was an error saving the field, please try again.', 'pods' ) }
					cancelEditing={ () => setShowAddFieldModal( false ) }
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
						{ fields.map( ( field, index ) => (
							<FieldListItem
								key={ field.id }
								field={ field }
								index={ index }
								// position={ field.position }
								moveField={ moveField }
								groupName={ groupName }
								groupID={ groupID }
								cloneField={ handleCloneField }
								deleteField={ deleteAndRemoveField }
							/>
						) ) }
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
	podName: PropTypes.string.isRequired,
	podID: PropTypes.number.isRequired,
	groupName: PropTypes.string.isRequired,
	groupLabel: PropTypes.string.isRequired,
	groupID: PropTypes.number.isRequired,
	fields: PropTypes.arrayOf(
		FIELD_PROP_TYPE_SHAPE
	).isRequired,
	fieldSaveStatuses: PropTypes.object.isRequired,
	editFieldPod: PropTypes.object.isRequired,
	deleteAndRemoveField: PropTypes.func.isRequired,
	saveField: PropTypes.func.isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		return {
			editFieldPod: storeSelect.getGlobalFieldOptions(),
			fieldSaveStatuses: storeSelect.getFieldSaveStatuses(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			setGroupFields: storeDispatch.setGroupFields,
			deleteAndRemoveField: ( groupID, fieldID ) => {
				storeDispatch.deleteField( fieldID );
				storeDispatch.removeGroupField( groupID, fieldID );
			},
			saveField: storeDispatch.saveField,
		};
	} ),
] )( FieldList );

