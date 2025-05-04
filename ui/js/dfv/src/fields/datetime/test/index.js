/**
 * External dependencies
 */
import { render, screen, fireEvent } from '@testing-library/react';
import { act } from 'react';
import ReactDatetime from 'react-datetime';

/**
 * Internal dependencies
 */
import DateTime from '..';

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

describe( 'DateTime field component', () => {
	it( 'renders the DateTime component with the correct formats', () => {
		const props = { ...BASE_PROPS };

		render( <DateTime { ...props } /> );

		const input = screen.getByRole( 'textbox' );

		expect( input.type ).toEqual( 'text' );

		expect( () => screen.getAllByRole( 'table' ) ).toThrow();

		// Click Dropdown to open and render the actual field.
		act( () => fireEvent.click( input ) );

		expect( screen.getAllByRole( 'table' ) ).toHaveLength( 1 );

		// @todo Figure out this test.
		/*expect( wrapper.find( ReactDatetime ).dateFormat ).toEqual( 'MM-DD-YYYY' );
		expect( wrapper.find( ReactDatetime ).props().timeFormat ).toEqual( 'h:mm A' );*/
	} );

	it( 'renders the DateTime component with only a time picker', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				type: 'time',
			},
		};

		// This is really messed up with React state update handling with wordpress/components dropdown.
		expect( () => render( <DateTime { ...props } /> ) ).toThrow();

		const input = screen.getByRole( 'textbox' );

		expect( input.type ).toEqual( 'text' );

		expect( () => screen.getAllByRole( 'table' ) ).toThrow();

		// Click Dropdown to open and render the actual field.
		act( () => fireEvent.click( input ) );

		expect( screen.getAllByRole( 'table' ) ).toHaveLength( 1 );

		// @todo Figure out this test.
		/*expect( wrapper.find( ReactDatetime ).props().dateFormat ).toEqual( false );
		expect( wrapper.find( ReactDatetime ).props().timeFormat ).toEqual( 'h:mm A' );*/
	} );

	it( 'renders the DateTime component with only a date picker', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				type: 'date',
			},
		};

		render( <DateTime { ...props } /> );

		const input = screen.getByRole( 'textbox' );

		expect( input.type ).toEqual( 'text' );

		expect( () => screen.getAllByRole( 'table' ) ).toThrow();

		// Click Dropdown to open and render the actual field.
		act( () => fireEvent.click( input ) );

		expect( screen.getAllByRole( 'table' ) ).toHaveLength( 1 );

		// @todo Figure out this test.
		/*expect( wrapper.find( ReactDatetime ).props().dateFormat ).toEqual( 'MM-DD-YYYY' );
		expect( wrapper.find( ReactDatetime ).props().timeFormat ).toEqual( false );*/
	} );
} );
