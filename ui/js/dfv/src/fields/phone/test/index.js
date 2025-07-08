/**
 * External dependencies
 */
import { render, screen, fireEvent } from '@testing-library/react';

/**
 * Internal dependencies
 */
import Phone from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Phone Field',
		name: 'test_phone_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'phone',
	},
};

describe( 'Phone field component', () => {
	it( 'creates a text field if the HTML5 phone option is not set', () => {
		const props = { ...BASE_PROPS };

		render( <Phone { ...props } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input.type ).toEqual( 'text' );
	} );

	it( 'applies the relevant attributes to the input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				phone_html5: true,
				phone_max_length: 20,
				phone_placeholder: 'Some placeholder for the field',
			},
		};

		render( <Phone { ...props } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input.type ).toEqual( 'tel' );
		expect( input.maxLength ).toEqual( 20 );
		expect( input.placeholder ).toEqual( 'Some placeholder for the field' );
	} );

	it( 'calls the setValue callback once updated', () => {
		const props = {
			...BASE_PROPS,
		};

		render( <Phone { ...props } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input.type ).toEqual( 'text' );

		fireEvent.change( input, {
			target: { value: '123-456-7890' },
		} );

		expect( props.setValue ).toHaveBeenCalledWith( '123-456-7890' );
	} );
} );
