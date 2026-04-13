/**
 * External dependencies
 */
import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import FieldErrorBoundary from '../field-error-boundary';

// Create a component that will throw an error when rendered
const ErrorComponent = () => {
	throw new Error( 'Test error' );
	// This line will never be reached
	return <div>This will not render</div>;
};

// Create a component that will not throw an error
const ValidComponent = () => {
	return <div data-testid="valid-component">Valid component content</div>;
};

describe( 'FieldErrorBoundary Component', () => {
	// Spy on console.warn before each test
	let consoleWarnSpy;

	beforeEach( () => {
		consoleWarnSpy = jest.spyOn( console, 'warn' ).mockImplementation( () => {} );
	} );

	afterEach( () => {
		consoleWarnSpy.mockRestore();
	} );

	// Test successful rendering without errors
	test( 'renders children when no errors occur', () => {
		render(
			<FieldErrorBoundary>
				<ValidComponent />
			</FieldErrorBoundary>,
		);

		// Verify the child component rendered successfully
		expect( screen.getByTestId( 'valid-component' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Valid component content' ) ).toBeInTheDocument();

		// Verify console.warn was not called
		expect( consoleWarnSpy ).not.toHaveBeenCalled();
	} );

	// Test error state rendering
	test( 'renders error message when an error occurs', () => {
		// We need to mock the error boundary's componentDidCatch to prevent test failure
		const originalError = console.error;
		console.error = jest.fn();

		render(
			<FieldErrorBoundary>
				<ErrorComponent />
			</FieldErrorBoundary>,
		);

		// Verify error message is displayed
		expect( screen.getByText( 'There was an error rendering the field.' ) ).toBeInTheDocument();

		// Verify console.warn was called with the error
		expect( consoleWarnSpy ).toHaveBeenCalledWith(
			'There was an error rendering this field.',
			expect.any( Error ),
			expect.anything(),
		);

		// Restore console.error
		console.error = originalError;
	} );

	// Test error state updates correctly
	test( 'updates state when an error occurs', () => {
		// We need to mock the error boundary's componentDidCatch to prevent test failure
		const originalError = console.error;
		console.error = jest.fn();

		const { rerender } = render(
			<FieldErrorBoundary>
				<ValidComponent />
			</FieldErrorBoundary>,
		);

		// Initially, no error state
		expect( screen.getByTestId( 'valid-component' ) ).toBeInTheDocument();

		// Update with a component that throws an error
		rerender(
			<FieldErrorBoundary>
				<ErrorComponent />
			</FieldErrorBoundary>,
		);

		// Verify error message is displayed
		expect( screen.getByText( 'There was an error rendering the field.' ) ).toBeInTheDocument();

		// Restore console.error
		console.error = originalError;
	} );

	// Test with complex child component structure
	test( 'handles nested components correctly', () => {
		render(
			<FieldErrorBoundary>
				<div className="wrapper">
					<div className="inner">
						<ValidComponent />
					</div>
				</div>
			</FieldErrorBoundary>,
		);

		// Verify the entire tree rendered
		expect( screen.getByTestId( 'valid-component' ) ).toBeInTheDocument();
	} );

	// Test with different prop values
	test( 'accepts different child elements', () => {
		// We need to mock the error boundary's componentDidCatch to prevent test failure
		const originalError = console.error;
		console.error = jest.fn();

		const { rerender } = render(
			<FieldErrorBoundary>
				<input data-testid="test-input" />
			</FieldErrorBoundary>,
		);

		// Verify input rendered
		expect( screen.getByTestId( 'test-input' ) ).toBeInTheDocument();

		// Change to a paragraph element
		rerender(
			<FieldErrorBoundary>
				<p data-testid="test-paragraph">Paragraph content</p>
			</FieldErrorBoundary>,
		);

		// Verify paragraph rendered
		expect( screen.getByTestId( 'test-paragraph' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Paragraph content' ) ).toBeInTheDocument();

		// Change to an error component
		rerender(
			<FieldErrorBoundary>
				<ErrorComponent />
			</FieldErrorBoundary>,
		);

		// Verify error message is displayed
		expect( screen.getByText( 'There was an error rendering the field.' ) ).toBeInTheDocument();

		// Restore console.error
		console.error = originalError;
	} );

	// Test with static getDerivedStateFromError
	test( 'static getDerivedStateFromError returns correct state', () => {
		const error = new Error( 'Test error' );
		const result = FieldErrorBoundary.getDerivedStateFromError( error );

		expect( result ).toEqual( {
			hasError: true,
			error,
		} );
	} );
} );
