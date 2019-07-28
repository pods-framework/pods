import React from 'react';
import * as PropTypes from 'prop-types';

import './manage-fields.scss';

// WordPress dependencies
// noinspection JSUnresolvedVariable
const { __ } = wp.i18n;
const { Dashicon } = wp.components;

export const FieldList = ( props ) => {

	if ( 0 === props.fields.length ) {
		return (
			<div className='pods-manage-fields no-fields'>
				{__( 'There are no fields in this group', 'pods' )}
			</div>
		);
	}

	return (
		<div className='pods-manage-fields'>
			<FieldHeader />
			{props.fields.map( field => (
				<FieldListItem
					key={field.id}
					id={field.id}
					fieldLabel={field.label}
					fieldName={field.name}
					required={field.required}
					type={field.type}
				/>
			) )}
			<FieldHeader />
		</div>
	);
};

FieldList.propTypes = {
	fields: PropTypes.array.isRequired,
};

/**
 *
 */
export const FieldListItem = ( props ) => {
	const { id, fieldName, fieldLabel, required, type } = props;

	return (
		<div className="pods-field_wrapper">
			<div className="pods-field pods-field_handle">
				<Dashicon icon='menu' />
			</div>
			<div className="pods-field pods-field_label">
				{fieldLabel}<span className={required && 'pods-field_required'}>*</span>
				<div className="pods-field_id"> [id = {id}]</div>
			</div>
			<div className="pods-field pods-field_name">
				{fieldName}
			</div>
			<div className="pods-field pods-field_type">
				{type}
				<div className="pods-field_id"> [type = [STILL NEED THIS]]</div>
			</div>
			<div className="pods-field pods-field_actions">
				<Dashicon icon='edit' /> <Dashicon icon='admin-page' />
				<Dashicon icon='trash' />
			</div>
		</div>
	);
};

FieldListItem.propTypes = {
	id: PropTypes.number.isRequired,
	fieldName: PropTypes.string.isRequired,
	fieldLabel: PropTypes.string.isRequired,
	required: PropTypes.string.isRequired,
	type: PropTypes.string.isRequired,
};

/**
 *
 */
export const FieldHeader = () => {
	return (
		<div className="pods-field_wrapper-labels">
			<div className="pods-field_wrapper-label-items">Label</div>
			<div className="pods-field_wrapper-label-items">Name</div>
			<div className="pods-field_wrapper-label-items">Field Type</div>
		</div>
	);
};
