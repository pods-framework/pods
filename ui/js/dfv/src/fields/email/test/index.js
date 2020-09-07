/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * Internal dependencies
 */
import Email from '..';

describe( 'Email field component', () => {
	it( 'applies the type option', () => {
		const props = {
			addValidationRules: jest.fn(),
			fieldConfig: {
				type: 'text',
			},
		};

		const wrapper = mount(
			<Email { ...props } />
		);
		const inputNode = wrapper.find( 'input' );

		expect( inputNode.type ).toBe( 'text' );
	} );

	it( 'overrides the type option if the html5 email option is set', () => {
		const props = {
			addValidationRules: jest.fn(),
			fieldConfig: {
				email_html5: true,
			},
		};

		const wrapper = mount(
			<Email { ...props } />
		);
		const inputNode = wrapper.find( 'input' );

		expect( inputNode.type ).toBe( 'email' );
	} );

	it( 'applies the max length attribute ', () => {
		const props = {
			addValidationRules: jest.fn(),
			fieldConfig: {
				max_length: 20,
			},
		};

		const wrapper = mount(
			<Email { ...props } />
		);
		const inputNode = wrapper.find( 'input' );

		expect( inputNode.maxlegnth ).toBe( 20 );
	} );

	it( 'applies the placeholder attribute ', () => {
		const props = {
			addValidationRules: jest.fn(),
			fieldConfig: {
				placeholder: 'Some placeholder for the field',
			},
		};

		const wrapper = mount(
			<Email { ...props } />
		);
		const inputNode = wrapper.find( 'input' );

		expect( inputNode.placeholder ).toEqual( 'Some placeholder for the field' );
	} );
} );
