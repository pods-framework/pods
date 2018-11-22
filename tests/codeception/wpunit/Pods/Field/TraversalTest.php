<?php

namespace Pods_Unit_Tests\Field;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * Class Test_Traversal
 *
 * @package Pods_Unit_Tests
 *
 * @group   pods-traversal
 * @group   pods-field
 * @group   pods-config-required
 */
class TraversalTest extends Pods_UnitTestCase {

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
		// Suppress MySQL errors
		add_filter( 'pods_error_die', '__return_false' );

		codecept_debug( $options );

		// global $wpdb;
		// $wpdb->suppress_errors( true );
		// $wpdb->hide_errors();
		// Options
		$pod_type     = $options['pod_type'];
		$storage_type = $options['storage_type'];
		$pod          = $options['pod'];

		$debug = array(
			'pod'          => $pod['name'],
			'pod_type'     => $pod_type,
			'storage_type' => $storage_type,
			'field_name'   => $options['field']['name'],
			'field_type'   => $options['field']['type'],
		);

		// Do setup for Pod (tearDown / setUp) per storage type
		if ( in_array( $pod_type, array( 'user', 'media', 'comment' ) ) && 'meta' !== $storage_type ) {
			$debug['skipped'] = 1;

			codecept_debug( $debug );

			return;

			// @todo do magic
			$this->assertTrue( false, sprintf( 'Pod / Storage type requires new setUp() not yet built to continue [%s]', $variant_id ) );
		}

		codecept_debug( $debug );

		$data = self::$related_items[ $pod['name'] ];

		$data['id'] = (int) $data['id'];

		$p = $this->get_pod( $pod['name'] );

		if ( (int) $p->id() !== $data['id'] ) {
			$p->fetch( $data['id'] );
		}

		$data['field_id']    = $p->pod_data['field_id'];
		$data['field_index'] = $p->pod_data['field_index'];

		$this->assertTrue( is_object( $p ), sprintf( 'Pod not object [%s]', $variant_id ) );
		$this->assertTrue( $p->valid(), sprintf( 'Pod object not valid [%s]', $variant_id ) );
		$this->assertInstanceOf( 'Pods', $p, sprintf( 'Pod object not a Pod [%s]', $variant_id ) );

		$this->assertTrue( $p->exists(), sprintf( 'Pod item not found [%s]', $variant_id ) );

		$this->assertEquals( (string) $data['id'], (string) $p->id(), sprintf( 'Item ID not as expected (%s) [%s]', $data['field_id'], $variant_id ) );
		$this->assertEquals( (string) $data['id'], (string) $p->field( $data['field_id'] ), sprintf( 'Item ID not as expected (%s) [%s]', $data['field_id'], $variant_id ) );
		$this->assertEquals( (string) $data['id'], (string) $p->display( $data['field_id'] ), sprintf( 'Item ID not as expected (%s) [%s]', $data['field_id'], $variant_id ) );

		$this->assertEquals( $data['field_index'], $p->data->field_index, sprintf( 'Item index not as expected (%s) [%s]', $data['field_index'], $variant_id ) );
		$this->assertEquals( $data['data'][ $data['field_index'] ], $p->display( $data['field_index'] ), sprintf( 'Item index not as expected (%s) [%s]', $data['field_index'], $variant_id ) );

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
		// Suppress MySQL errors
		add_filter( 'pods_error_die', '__return_false' );

		// global $wpdb;
		// $wpdb->suppress_errors( true );
		// $wpdb->hide_errors();
		// Options
		$pod_type     = $options['pod_type'];
		$storage_type = $options['storage_type'];
		$pod          = $options['pod'];

		if ( 'taxonomy' === $pod_type && 'none' === $storage_type && function_exists( 'get_term_meta' ) ) {
			$storage_type = 'meta';
		}

		$field      = $options['field'];
		$field_type = $field['type'];

		$debug = array(
			'pod'          => $pod['name'],
			'pod_type'     => $pod_type,
			'storage_type' => $storage_type,
			'field_name'   => $field['name'],
			'field_type'   => $field_type,
			'method'       => $method,
			'deep'         => (int) $deep,
		);

		$related_pod       = array();
		$related_pod_field = array();

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

		// Do setup for Pod (tearDown / setUp) per storage type
		if ( in_array( $pod_type, array( 'user', 'media', 'comment' ) ) && 'meta' !== $storage_type ) {
			$debug['skipped'] = 1;

			codecept_debug( $debug );

			return;

			// @todo do magic
			$this->assertTrue( false, sprintf( 'Pod / Storage type requires new setUp() not yet built to continue [%s]', $variant_id ) );
		}

