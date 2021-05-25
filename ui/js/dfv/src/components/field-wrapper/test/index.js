/**
 * External dependencies
 */
import { mount } from 'enzyme';
import { fake } from '@jackfranklin/test-data-bot';

/**
 * Internal dependencies
 */
import FieldWrapper from '..';

const BASE_PROPS = {
	field: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Text Field',
		name: 'test_text_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'text',
	},
	allPodFieldsMap: {},
	value: '',
	setOptionValue: jest.fn(),
	allPodValues: {},
};

describe( 'FieldWrapper component', () => {
	it( 'renders a field component', () => {
		const props = { ...BASE_PROPS };

		const wrapper = mount( <FieldWrapper { ...props } /> );

		expect( wrapper.find( 'label' ).text() ).toEqual( 'Test Text Field' );
		expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );
	} );

	// @todo add additional tests for the other types of dependencies
	it( 'doesn\'t render a field that doesn\'t meet all depends-on dependencies', () => {
		const companyName = fake( ( f ) => f.company.companyName() );
		const personName = fake( ( f ) => f.name.findName() );

		const props = {
			...BASE_PROPS,
			field: {
				...BASE_PROPS.field,
				'depends-on': {
					'some-company': companyName,
					'some-name': personName,
				},
			},
			allPodValues: {
				'some-company': '',
				'some-name': '',
			},
			allPodFieldsMap: new Map( [
				[ 'some-company', {
					name: 'some_company_field',
					object_type: 'field',
					parent: 'pod/_pods_pod',
					type: 'text',
				} ],
				[ 'some-name', {
					name: 'some_name_field',
					object_type: 'field',
					parent: 'pod/_pods_pod',
					type: 'text',
				} ],
			] ),
		};

		const wrapper = mount( <FieldWrapper { ...props } /> );

		expect( wrapper.find( 'input' ).exists() ).toEqual( false );

		wrapper.setProps( {
			allPodValues: {
				'some-company': companyName,
			},
		} );

		expect( wrapper.find( 'input' ).exists() ).toEqual( false );

		wrapper.setProps( {
			allPodValues: {
				'some-company': companyName,
				'some-name': personName,
			},
		} );

		expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );
	} );

	it( 'shows validation messages on a required field after the field has blurred', () => {
		const props = {
			...BASE_PROPS,
			field: {
				...BASE_PROPS.field,
				required: true,
			},
		};

		const wrapper = mount( <FieldWrapper { ...props } /> );

		expect(
			wrapper.findWhere( ( node ) => node.text() === 'Test Text Field is required.' ).exists()
		).toEqual( false );

		wrapper.find( 'input' ).at( 0 ).simulate( 'blur' );

		expect(
			wrapper.findWhere( ( node ) => node.text() === 'Test Text Field is required.' ).exists()
		).toEqual( true );

		wrapper.setProps( {
			value: 'A valid value',
		} );

		wrapper.find( 'input' ).at( 0 ).simulate( 'change' );

		expect(
			wrapper.findWhere( ( node ) => node.text() === 'Test Text Field is required.' ).exists()
		).toEqual( false );
	} );
} );
