/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

/**
 * Internal dependencies
 */
import Website from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Website Field',
		name: 'test_website_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'website',
	},
};

describe( 'Website field component', () => {
	it( 'creates a text field if the HTML5 website option is not set', () => {
		const props = { ...BASE_PROPS };

		render( <Website { ...props } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input.type ).toBe( 'text' );
	} );

	it( 'applies the relevant attributes to the input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				website_html5: true,
				website_max_length: 20,
				website_placeholder: 'Some placeholder for the field',
			},
		};

		render( <Website { ...props } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input.type ).toBe( 'url' );
		expect( input.maxLength ).toEqual( 20 );
		expect( input.placeholder ).toEqual( 'Some placeholder for the field' );
	} );
} );
