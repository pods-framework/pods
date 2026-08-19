<?php

namespace Pods_Unit_Tests\Pods\Field;

use PodsField_Code;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group              pods-field
 * @coversDefaultClass PodsField_Code
 */
class PodsField_CodeTest extends Pods_UnitTestCase {

	/**
	 * @var PodsField_Code
	 */
	private $field;

	public function setUp(): void {
		$this->field = new PodsField_Code();

		// Make sure no leftover shortcode from prior runs.
		if ( shortcode_exists( 'pods_code_test' ) ) {
			remove_shortcode( 'pods_code_test' );
		}
	}

	public function tearDown(): void {
		if ( shortcode_exists( 'pods_code_test' ) ) {
			remove_shortcode( 'pods_code_test' );
		}

		unset( $this->field );
	}

	/**
	 * @covers PodsField_Code::display
	 */
	public function test_display_preserves_external_script_src() {
		$value = '<script src="https://example.com/widget.js"></script>';

		$output = $this->field->display(
			$value,
			'code_field',
			[ 'code_sanitize_html' => 1 ],
			null,
			0
		);

		$this->assertIsString( $output );
		$this->assertStringContainsString( '<script', $output );
		$this->assertStringContainsString( 'src="https://example.com/widget.js"', $output );
	}

	/**
	 * @covers PodsField_Code::display
	 */
	public function test_display_preserves_iframe_src_with_dimensions() {
		$value = '<iframe src="https://www.youtube.com/embed/abc123" width="560" height="315" frameborder="0" allowfullscreen></iframe>';

		$output = $this->field->display(
			$value,
			'code_field',
			[ 'code_sanitize_html' => 1 ],
			null,
			0
		);

		$this->assertStringContainsString( '<iframe', $output );
		$this->assertStringContainsString( 'src="https://www.youtube.com/embed/abc123"', $output );
		$this->assertStringContainsString( 'allowfullscreen', $output );
	}

	/**
	 * @covers PodsField_Code::display
	 */
	public function test_display_runs_shortcodes_when_allowed() {
		add_shortcode( 'pods_code_test', static function () {
			return 'SHORTCODE_RAN';
		} );

		$output = $this->field->display(
			'[pods_code_test]',
			'code_field',
			[
				'code_sanitize_html'   => 1,
				'code_allow_shortcode' => 1,
			],
			null,
			0
		);

		$this->assertStringContainsString( 'SHORTCODE_RAN', $output );
	}

	/**
	 * @covers PodsField_Code::display
	 */
	public function test_display_does_not_run_shortcodes_when_disabled() {
		add_shortcode( 'pods_code_test', static function () {
			return 'SHORTCODE_RAN';
		} );

		$output = $this->field->display(
			'[pods_code_test]',
			'code_field',
			[
				'code_sanitize_html'   => 1,
				'code_allow_shortcode' => 0,
			],
			null,
			0
		);

		$this->assertStringNotContainsString( 'SHORTCODE_RAN', $output );
		$this->assertStringContainsString( '[pods_code_test]', $output );
	}

	/**
	 * @covers PodsField_Code::display
	 */
	public function test_display_with_sanitize_disabled_returns_raw() {
		$value = '<script src="https://example.com/x.js"></script>';

		$output = $this->field->display(
			$value,
			'code_field',
			[ 'code_sanitize_html' => 0 ],
			null,
			0
		);

		$this->assertSame( $value, $output );
	}
}
