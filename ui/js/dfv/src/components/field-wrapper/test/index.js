/**
 * External dependencies
 */
import { mount } from 'enzyme';
import faker from 'faker';

/**
 * Internal dependencies
 */
import FieldWrapper from '..';

const generateFieldConfig = ( name, additionalOptions = {} ) => {
	return {
		name,
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'text',
		enable_conditional_logic: false,
		...additionalOptions,
	};
};

const setup = ( additionalFieldProps = {}, otherProps = {} ) => {
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
		storeKey: 'test-pod',
		value: '',
		setOptionValue: jest.fn(),
		allPodValues: {
			'some-company': '',
			'some-name': '',
		},
		allPodFieldsMap: new Map( [
			[ 'some-company', generateFieldConfig( 'some-company' ) ],
			[ 'some-name', generateFieldConfig( 'some-name' ) ],
		] ),
	};

	const fullProps = {
		...BASE_PROPS,
		field: {
			...BASE_PROPS.field,
			...additionalFieldProps,
		},
		...otherProps,
	};

	const wrapper = mount( <FieldWrapper { ...fullProps } /> );

	const updatePodValue = ( fieldSlug, value ) => {
		wrapper.setProps( {
			...wrapper.props(),
			allPodValues: {
				...wrapper.props().allPodValues,
				[ fieldSlug ]: value,
			},
		} );
	};

	const updateFieldValue = ( value ) => {
		wrapper.setProps( { value } );
		wrapper.find( 'input' ).at( 0 ).simulate( 'change' );
	};

	return {
		wrapper,
		updatePodValue,
		updateFieldValue,
	};
};

test( 'renders a field component', () => {
	const { wrapper } = setup();

	expect( wrapper.find( 'label' ).text() ).toEqual( 'Test Text Field' );
	expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );
} );

test( 'doesn\'t render a field until conditional logic rules are met', () => {
	// Note: more complete tests for conditional logic are included in with the
	// useConditionalLogic hook.
	const companyName = faker.company.companyName();
	const personName = faker.name.findName();

	const { wrapper, updatePodValue } = setup( {
		enable_conditional_logic: true,
		conditional_logic: {
			action: 'show',
			logic: 'all',
			rules: [
				{
					field: 'some-company',
					compare: '=',
					value: companyName,
				},
				{
					field: 'some-name',
					compare: '=',
					value: personName,
				},
			],
		},
	} );

	// Should not render before updating dependencies.
	// expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Still shouldn't render with just the first dependency.
	updatePodValue( 'some-company', companyName );
	// expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Renders after the second dependency.
	updatePodValue( 'some-name', personName );
	expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );
} );

test( 'shows validation messages on a required field after the field has blurred', () => {
	const { wrapper, updateFieldValue } = setup( {
		required: true,
	} );

	// Don't show the error message before blurring the field.
	expect(
		wrapper.findWhere( ( node ) => node.text() === 'Test Text Field is required.' ).exists()
	).toEqual( false );

	// Show the error message after blurring.
	wrapper.find( 'input' ).at( 0 ).simulate( 'blur' );

	expect(
		wrapper.findWhere( ( node ) => node.text() === 'Test Text Field is required.' ).exists()
	).toEqual( true );

	// Hide the error once a valid value has been set.
	updateFieldValue( 'A valid value' );

	expect(
		wrapper.findWhere( ( node ) => node.text() === 'Test Text Field is required.' ).exists()
	).toEqual( false );
} );
