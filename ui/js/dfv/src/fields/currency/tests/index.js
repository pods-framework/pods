/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * Internal dependencies
 */
import Currency from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Currency Field',
		name: 'test_currency_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'number',
	},
};

describe( 'Currency field component', () => {
	it( 'creates a text field by default', () => {
		const props = { ...BASE_PROPS };

		const wrapper = mount( <Currency { ...props } /> );

		expect(
			wrapper.find( 'input' ).props().type
		).toBe( 'text' );
	} );
} );
