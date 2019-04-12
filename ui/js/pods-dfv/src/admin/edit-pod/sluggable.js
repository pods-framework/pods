/* eslint-disable react/prop-types */
import React from 'react';

export const PodsDFVSluggable = ( props ) => {
	return (
		<span className='pods-sluggable'>
			<span className='pods-slug'>
				<em>{props.value} </em>
				<input type='button' className='edit-slug-button button' value='Edit' />
			</span>
			<span className='-pods-slug-edit'>
				<input
					name='name'
					data-name-clean='name'
					id='pods-form-ui-name'
					className='pods-form-ui-field pods-form-ui-field-type-text pods-form-ui-field-name-name'
					type='text'
					value={props.value}
					onChange={props.onChange}
					maxLength='46'
					size='25'
				/>
				<input type='button' className='save-button button' value='OK' />
				<a className='cancel' href='#cancel-edit'>Cancel</a>
			</span>
		</span>
	);
};
