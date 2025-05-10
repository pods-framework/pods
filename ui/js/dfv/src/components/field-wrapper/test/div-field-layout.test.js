/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import DivFieldLayout from '../div-field-layout';

describe( 'DivFieldLayout Component', () => {
	// Test basic rendering with all props
	test( 'renders all components when provided', () => {
		const mockLabelComponent = <label data-testid="test-label">Test Label</label>;
		const mockDescriptionComponent = <div data-testid="test-description">Test Description</div>;
		const mockInputComponent = <input data-testid="test-input" />;
		const mockValidationMessagesComponent = <div data-testid="test-validation">Test Validation</div>;

		render( <DivFieldLayout
			fieldType="text"
			labelComponent={ mockLabelComponent }
			descriptionComponent={ mockDescriptionComponent }
			inputComponent={ mockInputComponent }
			validationMessagesComponent={ mockValidationMessagesComponent }
		/> );

		// Check that all components are rendered
		expect( screen.getByTestId( 'test-label' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'test-description' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'test-input' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'test-validation' ) ).toBeInTheDocument();

		// Check the parent container class
		const container = screen.getByTestId( 'test-label' ).closest( '.pods-field-option' );
		expect( container ).toBeInTheDocument();
	} );

	// Test conditional rendering of optional components
	test( 'renders without optional components', () => {
		const mockInputComponent = <input data-testid="test-input" />;

		render( <DivFieldLayout
			fieldType="text"
			inputComponent={ mockInputComponent }
		/> );

		// Only the input should be rendered
		expect( screen.getByTestId( 'test-input' ) ).toBeInTheDocument();
		expect( screen.queryByText( 'Test Label' ) ).not.toBeInTheDocument();
		expect( screen.queryByText( 'Test Description' ) ).not.toBeInTheDocument();
		expect( screen.queryByText( 'Test Validation' ) ).not.toBeInTheDocument();
	} );

	// Test with label but no description
	test( 'renders with label but no description', () => {
		const mockLabelComponent = <label data-testid="test-label">Test Label</label>;
		const mockInputComponent = <input data-testid="test-input" />;

		render( <DivFieldLayout
			fieldType="text"
			labelComponent={ mockLabelComponent }
			inputComponent={ mockInputComponent }
		/> );

		expect( screen.getByTestId( 'test-label' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'test-input' ) ).toBeInTheDocument();
		expect( screen.queryByText( 'Test Description' ) ).not.toBeInTheDocument();
	} );

	// Test with description but no label
	test( 'renders with description but no label', () => {
		const mockDescriptionComponent = <div data-testid="test-description">Test Description</div>;
		const mockInputComponent = <input data-testid="test-input" />;

		render( <DivFieldLayout
			fieldType="text"
			descriptionComponent={ mockDescriptionComponent }
			inputComponent={ mockInputComponent }
		/> );

		expect( screen.queryByText( 'Test Label' ) ).not.toBeInTheDocument();
		expect( screen.getByTestId( 'test-description' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'test-input' ) ).toBeInTheDocument();
	} );

	// Test with validation messages
	test( 'renders with validation messages', () => {
		const mockInputComponent = <input data-testid="test-input" />;
		const mockValidationMessagesComponent = <div data-testid="test-validation">Field is required</div>;

		render( <DivFieldLayout
			fieldType="text"
			inputComponent={ mockInputComponent }
			validationMessagesComponent={ mockValidationMessagesComponent }
		/> );

		expect( screen.getByTestId( 'test-input' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'test-validation' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Field is required' ) ).toBeInTheDocument();
	} );

	// Test CSS class generation based on fieldType
	test( 'generates correct container CSS classes based on fieldType', () => {
		const mockInputComponent = <input data-testid="test-input" />;
		const {
			container,
			rerender,
		} = render( <DivFieldLayout
			fieldType="text"
			inputComponent={ mockInputComponent }
		/> );

		// Check for text field type classes
		let dfvContainer = container.querySelector( '.pods-dfv-container' );
		expect( dfvContainer ).toHaveClass( 'pods-dfv-container-text' );

		// Rerender with a different field type
		rerender( <DivFieldLayout
			fieldType="paragraph"
			inputComponent={ mockInputComponent }
		/> );

		// Check for paragraph field type classes
		dfvContainer = container.querySelector( '.pods-dfv-container' );
		expect( dfvContainer ).toHaveClass( 'pods-dfv-container-paragraph' );
	} );

	// Test nested structure
	test( 'renders with correct nested structure', () => {
		const mockLabelComponent = <label data-testid="test-label">Test Label</label>;
		const mockDescriptionComponent = <div data-testid="test-description">Test Description</div>;
		const mockInputComponent = <input data-testid="test-input" />;

		const { container } = render( <DivFieldLayout
			fieldType="email"
			labelComponent={ mockLabelComponent }
			descriptionComponent={ mockDescriptionComponent }
			inputComponent={ mockInputComponent }
		/> );

		// Check the structure
		const rootElement = container.firstChild;
		expect( rootElement.className ).toEqual( 'pods-field-option' );

		// The label should be a direct child of the root
		expect( rootElement.querySelector( '[data-testid="test-label"]' ) ).not.toBeNull();

		// The input should be inside the pods-field-option__field div
		const fieldDiv = rootElement.querySelector( '.pods-field-option__field' );
		expect( fieldDiv ).not.toBeNull();

		// The input should be inside the dfvContainer div, which is inside the fieldDiv
		const dfvContainer = fieldDiv.querySelector( '.pods-dfv-container' );
		expect( dfvContainer ).not.toBeNull();
		expect( dfvContainer.querySelector( '[data-testid="test-input"]' ) ).not.toBeNull();

		// The description should be inside the fieldDiv but after the dfvContainer
		expect( fieldDiv.querySelector( '[data-testid="test-description"]' ) ).not.toBeNull();
	} );
} );
