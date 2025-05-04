/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

/**
 * Internal dependencies
 */
import Password from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Password Field',
		name: 'test_password_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'password',
	},
};

describe( 'Email field component', () => {
	it( 'applies the relevant attributes to the password input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				password_max_length: 20,
				password_placeholder: 'Some placeholder for the field',
			},
		};

		render( <Password { ...props } /> );

		const input = screen.getByRole( 'generic' ).firstChild;
		expect( input.type ).toEqual( 'password' );
		expect( input.maxLength ).toEqual( 20 );
		expect( input.placeholder ).toEqual( 'Some placeholder for the field' );
	} );
} );
