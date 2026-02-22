<?php

namespace Pods_Unit_Tests\Pods;

use WP_User;
use lucatume\WPBrowser\TestCase\WPTestCase;
use Pods\Data\Conditional_Logic;

/**
 * @group  pods-utils
 * @covers Conditional_Logic
 */
class Conditional_LogicTest extends WPTestCase {

	public function test_constructor_with_empty_action_and_logic() : void {
		$sut = $this->sut( '', '', [] );

		$this->assertEquals( 'show', $sut->get_action() );
		$this->assertEquals( 'any', $sut->get_logic() );
	}

	public function test_get_and_set_action() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertEquals( 'show', $sut->get_action() );

		$sut->set_action( 'something-else' );

		$this->assertEquals( 'something-else', $sut->get_action() );
	}

	public function test_get_and_set_logic() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertEquals( 'any', $sut->get_logic() );

		$sut->set_logic( 'something-else' );

		$this->assertEquals( 'something-else', $sut->get_logic() );
	}

	public function test_get_and_set_rules() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertEquals( [], $sut->get_rules() );

		$sut->set_rules( [
			[
				'field'   => 'field_one',
				'compare' => '=',
				'value'   => '12345',
			],
		] );

		$this->assertEquals( [
			[
				'field'   => 'field_one',
				'compare' => '=',
				'value'   => '12345',
			],
		], $sut->get_rules() );
	}

	public function test_to_array() : void {
		$sut = $this->sut( 'show', 'any', [
			[
				'field'   => 'field_one',
				'compare' => '=',
				'value'   => '12345',
			],
		] );

		$this->assertEquals( [
			'action' => 'show',
			'logic'  => 'any',
			'rules'  => [
				[
					'field'   => 'field_one',
					'compare' => '=',
					'value'   => '12345',
				],
			],
		], $sut->to_array() );
	}

	public function test_is_visible_with_show_action_and_empty_rules() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->is_visible( [] ) );
	}

	public function test_is_visible_with_hide_action_and_empty_rules() : void {
		$sut = $this->sut( 'hide', 'any', [] );

		$this->assertFalse( $sut->is_visible( [] ) );
	}

	public function test_is_visible_with_show_action() : void {
		$sut = $this->sut( 'show', 'any', [
			[
				'field'   => 'field_one',
				'compare' => '=',
				'value'   => '12345',
			],
		] );

		$this->assertTrue( $sut->is_visible( [
			'field_one' => '12345',
		] ) );
		$this->assertFalse( $sut->is_visible( [
			'field_one' => '1234567890',
		] ) );
	}

	public function test_is_visible_with_hide_action() : void {
		$sut = $this->sut( 'hide', 'any', [
			[
				'field'   => 'field_one',
				'compare' => '=',
				'value'   => '12345',
			],
		] );

		$this->assertFalse( $sut->is_visible( [
			'field_one' => '12345',
		] ) );
		$this->assertTrue( $sut->is_visible( [
			'field_one' => '1234567890',
		] ) );
	}

	public function test_validate_rules_with_any_logic() : void {
		$sut = $this->sut( 'show', 'any', [
			[
				'field'   => 'field_one',
				'compare' => '=',
				'value'   => '12345',
			],
			[
				'field'   => 'field_two',
				'compare' => '=',
				'value'   => '1234567890',
			],
			[
				'field'   => 'field_three',
				'compare' => '=',
				'value'   => 'abcdef',
			],
		] );

		$this->assertTrue( $sut->validate_rules( [
			'field_one' => '12345',
		] ) );
		$this->assertTrue( $sut->validate_rules( [
			'field_one' => '12345',
			'field_two' => '1234567890',
		] ) );
		$this->assertTrue( $sut->validate_rules( [
			'field_one' => 'abcdef',
			'field_two' => '1234567890',
		] ) );
		$this->assertFalse( $sut->validate_rules( [
			'field_one' => 'abcdef',
		] ) );
	}

	public function test_validate_rules_with_all_logic() : void {
		$sut = $this->sut( 'show', 'all', [
			[
				'field'   => 'field_one',
				'compare' => '=',
				'value'   => '12345',
			],
			[
				'field'   => 'field_two',
				'compare' => '=',
				'value'   => '1234567890',
			],
			[
				'field'   => 'field_three',
				'compare' => '=',
				'value'   => 'abcdef',
			],
		] );

		$this->assertFalse( $sut->validate_rules( [
			'field_one' => '12345',
		] ) );
		$this->assertFalse( $sut->validate_rules( [
			'field_one' => '12345',
			'field_two' => '1234567890',
		] ) );
		$this->assertTrue( $sut->validate_rules( [
			'field_one'   => '12345',
			'field_two'   => '1234567890',
			'field_three' => 'abcdef',
		] ) );
	}

	public function provider_validate_rule_comparison_provider() {
		yield 'validate rule with LIKE' => [
			[
				'compare'          => 'LIKE',
				'value'            => 'word',
				'value_assertions' => [
					'pass' => [
						'word',
						'word2',
						'1word',
						'Word',
						'WORD',
						'sentence with word in it',
						'word starts sentence',
						'sentence ends with word',
					],
					'fail' => [
						'wor d',
						'sentence without that in it',
					],
				],
			],
		];

		yield 'validate rule with NOT LIKE' => [
			[
				'compare'          => 'NOT LIKE',
				'value'            => 'word',
				'value_assertions' => [
					'pass' => [
						'wor d',
						'sentence without that in it',
					],
					'fail' => [
						'word',
						'word2',
						'1word',
						'Word',
						'WORD',
						'sentence with word in it',
						'word starts sentence',
						'sentence ends with word',
					],
				],
			],
		];

		yield 'validate rule with BEGINS' => [
			[
				'compare'          => 'BEGINS',
				'value'            => 'word',
				'value_assertions' => [
					'pass' => [
						'word',
						'word2',
						'Word',
						'WORD',
						'word starts sentence',
					],
					'fail' => [
						'1word',
						'sentence with word in it',
						'sentence ends with word',
						'wor d',
						'sentence without that in it',
					],
				],
			],
		];

		yield 'validate rule with NOT BEGINS' => [
			[
				'compare'          => 'NOT BEGINS',
				'value'            => 'word',
				'value_assertions' => [
					'pass' => [
						'1word',
						'sentence with word in it',
						'sentence ends with word',
						'wor d',
						'sentence without that in it',
					],
					'fail' => [
						'word',
						'word2',
						'Word',
						'WORD',
						'word starts sentence',
					],
				],
			],
		];

		yield 'validate rule with ENDS' => [
			[
				'compare'          => 'ENDS',
				'value'            => 'word',
				'value_assertions' => [
					'pass' => [
						'1word',
						'sentence ends with word',
						'word',
						'Word',
						'WORD',
					],
					'fail' => [
						'sentence with word in it',
						'wor d',
						'sentence without that in it',
						'word2',
						'word starts sentence',
					],
				],
			],
		];

		yield 'validate rule with NOT ENDS' => [
			[
				'compare'          => 'NOT ENDS',
				'value'            => 'word',
				'value_assertions' => [
					'pass' => [
						'sentence with word in it',
						'wor d',
						'sentence without that in it',
						'word2',
						'word starts sentence',
					],
					'fail' => [
						'1word',
						'sentence ends with word',
						'word',
						'Word',
						'WORD',
					],
				],
			],
		];

		yield 'validate rule with MATCHES' => [
			[
				'compare'          => 'MATCHES',
				'value'            => '^[a-z]+$',
				'value_assertions' => [
					'pass' => [
						'onlyletters',
					],
					'fail' => [
						'letters with spaces',
						'letterswithnumberslike12345',
						'lettersWithUpperCase',
					],
				],
			],
		];

		yield 'validate rule with NOT MATCHES' => [
			[
				'compare'          => 'NOT MATCHES',
				'value'            => '^[a-z]+$',
				'value_assertions' => [
					'pass' => [
						'letters with spaces',
						'letterswithnumberslike12345',
						'lettersWithUpperCase',
					],
					'fail' => [
						'onlyletters',
					],
				],
			],
		];

		yield 'validate rule with IN' => [
			[
				'compare'          => 'IN',
				'value'            => [
					'123456',
					'7890',
				],
				'value_assertions' => [
					'pass' => [
						123456,
						'123456',
						7890,
						'7890',
					],
					'fail' => [
						1234,
						'some other value',
						[
							'123456',
						],
					],
				],
			],
		];

		yield 'validate rule with NOT IN' => [
			[
				'compare'          => 'NOT IN',
				'value'            => [
					'123456',
					'7890',
				],
				'value_assertions' => [
					'pass' => [
						1234,
						'some other value',
						[
							'123456',
						],
					],
					'fail' => [
						123456,
						'123456',
						7890,
						'7890',
					],
				],
			],
		];

		yield 'validate rule with EMPTY' => [
			[
				'compare'          => 'EMPTY',
				'value'            => '',
				'value_assertions' => [
					'pass' => [
						'',
						null,
						[],
						false,
					],
					'fail' => [
						'some value',
						true,
						0,
						1,
						'0',
						'null',
						'[]',
						'false',
					],
				],
			],
		];

		yield 'validate rule with NOT EMPTY' => [
			[
				'compare'          => 'NOT EMPTY',
				'value'            => '',
				'value_assertions' => [
					'pass' => [
						'some value',
						true,
						0,
						1,
						'0',
						'null',
						'[]',
						'false',
					],
					'fail' => [
						'',
						null,
						[],
						false,
					],
				],
			],
		];

		yield 'validate rule with =' => [
			[
				'compare'          => '=',
				'value'            => '123456',
				'value_assertions' => [
					'pass' => [
						123456,
						'123456',
					],
					'fail' => [
						1234,
						'some other value',
						[
							'123456',
						],
					],
				],
			],
		];

		yield 'validate rule with !=' => [
			[
				'compare'          => '!=',
				'value'            => '123456',
				'value_assertions' => [
					'pass' => [
						1234,
						'some other value',
						[
							'123456',
						],
					],
					'fail' => [
						123456,
						'123456',
					],
				],
			],
		];

		yield 'validate rule with <' => [
			[
				'compare'          => '<',
				'value'            => '123456',
				'value_assertions' => [
					'pass' => [
						123457,
						'123457',
					],
					'fail' => [
						123455,
						123456,
						1234,
						[
							123457,
						],
					],
				],
			],
		];

		yield 'validate rule with <=' => [
			[
				'compare'          => '<=',
				'value'            => '123456',
				'value_assertions' => [
					'pass' => [
						123457,
						'123457',
						123456,
						'123456',
					],
					'fail' => [
						123455,
						1234,
						[
							123457,
						],
					],
				],
			],
		];

		yield 'validate rule with >' => [
			[
				'compare'          => '>',
				'value'            => '123456',
				'value_assertions' => [
					'pass' => [
						123455,
						'123455',
						1234,
						'1234',
					],
					'fail' => [
						123457,
						123456,
						[
							123455,
						],
					],
				],
			],
		];

		yield 'validate rule with >=' => [
			[
				'compare'          => '>=',
				'value'            => '123456',
				'value_assertions' => [
					'pass' => [
						123455,
						'123455',
						123456,
						'123456',
						1234,
						'1234',
					],
					'fail' => [
						123457,
						[
							123455,
						],
					],
				],
			],
		];
	}

	/**
	 * @dataProvider provider_validate_rule_comparison_provider
	 */
	public function test_validate_rule( array $test ) : void {
		$sut = $this->sut( 'show', 'any', [] );

		$rule_with_readable_compare = [
			'field'   => 'field_one',
			'compare' => $test['compare'],
			'value'   => $test['value'],
		];

		$rule_with_basic_compare = [
			'field'   => 'field_one',
			'compare' => str_replace( ' ', '-', strtolower( $test['compare'] ) ),
			'value'   => $test['value'],
		];

		foreach ( $test['value_assertions']['pass'] as $pass_value ) {
			$values = [
				'field_one' => $pass_value,
			];

			$this->assertTrue( $sut->validate_rule( $rule_with_readable_compare, $values ), 'Debug: ' . var_export( $values, true ) );
			$this->assertTrue( $sut->validate_rule( $rule_with_basic_compare, $values ), 'Debug: ' . var_export( $values, true ) );
		}

		foreach ( $test['value_assertions']['fail'] as $fail_value ) {
			$values = [
				'field_one' => $fail_value,
			];

			$this->assertFalse( $sut->validate_rule( $rule_with_readable_compare, $values ), 'Debug: ' . var_export( $values, true ) );
			$this->assertFalse( $sut->validate_rule( $rule_with_basic_compare, $values ), 'Debug: ' . var_export( $values, true ) );
		}
	}

	/**
	 * Direct unit tests for helper comparison methods
	 */

	/**
	 * Test loose_string_equality_check method
	 */
	public function test_loose_string_equality_check_identical_strings() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->loose_string_equality_check( 'test', 'test' ) );
	}

	public function test_loose_string_equality_check_case_insensitive() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->loose_string_equality_check( 'Test', 'test' ) );
		$this->assertTrue( $sut->loose_string_equality_check( 'TEST', 'test' ) );
		$this->assertTrue( $sut->loose_string_equality_check( 'abc', 'ABC' ) );
	}

	public function test_loose_string_equality_check_string_number_coercion() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->loose_string_equality_check( '123', 123 ) );
		$this->assertTrue( $sut->loose_string_equality_check( 123, '123' ) );
		$this->assertTrue( $sut->loose_string_equality_check( '456', 456 ) );
	}

	public function test_loose_string_equality_check_boolean_number_coercion() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->loose_string_equality_check( true, 1 ) );
		$this->assertTrue( $sut->loose_string_equality_check( 1, true ) );
		$this->assertTrue( $sut->loose_string_equality_check( false, 0 ) );
		$this->assertTrue( $sut->loose_string_equality_check( 0, false ) );
	}

	public function test_loose_string_equality_check_boolean_string_coercion() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->loose_string_equality_check( true, '1' ) );
		$this->assertTrue( $sut->loose_string_equality_check( '1', true ) );
		$this->assertTrue( $sut->loose_string_equality_check( false, '0' ) );
		$this->assertTrue( $sut->loose_string_equality_check( '0', false ) );
	}

	public function test_loose_string_equality_check_arrays() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->loose_string_equality_check( [ 1, 2 ], [ 1, 2 ] ) );
		$this->assertFalse( $sut->loose_string_equality_check( [ 1, 2 ], [ 2, 1 ] ) );
		$this->assertTrue( $sut->loose_string_equality_check( [ 'a' => 1 ], [ 'a' => 1 ] ) );
	}

	public function test_loose_string_equality_check_not_matching() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertFalse( $sut->loose_string_equality_check( 'abc', 'def' ) );
		$this->assertFalse( $sut->loose_string_equality_check( 123, 456 ) );
		$this->assertFalse( $sut->loose_string_equality_check( true, false ) );
	}

	/**
	 * Test convert_string_to_array method
	 */
	public function test_convert_string_to_array_comma_separated() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertEquals( [ '123', '456', '789' ], $sut->convert_string_to_array( '123,456,789' ) );
	}

	public function test_convert_string_to_array_with_whitespace() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertEquals( [ '123', '456', '789' ], $sut->convert_string_to_array( '123, 456, 789' ) );
		$this->assertEquals( [ '123', '456', '789' ], $sut->convert_string_to_array( ' 123 , 456 , 789 ' ) );
	}

	public function test_convert_string_to_array_already_array() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertEquals( [ '123', '456' ], $sut->convert_string_to_array( [ '123', '456' ] ) );
	}

	public function test_convert_string_to_array_non_string() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertEquals( [], $sut->convert_string_to_array( 123 ) );
		$this->assertEquals( [], $sut->convert_string_to_array( null ) );
	}

	/**
	 * Test is_value_empty method
	 */
	public function test_is_value_empty_returns_true() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->is_value_empty( '' ) );
		$this->assertTrue( $sut->is_value_empty( null ) );
		$this->assertTrue( $sut->is_value_empty( [] ) );
		$this->assertTrue( $sut->is_value_empty( false ) );
	}

	public function test_is_value_empty_returns_false() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertFalse( $sut->is_value_empty( 'value' ) );
		$this->assertFalse( $sut->is_value_empty( 'null' ) );
		$this->assertFalse( $sut->is_value_empty( '0' ) );
		$this->assertFalse( $sut->is_value_empty( 0 ) );
		$this->assertFalse( $sut->is_value_empty( 1 ) );
		$this->assertFalse( $sut->is_value_empty( true ) );
		$this->assertFalse( $sut->is_value_empty( [ 'item' ] ) );
	}

	/**
	 * Test string_comparison method
	 */
	public function test_string_comparison_contains() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->string_comparison( 'contains', 'word', 'sentence with word in it' ) );
		$this->assertTrue( $sut->string_comparison( 'contains', 'test', 'this is a test' ) );
		$this->assertTrue( $sut->string_comparison( 'contains', 'WORD', 'word' ) ); // Case insensitive
		$this->assertFalse( $sut->string_comparison( 'contains', 'word', 'no match' ) );
		$this->assertTrue( $sut->string_comparison( 'contains', '', 'anything' ) ); // Empty search
	}

	public function test_string_comparison_starts_with() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->string_comparison( 'starts_with', 'word', 'word starts' ) );
		$this->assertTrue( $sut->string_comparison( 'starts_with', 'test', 'testing' ) );
		$this->assertTrue( $sut->string_comparison( 'starts_with', 'WORD', 'word' ) ); // Case insensitive
		$this->assertFalse( $sut->string_comparison( 'starts_with', 'word', 'no word here' ) );
		$this->assertTrue( $sut->string_comparison( 'starts_with', '', 'anything' ) ); // Empty search
	}

	public function test_string_comparison_ends_with() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->string_comparison( 'ends_with', 'word', 'ends with word' ) );
		$this->assertTrue( $sut->string_comparison( 'ends_with', 'test', 'a test' ) );
		$this->assertTrue( $sut->string_comparison( 'ends_with', 'WORD', 'word' ) ); // Case insensitive
		$this->assertFalse( $sut->string_comparison( 'ends_with', 'word', 'word at start' ) );
		$this->assertTrue( $sut->string_comparison( 'ends_with', '', 'anything' ) ); // Empty search
	}

	public function test_string_comparison_non_scalar() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertFalse( $sut->string_comparison( 'contains', 'word', [ 'word' ] ) );
		$this->assertFalse( $sut->string_comparison( 'contains', [ 'word' ], 'word' ) );
	}

	/**
	 * Test regex_match method
	 */
	public function test_regex_match_pattern() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->regex_match( '^[a-z]+$', 'onlyletters' ) );
		$this->assertTrue( $sut->regex_match( '^\d+$', '12345' ) );
		$this->assertFalse( $sut->regex_match( '^[a-z]+$', 'Has123' ) );
		$this->assertFalse( $sut->regex_match( '^\d+$', 'abc' ) );
	}

	public function test_regex_match_partial() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->regex_match( 'test', 'this is a test' ) );
		$this->assertFalse( $sut->regex_match( 'test', 'no match' ) );
	}

	public function test_regex_match_array() : void {
		$sut = $this->sut( 'show', 'any', [] );

		// ANY match in array
		$this->assertTrue( $sut->regex_match( '^[a-z]+$', [ 'abc', '123' ] ) );
		$this->assertFalse( $sut->regex_match( '^[a-z]+$', [ '123', '456' ] ) );
	}

	/**
	 * Test in_comparison method
	 */
	public function test_in_comparison_any_match() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->in_comparison( [ '123', '456' ], '123', false ) );
		$this->assertTrue( $sut->in_comparison( [ '123', '456' ], '456', false ) );
		$this->assertFalse( $sut->in_comparison( [ '123', '456' ], '789', false ) );
	}

	public function test_in_comparison_loose_equality() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->in_comparison( [ '123', '456' ], 123, false ) );
		$this->assertTrue( $sut->in_comparison( [ 123, 456 ], '123', false ) );
	}

	public function test_in_comparison_string_to_array() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->in_comparison( '123,456', [ '123', '999' ], false ) );
		$this->assertFalse( $sut->in_comparison( '123,456', [ '999', '000' ], false ) );
	}

	public function test_in_comparison_all_match() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->in_comparison( [ '123' ], '123', true ) );
		$this->assertTrue( $sut->in_comparison( [ '123', '123' ], '123', true ) );
		$this->assertFalse( $sut->in_comparison( [ '123', '456' ], '123', true ) );
	}

	public function test_in_comparison_all_with_string_to_array() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->in_comparison( '123,456', [ '123', '456' ], true ) );
		$this->assertTrue( $sut->in_comparison( '123,456', [ '123', '456', '789' ], true ) );
		$this->assertFalse( $sut->in_comparison( '123,456,789', [ '123', '456' ], true ) );
	}

	/**
	 * Test in_values_comparison method
	 */
	public function test_in_values_comparison_any_match() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->in_values_comparison( '123', [ '123', '456' ], false ) );
		$this->assertTrue( $sut->in_values_comparison( '456', [ '123', '456' ], false ) );
		$this->assertFalse( $sut->in_values_comparison( '789', [ '123', '456' ], false ) );
	}

	public function test_in_values_comparison_loose_equality() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->in_values_comparison( 123, [ '123', '456' ], false ) );
		$this->assertTrue( $sut->in_values_comparison( '123', [ 123, 456 ], false ) );
	}

	public function test_in_values_comparison_empty_array() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertFalse( $sut->in_values_comparison( '123', [], false ) );
	}

	public function test_in_values_comparison_non_array() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertFalse( $sut->in_values_comparison( '123', '123', false ) );
		$this->assertFalse( $sut->in_values_comparison( '123', 123, false ) );
	}

	public function test_in_values_comparison_all_match() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->in_values_comparison( '123', [ '123' ], true ) );
		$this->assertTrue( $sut->in_values_comparison( '123', [ '123', '123' ], true ) );
		$this->assertFalse( $sut->in_values_comparison( '123', [ '123', '456' ], true ) );
	}

	public function test_in_values_comparison_all_empty_array() : void {
		$sut = $this->sut( 'show', 'any', [] );

		// Empty array with "all" returns true (vacuous truth)
		$this->assertTrue( $sut->in_values_comparison( '123', [], true ) );
	}

	/**
	 * Test equality_comparison method
	 */
	public function test_equality_comparison_identical() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->equality_comparison( '123', '123' ) );
		$this->assertTrue( $sut->equality_comparison( 123, 123 ) );
	}

	public function test_equality_comparison_type_coercion() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->equality_comparison( '123', 123 ) );
		$this->assertTrue( $sut->equality_comparison( 123, '123' ) );
	}

	public function test_equality_comparison_boolean_number() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->equality_comparison( true, 1 ) );
		$this->assertTrue( $sut->equality_comparison( 1, true ) );
		$this->assertTrue( $sut->equality_comparison( false, 0 ) );
		$this->assertTrue( $sut->equality_comparison( 0, false ) );
	}

	public function test_equality_comparison_boolean_string() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->equality_comparison( true, '1' ) );
		$this->assertTrue( $sut->equality_comparison( '1', true ) );
		$this->assertTrue( $sut->equality_comparison( false, '0' ) );
		$this->assertTrue( $sut->equality_comparison( '0', false ) );
	}

	public function test_equality_comparison_not_matching() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertFalse( $sut->equality_comparison( '123', '456' ) );
		$this->assertFalse( $sut->equality_comparison( 123, 456 ) );
		$this->assertFalse( $sut->equality_comparison( true, false ) );
	}

	public function test_equality_comparison_non_scalar() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertFalse( $sut->equality_comparison( '123', [ '123' ] ) );
	}

	/**
	 * Test numeric_comparison method
	 */
	public function test_numeric_comparison_less_than() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->numeric_comparison( '<', '100', 99 ) );
		$this->assertTrue( $sut->numeric_comparison( '<', '100', '99' ) );
		$this->assertFalse( $sut->numeric_comparison( '<', '100', 100 ) );
		$this->assertFalse( $sut->numeric_comparison( '<', '100', 101 ) );
	}

	public function test_numeric_comparison_less_than_or_equal() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->numeric_comparison( '<=', '100', 99 ) );
		$this->assertTrue( $sut->numeric_comparison( '<=', '100', 100 ) );
		$this->assertFalse( $sut->numeric_comparison( '<=', '100', 101 ) );
	}

	public function test_numeric_comparison_greater_than() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->numeric_comparison( '>', '100', 101 ) );
		$this->assertTrue( $sut->numeric_comparison( '>', '100', '101' ) );
		$this->assertFalse( $sut->numeric_comparison( '>', '100', 100 ) );
		$this->assertFalse( $sut->numeric_comparison( '>', '100', 99 ) );
	}

	public function test_numeric_comparison_greater_than_or_equal() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->numeric_comparison( '>=', '100', 101 ) );
		$this->assertTrue( $sut->numeric_comparison( '>=', '100', 100 ) );
		$this->assertFalse( $sut->numeric_comparison( '>=', '100', 99 ) );
	}

	public function test_numeric_comparison_non_scalar() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertFalse( $sut->numeric_comparison( '<', '100', [ 99 ] ) );
		$this->assertFalse( $sut->numeric_comparison( '>', '100', [ 101 ] ) );
	}

	private function sut( string $action, string $logic, array $rules ) : Conditional_Logic {
		return new Conditional_Logic( $action, $logic, $rules );
	}

}
