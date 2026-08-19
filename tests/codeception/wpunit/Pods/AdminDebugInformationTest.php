<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsAdmin;

/**
 * @group  pods
 * @covers PodsAdmin
 */
class AdminDebugInformationTest extends Pods_UnitTestCase {

	/**
	 * @var PodsAdmin
	 */
	private $admin;

	public function setUp(): void {
		parent::setUp();

		$this->admin = new PodsAdmin();

		remove_all_filters( 'pods_admin_settings_fields' );
	}

	public function tearDown(): void {
		remove_all_filters( 'pods_admin_settings_fields' );

		unset( $this->admin );
	}

	/**
	 * Issue #7263: add_debug_information() must not emit "Undefined array key data"
	 * when a third-party filter strips the `data` key from session_auto_start.
	 *
	 * @covers PodsAdmin::add_debug_information
	 */
	public function test_add_debug_information_handles_missing_session_data_key() {
		add_filter( 'pods_admin_settings_fields', static function ( $fields ) {
			if ( isset( $fields['session_auto_start'] ) ) {
				unset( $fields['session_auto_start']['data'] );
			}

			return $fields;
		} );

		$info = [];

		// Discard any warnings; we only care that no fatal/warning survives.
		$warning_triggers = [];
		set_error_handler( static function ( $errno, $errstr ) use ( &$warning_triggers ) {
			$warning_triggers[] = [ 'errno' => $errno, 'errstr' => $errstr ];

			return true;
		} );

		try {
			$result = $this->admin->add_debug_information( $info );
		} finally {
			restore_error_handler();
		}

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'pods', $result );

		foreach ( $warning_triggers as $trigger ) {
			$this->assertStringNotContainsString( 'session_auto_start', $trigger['errstr'] );
			$this->assertStringNotContainsString( 'Undefined array key "data"', $trigger['errstr'] );
		}
	}

	/**
	 * Issue #7263: add_debug_information() must not try to call into the
	 * filesystem when session_save_path() is empty (handles tcp://, redis,
	 * memcached), and must not emit file_exists/is_writable warnings when
	 * the path is outside open_basedir.
	 *
	 * @covers PodsAdmin::add_debug_information
	 */
	public function test_add_debug_information_handles_empty_session_save_path() {
		// Redirect session.save_path to an empty value for the call.
		$original = session_save_path();

		@ini_set( 'session.save_path', '' );

		$warning_triggers = [];
		set_error_handler( static function ( $errno, $errstr ) use ( &$warning_triggers ) {
			$warning_triggers[] = $errstr;

			return true;
		} );

		try {
			$result = $this->admin->add_debug_information( [] );
		} finally {
			restore_error_handler();
			@ini_set( 'session.save_path', $original );
		}

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'pods', $result );

		// No file_exists()/is_writable() warnings about the session path.
		foreach ( $warning_triggers as $errstr ) {
			$this->assertStringNotContainsString( 'session_save_path', $errstr );
			$this->assertStringNotContainsString( 'file_exists(', $errstr );
			$this->assertStringNotContainsString( 'is_writable(', $errstr );
		}
	}

	/**
	 * @covers PodsAdmin::add_debug_information
	 */
	public function test_add_debug_information_includes_session_save_path_label() {
		$result = $this->admin->add_debug_information( [] );

		$this->assertArrayHasKey( 'pods', $result );
		$this->assertArrayHasKey( 'fields', $result['pods'] );
		$this->assertArrayHasKey( 'pods-session-save-path', $result['pods']['fields'] );
		$this->assertArrayHasKey( 'pods-session-save-path-exists', $result['pods']['fields'] );
		$this->assertArrayHasKey( 'pods-session-save-path-writable', $result['pods']['fields'] );
	}
}
