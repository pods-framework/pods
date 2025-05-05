/**
 * External dependencies
 */
import React, { act } from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import CopyButton from '../copy-button';

describe( 'CopyButton Component', () => {
	// Setup for clipboard API tests
	let originalClipboard;
	let originalExecCommand;

	beforeEach( () => {
		// Save original implementations
		originalClipboard = { ...navigator.clipboard };
		originalExecCommand = document.execCommand;

		// Mock navigator.clipboard
		if ( ! navigator.clipboard ) {
			// Create the clipboard object if it doesn't exist in the test environment
			navigator.clipboard = { writeText: jest.fn( () => Promise.resolve() ) };
		} else {
			// Just mock the writeText method
			navigator.clipboard.writeText = jest.fn( () => Promise.resolve() );
		}

		// Mock document.execCommand for fallback
		document.execCommand = jest.fn( () => true );

		// Mock setTimeout and clearTimeout
		jest.useFakeTimers( { advanceTimers: true } );
	} );

	afterEach( () => {
		// Restore original implementations
		navigator.clipboard = originalClipboard;
		document.execCommand = originalExecCommand;

		// Restore real timers
		jest.useRealTimers();
	} );

	const setTimeoutFn = ( callback ) => act( () => callback() );

	test( 'renders with default props', () => {
		render( <CopyButton /> );

		// Check if button is rendered
		const button = screen.getByRole( 'button' );
		expect( button ).toBeInTheDocument();
		expect( button ).toHaveAttribute( 'aria-label', 'Copy' );

		// Check that SVG icon is visible initially
		expect( button.querySelector( 'svg' ) ).toBeInTheDocument();

		// And "Copied" text is not visible
		expect( () => screen.getByTestId( 'copy-button-copied' ) ).toThrow();
	} );

	test( 'renders with custom label', () => {
		render( <CopyButton label="Copy this text" /> );

		const button = screen.getByRole( 'button' );
		expect( button ).toHaveAttribute( 'aria-label', 'Copy this text' );
	} );

	test( 'maintains class name for styling', () => {
		render( <CopyButton /> );

		const button = screen.getByRole( 'button' );
		expect( button ).toHaveClass( 'pods-field_copy-button' );
	} );

	test( 'provides proper accessibility attributes', () => {
		render( <CopyButton label="Accessible copy button" /> );

		const button = screen.getByRole( 'button' );
		expect( button ).toHaveAttribute( 'aria-label', 'Accessible copy button' );

		// SVG should have correct accessibility attributes
		const svg = button.querySelector( 'svg' );
		expect( svg ).toHaveAttribute( 'aria-hidden', 'true' );
		expect( svg ).toHaveAttribute( 'focusable', 'false' );
	} );

	test( 'uses Clipboard API to copy text when available', async () => {
		const textToCopy = 'Test text to copy';

		render( <CopyButton setTimeoutFn={ setTimeoutFn } textToCopy={ textToCopy } /> );

		// Click the button
		await act( () => fireEvent.click( screen.getByRole( 'button' ) ) );

		// Check if clipboard API was called with the correct text
		expect( navigator.clipboard.writeText ).toHaveBeenCalledWith( textToCopy );
	} );

	test( 'falls back to execCommand when Clipboard API fails', async () => {
		// Make the Clipboard API fail
		navigator.clipboard.writeText.mockImplementation( () => Promise.reject() );

		const textToCopy = 'Fallback text';

		render( <CopyButton textToCopy={ textToCopy } /> );

		// Spy on document.body appendChild and removeChild
		const appendChildSpy = jest.spyOn( document.body, 'appendChild' );
		const removeChildSpy = jest.spyOn( document.body, 'removeChild' );

		// Click the button
		await act( () => fireEvent.click( screen.getByRole( 'button' ) ) );

		// Check if clipboard API was attempted
		expect( navigator.clipboard.writeText ).toHaveBeenCalled();

		// Check if fallback was used
		expect( document.execCommand ).toHaveBeenCalledWith( 'copy' );
		expect( appendChildSpy ).toHaveBeenCalled();
		expect( removeChildSpy ).toHaveBeenCalled();

		// Check if temporary input was created with correct text
		const inputElement = appendChildSpy.mock.calls[ 0 ][ 0 ];
		expect( inputElement.tagName ).toBe( 'INPUT' );
		expect( inputElement.value ).toBe( textToCopy );

		// Clean up
		appendChildSpy.mockRestore();
		removeChildSpy.mockRestore();
	} );

	test( 'executes custom onClick callback when provided', async () => {
		const onClickMock = jest.fn();

		render( <CopyButton onClick={ onClickMock } textToCopy="Some text" /> );

		// Click the button
		await act( () => fireEvent.click( screen.getByRole( 'button' ) ) );

		// Check if custom onClick was called
		expect( onClickMock ).toHaveBeenCalled();

		// Check that Clipboard API was NOT called (since onClick is provided)
		expect( navigator.clipboard.writeText ).not.toHaveBeenCalled();
	} );

	test( 'clears timeout on quick successive clicks', async () => {
		jest.useFakeTimers( { advanceTimers: false } );

		const setTimeoutSpy = jest.spyOn( window, 'setTimeout' );
		const clearTimeoutSpy = jest.spyOn( window, 'clearTimeout' );

		render( <CopyButton textToCopy="Quick click test" /> );

		// Click the button
		await act( () => fireEvent.click( screen.getByRole( 'button' ) ) );

		expect( jest.getTimerCount() ).toEqual( 1 );

		// Click it again quickly
		await act( () => fireEvent.click( screen.getByRole( 'button' ) ) );

		expect( jest.getTimerCount() ).toEqual( 1 );

		// Should have called clearTimeout at least once
		expect( setTimeoutSpy ).toHaveBeenCalledTimes( 2 );
		expect( clearTimeoutSpy ).toHaveBeenCalledTimes( 1 );

		// Fast forward the timers
		await act( () => jest.runAllTimers() );

		expect( clearTimeoutSpy ).toHaveBeenCalledTimes( 2 );

		clearTimeoutSpy.mockRestore();
	} );

	test( 'handles empty text to copy gracefully', async () => {
		render( <CopyButton textToCopy="" /> );

		// Click the button
		await act( () => fireEvent.click( screen.getByRole( 'button' ) ) );

		// Should still call clipboard API with empty string
		expect( navigator.clipboard.writeText ).toHaveBeenCalledWith( '' );
	} );

	test( 'handles null textToCopy gracefully', async () => {
		// This tests the default prop value for textToCopy being null
		render( <CopyButton /> );

		// Click the button
		await act( () => fireEvent.click( screen.getByRole( 'button' ) ) );

		// Should call clipboard API with null
		// This is a weird edge case but it's how JS works when passing null to string functions
		expect( navigator.clipboard.writeText ).toHaveBeenCalledWith( null );
	} );

	test( 'shows copied text and then text disappears', async () => {
		jest.useFakeTimers( { advanceTimers: false } );

		render( <CopyButton label="Copy" /> );

		// Click the button
		await act( () => fireEvent.click( screen.getByRole( 'button' ) ) );

		expect( screen.getByTestId( 'copy-button-copied' ).tagName ).toEqual( 'SPAN' );

		await act( () => jest.runAllTimers() );

		expect( () => screen.getByTestId( 'copy-button-copied' ) ).toThrow();
	} );
} );
