/**
 * External dependencies
 */
import { render, screen, fireEvent } from '@testing-library/react';
import { act } from 'react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import DateTime from '../index';

// Mock Dropdown component from WordPress
jest.mock(
	'@wordpress/components',
	() => (
		{
			Dropdown: ( {
				renderToggle,
				renderContent,
			} ) => (
				<div>
					{ renderToggle( { onToggle: () => {} } ) }
					<div data-testid="dropdown-content">{ renderContent() }</div>
				</div>
			),
		}
	),
);

// Mock react-datetime
jest.mock( 'react-datetime', () => {
	return jest.fn().mockImplementation( props => (
		<div data-testid="datetime-picker">
			<div data-testid="date-format">{ props.dateFormat || 'none' }</div>
			<div data-testid="time-format">{ props.timeFormat || 'none' }</div>
			<table role="table">
				<tbody>
					<tr>
						<td>Mock Calendar</td>
					</tr>
				</tbody>
			</table>
		</div>
	) );
} );

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test DateTime Field',
		name: 'test_datetime_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'datetime',
		datetime_format: 'mdy_dash',
		datetime_time_format: 'h_mm_A',
		datetime_time_type: '12',
		datetime_type: 'format',
	},
};

describe( 'DateTime Component', () => {
	beforeEach( () => {
		jest.clearAllMocks();
		// Mock the window.podsDFVConfig
		window.podsDFVConfig = {
			userLocale: 'en',
			datetime: {
				date_format: 'F j, Y',
				time_format: 'g:i a',
			},
		};
	} );

	it( 'renders with default props', () => {
		render( <DateTime { ...BASE_PROPS } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input ).toBeInTheDocument();
	} );

	it( 'handles value changes', () => {
		const props = {
			...BASE_PROPS,
			value: '2023-05-15 14:30:00',
		};

		render( <DateTime { ...props } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input ).toBeInTheDocument();

		// Simulate typing a new value
		fireEvent.change( input, { target: { value: '2023-06-20 15:45:00' } } );

		// Simulate blur to trigger value setting
		fireEvent.blur( input );

		expect( props.setValue ).toHaveBeenCalled();
		expect( props.setHasBlurred ).toHaveBeenCalled();
	} );

	it( 'renders a date-only picker', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				type: 'date',
			},
		};

		render( <DateTime { ...props } /> );

		const input = screen.getByRole( 'textbox' );

		// Open the dropdown
		act( () => {
			fireEvent.click( input );
		} );

		// Check that the picker is rendered with correct format
		const datePicker = screen.getByTestId( 'datetime-picker' );
		expect( datePicker ).toBeInTheDocument();

		const dateFormat = screen.getByTestId( 'date-format' );
		const timeFormat = screen.getByTestId( 'time-format' );

		// For date-only, we should have a date format but no time format
		expect( dateFormat.textContent ).not.toBe( 'none' );
		expect( timeFormat.textContent ).toBe( 'none' ); // false becomes 'none' in our mock
	} );

	it( 'renders a time-only picker', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				type: 'time',
			},
		};

		render( <DateTime { ...props } /> );

		const input = screen.getByRole( 'textbox' );

		// Open the dropdown
		act( () => {
			fireEvent.click( input );
		} );

		// Check that the picker is rendered with correct format
		const datePicker = screen.getByTestId( 'datetime-picker' );
		expect( datePicker ).toBeInTheDocument();

		const dateFormat = screen.getByTestId( 'date-format' );
		const timeFormat = screen.getByTestId( 'time-format' );

		// For time-only, we should have a time format but no date format
		expect( dateFormat.textContent ).toBe( 'none' ); // false becomes 'none' in our mock
		expect( timeFormat.textContent ).not.toBe( 'none' );
	} );

	it( 'handles read-only attribute', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				read_only: true,
			},
		};

		render( <DateTime { ...props } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input ).toHaveAttribute( 'readonly' );
	} );

	it( 'uses HTML5 input when enabled and supported', () => {
		// Mock the support check
		const originalCreateElement = document.createElement;
		document.createElement = jest.fn().mockImplementation( ( tag ) => {
			const element = originalCreateElement.call( document, tag );
			if ( tag === 'input' ) {
				Object.defineProperty( element, 'type', {
					get: function() { return this.getAttribute( 'type' ); },
					set: function( value ) { this.setAttribute( 'type', value ); },
				} );
				// Mock browser support for HTML5 date inputs
				element.value = '';
			}
			return element;
		} );

		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				datetime_html5: true,
			},
		};

		render( <DateTime { ...props } /> );

		const input = screen.getByTestId( 'datetime-input' );
		expect( input ).toHaveAttribute( 'type', 'datetime-local' );

		// Restore original createElement
		document.createElement = originalCreateElement;
	} );

	it( 'applies validation rules', () => {
		const props = {
			...BASE_PROPS,
		};

		render( <DateTime { ...props } /> );

		expect( props.addValidationRules ).toHaveBeenCalled();
	} );
} );
