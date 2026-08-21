<?php

namespace Pods_Unit_Tests\Pods;

use Pods\Theme\WP_Query_Integration;
use Pods_Unit_Tests\Pods_UnitTestCase;
use ReflectionMethod;

/**
 * The meta_query "type" is interpolated straight into a CAST() expression, so it
 * must be constrained to a known set of cast types. Without that, a caller can
 * inject arbitrary SQL through the meta_query "type" key.
 *
 * @group  pods-wp-query-table-meta
 * @group  pods-issue-7280
 * @covers \Pods\Theme\WP_Query_Integration::get_cast_for_type
 * @covers \Pods\Theme\WP_Query_Integration::build_meta_query_where
 */
class WP_QueryMetaQueryCastTest extends Pods_UnitTestCase {

	/**
	 * @var WP_Query_Integration
	 */
	protected $integration;

	public function setUp(): void {
		parent::setUp();

		$this->integration = new WP_Query_Integration();
	}

	public function tearDown(): void {
		$this->integration = null;

		parent::tearDown();
	}

	/**
	 * @param string $method Protected method name.
	 *
	 * @return ReflectionMethod
	 */
	protected function accessible( $method ) {
		$reflection = new ReflectionMethod( WP_Query_Integration::class, $method );
		$reflection->setAccessible( true );

		return $reflection;
	}

	/**
	 * @return array[]
	 */
	public function provider_injection_payloads() {
		return [
			'statement terminator'  => [ 'CHAR); DROP TABLE wp_posts; --' ],
			'boolean tautology'     => [ 'CHAR) OR 1=1 -- ' ],
			'union select'          => [ 'CHAR UNION SELECT user_pass FROM wp_users' ],
			'inline comment evasion' => [ 'SIGNED)/**/OR/**/1=1#' ],
			'quote break'           => [ "CHAR'" ],
		];
	}

	/**
	 * A hostile type must never survive into the generated SQL.
	 *
	 * @dataProvider provider_injection_payloads
	 *
	 * @param string $payload The hostile cast type.
	 */
	public function test_injected_cast_type_never_reaches_sql( $payload ) {
		$method = $this->accessible( 'build_meta_query_where' );

		$sql = $method->invoke(
			$this->integration,
			[
				'compare' => '=',
				'value'   => 'example',
				'type'    => $payload,
			],
			'alias',
			'',
			'subtitle',
			false
		);

		$this->assertStringContainsString( 'CAST(`alias`.`subtitle` AS CHAR)', $sql );
		$this->assertStringNotContainsString( 'DROP TABLE', strtoupper( $sql ) );
		$this->assertStringNotContainsString( 'UNION SELECT', strtoupper( $sql ) );
		$this->assertStringNotContainsString( 'OR 1=1', strtoupper( $sql ) );
		$this->assertStringNotContainsString( '--', $sql );
		$this->assertStringNotContainsString( '#', $sql );
	}

	/**
	 * Supported cast types must still be honoured.
	 *
	 * @return array[]
	 */
	public function provider_supported_types() {
		return [
			'empty defaults to CHAR' => [ '', 'CHAR' ],
			'CHAR'                   => [ 'CHAR', 'CHAR' ],
			'lowercase is upcased'   => [ 'char', 'CHAR' ],
			'SIGNED'                 => [ 'SIGNED', 'SIGNED' ],
			'UNSIGNED'               => [ 'UNSIGNED', 'UNSIGNED' ],
			'DATE'                   => [ 'DATE', 'DATE' ],
			'DATETIME'               => [ 'DATETIME', 'DATETIME' ],
			'TIME'                   => [ 'TIME', 'TIME' ],
			'BINARY'                 => [ 'BINARY', 'BINARY' ],
			'DECIMAL with precision' => [ 'DECIMAL(10,5)', 'DECIMAL(10,5)' ],
			'unknown falls back'     => [ 'NOT_A_TYPE', 'CHAR' ],
		];
	}

