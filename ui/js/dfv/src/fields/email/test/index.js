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
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
			},
			addValidationRules: jest.fn(),
			setValue: jest.fn(),
		};

		const wrapper = mount( <Email { ...props } /> );

		expect(
			wrapper.find( 'input' ).props().type
		).toBe( 'text' );
	} );

	it( 'creates an "email" type field if the HTML5 email option is set', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				email_html5: true,
			},
			addValidationRules: jest.fn(),
			setValue: jest.fn(),
		};

		const wrapper = mount( <Email { ...props } /> );

		expect(
			wrapper.find( 'input' ).props().type
		).toBe( 'email' );
	} );

	it( 'applies the max length attribute ', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				email_max_length: 20,
			},
			addValidationRules: jest.fn(),
			setValue: jest.fn(),
		};

		const wrapper = mount( <Email { ...props } /> );

		expect(
			wrapper.find( 'input' ).props().maxLength
		).toBe( 20 );
	} );

	it( 'applies the placeholder attribute ', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				placeholder: 'Some placeholder for the field',
			},
			addValidationRules: jest.fn(),
			setValue: jest.fn(),
		};

		const wrapper = mount( <Email { ...props } /> );

		expect(
			wrapper.find( 'input' ).props().placeholder
		).toEqual( 'Some placeholder for the field' );
	} );
} );
