import React from 'react';
import PropTypes from 'prop-types';

const { Dashicon } = wp.components;

import './manage-fields.scss';

export const ManageFields = ( props ) => {

	return (
		<div className='pods-manage-fields'>
			<FieldHeader />

			{props.fields.map( thisField => (
				<FieldRow
					key={thisField.id}
					id={thisField.id}
					fieldLabel={thisField.label}
					fieldName={thisField.name}
					required={thisField.required}
					type={thisField.type}
				/>
			) ) }
			<FieldHeader />
		</div>
	);
};
ManageFields.propTypes = {
	fields: PropTypes.array.isRequired,
};

/**
 *
 */
export const FieldHeader = () => {
	return (
		<div className="pods-field--wrapper-labels">
			<div className="pods-field--wrapper-label-items">Label</div>
			<div className="pods-field--wrapper-label-items">Name</div>
			<div className="pods-field--wrapper-label-items">Field Type</div>
		</div>
	);
};

/**
 *
 */
export const FieldRow = ( props ) => {
	const { id, fieldName, fieldLabel, required, type } = props;

	return (
		<div className="pods-field--wrapper">
			<div className="pods-field pods-field--handle">
				<Dashicon icon='menu' />
			</div>
			<div className="pods-field pods-field--label">
				{fieldLabel}<span className={required && 'pods-field--required'}>*</span>
				<div className="pods-field--id">[id = {id}]</div>
			</div>
			<div className="pods-field pods-field--name">
				{fieldName}
			</div>
			<div className="pods-field pods-field--type">
				{type}
				<div className="pods-field--id">[type = [STILL NEED THIS]]</div>
			</div>
			<div className="pods-field pods-field--actions">
				<Dashicon icon='edit' />
				<Dashicon icon='admin-page' />
				<Dashicon icon='trash' />
			</div>
		</div>
	);
};

FieldRow.propTypes = {
	id: PropTypes.number.isRequired,
	fieldName: PropTypes.string.isRequired,
	fieldLabel: PropTypes.string.isRequired,
	required: PropTypes.string.isRequired,
	type: PropTypes.string.isRequired,
};
