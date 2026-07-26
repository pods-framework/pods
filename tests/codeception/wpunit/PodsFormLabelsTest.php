<?php

namespace Pods_Unit_Tests\Pods;

use PodsForm;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * Tests for the `[pods-form labels="..."]` shortcode attribute.
 *
 * Verifies that `PodsForm::set_form_labels()` and `PodsForm::clear_form_labels()`
 * flow into `PodsForm::label()` correctly, and that overrides do not leak
 * between forms on the same page.
 *
 * @group  pods
 * @covers PodsForm
 */
class PodsFormLabelsTest extends Pods_UnitTestCase {

	public function setUp(): void {
		parent::setUp();

		// Always start with a clean slate.
		PodsForm::clear_form_labels();
	}

	public function tearDown(): void {
		PodsForm::clear_form_labels();

		parent::tearDown();
	}

	public function test_set_form_labels_sanitizes_and_stores() {
		$labels = PodsForm::set_form_labels( array(
			'user_nicename' => 'Utilizador',
			'display_name'  => 'Nome Completo',
		) );

		$this->assertSame( array(
			'user_nicename' => 'Utilizador',
			'display_name'  => 'Nome Completo',
		), $labels );

		$this->assertSame( $labels, PodsForm::$form_labels );
	}

	public function test_set_form_labels_drops_invalid_entries() {
		$labels = PodsForm::set_form_labels( array(
			''        => 'Empty Key',
			'valid'   => 'Keep Me',
			'bad_val' => array( 'nested' => 'array' ),
		) );

		$this->assertSame( array( 'valid' => 'Keep Me' ), $labels );
	}

	public function test_label_render_uses_override() {
		PodsForm::set_form_labels( array(
			'user_nicename' => 'Utilizador',
		) );

		$output = PodsForm::label( 'user_nicename', 'Nicename' );

		$this->assertStringContainsString( 'Utilizador', $output );
		$this->assertStringNotContainsString( '>Nicename<', $output );
	}

	public function test_label_render_uses_default_when_no_override() {
		PodsForm::set_form_labels( array(
			'user_nicename' => 'Utilizador',
		) );

		$output = PodsForm::label( 'display_name', 'Display Name' );

		$this->assertStringContainsString( 'Display Name', $output );
		$this->assertStringNotContainsString( 'Utilizador', $output );
	}

	public function test_clear_form_labels_removes_overrides() {
		PodsForm::set_form_labels( array(
			'user_nicename' => 'Utilizador',
		) );

		PodsForm::clear_form_labels();

		$this->assertSame( array(), PodsForm::$form_labels );

		$output = PodsForm::label( 'user_nicename', 'Nicename' );

		$this->assertStringContainsString( 'Nicename', $output );
		$this->assertStringNotContainsString( 'Utilizador', $output );
	}

	public function test_label_override_does_not_leak_between_helpers() {
		PodsForm::set_form_labels( array(
			'user_nicename' => 'Utilizador',
		) );

		$output_with_override = PodsForm::label( 'user_nicename', 'Nicename' );
		$this->assertStringContainsString( 'Utilizador', $output_with_override );

		PodsForm::clear_form_labels();

		$output_after_clear = PodsForm::label( 'user_nicename', 'Nicename' );
		$this->assertStringContainsString( 'Nicename', $output_after_clear );
		$this->assertStringNotContainsString( 'Utilizador', $output_after_clear );
	}
}
