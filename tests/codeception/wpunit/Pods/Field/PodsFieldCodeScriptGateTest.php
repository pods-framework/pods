<?php

namespace Pods_Unit_Tests\Pods\Field;

use PodsField_Code;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * wp_kses() filters tags and attributes but never the text content of an element,
 * so allowing <script> through the Code field allow-list lets anyone who can edit
 * the field store executable JavaScript. code_sanitize_html is the option an admin
 * enables specifically to prevent that, so the script allowance is gated on
 * unfiltered_html.
 *
 * @group  pods-field-code
 * @group  pods-issue-7263
 * @covers PodsField::get_post_with_embeds_allowed_html
 * @covers PodsField::maybe_sanitize_output
 */
class PodsFieldCodeScriptGateTest extends Pods_UnitTestCase {

	/**
	 * @var PodsField_Code
	 */
	protected $field;

	/**
	 * @var array
	 */
	protected $options = [ 'code_sanitize_html' => 1 ];

	public function setUp(): void {
		parent::setUp();

		$this->field = new PodsField_Code();
	}

	public function tearDown(): void {
		wp_set_current_user( 0 );

		$this->field = null;

		parent::tearDown();
	}

	/**
	 * A user without unfiltered_html must not be able to emit inline script.
	 */
	public function test_inline_script_is_stripped_without_unfiltered_html() {
		$subscriber = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $subscriber );

		$this->assertFalse( current_user_can( 'unfiltered_html' ) );

		$output = $this->field->maybe_sanitize_output(
			'<p>hi</p><script>alert(1)</script>',
			$this->options
		);

		$this->assertStringNotContainsString( '<script', $output );
		$this->assertStringContainsString( '<p>hi</p>', $output );
	}

	/**
	 * The same applies to external script embeds.
	 */
	public function test_external_script_is_stripped_without_unfiltered_html() {
		$subscriber = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $subscriber );

		$output = $this->field->maybe_sanitize_output(
			'<script src="https://evil.example.com/x.js"></script>',
			$this->options
		);

		$this->assertStringNotContainsString( '<script', $output );
		$this->assertStringNotContainsString( 'evil.example.com', $output );
	}

	/**
	 * Logged-out visitors -- the common front-end case -- must never receive script.
	 */
	public function test_script_is_stripped_for_anonymous_visitors() {
		wp_set_current_user( 0 );

		$output = $this->field->maybe_sanitize_output(
			'<script>alert(document.cookie)</script>',
			$this->options
		);

		$this->assertStringNotContainsString( '<script', $output );
		$this->assertStringNotContainsString( 'document.cookie', $output );
	}

	/**
	 * Iframe embeds are the common widget case and must keep working for everyone.
	 */
	public function test_iframe_embeds_survive_for_anonymous_visitors() {
		wp_set_current_user( 0 );

		$output = $this->field->maybe_sanitize_output(
			'<iframe src="https://www.youtube.com/embed/abc" width="560" height="315" allowfullscreen></iframe>',
			$this->options
		);

		$this->assertStringContainsString( '<iframe', $output );
		$this->assertStringContainsString( 'youtube.com/embed/abc', $output );
	}

	/**
	 * Users who hold unfiltered_html keep the embed capability the feature was added for.
	 */
	public function test_script_is_allowed_with_unfiltered_html() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'unfiltered_html is reserved for super admins on multisite.' );
		}

		$admin = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin );

		if ( ! current_user_can( 'unfiltered_html' ) ) {
			$this->markTestSkipped( 'unfiltered_html is not available in this environment.' );
		}

		$output = $this->field->maybe_sanitize_output(
			'<script src="https://example.com/widget.js"></script>',
			$this->options
		);

		$this->assertStringContainsString( '<script', $output );
		$this->assertStringContainsString( 'example.com/widget.js', $output );
	}

	/**
	 * The allow-list filter remains the documented escape hatch for sites that
	 * deliberately want public script embeds.
	 */
	public function test_allowed_html_filter_can_restore_script() {
		wp_set_current_user( 0 );

		$callback = static function ( $allowed ) {
			$allowed['script'] = [ 'src' => true ];

			return $allowed;
		};

		add_filter( 'pods_code_field_sanitize_allowed_html', $callback );

		$output = $this->field->maybe_sanitize_output(
			'<script src="https://example.com/widget.js"></script>',
			$this->options
		);

		remove_filter( 'pods_code_field_sanitize_allowed_html', $callback );

		$this->assertStringContainsString( 'example.com/widget.js', $output );
	}
}
