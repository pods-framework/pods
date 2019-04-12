/* eslint-disable react/prop-types */
import React from 'react';
const { __ } = wp.i18n;
import { PodsDFVTableFieldItem } from 'pods-dfv/src/admin/edit-pod/manage-fields/field-item';

export const PodsDFVTableFields = ( props ) => {

	return (
		<table className='widefat fixed pages'>
			<thead>
				<tr>
					<th scope='col' id='cb' className='manage-column field-cb check-column'>
						<span>&nbsp;</span>
					</th>
					<th scope='col' id='label' className='manage-column field-label'>
						<span>{__( 'Label', 'pods' )}</span>
					</th>
					<th scope='col' id='machine-name' className='manage-column field-machine-name'>
						<span>{__( 'Name', 'pods' )}</span>
					</th>
					<th scope='col' id='field-type' className='manage-column field-field-type'>
						<span>{__( 'Field Type', 'pods' )}</span>
					</th>
				</tr>
			</thead>
			<tbody className='pods-manage-list'>
				{props.fields.map( thisField => (
					<PodsDFVTableFieldItem
						key={thisField.id}
						id={thisField.id}
						onFieldEditClick={props.onFieldEditClick}
						fieldLabel={thisField.label}
						fieldName={thisField.name}
						required={thisField.required}
						type={thisField.type}
					/>
				) ) }
			</tbody>
			<tfoot>
				<tr>
					<th scope='col' className='manage-column field-cb check-column'>
						<span>&nbsp;</span>
					</th>
					<th scope='col' className='manage-column field-label'>
						<span>{__( 'Label', 'pods' )}</span>
					</th>
					<th scope='col' className='manage-column field-machine-name'>
						<span>{__( 'Name', 'pods' )}</span>
					</th>
					<th scope='col' className='manage-column field-field-type'>
						<span>{__( 'Field Type', 'pods' )}</span>
					</th>
				</tr>
			</tfoot>
		</table>
	);
};
