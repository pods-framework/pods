/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * Internal dependencies
 */
import NumberField from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Number Field',
		name: 'test_number_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'number',
	},
};

describe( 'Number field component', () => {
	it( 'creates a text field by default', () => {
		const props = { ...BASE_PROPS };

		const wrapper = mount( <NumberField { ...props } /> );

		expect(
			wrapper.find( 'input' ).props().type
		).toBe( 'text' );
	} );

	it( 'applies the relevant attributes to the text input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				number_decimals: '2',
				number_format: '9.999,99',
				number_format_soft: '0',
				number_format_type: 'number',
				number_max_length: '5',
				number_placeholder: 'Number Field',
			},
		};

		const wrapper = mount( <NumberField { ...props } /> );
		const input = wrapper.find( 'input' );

		expect( input.props().type ).toEqual( 'text' );
		expect( input.props().placeholder ).toEqual( 'Number Field' );
	} );

	it( 'applies the relevant attributes to the number input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				number_decimals: '2',
				number_format: '9.999,99',
				number_format_soft: '0',
				number_format_type: 'number',
				number_html5: '1',
				number_max_length: '5',
				number_placeholder: 'Number Field',
			},
		};

		const wrapper = mount( <NumberField { ...props } /> );
		const input = wrapper.find( 'input' );

		expect( input.props().type ).toEqual( 'number' );
		expect( input.props().placeholder ).toEqual( 'Number Field' );
		expect( input.props().step ).toEqual( 'any' );
	} );

	it( 'applies the relevant attributes to the slider input field', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				number_decimals: '2',
				number_format: '9.999,99',
				number_format_soft: '0',
				number_format_type: 'slider',
				number_placeholder: 'Number Field',
				number_max: '1000',
				number_min: '-1000',
				number_step: '100',
			},
		};

		const wrapper = mount( <NumberField { ...props } /> );
		const input = wrapper.find( 'input' );

		expect( input.props().type ).toEqual( 'range' );
		expect( input.props().placeholder ).toEqual( 'Number Field' );
		expect( input.props().max ).toEqual( 1000 );
		expect( input.props().min ).toEqual( -1000 );
		expect( input.props().step ).toEqual( 100 );
	} );

	it( 'calls the setValue callback once updated', () => {
		const props = {
			...BASE_PROPS,
			setValue: jest.fn(),
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				number_decimals: '2',
				number_format: '9.999,99',
			},
		};

		const wrapper = mount( <NumberField { ...props } /> );
		const input = wrapper.find( 'input' ).first();

		input.simulate( 'change', {
			target: { value: '1000' },
		} );

		input.simulate( 'blur' );

		expect( props.setValue ).toHaveBeenCalledWith( 1000 );
	} );
} );
