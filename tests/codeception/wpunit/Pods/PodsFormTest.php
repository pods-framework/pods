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

	/**
	 * Shortcode expansion must not be coupled to magic tag evaluation: a default that
	 * contains a magic tag AND a shortcode must still have both applied.
	 *
	 * @covers PodsForm::default_value
	 */
	public function test_default_value_evaluates_both_magic_tags_and_shortcodes() {
		add_shortcode( 'fooshortcode', static function () {
			return 'foobar';
		} );

		$value = PodsForm::default_value(
			'',
			'text',
			'my_field',
			array(
				'default'              => '{@user.ID} [fooshortcode]',
				'text_allow_shortcode' => 1,
			)
		);

		$this->assertStringContainsString( 'foobar', $value );
		$this->assertStringNotContainsString( '[fooshortcode]', $value );
	}

	/**
	 * default_evaluate_tags only governs magic tags. Turning it off must not silently
	 * disable shortcode expansion, which is a separate opt-in.
	 *
	 * @covers PodsForm::default_value
	 */
	public function test_default_value_evaluates_shortcode_when_tag_evaluation_disabled() {
		add_shortcode( 'fooshortcode', static function () {
			return 'foobar';
		} );

		$value = PodsForm::default_value(
			'',
			'text',
			'my_field',
			array(
				'default'               => '[fooshortcode]',
				'text_allow_shortcode'  => 1,
				'default_evaluate_tags' => 0,
			)
		);

		$this->assertSame( 'foobar', $value );
	}
}
