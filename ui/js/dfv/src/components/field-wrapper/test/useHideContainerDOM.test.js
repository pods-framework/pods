/**
 * External dependencies
 */
import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import useHideContainerDOM from '../useHideContainerDOM';

// Test component that implements the hook
const TestComponent = ( {
	name,
	meetsDependencies,
} ) => {
	const ref = React.useRef( null );

	// Use the hook with the provided props
	useHideContainerDOM( name, ref, meetsDependencies );

	return (
		<div>
			<span ref={ ref } data-testid="field-ref">Reference Element</span>
		</div>
	);
};

describe( 'useHideContainerDOM Hook', () => {
	// Helper function to create the required DOM structure for testing
	const setupDOMStructure = () => {
		// Create the parent container element that will be targeted by closest()
		const container = document.createElement( 'div' );
		container.className = 'pods-field__container';
		document.body.appendChild( container );

		// This is necessary to mock Element.closest() which is used in the hook
		Element.prototype.closest = jest.fn( selector => {
			if ( selector === '.pods-field__container' ) {
				return container;
			}
			return null;
		} );

		return container;
	};

	// Clean up after each test
	afterEach( () => {
		// Clean up any elements added to the body
		document.body.innerHTML = '';

		// Restore the original closest method
		Element.prototype.closest.mockRestore();
		delete Element.prototype.closest;
	} );

	test( 'shows container when dependencies are met', () => {
		// Setup the DOM structure
		const container = setupDOMStructure();

		// Initially set display to none to verify it changes
		container.style.display = 'none';

		// Render component with dependencies met
		render( <TestComponent name="test-field" meetsDependencies={ true } /> );

		// The hook should have set the display property to empty string (visible)
		expect( container.style.display ).toBe( '' );
	} );

	test( 'hides container when dependencies are not met', () => {
		// Setup the DOM structure
		const container = setupDOMStructure();

		// Initially set display to visible
		container.style.display = '';

		// Render component with dependencies not met
		render( <TestComponent name="test-field" meetsDependencies={ false } /> );

		// The hook should have set the display property to 'none' (hidden)
		expect( container.style.display ).toBe( 'none' );
	} );

	test( 'updates container visibility when dependencies change', () => {
		// Setup the DOM structure
		const container = setupDOMStructure();

		// Render with initial state (visible)
		const { rerender } = render(
			<TestComponent name="test-field" meetsDependencies={ true } />,
		);

		// Initial state should be visible
		expect( container.style.display ).toBe( '' );

		// Re-render with dependencies not met
		rerender( <TestComponent name="test-field" meetsDependencies={ false } /> );

		// Should now be hidden
		expect( container.style.display ).toBe( 'none' );

		// Re-render with dependencies met again
		rerender( <TestComponent name="test-field" meetsDependencies={ true } /> );

		// Should be visible again
		expect( container.style.display ).toBe( '' );
	} );

	test( 'does nothing when container element is not found', () => {
		// Mock closest to return null to simulate no container found
		Element.prototype.closest = jest.fn( () => null );

		// Initial body state
		document.body.innerHTML = '<div id="test"></div>';

		// Render component
		render( <TestComponent name="test-field" meetsDependencies={ true } /> );

		// Verify closest was called but no errors were thrown
		expect( Element.prototype.closest ).toHaveBeenCalledWith( '.pods-field__container' );

		// Body should remain unchanged
		expect( document.body.innerHTML ).toContain( '<div id="test"></div>' );
	} );

	test( 'handles different field names correctly', () => {
		// Setup the DOM structure
		const container = setupDOMStructure();

		// Render with a specific field name
		const { rerender } = render(
			<TestComponent name="field-one" meetsDependencies={ true } />,
		);

		// Should be visible
		expect( container.style.display ).toBe( '' );

		// Re-render with a different field name but same dependencies state
		rerender( <TestComponent name="field-two" meetsDependencies={ true } /> );

		// Should still be visible (hook should run again due to name change in dependencies array)
		expect( container.style.display ).toBe( '' );

		// Re-render with a different field name and different dependencies state
		rerender( <TestComponent name="field-three" meetsDependencies={ false } /> );

		// Should now be hidden
		expect( container.style.display ).toBe( 'none' );
	} );
} );
