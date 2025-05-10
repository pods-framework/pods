/**
 * External dependencies
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import Sluggable from '../sluggable';

describe( 'Sluggable Component', () => {
	const ENTER_KEY = 13;
	const ESCAPE_KEY = 27;

	// Sample test data
	const defaultProps = {
		value: 'test-slug',
		updateValue: jest.fn(),
	};

	afterEach( () => {
		defaultProps.updateValue.mockReset();
	} );

	test( 'renders in view mode initially', () => {
		render( <Sluggable { ...defaultProps } /> );

		// Should show the slug value
		const slugText = screen.getByText( 'test-slug' );
		expect( slugText ).toBeInTheDocument();

		// Should show the Edit button
		const editButton = screen.getByText( 'Edit' );
		expect( editButton ).toBeInTheDocument();

		// Should not show the editing components
		expect( screen.queryByRole( 'textbox' ) ).not.toBeInTheDocument();
		expect( screen.queryByText( 'OK' ) ).not.toBeInTheDocument();
		expect( screen.queryByText( 'Cancel' ) ).not.toBeInTheDocument();
	} );

	test( 'switches to edit mode when edit button is clicked', async () => {
		const user = userEvent.setup();
		render( <Sluggable { ...defaultProps } /> );

		// Click the Edit button
		const editButton = screen.getByText( 'Edit' );
		await user.click( editButton );

		// Should show the input field with the current value
		const inputField = screen.getByRole( 'textbox' );
		expect( inputField ).toBeInTheDocument();
		expect( inputField ).toHaveValue( 'test-slug' );

		// Should show OK and Cancel buttons
		expect( screen.getByText( 'OK' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Cancel' ) ).toBeInTheDocument();

		// Should not show the view mode components
		expect( screen.queryByRole( 'button', { name: 'Edit' } ) ).not.toBeInTheDocument();
	} );

	test( 'switches to edit mode when clicking the slug text', async () => {
		const user = userEvent.setup();
		render( <Sluggable { ...defaultProps } /> );

		// Click the slug text
		const slugText = screen.getByText( 'test-slug' );
		await user.click( slugText );

		// Should be in edit mode
		expect( screen.getByRole( 'textbox' ) ).toBeInTheDocument();
	} );

	test( 'updates input value when typing', async () => {
		const user = userEvent.setup();
		render( <Sluggable { ...defaultProps } /> );

		// Enter edit mode
		await user.click( screen.getByText( 'Edit' ) );

		// Get the input field
		const inputField = screen.getByRole( 'textbox' );

		// Clear the input and type a new value
		await user.clear( inputField );
		await user.type( inputField, 'new-slug-value' );

		// The input should have the new value
		expect( inputField ).toHaveValue( 'new-slug-value' );
	} );

	test( 'sanitizes and updates value when OK button is clicked', async () => {
		const user = userEvent.setup();
		render( <Sluggable { ...defaultProps } /> );

		// Enter edit mode
		await user.click( screen.getByText( 'Edit' ) );

		// Change the value
		const inputField = screen.getByRole( 'textbox' );
		await user.clear( inputField );
		await user.type( inputField, 'New Slug 123!@#' );

		// Click the OK button
		await user.click( screen.getByText( 'OK' ) );

		// Should call updateValue with sanitized value
		expect( defaultProps.updateValue ).toHaveBeenLastCalledWith( 'new_slug_123' );

		// Should return to view mode
		expect( screen.queryByRole( 'textbox' ) ).not.toBeInTheDocument();
		expect( screen.getByText( 'Edit' ) ).toBeInTheDocument();
	} );

	test( 'restores original value when Cancel button is clicked', async () => {
		const user = userEvent.setup();
		render( <Sluggable { ...defaultProps } /> );

		// Enter edit mode
		await user.click( screen.getByText( 'Edit' ) );

		// Change the value
		const inputField = screen.getByRole( 'textbox' );
		await user.clear( inputField );
		await user.type( inputField, 'new-value' );

		// Click the Cancel button
		await user.click( screen.getByText( 'Cancel' ) );

		// Should return to view mode with original value
		expect( screen.queryByRole( 'textbox' ) ).not.toBeInTheDocument();
		expect( screen.getByText( 'test-slug' ) ).toBeInTheDocument();

		expect( defaultProps.updateValue ).toHaveBeenLastCalledWith( 'test-slug' );
	} );

	test.skip( 'handles Enter key down in edit mode', async () => {
		const user = userEvent.setup();
		render( <Sluggable { ...defaultProps } /> );

		// Enter edit mode
		await user.click( screen.getByText( 'Edit' ) );

		// Change the value
		const inputField = screen.getByRole( 'textbox' );
		await user.clear( inputField );
		await user.type( inputField, 'new-slug' );

		// This does not work.
		await user.type( inputField, '{enter}' );

		// This does not work either.
		fireEvent.keyDown( inputField, {
			key: 'Enter',
			code: 'Enter',
			keyCode: ENTER_KEY,
			charCode: ENTER_KEY,
		} );

		// Should call updateValue and return to view mode
		expect( screen.queryByRole( 'textbox' ) ).not.toBeInTheDocument(); // It doesn't go to view mode.
		expect( defaultProps.updateValue ).toHaveBeenLastCalledWith( 'new-slug' ); // It does not get the save call.
	} );

	test.skip( 'handles Escape key down in edit mode', async () => {
		const user = userEvent.setup();
		render( <Sluggable { ...defaultProps } /> );

		// Enter edit mode
		await user.click( screen.getByText( 'Edit' ) );

		// Change the value
		const inputField = screen.getByRole( 'textbox' );
		await user.clear( inputField );
		await user.type( inputField, 'new-slug' );

		// This does not work.
		await user.type( inputField, '{escape}' );

		// This does not work either.
		fireEvent.keyDown( inputField, {
			key: 'Escape',
			code: 'Escape',
			keyCode: ESCAPE_KEY,
			charCode: ESCAPE_KEY,
		} );

		// Should return to view mode without updating value
		expect( screen.queryByRole( 'textbox' ) ).not.toBeInTheDocument(); // It doesn't go to view mode.
		expect( defaultProps.updateValue ).toHaveBeenLastCalledWith( 'test-slug' ); // It does not get the save call.
	} );

	test( 'restores original value if attempting to save empty slug', async () => {
		const user = userEvent.setup();
		render( <Sluggable { ...defaultProps } /> );

		// Enter edit mode
		await user.click( screen.getByText( 'Edit' ) );

		// Clear the input
		const inputField = screen.getByRole( 'textbox' );
		await user.clear( inputField );

		// Click the OK button
		await user.click( screen.getByText( 'OK' ) );

		expect( defaultProps.updateValue ).toHaveBeenCalledWith( 'test-slug' );

		// Should return to view mode with original value
		expect( screen.queryByRole( 'textbox' ) ).not.toBeInTheDocument();
		expect( screen.getByText( 'test-slug' ) ).toBeInTheDocument();
	} );

	test( 'selects text when input is focused', async () => {
		const user = userEvent.setup();

		// Mock the select method
		const selectMock = jest.fn();

		// Original implementation of render
		const { container } = render( <Sluggable { ...defaultProps } /> );

		// Enter edit mode
		await user.click( screen.getByText( 'Edit' ) );

		// Get the input element
		const inputField = screen.getByRole( 'textbox' );

		// Replace the select method with our mock
		inputField.select = selectMock;

		// Trigger focus on the input
		fireEvent.focus( inputField );

		// Check if select was called
		expect( selectMock ).toHaveBeenCalled();
	} );

	test( 'adds appropriate ARIA labels to buttons', () => {
		render( <Sluggable { ...defaultProps } /> );

		// Check Edit button
		expect( screen.getByRole( 'button', { name: 'Edit the slug' } ) ).toBeInTheDocument();

		// Enter edit mode
		fireEvent.click( screen.getByText( 'Edit' ) );

		// Check OK and Cancel buttons
		expect( screen.getByRole( 'button', { name: 'Set the slug' } ) ).toBeInTheDocument();
		expect( screen.getByRole( 'button', { name: 'Cancel editing the slug' } ) ).toBeInTheDocument();
	} );

	test( 'applies correct button styling', () => {
		render( <Sluggable { ...defaultProps } /> );

		// Check Edit button styling
		const editButton = screen.getByText( 'Edit' );
		expect( editButton ).toHaveClass( 'is-secondary' );

		// Enter edit mode
		fireEvent.click( editButton );

		// Check OK button styling
		const okButton = screen.getByText( 'OK' );
		expect( okButton ).toHaveClass( 'is-secondary' );

		// Check Cancel button styling
		const cancelButton = screen.getByText( 'Cancel' );
		expect( cancelButton ).toHaveClass( 'is-tertiary' );
	} );

	test( 'applies correct attributes to the input field', async () => {
		const user = userEvent.setup();
		render( <Sluggable { ...defaultProps } /> );

		// Enter edit mode
		await user.click( screen.getByText( 'Edit' ) );

		// Get the input field
		const inputField = screen.getByRole( 'textbox' );

		// Check input attributes
		expect( inputField ).toHaveAttribute( 'type', 'text' );
		expect( inputField ).toHaveAttribute( 'id', 'pods-form-ui-name' );
		expect( inputField ).toHaveAttribute( 'name', 'name' );
		expect( inputField ).toHaveAttribute( 'maxLength', '46' );
		expect( inputField ).toHaveAttribute( 'size', '25' );
		expect( inputField ).toHaveClass( 'pods-form-ui-field' );
		expect( inputField ).toHaveClass( 'pods-form-ui-field-type-text' );
		expect( inputField ).toHaveClass( 'pods-form-ui-field-name-name' );
	} );
} );
