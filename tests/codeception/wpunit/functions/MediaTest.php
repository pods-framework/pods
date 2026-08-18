<?php

namespace Pods_Unit_Tests\Functions;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * Test attachment import via pods_attachment_import().
 *
 * @group  pods
 */
class MediaTest extends Pods_UnitTestCase {

	/**
	 * Path to the source image fixture.
	 *
	 * @var string
	 */
	protected $source_file;

	public function setUp(): void {
		parent::setUp();

		$fixture = dirname( __DIR__, 2 ) . '/_data/images/zoltar.jpg';

		$this->assertFileExists( $fixture, 'Test image fixture is missing.' );

		$this->source_file = $fixture;
	}

	public function tearDown(): void {
		$this->source_file = null;

		parent::tearDown();
	}

	/**
	 * A file imported through a field configured with a custom upload directory
	 * should be stored under that directory and not the default yyyy/mm path.
	 */
	public function test_custom_upload_dir_used_for_import() {
		$pod = [
			'name' => 'test_media_pod',
			'type' => 'pod',
		];

		$field = [
			'type'                    => 'file',
			'file_upload_dir'         => 'custom',
			'file_upload_dir_custom'  => 'custom-test-dir',
		];

		$attachment_id = pods_attachment_import( $this->source_file, 0, false, false, $field, $pod );

		$this->assertIsInt( $attachment_id );
		$this->assertGreaterThan( 0, $attachment_id, 'Attachment import failed.' );

		$file = get_attached_file( $attachment_id );

		$this->assertIsString( $file );
		$this->assertStringContainsString( 'custom-test-dir', $file, 'Attachment was not stored in the custom upload directory.' );

		// The main file should live outside the default date-based uploads subdir.
		$default_subdir = current_time( 'Y/m' );

		$this->assertStringNotContainsString( $default_subdir, $file, 'Attachment was stored in the default uploads subdir instead of the custom directory.' );

		// Image sub-sizes must also land in the custom directory.
		$metadata = wp_get_attachment_metadata( $attachment_id );

		if ( ! empty( $metadata['sizes'] ) ) {
			$base_dir = dirname( $file );

			foreach ( $metadata['sizes'] as $size ) {
				$this->assertStringContainsString( 'custom-test-dir', $base_dir . '/' . $size['file'], 'Image sub-size was not stored in the custom upload directory.' );
			}
		}

		wp_delete_attachment( $attachment_id, true );
	}

	/**
	 * Default behavior is unchanged: importing without a custom directory uses
	 * the standard WordPress uploads path.
	 */
	public function test_default_wp_upload_dir_used_without_custom_config() {
		$attachment_id = pods_attachment_import( $this->source_file );

		$this->assertIsInt( $attachment_id );
		$this->assertGreaterThan( 0, $attachment_id, 'Attachment import failed.' );

		$file = get_attached_file( $attachment_id );

		$this->assertIsString( $file );
		$this->assertStringNotContainsString( 'custom-test-dir', $file );

		wp_delete_attachment( $attachment_id, true );
	}

	/**
	 * A field still using the default 'wp' upload directory should not apply a
	 * custom directory even when a custom path value is present.
	 */
	public function test_wp_upload_dir_option_ignores_custom_path() {
		$field = [
			'type'                    => 'file',
			'file_upload_dir'         => 'wp',
			'file_upload_dir_custom'  => 'should-be-ignored',
		];

		$attachment_id = pods_attachment_import( $this->source_file, 0, false, false, $field );

		$this->assertIsInt( $attachment_id );
		$this->assertGreaterThan( 0, $attachment_id, 'Attachment import failed.' );

		$file = get_attached_file( $attachment_id );

		$this->assertIsString( $file );
		$this->assertStringNotContainsString( 'should-be-ignored', $file );

		wp_delete_attachment( $attachment_id, true );
	}

}
