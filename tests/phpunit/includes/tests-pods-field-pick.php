<?php
namespace Pods_Unit_Tests;

use PodsField_Pick;

require_once PODS_TEST_PLUGIN_DIR . '/classes/fields/pick.php';

/**
 * Class Test_PodsField_Boolean
 *
 * @package            Pods_Unit_Tests
 * @group              pods-field
 * @coversDefaultClass PodsField_Pick
 */
class Test_PodsField_Pick extends Pods_UnitTestCase {

	/**
	 * @var PodsField_Pick
	 */
	private $field;

	public $defaultOptions = array(
		"pick_format_type"              => "single",
		"pick_format_single"            => "dropdown",
		"pick_format_multi"             => "checkbox",
		"pick_display_format_multi"     => "default",
		"pick_display_format_separator" => ", ",
	);

	public function setUp() {

		$this->field = new PodsField_Pick();
	}

	public function tearDown() {

		unset( $this->field );
	}

	/**
	 * Single values.
	 */
	public function test_format_defaults() {
		$options = $this->defaultOptions;

		$value = array(
			'item1',
		);

		$expected = 'item1';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	/**
	 * Multiple values and display formats.
	 */
	public function test_display_format_multi_simple() {
		$options = $this->defaultOptions;
		$options[ 'pick_format_type' ] = 'multi';

		$value = array(
			'item1',
			'item2',
			'item3',
		);

		$expected = 'item1, item2, and item3';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );

		// no_serial display format.
		$options[ 'pick_display_format_multi' ] = 'non_serial';

		$expected = 'item1, item2 and item3';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );

		// custom display format.
		$options[ 'pick_display_format_multi' ] = 'custom';

		$expected = 'item1, item2, item3';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );

		// custom display format separator.
		$options[ 'pick_display_format_multi' ]     = 'custom';
		$options[ 'pick_display_format_separator' ] = ' | ';

		$expected = 'item1 | item2 | item3';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	/**
	 * @todo Cover display tests with actual relationship values.
	 */
	public function test_display_format_multi_relationship() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	/**
	 * Covers ::get_object_data
	 */
	public function test_get_object_data() {
		$api = pods_api();

		$name     = 'pick_get_object_data';
		$rel_name = 'related_multi';

		$pod_id = $api->save_pod(
			array(
				'type' => 'post_type',
				'storage' => 'meta',
				'name' => 'pick_get_object_data',
			)
		);

		$params   = array(
			'pod'              => $name,
			'pod_id'           => $pod_id,
			'name'             => $rel_name,
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $name,
			'pick_format_type' => 'multi',
		);

		$api->save_field( $params );

		$posts = array(
			array(
				'post_title' => 'obj1',
				'post_date'  => date( 'Y-m-d H:i:s', time() )
			),
			array(
				'post_title' => 'obj2',
				'post_date'  => date( 'Y-m-d H:i:s', strtotime( '-1 week' ) )
			),
			array(
				'post_title' => 'obj3',
				'post_date'  => date( 'Y-m-d H:i:s', strtotime( '-1 year' ) )
			),
		);

		$post_ids = array();
		foreach ( $posts as $key => $post ) {
			unset( $posts[ $key ] );
			$post_id           = $this->factory()->post->create( $post );
			$post_ids[]        = $post_id;
			$posts[ $post_id ] = get_post( $post_id );
		}

		// Save relationships.
		pods( $name, $post_ids[0] )->save( $rel_name, array( $post_ids[1], $post_ids[2] ) );

		/**
		 * Setup done.
		 */

		// Default.

		$form = pods_form();

		$params = array(
			'id'      => $post_ids[0],
			'name'    => $rel_name,
			'value'   => null,
			'pod'     => $api->load_pod( array( 'id' => (int) $pod_id ) ),
			'options' => $api->load_field(
				array(
					'name'       => $rel_name,
					'table_info' => true,
				)
			),
		);

		// @todo.
		$values = $form::field_method( 'pick', 'get_object_data', $params );
	}
}
