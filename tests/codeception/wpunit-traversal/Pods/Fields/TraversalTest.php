<?php

namespace Pods_Unit_Tests\Pods\Fields;

use Pods;
use Pods_Unit_Tests\Pods_TraversalTestCase;

/**
 * Class Test_Traversal
 *
 * @package Pods_Unit_Tests
 *
 * @group   pods-traversal
 * @group   pods-field
 * @group   pods-config-required
 */
class TraversalTest extends Pods_TraversalTestCase {

	public static $db_reset_teardown = false;

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-field
	 * @group        pods-traversal-shallow
	 * @group        pods-traversal-field-shallow
	 *
	 * @covers       Pods::fields
	 *
	 * @dataProvider data_provider_base
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_fields_base( $variant_id, $options ) {
		$this->_test_fields_base( $variant_id, $options );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-field
	 * @group        pods-traversal-shallow
	 * @group        pods-traversal-field-shallow
	 *
	 * @covers       Pods::fields
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_fields_traversal( $variant_id, $options ) {
		$this->_test_fields_traversal( $variant_id, $options, false );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-field
	 * @group        pods-traversal-deep
	 * @group        pods-traversal-field-deep
	 *
	 * @covers       Pods::fields
	 *
	 * @dataProvider data_provider_deep
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_fields_deep_traversal( $variant_id, $options ) {
		$this->_test_fields_traversal( $variant_id, $options, true );
	}

	/**
	 * Handle all field() and display() tests based on variations
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function _test_fields_base( $variant_id, $options ) {
		pods_debug( $variant_id );

		// Suppress MySQL errors
		add_filter( 'pods_error_die', '__return_false' );

		// global $wpdb;
		// $wpdb->suppress_errors( true );
		// $wpdb->hide_errors();
		// Options
		$pod_type     = $options['pod_type'];
		$storage_type = $options['object_storage_type'];
		$pod          = $options['pod'];

		$debug = array(
			'pod'          => $pod['name'],
			'pod_type'     => $pod_type,
			'object_storage_type' => $storage_type,
		);

		$this->assertInstanceOf( Pods\Whatsit\Pod::class, $pod );

		$p = self::get_pod( $pod['name'] );

		$this->assertArrayHasKey( $pod['name'], self::$data );

		$data = self::$data[ $pod['name'] ];

		// pods_debug( 'Data: ' . var_export( $data, true ) );

		$this->assertArrayHasKey( 'id', $data, sprintf( 'Data has no ID [%s]', $variant_id ) );

		$data['id'] = (int) $data['id'];

		$this->assertInstanceOf( Pods::class, $p, sprintf( 'Pod not object of Pod [%s]', $variant_id ) );

		if ( (int) $p->id() !== $data['id'] ) {
			$p->fetch( $data['id'] );
		}

		$this->assertTrue( $p->valid(), sprintf( 'Pod object not valid [%s]', $variant_id ) );

		$this->assertTrue( $p->exists(), sprintf( 'Pod item not found [%s]', $variant_id ) );

		$this->assertEquals( (string) $data['_field_index'], (string) $p->fields( $data['_field_index'], 'name' ), sprintf( 'Item index field not as expected (%s) [%s]', $data['_field_index'], $variant_id ) );

		remove_filter( 'pods_error_die', '__return_false' );
	}

	/**
	 * Handle all field() and display() tests based on variations
	 *
	 * @param string  $variant_id Testing variant identification
	 * @param array   $options    Data config to test
	 * @param boolean $deep       Whether to test deep traversal
	 */
	private function _test_fields_traversal( $variant_id, $options, $deep ) {
		pods_debug( $variant_id );

		// Suppress MySQL errors
		add_filter( 'pods_error_die', '__return_false' );

		// global $wpdb;
		// $wpdb->suppress_errors( true );
		// $wpdb->hide_errors();
		// Options
		$pod_type          = $options['pod_type'];
		$storage_type      = $options['object_storage_type'];
		$pod               = $options['pod'];
		$field             = $options['field'];
		$field_name        = $field['name'];
		$field_type        = $field['type'];
		$related_pod       = array();
		$related_pod_field = array();

		if ( 'taxonomy' === $pod_type && 'none' === $storage_type && function_exists( 'get_term_meta' ) ) {
			$storage_type = 'meta';
		}

		$debug = array(
			'pod'          => $pod['name'],
			'pod_type'     => $pod_type,
			'object_storage_type' => $storage_type,
			'field_name'   => $field['name'],
			'field_type'   => $field_type,
			'deep'         => (int) $deep,
		);

		if ( $deep ) {
			$related_pod              = $options['related_pod'];
			$related_pod_type         = $related_pod['type'];
			$related_pod_storage_type = $related_pod['storage'];
			$related_pod_field        = $options['related_pod_field'];

			if ( 'taxonomy' === $related_pod_type && 'none' === $related_pod_storage_type && function_exists( 'get_term_meta' ) ) {
				$related_pod_storage_type = 'meta';
			}

			$debug['related_pod']              = $related_pod['name'];
			$debug['related_pod_type']         = $related_pod_type;
			$debug['related_pod_storage_type'] = $related_pod_storage_type;
			$debug['related_pod_field_name']   = $related_pod_field['name'];
			$debug['related_pod_field_type']   = $related_pod_field['type'];
		}

		// pods_debug( $debug );

		$this->assertInstanceOf( Pods\Whatsit\Pod::class, $pod );

		$p = self::get_pod( $pod['name'] );

		$this->assertArrayHasKey( $pod['name'], self::$data );

		$data = self::$data[ $pod['name'] ];

		// pods_debug( 'Data: ' . var_export( $data, true ) );

		$this->assertArrayHasKey( 'id', $data, sprintf( 'Data has no ID [%s]', $variant_id ) );

		$data['id'] = (int) $data['id'];

		$this->assertInstanceOf( Pods::class, $p, sprintf( 'Pod not object of Pod [%s]', $variant_id ) );

		if ( (int) $p->id() !== $data['id'] ) {
			$p->fetch( $data['id'] );
		}

		$this->assertTrue( $p->valid(), sprintf( 'Pod object not valid [%s]', $variant_id ) );

		$this->assertTrue( $p->exists(), sprintf( 'Pod item not found [%s]', $variant_id ) );

		$this->assertEquals( (string) $data['id'], (string) $p->id(), sprintf( 'Item ID not as expected (%s) [%s]', $data['_field_id'], $variant_id ) );

		// @todo other field type coverage for relational
		if ( in_array( $field_type, array( 'pick', 'taxonomy' ) ) ) {
			if ( ! isset( self::$related_data[ $field['name'] ] ) ) {
				$this->assertTrue( false, sprintf( 'No related item found [%s] [%s]', $variant_id, var_export( self::$related_data, true ) ) );

				return;
			}

			$related_data = self::$related_data[ $field['name'] ];

			$field_data = $pod->get_field( $field['name'] );

			if ( ! $field_data ) {
				$this->assertTrue( false, sprintf( 'No related field data found [%s]', $variant_id ) );

				return;
			}

			$prefix         = $field['name'] . '.';
			$traverse_index = $prefix . $related_data['_field_index'];

			if ( ! $deep ) {
				$the_field = $p->fields( $field['name'] );

				$this->assertEquals( $related_data['_field_index'], $p->fields( $traverse_index, 'name' ), sprintf( 'Related Item index field not as expected (%s) [%s] | %s', $traverse_index, $variant_id, var_export( array(
					'$related_data[\'_field_index\']'             => $related_data['_field_index'],
					'$traverse_index'                         => $traverse_index,
					'$p->fields( $traverse_index, \'name\' )' => $p->fields( $traverse_index, 'name' ),
				), true ) ) );
			} else {
				// Related pod traversal
				if ( in_array( $related_pod_field['type'], array( 'pick', 'taxonomy', 'avatar', 'author' ), true ) ) {
					if ( $field['name'] === $related_pod_field['name'] && ! isset( $related_data[ $related_pod_field['name'] ] ) ) {
						$this->assertTrue( false, sprintf( 'No deep related item found [%s] | %s', $variant_id, print_r( $related_data, true ) ) );

						return;
					}

					$related_object = $related_pod_field['name'];

					if ( ! empty( $related_pod_field['pick_val'] ) ) {
						$related_object = $related_pod_field['pick_val'];
					}

					if ( isset( self::$related_data[ $related_pod_field['name'] ] ) ) {
						$related_pod_data = self::$related_data[ $related_pod_field['name'] ];
					} elseif ( isset( self::$related_data[ $related_object ] ) ) {
						$related_pod_data = self::$related_data[ $related_object ];
					} else {
						// var_dump( array( 7, '$related_pod_field[ \'name\' ]' => $related_pod_field[ 'name' ], '$related_object' => $related_object ) );
						$this->assertTrue( false, sprintf( 'Invalid related item [%s]', $variant_id ) );

						return;
					}

					$related_prefix         = $related_pod_field['name'] . '.';
					$related_traverse_index = $prefix . $related_prefix . $related_pod_data['_field_index'];

					$this->assertEquals( $related_pod_data['_field_index'], $p->fields( $related_traverse_index, 'name' ), sprintf( 'Deep Related Item index field not as expected (%s) [%s] | %s', $related_traverse_index, $variant_id, var_export( array(
						'$related_pod_data[\'_field_index\']'             => $related_pod_data['_field_index'],
						'$related_traverse_index'                         => $related_traverse_index,
						'$p->fields( $related_traverse_index, \'name\' )' => $p->fields( $related_traverse_index, 'name' ),
					), true ) ) );
				} elseif ( 'none' !== $related_pod_storage_type ) {
					$related_traverse_index = $prefix . $related_pod_field['name'];

					$this->assertEquals( $related_pod_field['name'], $p->fields( $related_traverse_index, 'name' ), sprintf( 'Deep Related Item related field index not as expected (%s) [%s] | %s', $related_traverse_index, $variant_id, var_export( array(
						'$related_pod_field[\'name\']'                    => $related_pod_field['name'],
						'$related_traverse_index'                         => $related_traverse_index,
						'$p->fields( $related_traverse_index, \'name\' )' => $p->fields( $related_traverse_index, 'name' ),
					), true ) ) );
				}//end if
			}//end if
		} elseif ( isset( $data[ $field['name'] ] ) ) {
			// Other field assertions
			$this->assertEquals( $field['name'], $p->fields( $field['name'], 'name' ), sprintf( 'Item field name not as expected [%s]', $variant_id ) );
		}//end if

		remove_filter( 'pods_error_die', '__return_false' );
	}

}
