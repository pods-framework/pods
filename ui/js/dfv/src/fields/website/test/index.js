/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * Internal dependencies
 */
import Website from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Website Field',
		name: 'test_website_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'website',
	},
};

describe( 'Website field component', () => {
	it( 'creates a text field if the HTML5 website option is not set', () => {
		const props = { ...BASE_PROPS };

		const wrapper = mount( <Website { ...props } /> );

		expect(
			wrapper.find( 'input' ).props().type
		).toBe( 'text' );
	} );

	it( 'applies the relevant attributes to the input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				website_html5: true,
				website_max_length: 20,
				website_placeholder: 'Some placeholder for the field',
			},
		};

		const wrapper = mount( <Website { ...props } /> );
		const input = wrapper.find( 'input' );

		expect( input.props().type ).toEqual( 'url' );
		expect( input.props().maxLength ).toEqual( 20 );
		expect( input.props().placeholder ).toEqual( 'Some placeholder for the field' );
	} );

	it( 'calls the setValue callback once updated', () => {
		const props = {
			...BASE_PROPS,
			setValue: jest.fn(),
		};

		const wrapper = mount( <Website { ...props } /> );
		const input = wrapper.find( 'input' ).first();
		input.simulate( 'change', {
			target: { value: 'https://pods.io' },
		} );

		expect( props.setValue ).toHaveBeenCalledWith( 'https://pods.io' );
	} );
} );
