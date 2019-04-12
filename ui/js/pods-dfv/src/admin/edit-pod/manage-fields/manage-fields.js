/* eslint-disable react/prop-types */
import React from 'react';
import { PodsDFVTableFields } from 'pods-dfv/src/admin/edit-pod/manage-fields/pods-table-fields';
const { __ } = wp.i18n;

export const PodsDFVManageFields = ( props ) => {
	return (
		<div id='pods-manage-fields' className='-pods-nav-tab'>
			<p className='pods-manage-row-add pods-float-right'>
				<a href='#add-field' className='button-primary'>{__( 'Add Field', 'pods' )}</a>
			</p>

			<PodsDFVTableFields
				fields={props.fields}
				onFieldEditClick={props.onFieldEditClick}
			/>
		</div>

	);
};
