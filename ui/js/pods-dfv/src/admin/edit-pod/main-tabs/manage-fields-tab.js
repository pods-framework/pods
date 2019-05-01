import React from 'react';
import PropTypes from 'prop-types';

// noinspection JSUnresolvedVariable
const { __ } = wp.i18n;

export const ManageFieldsTab = ( props ) => {
	return (
		<div id='pods-manage-fields'>
			<p className='pods-manage-row-add pods-float-right'>
				<a href='#add-field' className='button-primary'>
					{__( 'Add Field', 'pods' )}
				</a>
			</p>
			<h2>{__( 'Manage Fields', 'pods' )}</h2>
			<PodsTableFieldList fields={props.fields} />
		</div>
	);
};
ManageFieldsTab.propTypes = {
	fields: PropTypes.array,
};

/**
 * PodsTableFieldList
 */
const PodsTableFieldList = ( props ) => {
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
					<PodsTableFieldItem
						key={thisField.id}
						id={thisField.id}
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

/**
 * PodsTableFieldItem
 */
const PodsTableFieldItem = ( props ) => {
	return (
		<tr className='pods-manage-row pods-field-init'>
			<th scope='row' className='check-field pods-manage-sort'>
				<img
					src={`${PODS_URL}/ui/images/handle.gif`}
					alt={__( 'Move', 'pods' )}
				/>
			</th>
			<td className='pods-manage-row-label'>
				<strong>
					<a
						title={__( 'Edit this field', 'pods' )}
						className='pods-manage-row-edit row-label'
						href='#edit-field'>
						{ props.fieldLabel }
					</a>
					{'\u00A0' /* &nbsp; */}
					<abbr
						title='required'
						className={'required' + ( '1' === props.required ? '' : ' hidden' )}>
						*
					</abbr>
				</strong>
				<span className='pods-manage-row-more'>{ `[id: ${props.id}]` }</span>
				<RowActions />
			</td>
			<td className='pods-manage-row-name'>
				<a
					title={__( 'Edit this field', 'pods' )}
					className='pods-manage-row-edit row-name'
					href='#edit-field'>
					{props.fieldName}
				</a>
			</td>
			<td className='pods-manage-row-type'>
				{props.type}
			</td>
		</tr>
	);
};

/**
 * RowActions
 */
const RowActions = () => {
	return (
		<div className='row-actions'>
			<span className='edit'>
				<a
					title={__( 'Edit this field', 'pods' )}
					className='pods-manage-row-edit'
					href='#edit-field'>
					{__( 'Edit', 'pods' )}
				</a> |
			</span> <span className='duplicate'>
				<a
					title={_( 'Duplicate this field', 'pods' )}
					className='pods-manage-row-duplicate'
					href='#duplicate-field'>
					{__( 'Duplicate', 'pods' )}
				</a> |
			</span> <span className='trash pods-manage-row-delete'>
				<a
					className='submitdelete'
					title={__( 'Delete this field', 'pods' )}
					href='#delete-field'>
					{__( 'Delete', 'pods' )}
				</a>
			</span>
		</div>
	);
};
