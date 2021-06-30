/**
 * External dependencies
 */
import { mount } from 'enzyme';

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

		const wrapper = mount( <Text { ...props } /> );

		expect(
			wrapper.find( 'input' ).props().type
		).toBe( 'text' );
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

		const wrapper = mount( <Text { ...props } /> );
		const input = wrapper.find( 'input' );

		expect( input.props().type ).toEqual( 'text' );
		expect( input.props().maxLength ).toEqual( 20 );
		expect( input.props().placeholder ).toEqual( 'Some placeholder for the field' );
	} );

	it( 'calls the setValue callback once updated', () => {
		const props = {
			...BASE_PROPS,
			setValue: jest.fn(),
		};

		const wrapper = mount( <Text { ...props } /> );
		const input = wrapper.find( 'input' ).first();
		input.simulate( 'change', {
			target: { value: 'test@example.com' },
		} );

		expect( props.setValue ).toHaveBeenCalledWith( 'test@example.com' );
	} );
} );
