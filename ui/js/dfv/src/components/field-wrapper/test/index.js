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

	const updatePodValue = ( fieldSlug, value ) => wrapper.setProps( {
		...wrapper.props(),
		allPodValues: {
			...wrapper.props().allPodValues,
			[ fieldSlug ]: value,
		},
	} );

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

test( 'doesn\'t render a field until meeting all depends-on dependencies', () => {
	const companyName = faker.company.companyName();
	const personName = faker.name.findName();

	const { wrapper, updatePodValue } = setup( {
		'depends-on': {
			'some-company': companyName,
			'some-name': personName,
		},
	} );

	// Should not render before updating dependencies.
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Still shouldn't render with just the first dependency.
	updatePodValue( 'some-company', companyName );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Renders after the second dependency.
	updatePodValue( 'some-name', personName );
	expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );
} );

test( 'doesn\'t render a field until meeting any depends-on-any dependencies', () => {
	const companyName = faker.company.companyName();
	const personName = faker.name.findName();

	const { wrapper, updatePodValue } = setup( {
		'depends-on-any': {
			'some-company': companyName,
			'some-name': personName,
		},
	} );

	// Input field won't be found if neither dependency is set.
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Set one of the depends-on-any values.
	updatePodValue( 'some-company', companyName );
	expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );

	// And try the other one.
	updatePodValue( 'some-name', personName );
	expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );
} );

test( 'doesn\'t render a field if it meets any excludes-on dependencies', () => {
	const companyName = faker.company.companyName();
	const personName = faker.name.findName();

	const { wrapper, updatePodValue } = setup( {
		'excludes-on': {
			'some-company': companyName,
			'some-name': personName,
		},
	} );

	// Input field will be found if neither exclusion dependency is set.
	expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );

	// Set one of the excludes-on values.
	updatePodValue( 'some-company', companyName );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// And try the other one.
	updatePodValue( 'some-name', personName );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );
} );

test( 'doesn\'t render a field until meeting all wildcard-on dependencies', () => {
	const taxonomyName = 'taxonomy-' + faker.company.companyName();
	const podName = 'pod-' + faker.company.companyName();

	const { wrapper, updatePodValue } = setup(
		{
			'wildcard-on': {
				type: [ '^taxonomy-.*$', '^pod-.*$' ],
			},
		},
		{
			allPodFieldsMap: new Map( [
				[ 'type', {
					name: 'some_type',
					object_type: 'field',
					parent: 'pod/_pods_pod',
					type: 'text',
				} ],
			] ),
		},
	);

	// Will not render if the wildcard value does not match.
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Set an invalid match.
	updatePodValue( 'type', 'something-else' );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Match the first pattern.
	updatePodValue( 'type', taxonomyName );
	expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );

	// Match the second pattern.
	updatePodValue( 'type', podName );
	expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );
} );

test( 'doesn\'t render a field until grandparent depends-on dependencies are met', () => {
	const firstParentValue = faker.name.findName();
	const secondParentValue = faker.name.findName();

	const firstGrandparentValue = faker.name.findName();
	const secondGrandparentValue = faker.name.findName();

	const { wrapper, updatePodValue } = setup(
		{
			'depends-on': {
				'first-parent': firstParentValue,
				'second-parent': secondParentValue,
			},
		},
		{
			allPodFieldsMap: new Map( [
				[
					'first-parent',
					generateFieldConfig(
						'first-parent',
						{
							'depends-on': { 'first-grandparent': firstGrandparentValue },
						}
					),
				],
				[
					'second-parent',
					generateFieldConfig(
						'second-parent',
						{
							'depends-on': { 'second-grandparent': secondGrandparentValue },
						}
					),
				],
				[ 'first-grandparent', generateFieldConfig( 'first-grandparent' ) ],
				[ 'second-grandparent', generateFieldConfig( 'second-grandparent' ) ],
			] ),
		},
	);

	// Shouldn't render if no dependencies are met.
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Shouldn't render if at least one parent dependency is not met.
	updatePodValue( 'first-parent', firstParentValue );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Shouldn't render if parent dependencies are met, but their parent dependencies are not.
	updatePodValue( 'second-parent', secondParentValue );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Shouldn't render if parent dependencies are met, but only one grandparent dep is met.
	updatePodValue( 'first-grandparent', firstGrandparentValue );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Shouldn't render if grandparent dependencies are met, but parent dependencies are not.
	updatePodValue( 'first-parent', '' );
	updatePodValue( 'second-parent', '' );
	updatePodValue( 'first-grandparent', firstGrandparentValue );
	updatePodValue( 'second-grandparent', secondGrandparentValue );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Should render if both parent's values and the parent's parent's values match.
	updatePodValue( 'first-parent', firstParentValue );
	updatePodValue( 'second-parent', secondParentValue );
	expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );
} );

test( 'doesn\'t render a field until parent dependencies are met', () => {
	const firstParentValue = faker.name.findName();
	const secondParentValue = faker.name.findName();

	const firstGrandparentValue = faker.name.findName();
	const secondGrandparentValue = faker.name.findName();

	const { wrapper, updatePodValue } = setup(
		{
			'depends-on-any': {
				'first-parent': firstParentValue,
				'second-parent': secondParentValue,
			},
		},
		{
			allPodFieldsMap: new Map( [
				[
					'first-parent',
					generateFieldConfig(
						'first-parent',
						{
							'depends-on': { 'first-grandparent': firstGrandparentValue },
						}
					),
				],
				[
					'second-parent',
					generateFieldConfig(
						'second-parent',
						{
							'depends-on': { 'second-grandparent': secondGrandparentValue },
						}
					),
				],
				[ 'first-grandparent', generateFieldConfig( 'first-grandparent' ) ],
				[ 'second-grandparent', generateFieldConfig( 'second-grandparent' ) ],
			] ),
		},
	);

	// Shouldn't render if no dependencies are met.
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Shouldn't render if one parent dependency is met, but that parent's dependencies are not met.
	updatePodValue( 'first-parent', firstParentValue );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Shouldn't render if parent dependencies are met, but neither of their parent
	// dependencies are not.
	updatePodValue( 'second-parent', secondParentValue );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Should render if either/both parent dependencies are met, but only one grandparent dep is met.
	updatePodValue( 'first-grandparent', firstGrandparentValue );
	expect( wrapper.find( 'input' ).props().type ).toBe( 'text' );

	// Shouldn't render if grandparent dependencies are met, but parent dependencies are not.
	updatePodValue( 'first-parent', '' );
	updatePodValue( 'second-parent', '' );
	updatePodValue( 'first-grandparent', firstGrandparentValue );
	updatePodValue( 'second-grandparent', secondGrandparentValue );
	expect( wrapper.find( 'input' ).exists() ).toEqual( false );

	// Should render if both parent's values and the parent's parent's values match.
	updatePodValue( 'first-parent', firstParentValue );
	updatePodValue( 'second-parent', secondParentValue );
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
