/**
 * External dependencies
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Mock DND functionality since it's difficult to test and not essential for our unit tests
 */
jest.mock(
	'@dnd-kit/core',
	() => (
		{
			...jest.requireActual( '@dnd-kit/core' ),
			DndContext: ( { children } ) => <div data-testid="dnd-context">{ children }</div>,
			useSensor: jest.fn(),
			useSensors: jest.fn( () => (
				{}
			) ),
			PointerSensor: jest.fn(),
			KeyboardSensor: jest.fn(),
			closestCenter: jest.fn(),
		}
	),
);

jest.mock(
	'@dnd-kit/sortable',
	() => (
		{
			...jest.requireActual( '@dnd-kit/sortable' ),
			SortableContext: ( { children } ) => <div data-testid="sortable-context">{ children }</div>,
			arrayMove: jest.fn( ( array, from, to ) => {
				const result = [ ...array ];
				const item = result[ from ];
				result.splice( from, 1 );
				result.splice( to, 0, item );
				return result;
			} ),
			sortableKeyboardCoordinates: jest.fn(),
			verticalListSortingStrategy: jest.fn(),
		}
	),
);

/**
 * Internal dependencies
 */
import RepeatableFieldList from '../repeatable-field-list';

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

// Setup function for easier test setup
const setup = ( props = {} ) => {
	const defaultProps = {
		fieldConfig: {
			id: 12345,
			name: 'test_field',
			type: 'text',
			label: 'Test Field',
		},
		valuesArray: [ 'First value', 'Second value' ],
		FieldComponent: TestFieldComponent,
		podType: 'post',
		podName: 'post',
		allPodValues: {},
		allPodFieldsMap: {},
		setFullValue: jest.fn(),
		setHasBlurred: jest.fn(),
	};

	return render( <RepeatableFieldList { ...defaultProps } { ...props } /> );
};

describe( 'RepeatableFieldList Component', () => {
	// Test basic rendering
	test( 'renders repeatable fields with values', () => {
		setup();

		// Check if the component is rendered
		expect( screen.getByTestId( 'dnd-context' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'sortable-context' ) ).toBeInTheDocument();

		// Check if the field values are displayed
		const fieldComponents = screen.getAllByTestId( 'test-field-component' );
		expect( fieldComponents ).toHaveLength( 2 );
		expect( screen.getByText( 'Field value: First value' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Field value: Second value' ) ).toBeInTheDocument();

		// Check if add button is displayed
		expect( screen.getByText( 'Add New' ) ).toBeInTheDocument();
	} );

	// Test adding a new field
	test( 'adds a new field when "Add New" button is clicked', () => {
		const setFullValueMock = jest.fn();
		setup( {
			valuesArray: [ 'Existing value' ],
			setFullValue: setFullValueMock,
		} );

		// Click the "Add New" button
		const addButton = screen.getByText( 'Add New' );
		fireEvent.click( addButton );

		// Check if setFullValue was called with the correct values
		expect( setFullValueMock ).toHaveBeenCalledWith( [ 'Existing value', '' ] );
	} );

	// Test deleting a field
	test( 'deletes a field when delete button is clicked and confirmed', () => {
		const setFullValueMock = jest.fn();
		window.confirm = jest.fn( () => true ); // Mock confirm dialog to return true

		setup( {
			valuesArray: [ 'First value', 'Second value', 'Third value' ],
			setFullValue: setFullValueMock,
		} );

		// Find all delete buttons and click the second one
		const deleteButtons = screen.getAllByLabelText( 'Delete' );
		fireEvent.click( deleteButtons[ 1 ] );

		// Check if confirmation was displayed
		expect( window.confirm ).toHaveBeenCalledWith(
			'Are you sure you want to delete this value?',
		);

		// Check if setFullValue was called with the correct values (removing second item)
		expect( setFullValueMock ).toHaveBeenCalledWith( [ 'First value', 'Third value' ] );
	} );

	// Test not deleting a field when cancel is clicked
	test( 'does not delete field when delete button is clicked but not confirmed', () => {
		const setFullValueMock = jest.fn();
		window.confirm = jest.fn( () => false ); // Mock confirm dialog to return false

		setup( {
			valuesArray: [ 'First value', 'Second value' ],
			setFullValue: setFullValueMock,
		} );

		// Find all delete buttons and click the first one
		const deleteButtons = screen.getAllByLabelText( 'Delete' );
		fireEvent.click( deleteButtons[ 0 ] );

		// Check if confirmation was displayed
		expect( window.confirm ).toHaveBeenCalledWith(
			'Are you sure you want to delete this value?',
		);

		// Check that setFullValue was not called
		expect( setFullValueMock ).not.toHaveBeenCalled();
	} );

	// Test using custom add button text
	test( 'uses custom add button text when provided', () => {
		setup( {
			fieldConfig: {
				id: 12345,
				name: 'test_field',
				type: 'text',
				label: 'Test Field',
				repeatable_add_new_label: 'Add Custom Item',
			},
		} );

		// Check if custom button text is displayed
		expect( screen.getByText( 'Add Custom Item' ) ).toBeInTheDocument();
		expect( screen.queryByText( 'Add New' ) ).not.toBeInTheDocument();
	} );

	// Test that only single field doesn't show move/delete controls
	test( 'does not show move and delete controls for single field', () => {
		setup( {
			valuesArray: [ 'Single value' ],
		} );

		// Check that the move and delete buttons are not present
		expect( screen.queryByLabelText( 'Move up' ) ).not.toBeInTheDocument();
		expect( screen.queryByLabelText( 'Move down' ) ).not.toBeInTheDocument();
		expect( screen.queryByLabelText( 'Delete' ) ).not.toBeInTheDocument();
	} );

	// Test updating a field value
	test( 'updates field value when input changes', () => {
		const setFullValueMock = jest.fn();
		setup( {
			valuesArray: [ 'Initial value', 'Second value' ],
			setFullValue: setFullValueMock,
		} );

		// Find the first input field and change its value
		const inputs = screen.getAllByTestId( 'test-field-input' );
		fireEvent.change( inputs[ 0 ], { target: { value: 'Updated value' } } );

		// Check if setValue was called correctly
		expect( setFullValueMock ).toHaveBeenCalledWith( [ 'Updated value', 'Second value' ] );
	} );
} );