		codecept_debug( $debug );

		$data = self::$related_items[ $pod['name'] ];

		$data['id'] = (int) $data['id'];

		$p = $this->get_pod( $pod['name'] );

		if ( (int) $p->id() !== $data['id'] ) {
			$p->fetch( $data['id'] );
		}

		$data['field_id']    = $p->pod_data['field_id'];
		$data['field_index'] = $p->pod_data['field_index'];

		$this->assertTrue( is_object( $p ), sprintf( 'Pod not object [%s]', $variant_id ) );
		$this->assertTrue( $p->valid(), sprintf( 'Pod object not valid [%s]', $variant_id ) );
		$this->assertInstanceOf( 'Pods', $p, sprintf( 'Pod object not a Pod [%s]', $variant_id ) );

		$this->assertTrue( $p->exists(), sprintf( 'Pod item not found [%s]', $variant_id ) );

		$this->assertEquals( (string) $data['id'], (string) $p->id(), sprintf( 'Item ID not as expected (%s) [%s]', $data['field_id'], $variant_id ) );

		if ( 'post_type' === $pod_type || 'media' === $pod_type ) {
			$metadata_type = 'post';
		} elseif ( 'taxonomy' === $pod_type ) {
			$metadata_type = 'term';
		} else {
			$metadata_type = $pod_type;
		}

