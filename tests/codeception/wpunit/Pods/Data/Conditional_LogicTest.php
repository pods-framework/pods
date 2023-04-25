<?php

namespace Pods_Unit_Tests\Pods;

use WP_User;
use Codeception\TestCase\WPTestCase;
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

	public function test_is_field_visible_with_show_action_and_empty_rules() : void {
		$sut = $this->sut( 'show', 'any', [] );

		$this->assertTrue( $sut->is_field_visible( [] ) );
	}

	public function test_is_field_visible_with_hide_action_and_empty_rules() : void {
		$sut = $this->sut( 'hide', 'any', [] );

		$this->assertFalse( $sut->is_field_visible( [] ) );
	}

	public function test_is_field_visible_with_show_action() : void {
		$sut = $this->sut( 'show', 'any', [
			[
				'field'   => 'field_one',
				'compare' => '=',
				'value'   => '12345',
			],
		] );

		$this->assertTrue( $sut->is_field_visible( [
			'field_one' => '12345',
		] ) );
		$this->assertFalse( $sut->is_field_visible( [
			'field_one' => '1234567890',
		] ) );
	}

	public function test_is_field_visible_with_hide_action() : void {
		$sut = $this->sut( 'hide', 'any', [
			[
				'field'   => 'field_one',
				'compare' => '=',
				'value'   => '12345',
			],
		] );

		$this->assertFalse( $sut->is_field_visible( [
			'field_one' => '12345',
		] ) );
		$this->assertTrue( $sut->is_field_visible( [
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

	private function sut( string $action, string $logic, array $rules ) : Conditional_Logic {
		return new Conditional_Logic( $action, $logic, $rules );
	}

}
