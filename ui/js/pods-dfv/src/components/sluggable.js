import React, { useState } from 'react';
import * as PropTypes from 'prop-types';
import sanitizeHtml from 'sanitize-html';

/* WordPress dependencies */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { cleanForSlug } from '@wordpress/editor';

const ENTER_KEY = 13;
const ESCAPE_KEY = 27;

// Helper functions
const sanitizeSlug = ( value ) => cleanForSlug( sanitizeHtml( value, { allowedTags: [] } ) );

// Helper components
const NotEditing = ( {
	handleEditClick,
	value,
} ) => {
	const handleKeyPress = ( event ) => {
		if ( event.keyCode === ENTER_KEY ) {
			handleEditClick();
		}
	};

	return (
		<span>
			<em
				role="button"
				tabIndex="0"
				onClick={ handleEditClick }
				style={ { cursor: 'pointer' } }
				onKeyPress={ handleKeyPress }
			>
				{ value }
			</em>
			{ '\u00A0' /* &nbsp; */ }
			<Button
				isSecondary
				onClick={ handleEditClick }
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
		if ( event.keyCode === ENTER_KEY ) {
			handleOkClick();
		} else if ( event.keyCode === ESCAPE_KEY ) {
			handleCancelClick();
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
			>
				{ __( 'OK', 'pods' ) }
			</Button>
			{ '\u00A0' /* &nbsp; */ }
			<Button
				isTertiary
				isLink
				onClick={ handleCancelClick }
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
		}

		updateValue( cleanLocalValue );
	};

	const handleCancelClick = () => {
		setEditing( false );
		setLocalValue( value );
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