		// @todo other field type coverage for relational
		if ( in_array( $field_type, array( 'pick', 'taxonomy' ) ) ) {
			if ( ! isset( self::$related_items[ $field['name'] ] ) ) {
				$this->assertTrue( false, sprintf( 'No related item found [%s]', $variant_id ) );

				return;
			}

			$related_data = self::$related_items[ $field['name'] ];

			$check_value = $related_data['id'];
			$check_index = $related_data['data'][ $related_data['field_index'] ];

			$check_display_value = $check_value;
			$check_display_index = $check_index;

			$field_data = array();

			if ( isset( $pod['fields'][ $field['name'] ] ) ) {
				$field_data = $pod['fields'][ $field['name'] ];
			} elseif ( isset( $pod['object_fields'][ $field['name'] ] ) ) {
				$field_data = $pod['object_fields'][ $field['name'] ];
			} elseif ( ! empty( $field ) ) {
				$field_data = $field;
			} else {
				$this->assertTrue( false, sprintf( 'No related field data found [%s]', $variant_id ) );

				return;
			}

			if ( ! is_object( $field_data ) && ! empty( $field_data['options'] ) ) {
				$field_data = array_merge( $field_data['options'], $field_data );
			}

			if ( ! empty( $field_data[ $field_type . '_format_type' ] ) && 'multi' === $field_data[ $field_type . '_format_type' ] ) {
				$check_value = (array) $check_value;

				if ( ! empty( $related_data['limit'] ) ) {
					$check_indexes = array();

					$check_indexes[] = $check_index;

					for ( $x = 1; $x < $related_data['limit']; $x ++ ) {
						$check_indexes[] = $check_index . ' (' . $x . ')';
					}

					$check_index = $check_indexes;
				} else {
					$check_index = (array) $check_index;
				}

				$check_display_value = pods_serial_comma( $check_value );
				$check_display_index = pods_serial_comma( $check_index );
			}

			$prefix         = $field['name'] . '.';
			$traverse_id    = $prefix . $related_data['field_id'];
			$traverse_index = $prefix . $related_data['field_index'];

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
				if ( in_array( $related_pod_field['type'], array( 'pick', 'taxonomy', 'avatar', 'author' ) ) ) {
					if ( $field['name'] == $related_pod_field['name'] && ! isset( $related_data['data'][ $related_pod_field['name'] ] ) ) {
						$this->assertTrue( false, sprintf( 'No deep related item found [%s] | %s', $variant_id, print_r( $related_data['data'], true ) ) );

						return;
					}

					$related_object = $related_pod_field['name'];

					if ( ! empty( $related_pod_field['pick_val'] ) ) {
						$related_object = $related_pod_field['pick_val'];
					}

					if ( isset( self::$related_items[ $related_pod_field['name'] ] ) ) {
						$related_pod_data = self::$related_items[ $related_pod_field['name'] ];
					} elseif ( isset( self::$related_items[ $related_object ] ) ) {
						$related_pod_data = self::$related_items[ $related_object ];
					} else {
						// var_dump( array( 7, '$related_pod_field[ \'name\' ]' => $related_pod_field[ 'name' ], '$related_object' => $related_object ) );
						$this->assertTrue( false, sprintf( 'Invalid related item [%s]', $variant_id ) );

						return;
					}

					$related_prefix         = $related_pod_field['name'] . '.';
					$related_traverse_id    = $prefix . $related_prefix . $related_pod_data['field_id'];
					$related_traverse_index = $prefix . $related_prefix . $related_pod_data['field_index'];

					$check_value = $related_pod_data['id'];

					$check_index = '';

					if ( isset( $related_pod_data['data'][ $related_pod_data['field_index'] ] ) ) {
						$check_index = $related_pod_data['data'][ $related_pod_data['field_index'] ];
					}

					$check_display_value = $check_value;
					$check_display_index = $check_index;

					if ( ! empty( $related_pod['fields'][ $related_pod_field['name'] ][ $related_pod_field['type'] . '_format_type' ] ) && 'multi' === $related_pod['fields'][ $related_pod_field['name'] ][ $related_pod_field['type'] . '_format_type' ] ) {
						$check_value = (array) $check_value;

						$check_indexes = array();

						$check_indexes[] = $check_index;

						for ( $x = 1; $x < $related_pod_data['limit']; $x ++ ) {
							$check_indexes[] = $check_index . ' (' . $x . ')';
						}

						$check_index = $check_indexes;

						$check_display_value = pods_serial_comma( $check_value );
						$check_display_index = pods_serial_comma( $check_index );
					}

					if ( 'field' === $method ) {
						$this->assertEquals( $check_value, $p->field( $related_traverse_id, ! is_array( $check_value ) ), sprintf( 'Deep Related Item field value not as expected (%s) [%s] | %s', $related_traverse_id, $variant_id, var_export( array(
							'$check_value'                            => $check_value,
							'$p->field( $related_traverse_id, true )' => $p->field( $related_traverse_id, ! is_array( $check_value ) ),
						), true ) ) );
						$this->assertEquals( $check_index, $p->field( $related_traverse_index, ! is_array( $check_index ) ), sprintf( 'Deep Related Item index field value not as expected (%s) [%s] | %s', $related_traverse_index, $variant_id, var_export( array(
							'$check_index'                               => $check_index,
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
					$check_related_value         = '';
					$check_related_value_display = '';

					if ( isset( $related_data['data'][ $related_pod_field['name'] ] ) ) {
						$check_related_value = $related_data['data'][ $related_pod_field['name'] ];

						$check_related_value_display = $check_related_value;

						if ( is_array( $check_value ) ) {
							$check_related_value = array_fill( 0, count( $check_value ), $check_related_value );

							$check_related_value_display = pods_serial_comma( $check_related_value );
						}
					}

					$related_traverse_index = $prefix . $related_pod_field['name'];

					if ( 'field' === $method ) {
						$this->assertEquals( $check_related_value, $p->field( $related_traverse_index, ! is_array( $check_related_value ) ), sprintf( 'Deep Related Item related field index not as expected (%s) [%s] | %s', $related_traverse_index, $variant_id, var_export( array(
							'$check_related_value'                       => $check_related_value,
							'$p->field( $related_traverse_index, true )' => $p->field( $related_traverse_index, ! is_array( $check_related_value ) ),
							'$related_data'                              => $related_data,
						), true ) ) );

						if ( 'meta' === $storage_type && 'taxonomy' !== $related_pod_field['type'] ) {
							$check_related_value = (array) $check_related_value;

							$this->assertEquals( $check_related_value, get_metadata( $metadata_type, $data['id'], $related_traverse_index ), sprintf( 'Deep Related Item related field meta value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
							$this->assertEquals( current( $check_related_value ), get_metadata( $metadata_type, $data['id'], $related_traverse_index, true ), sprintf( 'Deep Related Item related field single meta value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
						}
					} elseif ( 'display' === $method ) {
						$this->assertEquals( $check_related_value_display, $p->display( $related_traverse_index, ! is_array( $check_related_value ) ), sprintf( 'Deep Related Item related field display value not as expected (%s) [%s] | %s', $related_traverse_index, $variant_id, var_export( array(
							'$check_related_value'                         => $check_related_value,
							'$p->display( $related_traverse_index, true )' => $p->display( $related_traverse_index, ! is_array( $check_related_value ) ),
							'$related_data'                                => $related_data,
						), true ) ) );
					}//end if
				}//end if
			}//end if
		} elseif ( isset( $data['data'][ $field['name'] ] ) ) {
			// Other field assertions
			$check_value = $data['data'][ $field['name'] ];

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
