/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * Internal dependencies
 */
import Slug from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Slug Field',
		name: 'test_slug_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'slug',
	},
};

describe( 'Slug field component', () => {
	it( 'creates a field with the relevant attributes', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				slug_placeholder: 'Some placeholder for the field',
			},
		};

		const wrapper = mount( <Slug { ...props } /> );
		const input = wrapper.find( 'input' );

		expect( input.props().type ).toBe( 'text' );
		expect( input.props().placeholder ).toEqual( 'Some placeholder for the field' );
	} );

	it( 'calls the setValue callback once updated', () => {
		const props = {
			...BASE_PROPS,
			setValue: jest.fn(),
		};

		const wrapper = mount( <Slug { ...props } /> );
		const input = wrapper.find( 'input' ).first();

		input.simulate( 'change', {
			target: { value: 'test-123' },
		} );

		input.simulate( 'change', {
			target: { value: 'Something that needs to be formatted' },
		} );

		input.simulate( 'change', {
			target: { value: 'Test )*&^*😬and*()*)**&^*^# Test' },
		} );

		expect( props.setValue ).toHaveBeenNthCalledWith( 1, 'test-123' );
		expect( props.setValue ).toHaveBeenNthCalledWith( 2, 'something_that_needs_to_be_formatted' );
		expect( props.setValue ).toHaveBeenNthCalledWith( 3, 'test_and_test' );
	} );

	it( 'calls the setValue callback once updated with dash fallback', () => {
		const props = {
			...BASE_PROPS,
			setValue: jest.fn(),
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				slug_separator: '-',
			},
		};

		const wrapper = mount( <Slug { ...props } /> );
		const input = wrapper.find( 'input' ).first();

		input.simulate( 'change', {
			target: { value: 'test-123' },
		} );

		input.simulate( 'change', {
			target: { value: 'Something that needs to be_formatted' },
		} );

		input.simulate( 'change', {
			target: { value: 'Test )*&^*😬and*()*)**&^*^# Test' },
		} );

		expect( props.setValue ).toHaveBeenNthCalledWith( 1, 'test-123' );
		expect( props.setValue ).toHaveBeenNthCalledWith( 2, 'something-that-needs-to-be_formatted' );
		expect( props.setValue ).toHaveBeenNthCalledWith( 3, 'test-and-test' );
	} );
} );
