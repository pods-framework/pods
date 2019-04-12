/* eslint-disable react/prop-types */
import React from 'react';

const { __ } = wp.i18n;

export const PodsDFVFieldRowActions = ( props ) => {
	return (
		<div className='row-actions'>
			<span className='edit'>
				<a
					title={__( 'Edit this field', 'pods' )}
					onClick={( e ) => props.onFieldEditClick( e, props.id )}
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
