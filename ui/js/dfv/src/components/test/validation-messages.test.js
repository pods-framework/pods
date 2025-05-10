/**
 * External dependencies
 */
import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import ValidationMessages from '../validation-messages';

// Mock the WordPress Notice component
jest.mock(
	'@wordpress/components',
	() => (
		{
			Notice: ( {
				children,
				status,
				isDismissible,
				politeness,
			} ) => (
				<div
					data-testid="mock-notice"
					data-status={ status }
					data-dismissible={ isDismissible ? 'true' : 'false' }
					data-politeness={ politeness }
				>
					{ children }
				</div>
			),
		}
	),
);

describe( 'ValidationMessages Component', () => {
	test( 'renders a single validation message', () => {
		const messages = [ 'This field is required' ];

		render( <ValidationMessages messages={ messages } /> );

		// Check if the validation messages container exists
		const container = screen.getByTestId( 'validation-messages' );
		expect( container ).toBeInTheDocument();
		expect( container ).toHaveClass( 'pods-validation-messages' );

		// Check if the message is rendered
		expect( screen.getByText( 'This field is required' ) ).toBeInTheDocument();

		// Check Notice properties
		const notice = screen.getByText( 'This field is required' ).closest( 'div' );
		expect( notice ).toHaveAttribute( 'data-status', 'error' );
		expect( notice ).toHaveAttribute( 'data-dismissible', 'false' );
		expect( notice ).toHaveAttribute( 'data-politeness', 'polite' );
	} );

	test( 'renders multiple validation messages', () => {
		const messages = [
			'This field is required', 'Value must be between 1 and 10', 'Invalid format',
		];

		render( <ValidationMessages messages={ messages } /> );

		// Check if all messages are rendered
		expect( screen.getByText( 'This field is required' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Value must be between 1 and 10' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Invalid format' ) ).toBeInTheDocument();

		// Check that we have the correct number of notices
		const notices = screen.getAllByText( /This field is required|Value must be between 1 and 10|Invalid format/ );
		expect( notices ).toHaveLength( 3 );
	} );

	test( 'does not render anything when messages array is empty', () => {
		const { container } = render( <ValidationMessages messages={ [] } /> );

		// Component should return null, so container should be empty
		expect( container ).toBeEmptyDOMElement();

		// The validation messages container should not exist
		expect( screen.queryByTestId( 'validation-messages' ) ).not.toBeInTheDocument();
	} );

	test( 'renders notices with correct attributes', () => {
		const messages = [ 'Validation error' ];

		render( <ValidationMessages messages={ messages } /> );

		// Get the Notice element
		const notice = screen.getByText( 'Validation error' ).closest( 'div' );

		// Check attributes
		expect( notice ).toHaveAttribute( 'data-status', 'error' );
		expect( notice ).toHaveAttribute( 'data-dismissible', 'false' );
		expect( notice ).toHaveAttribute( 'data-politeness', 'polite' );
	} );

	test( 'renders each message in its own Notice', () => {
		const messages = [
			'Error 1', 'Error 2', 'Error 3',
		];

		render( <ValidationMessages messages={ messages } /> );

		// Get all notices
		const notices = screen.getAllByTestId( 'mock-notice' );

		// Should have one notice per message
		expect( notices.length ).toEqual( 3 );

		// Each notice should contain exactly one message
		expect( notices[ 0 ].textContent ).toEqual( 'Error 1' );
		expect( notices[ 1 ].textContent ).toEqual( 'Error 2' );
		expect( notices[ 2 ].textContent ).toEqual( 'Error 3' );
	} );

	test( 'handles messages with HTML content safely', () => {
		const messages = [ '<b>Bold error</b>' ];

		render( <ValidationMessages messages={ messages } /> );

		// The text should be rendered as-is, not as HTML
		expect( screen.getByText( '<b>Bold error</b>' ) ).toBeInTheDocument();
	} );

	test( 'has correct container class', () => {
		const messages = [ 'Error message' ];

		render( <ValidationMessages messages={ messages } /> );

		// Check container class
		const container = screen.getByTestId( 'validation-messages' );
		expect( container ).toHaveClass( 'pods-validation-messages' );
	} );

	test( 'handles empty messages array gracefully', () => {
		// Should not throw error with empty array
		const { container } = render( <ValidationMessages messages={ [] } /> );
		expect( container ).toBeEmptyDOMElement();
	} );
} );
