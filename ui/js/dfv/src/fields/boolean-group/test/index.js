/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * Internal dependencies
 */
import BooleanGroup from '..';

const BASE_PROPS = {
	values: {},
	allPodValues: {},
	allFieldsMap: new Map(),
	setOptionValue: jest.fn(),
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
				'depends-on': {
					type: [
						'boolean',
						'color',
						'currency',
						'date',
					],
				},
			},
		],
	},
};

describe( 'Boolean Group field component', () => {
	it( 'renders the correct subfields with labels and tooltips', () => {
		const props = { ...BASE_PROPS };

		const wrapper = mount( <BooleanGroup { ...props } /> );

		expect( wrapper.find( 'input' ).first().props().type ).toEqual( 'checkbox' );
		expect( wrapper.find( 'label' ).first().text() ).toEqual( 'Restrict access to Admins' );

		expect( wrapper.find( 'input' ).at( 1 ).props().type ).toEqual( 'checkbox' );
		expect( wrapper.find( 'label' ).at( 1 ).text() ).toEqual( 'Restrict access by Role' );
	} );

	it( 'displays and updates values', () => {
		const props = {
			...BASE_PROPS,
			setOptionValue: jest.fn(),
			values: {
				admin_only: false,
				restrict_role: '1',
			},
		};

		const wrapper = mount( <BooleanGroup { ...props } /> );
		const firstCheckbox = wrapper.find( 'input[name="admin_only"]' );
		const secondCheckbox = wrapper.find( 'input[name="restrict_role"]' );

		expect( firstCheckbox.getDOMNode().checked ).toEqual( false );
		expect( secondCheckbox.getDOMNode().checked ).toEqual( true );

		firstCheckbox.getDOMNode().checked = ! firstCheckbox.getDOMNode().checked;
		secondCheckbox.getDOMNode().checked = ! secondCheckbox.getDOMNode().checked;

		firstCheckbox.simulate( 'change' );
		secondCheckbox.simulate( 'change' );

		expect( props.setOptionValue ).toHaveBeenNthCalledWith( 1, 'admin_only', true );
		expect( props.setOptionValue ).toHaveBeenNthCalledWith( 2, 'restrict_role', false );
	} );

	it( 'handles dependency logic', () => {
		const props = { ...BASE_PROPS };

		const wrapper = mount( <BooleanGroup { ...props } /> );

		expect( wrapper.find( 'input[name="read_only"]' ) ).toHaveLength( 0 );

		wrapper.setProps( {
			allPodValues: {
				type: 'currency',
			},
		} );

		expect( wrapper.find( 'input[name="read_only"]' ) ).toHaveLength( 1 );

		wrapper.setProps( {
			allPodValues: {
				type: 'something else',
			},
		} );

		expect( wrapper.find( 'input[name="read_only"]' ) ).toHaveLength( 0 );
	} );
} );
