/**
 * External dependencies
 */
import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import sanitizeHtml from 'sanitize-html';
import { removep } from '@wordpress/autop';

/**
 * Internal dependencies
 */
import FieldDescription from '../field-description';

describe( 'FieldDescription Component', () => {

	test( 'renders with plain text description', () => {
		const description = 'This is a simple description';

		render( <FieldDescription description={ description } /> );

		// Check if the description is rendered
		expect( screen.getByText( description ) ).toBeInTheDocument();

		// Check that it's wrapped in a paragraph with the right class
		const paragraph = screen.getByText( description ).closest( 'p' );
		expect( paragraph ).toHaveClass( 'pods-field-description' );
	} );

	test( 'renders with HTML description', () => {
		const description = 'This is <strong>important</strong> information';

		render( <FieldDescription description={ description } /> );

		// Check if the HTML is processed correctly
		// Note: When using dangerouslySetInnerHTML, we need to use container queries
		const { container } = render( <FieldDescription description={ description } /> );

		// Check the rendered HTML contains the strong tag
		expect( container.innerHTML ).toContain( '<strong>important</strong>' );
	} );

	test( 'sanitizes potentially dangerous HTML', () => {
		const description = 'This contains a <script>alert("dangerous!");</script> script tag';

		const { container } = render( <FieldDescription description={ description } /> );

		// Check that script tag is removed
		expect( container.innerHTML ).not.toContain( '<script>' );
		expect( container.innerHTML ).not.toContain( 'alert("dangerous!");' );
	} );

	test( 'removes paragraph tags from the description', () => {
		const description = '<p>This is wrapped in a paragraph</p>';

		const { container } = render( <FieldDescription description={ description } /> );

		// Check that the <p> tags from the content are removed
		// but the component still wraps the content in its own <p> tag
		const html = container.innerHTML;
		expect( html ).toContain( '<p class="pods-field-description">' );

		// The original paragraph tags should be removed by removep
		expect( html ).not.toContain( '<p>This is wrapped in a paragraph</p>' );
		expect( html ).toContain( 'This is wrapped in a paragraph' );
	} );

	test( 'handles empty description', () => {
		const description = '';

		const { container } = render( <FieldDescription description={ description } /> );

		// Check that an empty paragraph is rendered
		expect( container.querySelector( '.pods-field-description' ) ).toBeInTheDocument();
		expect( container.querySelector( '.pods-field-description' ).textContent ).toBe( '' );
	} );

	test( 'uses richTextNoLinks configuration', () => {
		const description = '<div data-testid="test-description"><a href="https://example.com">Link</a> <b>Bold</b> <script>alert("test");</script></div>';
		const expectedDescription = 'Link <b>Bold</b>';

		render( <FieldDescription description={ description } /> );

		// Verify the HTML is sanitized correctly
		expect( screen.getByTestId( 'test-description' ).innerHTML ).toEqual( expectedDescription );
	} );

	test( 'applies appropriate CSS class', () => {
		render( <FieldDescription description="Test description" /> );

		const element = screen.getByText( 'Test description' );
		expect( element ).toHaveClass( 'pods-field-description' );
	} );

	test( 'handles undefined description gracefully', () => {
		// This wouldn't actually happen in practice due to the PropTypes requirement,
		// but we should test error handling

		// Mock console.error to suppress PropType warnings during test
		const originalConsoleError = console.error;
		console.error = jest.fn();

		expect( () => {
			render( <FieldDescription description={ undefined } /> );
		} ).not.toThrow();

		// Restore console.error
		console.error = originalConsoleError;
	} );
} );
