import { renderHook } from '@testing-library/react-hooks';

import useConditionalLogic from '../useConditionalLogic';

const generateFieldConfig = ( name, additionalOptions = {} ) => {
	return {
		name,
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Text Field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'text',
		...additionalOptions,
	};
};

const generateFieldConfigWithConditionalRules = (
	enableConditionalLogic,
	action,
	logic,
	rules,
) => generateFieldConfig(
	'test_text_field',
	{
		enable_conditional_logic: enableConditionalLogic,
		conditional_logic: {
			action,
			logic,
			rules,
		},
	},
);

const BASE_ALL_POD_VALUES = {
	'some-company': '',
	'some-name': '',
};

const BASE_ALL_POD_FIELDS_MAP = new Map( [
	[ 'some-company', generateFieldConfig( 'some-company' ) ],
	[ 'some-name', generateFieldConfig( 'some-name' ) ],
] );

describe( 'conditional logic validation hook', () => {
	it( 'should pass if conditional logic is disabled', () => {
		const fieldConfig = generateFieldConfigWithConditionalRules(
			false,
			'show',
			'any',
			[],
		);

		const { result } = renderHook(
			() => useConditionalLogic(
				fieldConfig,
				BASE_ALL_POD_VALUES,
				BASE_ALL_POD_FIELDS_MAP,
			)
		);

		expect( result.current ).toBe( true );
	} );

	it( 'should pass if rules are empty', () => {
		const fieldConfig = generateFieldConfigWithConditionalRules(
			true,
			'show',
			'any',
			[],
		);

		const { result } = renderHook(
			() => useConditionalLogic(
				fieldConfig,
				BASE_ALL_POD_VALUES,
				BASE_ALL_POD_FIELDS_MAP,
			)
		);

		expect( result.current ).toBe( true );
	} );

	it( 'should show the field with show action and empty rules', () => {
		const fieldConfig = generateFieldConfigWithConditionalRules(
			true,
			'show',
			'any',
			[],
		);

		const { result } = renderHook(
			() => useConditionalLogic(
				fieldConfig,
				BASE_ALL_POD_VALUES,
				BASE_ALL_POD_FIELDS_MAP,
			)
		);

		expect( result.current ).toBe( true );
	} );

	it( 'should hide the field with hide action and empty rules', () => {
		const fieldConfig = generateFieldConfigWithConditionalRules(
			true,
			'hide',
			'any',
			[],
		);

		const { result } = renderHook(
			() => useConditionalLogic(
				fieldConfig,
				BASE_ALL_POD_VALUES,
				BASE_ALL_POD_FIELDS_MAP,
			)
		);

		expect( result.current ).toBe( false );
	} );

	it( 'should validate rules with "any" logic', () => {} );

	it( 'should validate rules with "all" logic', () => {} );

	it( 'should validate "LIKE" rules', () => {} );

	it( 'should validate "NOT LIKE" rules', () => {} );

	it( 'should validate "BEGINS" rules', () => {} );

	it( 'should validate "NOT BEGINS" rules', () => {} );

	it( 'should validate "ENDS" rules', () => {} );

	it( 'should validate "NOT ENDS" rules', () => {} );

	it( 'should validate "MATCHES" rules', () => {} );

	it( 'should validate "NOT MATCHES" rules', () => {} );

	it( 'should validate "IN" rules', () => {} );

	it( 'should validate "NOT IN" rules', () => {} );

	it( 'should validate "EMPTY" rules', () => {} );

	it( 'should validate "NOT EMPTY" rules', () => {} );

	it( 'should validate "=" rules', () => {} );

	it( 'should validate "!=" rules', () => {} );

	it( 'should validate "<" rules', () => {} );

	it( 'should validate "<=" rules', () => {} );

	it( 'should validate ">" rules', () => {} );

	it( 'should validate ">=" rules', () => {} );
} );
