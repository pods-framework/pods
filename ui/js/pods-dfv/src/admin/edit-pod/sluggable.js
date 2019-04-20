/* eslint-disable react/prop-types */
import React from 'react';

const { useState } = React;

/* WordPress dependencies */
// noinspection JSUnresolvedVariable
const { __ } = wp.i18n;

export const PodsDFVSluggable = ( props ) => {
	const [ editing, setEditing ] = useState( false );
	const [ localValue, setLocalValue ] = useState( props.value );

	const handleValueChange = ( newValue ) => {
		setLocalValue( newValue );
	};

	const handleEditClick = () => {
		setEditing( true );
	};

	const handleOkClick = () => {
		setEditing( false );
		props.updateValue( localValue );
	};

	const handleCancelClick = ( e ) => {
		e.preventDefault();
		setEditing( false );
		setLocalValue( props.value );
	};

	if ( !editing ) {
		return (
			<NotEditing
				value={props.value}
				handleEditClick={handleEditClick}
			/>
		);
	} else {
		return (
			<Editing
				value={localValue}
				handleValueChange={handleValueChange}
				handleOkClick={handleOkClick}
				handleCancelClick={handleCancelClick}
			/>
		);
	}
};

const NotEditing = ( props ) => {
	return (
		<span>
			<em
				onClick={props.handleEditClick}
				style={{ cursor: 'pointer' }}>
				{props.value}
			</em>
			{'\u00A0' /* &nbsp; */}
			<input
				type='button'
				className='edit-slug-button button'
				value={__( 'Edit', 'pods' )}
				onClick={props.handleEditClick}
			/>
		</span>
	);
};

const Editing = ( props ) => {
	const handleFocus = ( e ) => e.target.select();

	return (
		<span>
			<input
				type='text'
				autoFocus
				id='pods-form-ui-name'
				name='name'
				className='pods-form-ui-field pods-form-ui-field-type-text pods-form-ui-field-name-name'
				value={props.value}
				onChange={( e ) => props.handleValueChange( e.target.value )}
				onFocus={handleFocus}
				maxLength='46'
				size='25'
			/>
			{'\u00A0' /* &nbsp; */}
			<input
				type='button'
				className='save-button button'
				value={__( 'OK', 'pods' )}
				onClick={props.handleOkClick}
			/>
			{'\u00A0' /* &nbsp; */}
			<a
				className='cancel'
				href='#cancel-edit'
				onClick={props.handleCancelClick}>
				{__( 'Cancel', 'pods' )}
			</a>
		</span>
	);
};
