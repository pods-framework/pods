<?php

namespace Pods_Unit_Tests\Functions;

use Pods;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group  pods
 * @group  pods-functions
 * @group  pods-functions-field-display
 *
 * @covers ::pods_field_display
 */
class FieldDisplayTest extends Pods_UnitTestCase {

	/**
	 * @var int
	 */
	protected $pod_id = 0;

	/**
	 * @var string
	 */
	protected $pod_name = 'test_field_display';

	/**
	 * @var int
	 */
	protected $item_id = 0;

	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		$this->pod_id = $api->save_pod( array(
			'type'    => 'post_type',
			'storage' => 'meta',
			'name'    => $this->pod_name,
		) );

		$api->save_field( array(
			'pod_id' => $this->pod_id,
			'name'   => 'display_field',
			'type'   => 'text',
		) );

		$this->item_id = pods( $this->pod_name )->add( array(
			'display_field' => 'displayed value',
		) );
	}

	public function tearDown(): void {
		$this->pod_id  = 0;
		$this->item_id = 0;

		parent::tearDown();
	}

	/**
	 * Backward-compat: passing a pod name string still works.
	 */
	public function test_pods_field_display_with_pod_name_string() {
		$value = pods_field_display( $this->pod_name, $this->item_id, 'display_field' );

		$this->assertSame( 'displayed value', $value );
	}

	/**
	 * Passing a Pods object as the first argument should use it directly.
	 *
	 * @link https://github.com/pods-framework/pods/issues/7293
	 */
	public function test_pods_field_display_accepts_pods_object() {
		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertInstanceOf( Pods::class, $pod );
		$this->assertTrue( $pod->exists() );

		$value = pods_field_display( $pod, null, 'display_field' );

		$this->assertSame( 'displayed value', $value );
	}

	/**
	 * Field-name shortcut: pods_field_display( 'field_name' ) still works.
	 */
	public function test_pods_field_display_with_field_name_only() {
		global $post, $wpdb;

		// Use the created item as the current global post so the loop shortcut resolves.
		$post = get_post( $this->item_id );
		setup_postdata( $post );

		$value = pods_field_display( 'display_field' );

		wp_reset_postdata();

		$this->assertSame( 'displayed value', $value );
	}

	/**
	 * Non-existent pod name returns null.
	 */
	public function test_pods_field_display_unknown_pod_returns_null() {
		$value = pods_field_display( 'no_such_pod_xyz', $this->item_id, 'display_field' );

		$this->assertNull( $value );
	}

	/**
	 * Empty text field returns falsy, distinct from populated value.
	 */
	public function test_pods_field_display_empty_field_returns_falsy() {
		$empty_id = pods( $this->pod_name )->add( array(
			'display_field' => '',
		) );

		$value = pods_field_display( $this->pod_name, $empty_id, 'display_field' );

		// Pods::display() returns the underlying stored value for empty text fields, do not assert
		// a specific empty type here — only that it is NOT the populated string.
		$this->assertNotSame( 'displayed value', $value );
		$this->assertEmpty( $value );
	}

	/**
	 * First arg accepts a WP_Post (pods()'s $type documents this object type).
	 */
	public function test_pods_field_display_accepts_wp_post() {
		$value = pods_field_display( get_post( $this->item_id ), null, 'display_field' );

		$this->assertSame( 'displayed value', $value );
	}
}
