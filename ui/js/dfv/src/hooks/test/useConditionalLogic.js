import { renderHook } from '@testing-library/react';

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
	fieldName = 'test_text_field',
) => generateFieldConfig(
	fieldName,
	{
		enable_conditional_logic: enableConditionalLogic,
		conditional_logic: {
			action,
			logic,
			rules,
		},
	},
);

const generateAllPodFieldsMap = ( fieldNames = [] ) => new Map(
	fieldNames.map( ( fieldName ) => [ fieldName, generateFieldConfig( fieldName ) ] ),
);

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
				{},
				generateAllPodFieldsMap(),
			)
		);

		expect( result.current ).toEqual( true );
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
				{},
				generateAllPodFieldsMap(),
			)
		);

		expect( result.current ).toEqual( true );
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
				{},
				generateAllPodFieldsMap(),
			)
		);

		expect( result.current ).toEqual( true );
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
				{},
				generateAllPodFieldsMap(),
			)
		);

		expect( result.current ).toEqual( false );
	} );

	it( 'should validate rules with "any" logic', () => {
		const fieldConfig = generateFieldConfigWithConditionalRules(
			true,
			'show',
			'any',
			[
				{
					field: 'field_one',
					compare: '=',
					value: '12345',
				},
				{
					field: 'field_two',
					compare: '=',
					value: '1234567890',
				},
				{
					field: 'field_three',
					compare: '=',
					value: 'abcdef',
				},
			]
		);

		const fieldsMap = generateAllPodFieldsMap( [ 'field_one', 'field_two', 'field_three' ] );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: '12345' }, fieldsMap )
			).result.current
		).toEqual( true );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: '12345', field_two: '1234567890' }, fieldsMap )
			).result.current
		).toEqual( true );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: 'abcdef', field_two: '1234567890' }, fieldsMap )
			).result.current
		).toEqual( true );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: 'abcdef' }, fieldsMap )
			).result.current
		).toEqual( false );
	} );

	it( 'should validate rules with "all" logic', () => {
		const fieldConfig = generateFieldConfigWithConditionalRules(
			true,
			'show',
			'all',
			[
				{
					field: 'field_one',
					compare: '=',
					value: '12345',
				},
				{
					field: 'field_two',
					compare: '=',
					value: '1234567890',
				},
				{
					field: 'field_three',
					compare: '=',
					value: 'abcdef',
				},
			]
		);

		const fieldsMap = generateAllPodFieldsMap( [ 'field_one', 'field_two', 'field_three' ] );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: '12345' }, fieldsMap )
			).result.current
		).toEqual( false );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: '12345', field_two: '1234567890', field_three: 'abcdef' }, fieldsMap )
			).result.current
		).toEqual( true );
	} );

	// Simplified individualRulesMatrix - basic smoke tests for integration
	// Comprehensive tests for helper functions are in useConditionalLogic.helpers.test.js
	const individualRulesMatrix = [
		// format: rule, ruleValue, testSubject, expected
		// String comparisons
		[ 'like', 'word', 'sentence with word in it', true ],
		[ 'like', 'word', 'no match', false ],
		[ 'not like', 'word', 'no match', true ],
		[ 'not like', 'word', 'word', false ],
		[ 'begins', 'word', 'word starts', true ],
		[ 'begins', 'word', 'no word', false ],
		[ 'not begins', 'word', 'no word', true ],
		[ 'not begins', 'word', 'word', false ],
		[ 'ends', 'word', 'ends with word', true ],
		[ 'ends', 'word', 'word at start', false ],
		[ 'not ends', 'word', 'word at start', true ],
		[ 'not ends', 'word', 'word', false ],

		// Regex
		[ 'matches', '^[a-z]+$', 'onlyletters', true ],
		[ 'matches', '^[a-z]+$', 'With123', false ],
		[ 'not matches', '^[a-z]+$', 'With123', true ],
		[ 'not matches', '^[a-z]+$', 'onlyletters', false ],

		// In/All comparisons
		[ 'in', [ '123', '456' ], '123', true ],
		[ 'in', [ '123', '456' ], '789', false ],
		[ 'not in', [ '123', '456' ], '789', true ],
		[ 'not in', [ '123', '456' ], '123', false ],
		[ 'all', [ '123', '123' ], '123', true ],
		[ 'all', [ '123', '456' ], '123', false ],
		[ 'not all', [ '123', '456' ], '123', true ],
		[ 'not all', [ '123', '123' ], '123', false ],

		// In Values/All Values
		[ 'in values', '123', [ '123', '456' ], true ],
		[ 'in values', '123', [ '456', '789' ], false ],
		[ 'not in values', '123', [ '456', '789' ], true ],
		[ 'not in values', '123', [ '123', '456' ], false ],
		[ 'all values', '123', [ '123', '123' ], true ],
		[ 'all values', '123', [ '123', '456' ], false ],
		[ 'not all values', '123', [ '123', '456' ], true ],
		[ 'not all values', '123', [ '123', '123' ], false ],

		// Empty/Not Empty
		[ 'empty', '', '', true ],
		[ 'empty', '', 'value', false ],
		[ 'not empty', '', 'value', true ],
		[ 'not empty', '', '', false ],

		// Equality
		[ '=', '123', 123, true ],
		[ '=', '123', '456', false ],
		[ '!=', '123', '456', true ],
		[ '!=', '123', 123, false ],

		// Numeric comparisons
		[ '<', '100', 99, true ],
		[ '<', '100', 101, false ],
		[ '<=', '100', 100, true ],
		[ '<=', '100', 101, false ],
		[ '>', '100', 101, true ],
		[ '>', '100', 99, false ],
		[ '>=', '100', 100, true ],
		[ '>=', '100', 99, false ],
	];

	it.each( individualRulesMatrix )(
		'should validate passing and nonpassing rules: rule %p, rule value %p, value %p, should match %p',
		( rule, ruleValue, testSubject, expected ) => {
			const fieldConfig = generateFieldConfigWithConditionalRules(
				true,
				'show',
				'any',
				[
					{
						field: 'field_one',
						compare: rule,
						value: ruleValue,
					},
				]
			);

			const fieldsMap = generateAllPodFieldsMap( [ 'field_one' ] );

			expect(
				renderHook(
					() => useConditionalLogic( fieldConfig, { field_one: testSubject }, fieldsMap )
				).result.current
			).toEqual( expected );
		}
	);

	it( 'should validate based "any" logic on parent field conditional logic', () => {
		const fieldConfig = generateFieldConfigWithConditionalRules(
			true,
			'show',
			'any',
			[
				{
					field: 'field_one',
					compare: '=',
					value: 'abc',
				},
				{
					field: 'field_two',
					compare: '=',
					value: 'def',
				},
			]
		);

		const fieldsMap = new Map(
			[
				[
					'field_one',
					generateFieldConfigWithConditionalRules(
						true,
						'show',
						'any',
						[],
						'field_one'
					),
				],
				[
					'field_two',
					generateFieldConfigWithConditionalRules(
						true,
						'show',
						'all',
						[
							{
								field: 'field_three',
								compare: '=',
								value: 'abc',
							},
							{
								field: 'field_four',
								compare: '=',
								value: 'def',
							},
						],
						'field_two'
					),
				],
				[
					'field_three',
					generateFieldConfigWithConditionalRules(
						true,
						'show',
						'any',
						[],
						'field_three'
					),
				],
				[
					'field_four',
					generateFieldConfigWithConditionalRules(
						true,
						'show',
						'any',
						[],
						'field_four'
					),
				],
			]
		);

		// Field 1 does not have parents with any conditional logic, so it should pass.
		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: 'abc' }, fieldsMap )
			).result.current
		).toEqual( true );

		// Field 2 has ALL logic based on fields 3 and 4, so it should fail until both also are met.
		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_two: 'def' }, fieldsMap )
			).result.current
		).toEqual( false );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_three: 'abc', field_four: 'def' }, fieldsMap )
			).result.current
		).toEqual( false );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_two: 'def', field_three: 'abc' }, fieldsMap )
			).result.current
		).toEqual( false );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_two: 'def', field_three: 'abc', field_four: 'def' }, fieldsMap )
			).result.current
		).toEqual( true );
	} );
} );
