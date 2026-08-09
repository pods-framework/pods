<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsForm;

/**
 * @group   pods-form
 * @coversDefaultClass PodsForm
 */
class PodsFormTest extends Pods_UnitTestCase {

	public function tearDown(): void {
		parent::tearDown();

		if ( shortcode_exists( 'fooshortcode' ) ) {
			remove_shortcode( 'fooshortcode' );
		}
	}

	/**
	 * @covers PodsForm::default_value
	 */
	public function test_default_value_evaluates_shortcode_when_allow_shortcode_is_on() {
		add_shortcode( 'fooshortcode', static function () {
			return 'foobar';
		} );

		$value = PodsForm::default_value(
			'',
			'text',
			'my_field',
			array(
				'default'              => '[fooshortcode]',
				'text_allow_shortcode' => 1,
			)
		);

		$this->assertSame( 'foobar', $value );
	}

	/**
	 * @covers PodsForm::default_value
	 */
	public function test_default_value_keeps_shortcode_literal_when_allow_shortcode_is_off() {
		add_shortcode( 'fooshortcode', static function () {
			return 'foobar';
		} );

		$value = PodsForm::default_value(
			'',
			'text',
			'my_field',
			array(
				'default'              => '[fooshortcode]',
				'text_allow_shortcode' => 0,
			)
		);

		$this->assertSame( '[fooshortcode]', $value );
	}

	/**
	 * @covers PodsForm::default_value
	 */
	public function test_default_value_keeps_unknown_shortcode_literal_even_when_allow_shortcode_is_on() {
		$value = PodsForm::default_value(
			'',
			'text',
			'my_field',
			array(
				'default'              => 'Hello [unregistered] world',
				'text_allow_shortcode' => 1,
			)
		);

		$this->assertSame( 'Hello [unregistered] world', $value );
	}

	/**
	 * @covers PodsForm::default_value
	 */
	public function test_default_value_does_not_evaluate_shortcode_for_field_types_without_allow_shortcode() {
		add_shortcode( 'fooshortcode', static function () {
			return 'foobar';
		} );

		$value = PodsForm::default_value(
			'',
			'pick',
			'my_field',
			array( 'default' => '[fooshortcode]' )
		);

		$this->assertSame( '[fooshortcode]', $value );
	}

	/**
	 * @covers PodsForm::default_value
	 */
	public function test_default_value_keeps_request_supplied_value_literal_even_when_allow_shortcode_is_on() {
		add_shortcode( 'fooshortcode', static function () {
			return 'foobar';
		} );

		$_GET['my_param'] = '[fooshortcode]';

		$value = PodsForm::default_value(
			'',
			'text',
			'my_field',
			array(
				'default'                  => 'static default',
				'default_value_parameter'  => 'my_param',
				'text_allow_shortcode'     => 1,
			)
		);

		unset( $_GET['my_param'] );

		$this->assertSame( '[fooshortcode]', $value );
	}
}
