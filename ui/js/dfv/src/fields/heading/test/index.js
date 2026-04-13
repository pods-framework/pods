/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

/**
 * Internal dependencies
 */
import Heading from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Heading Field',
		name: 'test_heading_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'heading',
	},
};

describe( 'Heading field component', () => {
	it( 'creates a heading field', () => {
		const props = { ...BASE_PROPS };

		render( <Heading { ...props } /> );

		const input = screen.getByText( 'Test Heading Field' );

		expect( input.tagName ).toEqual( 'H2' );
	} );

	it( 'creates a heading field for h3', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				heading_tag: 'h3',
			},
		};

		render( <Heading { ...props } /> );

		const input = screen.getByText( 'Test Heading Field' );

		expect( input.tagName ).toEqual( 'H3' );
	} );

	it( 'creates a heading field for p', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				heading_tag: 'p',
			},
		};

		render( <Heading { ...props } /> );

		const input = screen.getByText( 'Test Heading Field' );

		expect( input.tagName ).toEqual( 'P' );
	} );

	it( 'creates a heading field for div', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				heading_tag: 'div',
			},
		};

		render( <Heading { ...props } /> );

		const input = screen.getByText( 'Test Heading Field' );

		expect( input.tagName ).toEqual( 'DIV' );
	} );
} );
