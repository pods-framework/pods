/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

/**
 * Internal dependencies
 */
import Email from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Email Field',
		name: 'test_email_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'email',
	},
};

describe( 'Email field component', () => {
	it( 'creates a text field if the HTML5 email option is not set', () => {
		const props = { ...BASE_PROPS };

		render( <Email { ...props } /> );

		const input = screen.getByRole( 'textbox' );

		expect( input.type ).toEqual( 'text' );
	} );

	it( 'applies the relevant attributes to the input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				email_html5: true,
				email_max_length: 20,
				email_placeholder: 'Some placeholder for the field',
			},
		};

		render( <Email { ...props } /> );

		const input = screen.getByRole( 'textbox' );

		expect( input.type ).toEqual( 'email' );
		expect( input.maxLength ).toEqual( 20 );
		expect( input.placeholder ).toEqual( 'Some placeholder for the field' );
	} );
} );
