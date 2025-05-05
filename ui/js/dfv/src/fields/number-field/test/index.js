/**
 * External dependencies
 */
import { render, screen, fireEvent } from '@testing-library/react';

/**
 * Internal dependencies
 */
import NumberField from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Number Field',
		name: 'test_number_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'number',
	},
};

describe( 'Number field component', () => {
	it( 'creates a text field by default', () => {
		const props = { ...BASE_PROPS };

		render( <NumberField { ...props } /> );

		const input = screen.getByRole( 'textbox' );

		expect( input.type ).toEqual( 'text' );
	} );

	it( 'applies the relevant attributes to the text input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				number_decimals: '2',
				number_format: '9.999,99',
				number_format_soft: '0',
				number_format_type: 'number',
				number_max_length: '5',
				number_placeholder: 'Number Field',
			},
		};

		render( <NumberField { ...props } /> );

		const input = screen.getByRole( 'textbox' );

		expect( input.type ).toEqual( 'text' );
		expect( input.placeholder ).toEqual( 'Number Field' );
	} );

	it( 'applies the relevant attributes to the number input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				number_decimals: '2',
				number_format: '9.999,99',
				number_format_soft: '0',
				number_format_type: 'number',
				number_html5: '1',
				number_max_length: '5',
				number_placeholder: 'Number Field',
			},
		};

		render( <NumberField { ...props } /> );

		const input = screen.getByRole( 'spinbutton' );

		expect( input.type ).toEqual( 'number' );
		expect( input.placeholder ).toEqual( 'Number Field' );
		expect( input.max ).toEqual( '' );
		expect( input.min ).toEqual( '' );
		expect( input.step ).toEqual( 'any' );
	} );

	it( 'applies the relevant attributes to the number input field with min max', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				number_decimals: '2',
				number_format: '9.999,99',
				number_format_soft: '0',
				number_format_type: 'number',
				number_html5: '1',
				number_max_length: '5',
				number_placeholder: 'Number Field',
				number_max: '1000',
				number_min: '-1000',
			},
		};

		render( <NumberField { ...props } /> );

		const input = screen.getByRole( 'spinbutton' );

		expect( input.type ).toEqual( 'number' );
		expect( input.placeholder ).toEqual( 'Number Field' );
		expect( input.max ).toEqual( '1000' );
		expect( input.min ).toEqual( '-1000' );
		expect( input.step ).toEqual( 'any' );
	} );

	it( 'applies the relevant attributes to the slider input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				number_decimals: '2',
				number_format: '9.999,99',
				number_format_soft: '0',
				number_format_type: 'slider',
				number_placeholder: 'Number Field',
				number_max: '1000',
				number_min: '-1000',
				number_step: '100',
			},
		};

		render( <NumberField { ...props } /> );

		const input = screen.getByRole( 'slider' );

		expect( input.type ).toEqual( 'range' );
		expect( input.placeholder ).toEqual( 'Number Field' );
		expect( input.max ).toEqual( '1000' );
		expect( input.min ).toEqual( '-1000' );
		expect( input.step ).toEqual( '100' );
	} );

	it( 'calls the setValue callback once updated', () => {
		const props = {
			...BASE_PROPS,
			setValue: jest.fn(),
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				number_decimals: '2',
				number_format: '9.999,99',
			},
		};

		render( <NumberField { ...props } /> );

		const input = screen.getByRole( 'textbox' );

		fireEvent.change( input, {
			target: { value: '1000' },
		} );

		fireEvent.blur( input );

		expect( props.setValue ).toHaveBeenCalledWith( 1000 );
	} );
} );
