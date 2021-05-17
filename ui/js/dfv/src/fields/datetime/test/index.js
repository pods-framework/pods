/**
 * External dependencies
 */
import { mount } from 'enzyme';
import ReactDatetime from 'react-datetime';

/**
 * Internal dependencies
 */
import DateTime from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	setHasBlurred: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test DateTime Field',
		name: 'test_datetime_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'datetime',
		datetime_format: 'mdy_dash',
		datetime_time_format: 'h_mm_A',
		datetime_time_type: '12',
		datetime_type: 'format',
	},
};

describe( 'DateTime field component', () => {
	it( 'renders the DateTime component with the correct formats', () => {
		const props = { ...BASE_PROPS };

		const wrapper = mount( <DateTime { ...props } /> );

		expect( wrapper.find( ReactDatetime ).props().dateFormat ).toEqual( 'MM-DD-YYYY' );
		expect( wrapper.find( ReactDatetime ).props().timeFormat ).toEqual( 'h:mm A' );
	} );

	it( 'renders the DateTime component with only a time picker', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				type: 'time',
			},
		};

		const wrapper = mount( <DateTime { ...props } /> );

		expect( wrapper.find( ReactDatetime ).props().dateFormat ).toEqual( false );
		expect( wrapper.find( ReactDatetime ).props().timeFormat ).toEqual( 'h:mm A' );
	} );

	it( 'renders the DateTime component with only a date picker', () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				type: 'date',
			},
		};

		const wrapper = mount( <DateTime { ...props } /> );

		expect( wrapper.find( ReactDatetime ).props().dateFormat ).toEqual( 'MM-DD-YYYY' );
		expect( wrapper.find( ReactDatetime ).props().timeFormat ).toEqual( false );
	} );
} );
