/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

/**
 * Internal dependencies
 */
import HTMLField from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test HTML Field',
		name: 'test_heading_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'html',
		html_content: '<strong>Test content</strong>',
	},
};

describe( 'HTMLField field component', () => {
	it( 'creates a HTML field', () => {
		const props = { ...BASE_PROPS };

		render( <HTMLField { ...props } /> );

		expect( screen.getByRole( 'paragraph' ).outerHTML ).toEqual( '<p><strong>Test content</strong></p>' );
	} );

	it( 'creates a HTML field without wpautop', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				html_wpautop: false,
			},
		};

		render( <HTMLField { ...props } /> );

		expect( () => screen.getAllByRole( 'paragraph' ) ).toThrow();

		expect( screen.getByRole( 'strong' ).outerHTML ).toEqual( '<strong>Test content</strong>' );
	} );
} );