	/**
	 * @dataProvider provider_supported_types
	 *
	 * @param string $input    Requested cast type.
	 * @param string $expected Resolved cast type.
	 */
	public function test_supported_cast_types_are_preserved( $input, $expected ) {
		$method = $this->accessible( 'get_cast_for_type' );

		$this->assertSame( $expected, $method->invoke( $this->integration, $input ) );
	}

	/**
	 * NUMERIC is not valid MySQL CAST syntax; core maps it to SIGNED and so must we.
	 */
	public function test_numeric_is_mapped_to_signed() {
		$method = $this->accessible( 'get_cast_for_type' );

		$this->assertSame( 'SIGNED', $method->invoke( $this->integration, 'NUMERIC' ) );

		$where = $this->accessible( 'build_meta_query_where' );

		$sql = $where->invoke(
			$this->integration,
			[
				'compare' => '=',
				'value'   => 5,
				'type'    => 'NUMERIC',
			],
			'alias',
			'',
			'rating',
			false
		);

		$this->assertStringContainsString( 'AS SIGNED)', $sql );
		$this->assertStringNotContainsString( 'AS NUMERIC)', $sql );
	}

	/**
	 * @return array[]
	 */
	public function provider_like_values() {
		return [
			'plain value'      => [ 'Alpha', 'LIKE', 'LIKE \'%Alpha%\'' ],
			'escaped percent' => [ '50%', 'LIKE', 'LIKE \'%50\\\\%%\'' ],
			'escaped underscore' => [ 'a_b', 'LIKE', 'LIKE \'%a\\\\_b%\'' ],
			'not like'        => [ 'Beta', 'NOT LIKE', 'NOT LIKE \'%Beta%\'' ],
		];
	}

	/**
	 * LIKE / NOT LIKE must escape the value and wrap it in wildcards so a table
	 * field matches the way the same clause does against postmeta. Without the
	 * escape a value such as "50%" is read as a SQL wildcard, and without the
	 * surrounding "%" the clause matches exactly instead of as a substring.
	 *
	 * @dataProvider provider_like_values
	 *
	 * @param string $value    Raw value.
	 * @param string $compare  Comparison operator.
	 * @param string $expected Expected LIKE fragment in the SQL.
	 */
	public function test_like_value_gets_wildcards_and_esc_like( $value, $compare, $expected ) {
		$method = $this->accessible( 'build_meta_query_where' );

		$sql = $method->invoke(
			$this->integration,
			[
				'compare' => $compare,
				'value'   => $value,
				'type'    => 'CHAR',
			],
			'alias',
			'',
			'subtitle',
			false
		);

		$this->assertStringContainsString( $expected, $sql );
	}

	/**
	 * REGEXP binds a plain value with no added wildcards or escaping, the same
	 * as WP_Meta_Query.
	 */
	public function test_regexp_value_has_no_added_wildcards() {
		$method = $this->accessible( 'build_meta_query_where' );

		$sql = $method->invoke(
			$this->integration,
			[
				'compare' => 'REGEXP',
				'value'   => '^beta',
				'type'    => 'CHAR',
			],
			'alias',
			'',
			'subtitle',
			false
		);

		$this->assertStringContainsString( 'REGEXP \'^beta\'', $sql );
		$this->assertStringNotContainsString( '%^beta%', $sql );
	}

	/**
	 * A LIKE clause given an array value has no single literal meaning; the
	 * clause is skipped rather than emitted as malformed SQL.
	 */
	public function test_like_with_array_value_bails_closed() {
		$method = $this->accessible( 'build_meta_query_where' );

		$sql = $method->invoke(
			$this->integration,
			[
				'compare' => 'LIKE',
				'value'   => [ 'Alpha', 'Beta' ],
				'type'    => 'CHAR',
			],
			'alias',
			'',
			'subtitle',
			false
		);

		$this->assertSame( '', $sql );
	}
}
