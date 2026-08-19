<?php

namespace Pods_Unit_Tests\Pods\Field;

use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsField_File;
use ReflectionProperty;

/**
 * @group              pods-field
 * @group              pods-field-file
 * @coversDefaultClass PodsField_File
 */
class PodsField_FileTest extends Pods_UnitTestCase {

	/**
	 * @var PodsField_File
	 */
	private $field;

	public function setUp(): void {
		parent::setUp();

		$this->field = new PodsField_File();
	}

	public function tearDown(): void {
		$this->set_tmp_upload_dir( null );

		unset( $this->field );

		parent::tearDown();
	}

	/**
	 * Set the private static PodsField_File::$tmp_upload_dir used by
	 * filter_upload_dir() (only settable internally during an upload).
	 *
	 * @param string|null $value
	 */
	private function set_tmp_upload_dir( $value ) {
		$property = new ReflectionProperty( 'PodsField_File', 'tmp_upload_dir' );
		$property->setAccessible( true );
		$property->setValue( null, $value );
	}

	/**
	 * A representative WordPress upload directory array.
	 *
	 * @return array
	 */
	private function get_uploads_fixture() {
		return [
			'path'    => '/var/www/wp-content/uploads/2026/08',
			'url'     => 'http://example.test/wp-content/uploads/2026/08',
			'subdir'  => '/2026/08',
			'basedir' => '/var/www/wp-content/uploads',
			'baseurl' => 'http://example.test/wp-content/uploads',
			'error'   => false,
		];
	}

	/**
	 * @covers ::filter_upload_dir
	 */
	public function test_filter_upload_dir_returns_uploads_unchanged_without_custom_dir() {
		$this->set_tmp_upload_dir( null );

		$uploads = $this->get_uploads_fixture();

		$this->assertSame( $uploads, $this->field->filter_upload_dir( $uploads ) );
	}

	/**
	 * @covers ::filter_upload_dir
	 */
	public function test_filter_upload_dir_strips_traversal_segments() {
		$this->set_tmp_upload_dir( 'foo/../../bar' );

		$result = $this->field->filter_upload_dir( $this->get_uploads_fixture() );

		// The ".." segments are dropped, leaving only "foo/bar".
		$this->assertSame( '/var/www/wp-content/uploads/foo/bar', $result['path'] );
		$this->assertSame( '/foo/bar', $result['subdir'] );
		$this->assertStringNotContainsString( '..', $result['path'] );
		$this->assertStringNotContainsString( '..', $result['url'] );
	}

	/**
	 * @covers ::filter_upload_dir
	 */
	public function test_filter_upload_dir_returns_uploads_unchanged_when_only_traversal() {
		$this->set_tmp_upload_dir( '../../../..' );

		$uploads = $this->get_uploads_fixture();

		// Nothing safe remains, so the default uploads array is left untouched.
		$this->assertSame( $uploads, $this->field->filter_upload_dir( $uploads ) );
	}

	/**
	 * @covers ::filter_upload_dir
	 */
	public function test_filter_upload_dir_normalizes_backslashes_and_null_bytes() {
		$this->set_tmp_upload_dir( "..\\..\\secret\0" );

		$result = $this->field->filter_upload_dir( $this->get_uploads_fixture() );

		$this->assertSame( '/var/www/wp-content/uploads/secret', $result['path'] );
		$this->assertStringNotContainsString( '..', $result['path'] );
		$this->assertStringNotContainsString( "\0", $result['path'] );
		$this->assertStringNotContainsString( '\\', $result['path'] );
	}

	/**
	 * @covers ::filter_upload_dir
	 */
	public function test_filter_upload_dir_allows_plain_custom_dir() {
		$this->set_tmp_upload_dir( 'my-custom-dir' );

		$result = $this->field->filter_upload_dir( $this->get_uploads_fixture() );

		$this->assertSame( '/var/www/wp-content/uploads/my-custom-dir', $result['path'] );
		$this->assertSame( '/my-custom-dir', $result['subdir'] );
	}
}
