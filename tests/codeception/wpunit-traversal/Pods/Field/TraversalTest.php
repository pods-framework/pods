<?php

namespace Pods_Unit_Tests\Pods\Field;

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
	 * @covers       Pods::valid
	 * @covers       Pods::exists
	 * @covers       Pods::field
	 * @covers       Pods::display
	 * @covers       Pods::id
	 * @covers       PodsData::query
	 *
	 * @dataProvider data_provider_base
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_field_base( $variant_id, $options ) {
		$this->_test_field_base( $variant_id, $options );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-field
	 * @group        pods-traversal-shallow
	 * @group        pods-traversal-field-shallow
	 *
	 * @covers       Pods::valid
	 * @covers       Pods::exists
	 * @covers       Pods::field
	 * @covers       Pods::id
	 * @covers       PodsData::query
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_field_traversal( $variant_id, $options ) {
		$this->_test_field_traversal( $variant_id, $options, 'field', false );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-field
	 * @group        pods-traversal-deep
	 * @group        pods-traversal-field-deep
	 *
	 * @covers       Pods::valid
	 * @covers       Pods::exists
	 * @covers       Pods::field
	 * @covers       Pods::id
	 * @covers       PodsData::query
	 *
	 * @dataProvider data_provider_deep
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_field_deep_traversal( $variant_id, $options ) {
		$this->_test_field_traversal( $variant_id, $options, 'field', true );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-display
	 * @group        pods-traversal-shallow
	 * @group        pods-traversal-display-shallow
	 *
	 * @covers       Pods::valid
	 * @covers       Pods::exists
	 * @covers       Pods::field
	 * @covers       Pods::display
	 * @covers       Pods::id
	 * @covers       PodsData::query
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_display_traversal( $variant_id, $options ) {
		$this->_test_field_traversal( $variant_id, $options, 'display', false );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-display
	 * @group        pods-traversal-deep
	 * @group        pods-traversal-display-deep
	 *
	 * @covers       Pods::valid
	 * @covers       Pods::exists
	 * @covers       Pods::field
	 * @covers       Pods::display
	 * @covers       Pods::id
	 * @covers       PodsData::query
	 *
	 * @dataProvider data_provider_deep
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_display_deep_traversal( $variant_id, $options ) {
		$this->_test_field_traversal( $variant_id, $options, 'display', true );
	}

	/**
	 * Handle all field() and display() tests based on variations
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function _test_field_base( $variant_id, $options ) {
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

		$this->assertEquals( (string) $data['id'], (string) $p->id(), sprintf( 'Item ID not as expected (%s) [%s]', $data['_field_id'], $variant_id ) );
		$this->assertEquals( (string) $data['id'], (string) $p->field( $data['_field_id'] ), sprintf( 'Item ID not as expected (%s) [%s]', $data['_field_id'], $variant_id ) );
		$this->assertEquals( (string) $data['id'], (string) $p->display( $data['_field_id'] ), sprintf( 'Item ID not as expected (%s) [%s]', $data['_field_id'], $variant_id ) );

		$this->assertEquals( $data['_field_index'], $p->data->field_index, sprintf( 'Item index not as expected (%s) [%s]', $data['_field_index'], $variant_id ) );
		$this->assertEquals( $data[ $data['_field_index'] ], $p->display( $data['_field_index'] ), sprintf( 'Item index not as expected (%s) [%s]', $data['_field_index'], $variant_id ) );

		remove_filter( 'pods_error_die', '__return_false' );
	}

	/**
	 * Handle all field() and display() tests based on variations
	 *
	 * @param string  $variant_id Testing variant identification
	 * @param array   $options    Data config to test
	 * @param string  $method     Method to test
	 * @param boolean $deep       Whether to test deep traversal
	 */
	private function _test_field_traversal( $variant_id, $options, $method, $deep ) {
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
			'method'       => $method,
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

		if ( 'post_type' === $pod_type || 'media' === $pod_type ) {
			$metadata_type = 'post';
		} elseif ( 'taxonomy' === $pod_type ) {
			$metadata_type = 'term';
		} else {
			$metadata_type = $pod_type;
		}

		// @todo other field type coverage for relational
		if ( in_array( $field_type, array( 'pick', 'taxonomy' ) ) ) {
			if ( ! isset( self::$related_data[ $field['name'] ] ) ) {
				$this->assertTrue( false, sprintf( 'No related item found [%s] [%s]', $variant_id, var_export( self::$related_data, true ) ) );

				return;
			}

			$related_data = self::$related_data[ $field['name'] ];

			// pods_debug( 'Related data for ' . $field['name'] . ': ' . var_export( $related_data, true ) );

			$check_value = $related_data['id'];
			$check_index = $related_data['_index'];

			$check_display_value = $check_value;
			$check_display_index = $check_index;

			$field_data = $pod->get_field( $field['name'] );

			if ( ! $field_data ) {
				$this->assertTrue( false, sprintf( 'No related field data found [%s]', $variant_id ) );

				return;
			}

			if ( ! empty( $field_data[ $field_type . '_format_type' ] ) && 'multi' === $field_data[ $field_type . '_format_type' ] ) {
				$check_value = (array) $check_value;
				$check_index = (array) $check_index;

				$check_display_value = pods_serial_comma( $check_value );
				$check_display_index = pods_serial_comma( $check_index );
			}

			$prefix         = $field['name'] . '.';
			$traverse_id    = $prefix . $related_data['_field_id'];
			$traverse_index = $prefix . $related_data['_field_index'];

			/*
			if ( false === $p->field( $traverse_id ) ) {
				var_dump( array(
					'pod'                 => $pod[ 'name' ],
					'storage'             => $storage_type,
					'traverse_id'         => $traverse_id,
					'check_value'         => $check_value,
					'field_value'         => $p->field( $traverse_id ),
					'check_display_value' => $check_display_value,
					'display_value'       => $p->display( $traverse_id ),
					'check_index'         => $check_index,
					'field_index'         => $p->field( $traverse_index ),
					'check_display_index' => $check_display_index,
					'display_index'       => $p->display( $traverse_index ),
					'field_full'          => $p->field( $field[ 'name' ] ),
					//'field_data'          => $p->fields( $field[ 'name' ] )
				) );
			}*/

			if ( ! $deep ) {
				if ( 'field' === $method ) {
					// Reset value/index array keys.
					if ( is_array( $check_value ) ) {
						$check_value = array_values( $check_value );
					}

					if ( is_array( $check_index ) ) {
						$check_index = array_values( $check_index );
					}

					$this->assertEquals( $check_value, $p->field( $traverse_id ), sprintf( 'Related Item field value not as expected (%s) [%s] {%s should be %s}', $traverse_id, $variant_id, var_export( $p->field( $traverse_id ), true ), var_export( $check_value, true ) ) );
					$this->assertEquals( $check_index, $p->field( $traverse_index ), sprintf( 'Related Item index field value not as expected (%s) [%s] {%s should be %s}', $traverse_index, $variant_id, var_export( $p->field( $traverse_index ), true ), var_export( $check_index, true ) ) );

					if ( 'meta' === $storage_type && 'taxonomy' !== $field_type ) {
						$check_value = array_map( 'absint', (array) $check_value );
						$check_index = (array) $check_index;

						/*
						var_dump( array(
							'check_array' => $check_value,
							'metadata_array' => array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $traverse_id ) ),

							'check_single' => current( $check_value ),
							'metadata_single' => (int) get_metadata( $metadata_type, $data[ 'id' ], $traverse_id, true ),

							'check_index_array' => $check_index,
							'metadata_index_array' => get_metadata( $metadata_type, $data[ 'id' ], $traverse_index ),

							'check_index_single' => current( $check_index ),
							'metadata_index_single' => get_metadata( $metadata_type, $data[ 'id' ], $traverse_index, true ),

							'metadata_full' => array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $field[ 'name' ] ) )
						) );*/

						$this->assertEquals( $check_value, array_map( 'absint', get_metadata( $metadata_type, $data['id'], $traverse_id ) ), sprintf( 'Related Item field meta value not as expected (%s) [%s]', $traverse_id, $variant_id ) );
						$this->assertEquals( current( $check_value ), (int) get_metadata( $metadata_type, $data['id'], $traverse_id, true ), sprintf( 'Related Item field single meta value not as expected (%s) [%s]', $traverse_id, $variant_id ) );

						$this->assertEquals( $check_index, get_metadata( $metadata_type, $data['id'], $traverse_index ), sprintf( 'Related Item index field meta value not as expected (%s) [%s]', $traverse_index, $variant_id ) );
						$this->assertEquals( current( $check_index ), get_metadata( $metadata_type, $data['id'], $traverse_index, true ), sprintf( 'Related Item index field single meta value not as expected (%s) [%s]', $traverse_index, $variant_id ) );
					}//end if
				} elseif ( 'display' === $method ) {
					$this->assertEquals( $check_display_value, $p->display( $traverse_id ), sprintf( 'Related Item field display value not as expected (%s) [%s]', $traverse_id, $variant_id ) );
					$this->assertEquals( $check_display_index, $p->display( $traverse_index ), sprintf( 'Related Item index field display value not as expected (%s) [%s]', $traverse_index, $variant_id ) );
				}//end if
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
					$related_traverse_id    = $prefix . $related_prefix . $related_pod_data['_field_id'];
					$related_traverse_index = $prefix . $related_prefix . $related_pod_data['_field_index'];

					$check_value = $related_pod_data['id'];
					$check_index = $related_pod_data['_index'];

					$check_display_value = $check_value;
					$check_display_index = $check_index;

					if ( ! empty( $related_pod['fields'][ $related_pod_field['name'] ][ $related_pod_field['type'] . '_format_type' ] ) && 'multi' === $related_pod['fields'][ $related_pod_field['name'] ][ $related_pod_field['type'] . '_format_type' ] ) {
						$check_value = (array) $check_value;
						$check_index = (array) $check_index;

						$check_display_value = pods_serial_comma( $check_value );
						$check_display_index = pods_serial_comma( $check_index );
					}

					// Reset value/index array keys.
					if ( is_array( $check_value ) ) {
						$check_value = array_values( $check_value );
					}

					if ( is_array( $check_index ) ) {
						$check_index = array_values( $check_index );
					}

					if ( 'field' === $method ) {
						$this->assertEquals( $check_value, $p->field( $related_traverse_id, ! is_array( $check_value ) ), sprintf( 'Deep Related Item field value not as expected (%s) [%s] | %s', $related_traverse_id, $variant_id, var_export( array(
							'$check_value'                            => $check_value,
							'$check_index'                            => $check_index,
							'$related_traverse_id'                    => $related_traverse_id,
							'$p->field( $related_traverse_id, true )' => $p->field( $related_traverse_id, ! is_array( $check_value ) ),
						), true ) ) );
						$this->assertEquals( $check_index, $p->field( $related_traverse_index, ! is_array( $check_index ) ), sprintf( 'Deep Related Item index field value not as expected (%s) [%s] | %s', $related_traverse_index, $variant_id, var_export( array(
							'$check_value'                               => $check_value,
							'$check_index'                               => $check_index,
							'$related_traverse_index'                    => $related_traverse_index,
							'$p->field( $related_traverse_index, true )' => $p->field( $related_traverse_index, ! is_array( $check_value ) ),
						), true ) ) );

						if ( 'meta' === $storage_type && 'taxonomy' !== $related_pod_field['type'] ) {
							$check_value = array_map( 'absint', (array) $check_value );
							$check_index = (array) $check_index;

							/*
							var_dump( array(
								'check_array' => $check_value,
								'metadata_array' => array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_id ) ),

								'check_single' => current( $check_value ),
								'metadata_single' => (int) get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_id, true ),

								'check_index_array' => $check_index,
								'metadata_index_array' => get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_index ),

								'check_index_single' => current( $check_index ),
								'metadata_index_single' => get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_index, true ),

								'metadata_full' => array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $prefix . $related_pod_field[ 'name' ] ) )
							) );*/

							$this->assertEquals( $check_value, array_map( 'absint', get_metadata( $metadata_type, $data['id'], $related_traverse_id ) ), sprintf( 'Deep Related Item field meta value not as expected (%s) [%s]', $related_traverse_id, $variant_id ) );
							$this->assertEquals( current( $check_value ), (int) get_metadata( $metadata_type, $data['id'], $related_traverse_id, true ), sprintf( 'Deep Related Item field single meta value not as expected (%s) [%s]', $related_traverse_id, $variant_id ) );

							$this->assertEquals( $check_index, get_metadata( $metadata_type, $data['id'], $related_traverse_index ), sprintf( 'Deep Related Item index field meta value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
							$this->assertEquals( current( $check_index ), get_metadata( $metadata_type, $data['id'], $related_traverse_index, true ), sprintf( 'Deep Related Item index field single meta value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
						}//end if
					} elseif ( 'display' === $method ) {
						$this->assertEquals( $check_display_value, $p->display( $related_traverse_id ), sprintf( 'Deep Related Item field display value not as expected (%s) [%s]', $related_traverse_id, $variant_id ) );
						$this->assertEquals( $check_display_index, $p->display( $related_traverse_index ), sprintf( 'Deep Related Item index field display value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
					}//end if
				} elseif ( 'none' !== $related_pod_storage_type ) {
					$check_related_value = '';
					$check_related_index = '';

					$check_related_display_value = '';
					$check_related_display_index = '';

					if ( isset( $related_data[ $related_pod_field['name'] ] ) ) {
						$check_related_value = $related_data[ $related_pod_field['name'] ];
						$check_related_index = $check_related_value;

						// Setup test data for multiple pages.
						if ( 'test_rel_pages' === $field['name'] ) {
							$check_related_value = wp_list_pluck( $related_data['_items'], $related_pod_field['name'] );

							if ( 1 === count( $check_related_value ) ) {
								$check_related_value = reset( $check_related_value );
							}

							$check_related_index = $check_related_value;
						}

						if ( is_array( $check_value ) && ! is_array( $check_related_value ) ) {
							$check_related_value = array_fill( 0, count( $check_value ), $check_related_value );

							if ( ! is_array( $check_related_index ) ) {
								$check_related_index = array_fill( 0, count( $check_value ), $check_related_index );
							}
						}

						$check_related_display_value = pods_serial_comma( (array) $check_related_value );
						$check_related_display_index = pods_serial_comma( (array) $check_related_index );
					}

					$related_traverse_index = $prefix . $related_pod_field['name'];

					// Reset value/index array keys.
					if ( is_array( $check_related_value ) ) {
						$check_related_value = array_values( $check_related_value );
					}

					if ( is_array( $check_related_display_index ) ) {
						$check_related_display_index = array_values( $check_related_display_index );
					}

					if ( 'field' === $method ) {
						$this->assertEquals( $check_related_value, $p->field( $related_traverse_index, ! is_array( $check_related_value ) ), sprintf( 'Deep Related Item related field index not as expected (%s) [%s] | %s', $related_traverse_index, $variant_id, var_export( array(
							'$check_related_value'                       => $check_related_value,
							'$check_related_index'                       => $check_related_index,
							'$p->field( $related_traverse_index, true )' => $p->field( $related_traverse_index, ! is_array( $check_related_value ) ),
							'$related_data'                              => $related_data,
						), true ) ) );

						if ( 'meta' === $storage_type && 'taxonomy' !== $related_pod_field['type'] ) {
							$check_related_value = (array) $check_related_value;

							$this->assertEquals( $check_related_value, get_metadata( $metadata_type, $data['id'], $related_traverse_index ), sprintf( 'Deep Related Item related field meta value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
							$this->assertEquals( current( $check_related_value ), get_metadata( $metadata_type, $data['id'], $related_traverse_index, true ), sprintf( 'Deep Related Item related field single meta value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
						}
					} elseif ( 'display' === $method ) {
						$this->assertEquals( $check_related_display_index, $p->display( $related_traverse_index, ! is_array( $check_related_value ) ), sprintf( 'Deep Related Item related field display value not as expected (%s) [%s] | %s', $related_traverse_index, $variant_id, var_export( array(
							'$check_related_value'                         => $check_related_value,
							'$check_related_index'                         => $check_related_index,
							'$check_related_display_value'                 => $check_related_display_value,
							'$check_related_display_index'                 => $check_related_display_index,
							'$p->display( $related_traverse_index, true )' => $p->display( $related_traverse_index, ! is_array( $check_related_value ) ),
							'$related_data'                                => $related_data,
						), true ) ) );
					}//end if
				}//end if
			}//end if
		} elseif ( isset( $data[ $field['name'] ] ) ) {
			// Other field assertions
			$check_value = $data[ $field['name'] ];

			if ( 'field' === $method ) {
				$this->assertEquals( $check_value, $p->field( $field['name'] ), sprintf( 'Item field value not as expected [%s]', $variant_id ) );

				if ( 'meta' === $storage_type ) {
					$check_value = (array) $check_value;

					$this->assertEquals( $check_value, get_metadata( $metadata_type, $data['id'], $field['name'] ), sprintf( 'Item field meta value not as expected [%s]', $variant_id ) );
					$this->assertEquals( current( $check_value ), get_metadata( $metadata_type, $data['id'], $field['name'], true ), sprintf( 'Item field single meta value not as expected [%s]', $variant_id ) );
				}
			} elseif ( 'display' === $method ) {
				$this->assertEquals( $check_value, $p->display( $field['name'] ), sprintf( 'Item field display value not as expected [%s]', $variant_id ) );
			}
		}//end if

		remove_filter( 'pods_error_die', '__return_false' );
	}

}
