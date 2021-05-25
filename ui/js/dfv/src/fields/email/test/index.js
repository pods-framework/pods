/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * Internal dependencies
 */
import Email from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Email Field',
		name: 'test_email_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'email',
	},
};

describe( 'Email field component', () => {
	it( 'creates a text field if the HTML5 email option is not set', () => {
		const props = { ...BASE_PROPS };

		const wrapper = mount( <Email { ...props } /> );

		expect(
			wrapper.find( 'input' ).props().type
		).toBe( 'text' );
	} );

	it( 'applies the relevant attributes to the input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				email_html5: true,
				email_max_length: 20,
				email_placeholder: 'Some placeholder for the field',
			},
		};

		const wrapper = mount( <Email { ...props } /> );
		const input = wrapper.find( 'input' );

		expect( input.props().type ).toEqual( 'email' );
		expect( input.props().maxLength ).toEqual( 20 );
		expect( input.props().placeholder ).toEqual( 'Some placeholder for the field' );
	} );

	it( 'calls the setValue callback once updated', () => {
		const props = {
			...BASE_PROPS,
			setValue: jest.fn(),
		};

		const wrapper = mount( <Email { ...props } /> );
		const input = wrapper.find( 'input' ).first();
		input.simulate( 'change', {
			target: { value: 'test@example.com' },
		} );

		expect( props.setValue ).toHaveBeenCalledWith( 'test@example.com' );
	} );
} );
