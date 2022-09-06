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
				{},
				generateAllPodFieldsMap(),
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
				{},
				generateAllPodFieldsMap(),
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
				{},
				generateAllPodFieldsMap(),
			)
		);

		expect( result.current ).toBe( false );
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
		).toBe( true );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: '12345', field_two: '1234567890' }, fieldsMap )
			).result.current
		).toBe( true );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: 'abcdef', field_two: '1234567890' }, fieldsMap )
			).result.current
		).toBe( true );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: 'abcdef' }, fieldsMap )
			).result.current
		).toBe( false );
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
		).toBe( false );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_one: '12345', field_two: '1234567890', field_three: 'abcdef' }, fieldsMap )
			).result.current
		).toBe( true );
	} );

	const individualRulesMatrix = [
		[ 'like', 'word', 'word', true ],
		[ 'like', 'word', 'word2', true ],
		[ 'like', 'word', '1word', true ],
		[ 'like', 'word', 'Word', true ],
		[ 'like', 'word', 'WORD', true ],
		[ 'like', 'word', 'sentence with word in it', true ],
		[ 'like', 'word', 'word starts sentence', true ],
		[ 'like', 'word', 'sentence ends with word', true ],
		[ 'like', 'word', 'wor d', false ],
		[ 'like', 'word', 'sentence without that in it', false ],

		[ 'not like', 'word', 'wor d', true ],
		[ 'not like', 'word', 'sentence without that in it', true ],
		[ 'not like', 'word', 'word', false ],
		[ 'not like', 'word', 'word2', false ],
		[ 'not like', 'word', '1word', false ],
		[ 'not like', 'word', 'Word', false ],
		[ 'not like', 'word', 'WORD', false ],
		[ 'not like', 'word', 'sentence with word in it', false ],
		[ 'not like', 'word', 'word starts sentence', false ],
		[ 'not like', 'word', 'sentence ends with word', false ],

		[ 'begins', 'word', 'word', true ],
		[ 'begins', 'word', 'word', true ],
		[ 'begins', 'word', 'word2', true ],
		[ 'begins', 'word', 'Word', true ],
		[ 'begins', 'word', 'WORD', true ],
		[ 'begins', 'word', 'word starts sentence', true ],
		[ 'begins', 'word', '1word', false ],
		[ 'begins', 'word', 'sentence with word in it', false ],
		[ 'begins', 'word', 'sentence ends with word', false ],
		[ 'begins', 'word', 'wor d', false ],
		[ 'begins', 'word', 'sentence without that in it', false ],

		[ 'not begins', 'word', '1word', true ],
		[ 'not begins', 'word', 'sentence with word in it', true ],
		[ 'not begins', 'word', 'sentence ends with word', true ],
		[ 'not begins', 'word', 'wor d', true ],
		[ 'not begins', 'word', 'sentence without that in it', true ],
		[ 'not begins', 'word', 'word', false ],
		[ 'not begins', 'word', 'word2', false ],
		[ 'not begins', 'word', 'Word', false ],
		[ 'not begins', 'word', 'WORD', false ],
		[ 'not begins', 'word', 'word starts sentence', false ],

		[ 'ends', 'word', '1word', true ],
		[ 'ends', 'word', 'sentence ends with word', true ],
		[ 'ends', 'word', 'word', true ],
		[ 'ends', 'word', 'Word', true ],
		[ 'ends', 'word', 'WORD', true ],
		[ 'ends', 'word', 'sentence with word in it', false ],
		[ 'ends', 'word', 'wor d', false ],
		[ 'ends', 'word', 'sentence without that in it', false ],
		[ 'ends', 'word', 'word2', false ],
		[ 'ends', 'word', 'word starts sentence', false ],

		[ 'not ends', 'word', 'sentence with word in it', true ],
		[ 'not ends', 'word', 'wor d', true ],
		[ 'not ends', 'word', 'sentence without that in it', true ],
		[ 'not ends', 'word', 'word2', true ],
		[ 'not ends', 'word', 'word starts sentence', true ],
		[ 'not ends', 'word', '1word', false ],
		[ 'not ends', 'word', 'sentence ends with word', false ],
		[ 'not ends', 'word', 'word', false ],
		[ 'not ends', 'word', 'Word', false ],
		[ 'not ends', 'word', 'WORD', false ],

		[ 'matches', '^[a-z]+$', 'onlyletters', true ],
		[ 'matches', '^[a-z]+$', 'letters with spaces', false ],
		[ 'matches', '^[a-z]+$', 'letterswithnumberslike12345', false ],
		[ 'matches', '^[a-z]+$', 'lettersWithUpperCase', false ],

		[ 'not matches', '^[a-z]+$', 'letters with spaces', true ],
		[ 'not matches', '^[a-z]+$', 'letterswithnumberslike12345', true ],
		[ 'not matches', '^[a-z]+$', 'lettersWithUpperCase', true ],
		[ 'not matches', '^[a-z]+$', 'onlyletters', false ],

		[ 'in', [ '123456', '7890' ], 123456, true ],
		[ 'in', [ '123456', '7890' ], '123456', true ],
		[ 'in', [ '123456', '7890' ], 7890, true ],
		[ 'in', [ '123456', '7890' ], '7890', true ],
		[ 'in', [ '123456', '7890' ], 1234, false ],
		[ 'in', [ '123456', '7890' ], 'some other value', false ],
		[ 'in', [ '123456', '7890' ], [ '123456' ], false ],

		[ 'not in', [ '123456', '7890' ], 1234, true ],
		[ 'not in', [ '123456', '7890' ], 'some other value', true ],
		[ 'not in', [ '123456', '7890' ], [ '123456' ], true ],
		[ 'not in', [ '123456', '7890' ], 123456, false ],
		[ 'not in', [ '123456', '7890' ], '123456', false ],
		[ 'not in', [ '123456', '7890' ], 7890, false ],
		[ 'not in', [ '123456', '7890' ], '7890', false ],

		[ 'empty', '', '', true ],
		[ 'empty', '', null, true ],
		[ 'empty', '', [], true ],
		[ 'empty', '', false, true ],
		[ 'empty', '', 'some value', false ],
		[ 'empty', '', true, false ],
		[ 'empty', '', 0, false ],
		[ 'empty', '', 1, false ],
		[ 'empty', '', '0', false ],
		[ 'empty', '', 'null', false ],
		[ 'empty', '', '[]', false ],
		[ 'empty', '', 'false', false ],

		[ 'not empty', '', 'some value', true ],
		[ 'not empty', '', true, true ],
		[ 'not empty', '', 0, true ],
		[ 'not empty', '', 1, true ],
		[ 'not empty', '', '0', true ],
		[ 'not empty', '', 'null', true ],
		[ 'not empty', '', '[]', true ],
		[ 'not empty', '', 'false', true ],
		[ 'not empty', '', '', false ],
		[ 'not empty', '', null, false ],
		[ 'not empty', '', [], false ],
		[ 'not empty', '', false, false ],

		[ '=', '123456', 123456, true ],
		[ '=', '123456', '123456', true ],
		[ '=', '123456', 1234, false ],
		[ '=', '123456', 'some other value', false ],
		[ '=', '123456', [ '123456' ], false ],

		[ '!=', '123456', 1234, true ],
		[ '!=', '123456', 'some other value', true ],
		[ '!=', '123456', [ '123456' ], true ],
		[ '!=', '123456', 123456, false ],
		[ '!=', '123456', '123456', false ],

		[ '<', '123456', 123457, true ],
		[ '<', '123456', '123457', true ],
		[ '<', '123456', 123455, false ],
		[ '<', '123456', 123456, false ],
		[ '<', '123456', 1234, false ],
		[ '<', '123456', [ 123457 ], false ],

		[ '<=', '123456', 123457, true ],
		[ '<=', '123456', '123457', true ],
		[ '<=', '123456', 123456, true ],
		[ '<=', '123456', '123456', true ],
		[ '<=', '123456', 123455, false ],
		[ '<=', '123456', 1234, false ],
		[ '<=', '123456', [ 123457 ], false ],

		[ '>', '123456', 123455, true ],
		[ '>', '123456', '123455', true ],
		[ '>', '123456', 1234, true ],
		[ '>', '123456', '1234', true ],
		[ '>', '123456', 123457, false ],
		[ '>', '123456', 123456, false ],
		[ '>', '123456', [ 123455 ], false ],

		[ '>=', '123456', 123455, true ],
		[ '>=', '123456', '123455', true ],
		[ '>=', '123456', 123456, true ],
		[ '>=', '123456', '123456', true ],
		[ '>=', '123456', 1234, true ],
		[ '>=', '123456', '1234', true ],
		[ '>=', '123456', 123457, false ],
		[ '>=', '123456', [ 123455 ], false ],
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
			).toBe( expected );
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
		).toBe( true );

		// Field 2 has ALL logic based on fields 3 and 4, so it should fail until both also are met.
		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_two: 'def' }, fieldsMap )
			).result.current
		).toBe( false );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_three: 'abc', field_four: 'def' }, fieldsMap )
			).result.current
		).toBe( false );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_two: 'def', field_three: 'abc' }, fieldsMap )
			).result.current
		).toBe( false );

		expect(
			renderHook(
				() => useConditionalLogic( fieldConfig, { field_two: 'def', field_three: 'abc', field_four: 'def' }, fieldsMap )
			).result.current
		).toBe( true );
	} );
} );
