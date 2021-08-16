/**
 * External dependencies
 */
import { mount } from 'enzyme';

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

		const wrapper = mount( <Boolean { ...props } /> );

		expect( wrapper.find( 'input' ).props().type ).toEqual( 'checkbox' );
		expect( wrapper.find( 'label' ).text() ).toEqual( 'Yes' );
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
			setValue: jest.fn(),
		};

		const wrapper = mount( <Boolean { ...props } /> );
		const labels = wrapper.find( 'label' );
		const yesInput = wrapper.find( 'input[value="1"]' );
		const noInput = wrapper.find( 'input[value="0"]' );

		yesInput.getDOMNode().checked = ! yesInput.getDOMNode().checked;
		yesInput.simulate( 'change' );

		noInput.getDOMNode().checked = ! noInput.getDOMNode().checked;
		noInput.simulate( 'change' );

		expect( yesInput.props().type ).toEqual( 'radio' );
		expect( labels.at( 0 ).text() ).toEqual( 'True' );
		expect( labels.at( 1 ).text() ).toEqual( 'False' );

		expect( props.setValue ).toHaveBeenNthCalledWith( 1, '1' );
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
			setValue: jest.fn(),
		};

		const wrapper = mount( <Boolean { ...props } /> );
		const input = wrapper.find( 'select' );

		input.simulate( 'change', {
			target: { value: '1' },
		} );

		expect( wrapper.find( 'option' ).at( 0 ).text() ).toEqual( '-- Select One --' );
		expect( wrapper.find( 'option' ).at( 1 ).text() ).toEqual( 'True' );
		expect( wrapper.find( 'option' ).at( 2 ).text() ).toEqual( 'False' );

		expect( props.setValue ).toHaveBeenNthCalledWith( 1, '1' );
	} );
} );
