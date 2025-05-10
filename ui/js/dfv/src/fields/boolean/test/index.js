/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

/**
 * Internal dependencies
 */
import Boolean from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Boolean Field',
		name: 'test_boolean_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'boolean',
	},
};

describe( 'Boolean field component', () => {
	it( 'creates a checkbox field with the default label', () => {
		const props = { ...BASE_PROPS };

		render( <Boolean { ...props } /> );

		expect( screen.getByRole( 'checkbox' ).type ).toEqual( 'checkbox' );

		const yesInput = screen.getByLabelText( 'Yes' );
		expect( yesInput.type ).toEqual( 'checkbox' );
		expect( yesInput.value ).toEqual( '1' );
	} );

	it( 'renders radio buttons with custom labels and handles changes', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				boolean_format_type: 'radio',
				boolean_yes_label: 'True',
				boolean_no_label: 'False',
			},
		};

		render( <Boolean { ...props } /> );

		expect( screen.getAllByRole( 'radio' ) ).toHaveLength( 2 );

		const yesInput = screen.getByLabelText( 'True' );
		expect( yesInput.type ).toEqual( 'radio' );
		expect( yesInput.value ).toEqual( '1' );
		expect( yesInput.checked ).toEqual( false );

		const noInput = screen.getByLabelText( 'False' );
		expect( noInput.type ).toEqual( 'radio' );
		expect( noInput.value ).toEqual( '0' );
		expect( noInput.checked ).toEqual( true );
	} );

	it( 'renders a dropdown menu with custom labels and handles changes', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				boolean_format_type: 'dropdown',
				boolean_yes_label: 'True',
				boolean_no_label: 'False',
			},
		};

		render( <Boolean { ...props } /> );

		const select = screen.getAllByRole( 'combobox' );
		expect( select ).toHaveLength( 1 );

		const options = screen.getAllByRole( 'option' );
		expect( options ).toHaveLength( 2 );

		const yesInput = screen.getByRole( 'option', { name: 'True' } );
		expect( yesInput.value ).toEqual( '1' );
		expect( yesInput.selected ).toEqual( false );

		const noInput = screen.getByRole( 'option', { name: 'False' } );
		expect( noInput.value ).toEqual( '0' );
		expect( noInput.selected ).toEqual( true );
	} );
} );
