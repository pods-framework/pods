import React, { useState } from 'react';
import PropTypes from 'prop-types';

/* WordPress dependencies */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

import sanitizeSlug from 'dfv/src/helpers/sanitizeSlug';

const ENTER_KEY = 13;
const ESCAPE_KEY = 27;

// Helper components
const NotEditing = ( {
	handleEditClick,
	value,
} ) => {
	return (
		<span>
			<em
				role="button"
				tabIndex="0"
				onClick={ handleEditClick }
				style={ { cursor: 'pointer' } }
			>
				{ value }
			</em>
			{ '\u00A0' /* &nbsp; */ }
			<Button
				isSecondary
				onClick={ handleEditClick }
				aria-label={ __( 'Edit the slug', 'pods' ) }
			>
				{ __( 'Edit', 'pods' ) }
			</Button>
		</span>
	);
};

NotEditing.propTypes = {
	value: PropTypes.string.isRequired,
	handleEditClick: PropTypes.func.isRequired,
};

const Editing = ( {
	value,
	handleValueChange,
	handleOkClick,
	handleCancelClick,
} ) => {
	const handleFocus = ( event ) => event.target.select();

	const handleKeyDown = ( event ) => {
		// "Enter" or "Escape" keys
		if ( event.charCode === ENTER_KEY ) {
			handleOkClick();
		} else if ( event.charCode === ESCAPE_KEY ) {
			handleCancelClick();
		} else if ( event.charCode !== 0 ) {
			console.log( event.charCode );
		}
	};

	return (
		<span>
			<input
				type="text"
				id="pods-form-ui-name"
				name="name"
				className="pods-form-ui-field pods-form-ui-field-type-text pods-form-ui-field-name-name"
				value={ value }
				onKeyDown={ handleKeyDown }
				onChange={ ( event ) => handleValueChange( event.target.value ) }
				onFocus={ handleFocus }
				maxLength="46"
				size="25"
			/>
			{ '\u00A0' /* &nbsp; */ }
			<Button
				isSecondary
				onClick={ handleOkClick }
				aria-label={ __( 'Set the slug', 'pods' ) }
			>
				{ __( 'OK', 'pods' ) }
			</Button>
			{ '\u00A0' /* &nbsp; */ }
			<Button
				isTertiary
				isLink
				onClick={ handleCancelClick }
				aria-label={ __( 'Cancel editing the slug', 'pods' ) }
			>
				{ __( 'Cancel', 'pods' ) }
			</Button>
		</span>
	);
};

Editing.propTypes = {
	value: PropTypes.string.isRequired,
	handleValueChange: PropTypes.func.isRequired,
	handleOkClick: PropTypes.func.isRequired,
	handleCancelClick: PropTypes.func.isRequired,
};

const Sluggable = ( {
	value,
	updateValue,
} ) => {
	const [ editing, setEditing ] = useState( false );
	const [ localValue, setLocalValue ] = useState( value );

	const handleEditClick = () => {
		setEditing( true );
	};

	const handleOkClick = () => {
		setEditing( false );

		const cleanLocalValue = sanitizeSlug( localValue );

		// Don't allow an empty value, reset the old one
		// if that happens.
		if ( ! cleanLocalValue.length ) {
			setLocalValue( value );
			updateValue( value );
			return;
		}

		setLocalValue( cleanLocalValue );
		updateValue( cleanLocalValue );
	};

	const handleCancelClick = () => {
		setEditing( false );
		setLocalValue( value );
		updateValue( value );
	};

	if ( ! editing ) {
		return (
			<NotEditing
				value={ value }
				handleEditClick={ handleEditClick }
			/>
		);
	}
	return (
		<Editing
			value={ localValue }
			handleValueChange={ setLocalValue }
			handleOkClick={ handleOkClick }
			handleCancelClick={ handleCancelClick }
		/>
	);
};

Sluggable.propTypes = {
	value: PropTypes.string.isRequired,
	updateValue: PropTypes.func.isRequired,
};

export default Sluggable;
