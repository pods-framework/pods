/**
 * External dependencies
 */
import { render, screen, fireEvent } from '@testing-library/react';

/**
 * Internal dependencies
 */
import Slug from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Slug Field',
		name: 'test_slug_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'slug',
	},
};

describe( 'Slug field component', () => {
	it( 'creates a field with the relevant attributes', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				slug_placeholder: 'Some placeholder for the field',
			},
		};

		render( <Slug { ...props } /> );

		const input = screen.getByRole( 'textbox' );
		expect( input.type ).toBe( 'text' );
		expect( input.placeholder ).toEqual( 'Some placeholder for the field' );
	} );

	it( 'calls the setValue callback once updated', () => {
		render( <Slug { ...BASE_PROPS } /> );

		const input = screen.getByRole( 'textbox' );

		fireEvent.change( input, {
			target: { value: 'test-123' },
		} );

		fireEvent.change( input, {
			target: { value: 'Something that needs to be formatted' },
		} );

		fireEvent.change( input, {
			target: { value: 'Test )*&^*😬and*()*)**&^*^# Test' },
		} );

		expect( BASE_PROPS.setValue ).toHaveBeenNthCalledWith( 1, 'test-123' );
		expect( BASE_PROPS.setValue ).toHaveBeenNthCalledWith( 2, 'something_that_needs_to_be_formatted' );
		expect( BASE_PROPS.setValue ).toHaveBeenNthCalledWith( 3, 'test_and_test' );
	} );

	it( 'calls the setValue callback once updated with dash fallback', () => {
		const props = {
			...BASE_PROPS,
			setValue: jest.fn(),
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				slug_separator: '-',
			},
		};

		render( <Slug { ...props } /> );

		const input = screen.getByRole( 'textbox' );

		fireEvent.change( input, {
			target: { value: 'test-123' },
		} );

		fireEvent.change( input, {
			target: { value: 'Something that needs to be_formatted' },
		} );

		fireEvent.change( input, {
			target: { value: 'Test )*&^*😬and*()*)**&^*^# Test' },
		} );

		expect( props.setValue ).toHaveBeenNthCalledWith( 1, 'test-123' );
		expect( props.setValue ).toHaveBeenNthCalledWith( 2, 'something-that-needs-to-be_formatted' );
		expect( props.setValue ).toHaveBeenNthCalledWith( 3, 'test-and-test' );
	} );
} );
