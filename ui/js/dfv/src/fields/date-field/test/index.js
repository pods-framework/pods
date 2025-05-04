/**
 * External dependencies
 */
import { render, screen, fireEvent } from '@testing-library/react';
import { act } from 'react';
import ReactDatetime from 'react-datetime';

/**
 * Internal dependencies
 */
import DateField from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Date Field',
		name: 'test_date_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'date',
		date_format: 'mdy_dash',
		date_type: 'format',
	},
};

describe( 'DateField field component', () => {
	it( 'renders the DateField component with the correct formats', () => {
		const props = { ...BASE_PROPS };

		render( <DateField { ...props } /> );

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
