/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * Internal dependencies
 */
import Password from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Password Field',
		name: 'test_password_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'password',
	},
};

describe( 'Email field component', () => {
	it( 'applies the relevant attributes to the password input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				password_max_length: 20,
				password_placeholder: 'Some placeholder for the field',
			},
		};

		const wrapper = mount( <Password { ...props } /> );
		const input = wrapper.find( 'input' );

		expect( input.props().type ).toBe( 'password' );
		expect( input.props().type ).toEqual( 'password' );
		expect( input.props().maxLength ).toEqual( 20 );
		expect( input.props().placeholder ).toEqual( 'Some placeholder for the field' );
	} );

	it( 'calls the setValue callback once updated', () => {
		const props = {
			...BASE_PROPS,
			setValue: jest.fn(),
		};

		const wrapper = mount( <Password { ...props } /> );
		const input = wrapper.find( 'input' ).first();
		input.simulate( 'change', {
			target: { value: 'test123' },
		} );

		expect( props.setValue ).toHaveBeenCalledWith( 'test123' );
	} );
} );
