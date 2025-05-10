/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

/**
 * Internal dependencies
 */
import Text from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Text Field',
		name: 'test_text_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'text',
	},
};

describe( 'Text field component', () => {
	it( 'creates a text field', () => {
		const props = { ...BASE_PROPS };

		render( <Text { ...props } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input.type ).toEqual( 'text' );
	} );

	it( 'applies the relevant attributes to the input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				text_max_length: 20,
				text_placeholder: 'Some placeholder for the field',
			},
		};

		render( <Text { ...props } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input.type ).toEqual( 'text' );
		expect( input.maxLength ).toEqual( 20 );
		expect( input.placeholder ).toEqual( 'Some placeholder for the field' );
	} );
} );
