/* eslint-disable react/prop-types */
import React from 'react';
const { __ } = wp.i18n;
import { PodsDFVFieldRowActions } from 'pods-dfv/src/admin/edit-pod/manage-fields/field-row-actions';

export const PodsDFVTableFieldItem = ( props ) => {
	return (
		<tr className='pods-manage-row pods-field-init'>
			<th scope='row' className='check-field pods-manage-sort'>
				<img src={`${PODS_URL}/ui/images/handle.gif`} alt={__( 'Move', 'pods' )} />
			</th>
			<td className='pods-manage-row-label'>
				<strong>
					<a
						title={__( 'Edit this field', 'pods' )}
						onClick={( e ) => props.onFieldEditClick( e, props.id )}
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
				<PodsDFVFieldRowActions
					onFieldEditClick={props.onFieldEditClick}
					id={props.id}
				/>
			</td>
			<td className='pods-manage-row-name'>
				<a
					title={__( 'Edit this field', 'pods' )}
					onClick={( e ) => props.onFieldEditClick( e, props.id )}
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
