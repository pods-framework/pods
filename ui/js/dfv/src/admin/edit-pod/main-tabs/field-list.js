import React from 'react';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/prop-types';

import FieldListItem from './field-list-item';
import './manage-fields.scss';
import './field-list.scss';

const FieldList = ( props ) => {
	const { groupName, addField, cloneField, deleteField, moveField } = props;

	if ( 0 === props.fields.length ) {
		return (
			<div className="pods-manage-fields no-fields">
				<p>{ __( 'There are no fields in this group.', 'pods' ) }</p>
				<Button
					isPrimary
					className="pods-field-group_add_field_link"
					onClick={ () => addField() }
				>
					{ __( 'Add Field', 'pods' ) }
				</Button>
			</div>
		);
	}

	return (
		<div className="pods-manage-fields">

			<Button
				isSecondary
				className="pods-field-group_add_field_link"
				onClick={ () => addField() } // TODO: This should add field to top of list, not the bottom
			>
				{ __( 'Add Field', 'pods' ) }
			</Button>

			<div className="pods-field_wrapper-labels">
				<div className="pods-field_wrapper-label">{ __( 'Label', 'pods' ) }</div>
				<div className="pods-field_wrapper-label_name">{ __( 'Name', 'pods' ) }</div>
				<div className="pods-field_wrapper-label_type">{ __( 'Type', 'pods' ) }</div>
			</div>

			<div className="pods-field_wrapper-items">
				{ props.fields.map( ( field, index ) => (
					<FieldListItem
						key={ field.id }
						field={ field }
						index={ index }
						// position={ field.position }
						moveField={ moveField }
						groupName={ groupName }
						cloneField={ cloneField }
						deleteField={ deleteField }
					/>
				) ) }
			</div>

			<Button
				isSecondary
				className="pods-field-group_add_field_link"
				onClick={ () => addField() }
			>
				{ __( 'Add Field', 'pods' ) }
			</Button>

		</div>
	);
};

FieldList.propTypes = {
	fields: PropTypes.arrayOf(
		FIELD_PROP_TYPE_SHAPE
	).isRequired,
	addField: PropTypes.func.isRequired,
	groupName: PropTypes.string.isRequired,
	cloneField: PropTypes.func.isRequired,
	deleteField: PropTypes.func.isRequired,
	moveField: PropTypes.func.isRequired,
};

export default FieldList;
