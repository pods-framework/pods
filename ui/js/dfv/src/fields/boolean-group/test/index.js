/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

/**
 * Internal dependencies
 */
import BooleanGroup from '..';

const BASE_PROPS = {
	values: {},
	allPodValues: {},
	allFieldsMap: new Map(),
	setOptionValue: jest.fn(),
	setHasBlurred: jest.fn(),
	addValidationRules: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Boolean Group Field',
		name: 'test_boolean_group_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'boolean_group',
		boolean_group: [
			{
				name: 'admin_only',
				label: 'Restrict access to Admins',
				default: 0,
				type: 'boolean',
				dependency: true,
				help: 'Some help text.',
			},
			{
				name: 'restrict_role',
				label: 'Restrict access by Role',
				default: 0,
				type: 'boolean',
				dependency: true,
			},
			{
				name: 'read_only',
				label: 'Make field "Read Only" in UI',
				default: 0,
				type: 'boolean',
				help: 'Some help text.',
				enable_conditional_logic: '1',
				conditional_logic: {
					action: 'show',
					logic: 'any',
					rules: [
						{
							field: 'type',
							compare: 'in',
							value: [
								'boolean',
								'color',
								'currency',
								'date',
							],
						},
					],
				},
			},
		],
	},
};

describe( 'Boolean Group field component', () => {
	it( 'renders the correct subfields with labels and tooltips', () => {
		const props = { ...BASE_PROPS };

		render( <BooleanGroup {...props} /> );

		const firstCheckbox = screen.getByLabelText( 'Restrict access to Admins' );
		expect( firstCheckbox.checked ).toEqual( false );
		expect( firstCheckbox.type ).toEqual( 'checkbox' );

		const secondCheckbox = screen.getByLabelText( 'Restrict access by Role' );
		expect( secondCheckbox.checked ).toEqual( false );
		expect( secondCheckbox.type ).toEqual( 'checkbox' );

		expect( () => screen.getByLabelText( 'Make field "Read Only" in UI' ) ).toThrow();
	} );

	it( 'displays correct values', () => {
		const props = {
			...BASE_PROPS,
			setOptionValue: jest.fn(),
			values: {
				admin_only: false,
				restrict_role: '1',
			},
		};

		render( <BooleanGroup {...props} /> );

		const firstCheckbox = screen.getByLabelText( 'Restrict access to Admins' );
		expect( firstCheckbox.checked ).toEqual( false );
		expect( firstCheckbox.type ).toEqual( 'checkbox' );

		const secondCheckbox = screen.getByLabelText( 'Restrict access by Role' );
		expect( secondCheckbox.checked ).toEqual( true );
		expect( secondCheckbox.type ).toEqual( 'checkbox' );

		expect( () => screen.getByLabelText( 'Make field "Read Only" in UI' ) ).toThrow();
	} );

	it( 'handles dependency logic', () => {
		const props = {
			...BASE_PROPS,
			allPodValues: {
				type: 'currency',
			},
		};

		render( <BooleanGroup {...props} /> );

		const firstCheckbox = screen.getByLabelText( 'Restrict access to Admins' );
		expect( firstCheckbox.checked ).toEqual( false );
		expect( firstCheckbox.type ).toEqual( 'checkbox' );

		const secondCheckbox = screen.getByLabelText( 'Restrict access by Role' );
		expect( secondCheckbox.checked ).toEqual( false );
		expect( secondCheckbox.type ).toEqual( 'checkbox' );

		const thirdCheckbox = screen.getByLabelText( 'Make field "Read Only" in UI' );
		expect( thirdCheckbox.checked ).toEqual( false );
		expect( thirdCheckbox.type ).toEqual( 'checkbox' );
	} );
} );
