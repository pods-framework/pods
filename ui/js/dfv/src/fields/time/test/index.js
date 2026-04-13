/**
 * External dependencies
 */
import { render, screen, fireEvent } from '@testing-library/react';
import { act } from 'react';
import ReactDatetime from 'react-datetime';

/**
 * Internal dependencies
 */
import Time from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Time Field',
		name: 'test_datetime_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'time',
		time_format: 'h_mm_A',
		time_type: '12',
	},
};

describe( 'Time field component', () => {
	it( 'renders the Time component with the correct formats', () => {
		const props = { ...BASE_PROPS };

		render( <Time { ...props } /> );

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
} );
