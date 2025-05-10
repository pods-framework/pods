/**
 * External dependencies
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import SubfieldWrapper from '../subfield-wrapper';

// Simple field component for testing
const TestFieldComponent = ( {
	value,
	setValue,
	setHasBlurred,
	fieldConfig,
} ) => (
	<div data-testid="test-field-component">
		<input
			data-testid="test-field-input"
			value={ value || '' }
			onChange={ ( e ) => setValue( e.target.value ) }
			onBlur={ () => setHasBlurred() }
			aria-label={ fieldConfig.name }
		/>
		<span>Field value: { value }</span>
	</div>
);

// Setup function for easier test configuration
const setup = ( props = {} ) => {
	const defaultProps = {
		fieldConfig: {
			id: 12345,
			name: 'test_field',
			type: 'text',
			label: 'Test Field',
			htmlAttr: {
				name: 'test_field_html_name',
				id: 'test_field_id',
			},
		},
		FieldComponent: TestFieldComponent,
		isDraggable: true,
		value: 'Test value',
		podType: 'post',
		podName: 'post',
		index: 0,
		allPodValues: {},
		allPodFieldsMap: {},
		setValue: jest.fn(),
		setHasBlurred: jest.fn(),
	};

	return render( <SubfieldWrapper { ...defaultProps } { ...props } /> );
};

describe( 'SubfieldWrapper Component', () => {
	// Reset mocks before each test
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	// Test basic rendering
	test( 'renders field component with correct props', () => {
		setup();

		// Check if the component is rendered
		expect( screen.getByTestId( 'test-field-component' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Field value: Test value' ) ).toBeInTheDocument();

		// Check field container
		const fieldContainer = screen.getByTestId( 'test-field-component' ).closest( '.pods-field-wrapper__field' );
		expect( fieldContainer ).toBeInTheDocument();
	} );

	// Test that field names and IDs are modified with index for repeatable fields
	test( 'transforms field name and id correctly with index', () => {
		const { debug } = setup( {
			index: 2,
		} );

		// Get the field input
		const fieldInput = screen.getByTestId( 'test-field-input' );

		// Check aria-label which reflects the field name
		expect( fieldInput ).toHaveAttribute( 'aria-label', 'test_field[2]' );
	} );

	// Test handling field blur
	test( 'calls setHasBlurred when field blurs', () => {
		const setHasBlurredMock = jest.fn();
		setup( {
			setHasBlurred: setHasBlurredMock,
		} );

		// Trigger blur on input
		fireEvent.blur( screen.getByTestId( 'test-field-input' ) );

		// Check if setHasBlurred was called
		expect( setHasBlurredMock ).toHaveBeenCalled();
	} );

	// Test delete control rendering
	test( 'renders delete control when provided', () => {
		const deleteControl = <button data-testid="delete-control">Delete</button>;
		setup( {
			isDraggable: true,
			deleteControl,
		} );

		// Check if delete control is rendered
		expect( screen.getByTestId( 'delete-control' ) ).toBeInTheDocument();
	} );

	// Test handling setValue
	test( 'calls setValue when field value changes', () => {
		const setValueMock = jest.fn();
		setup( {
			setValue: setValueMock,
		} );

		// Change input value
		fireEvent.change( screen.getByTestId( 'test-field-input' ), { target: { value: 'New value' } } );

		// Check if setValue was called with new value
		expect( setValueMock ).toHaveBeenCalledWith( 'New value' );
	} );
} );
