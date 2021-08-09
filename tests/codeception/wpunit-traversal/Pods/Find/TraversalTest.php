<?php

namespace Pods_Unit_Tests\Pods\Find;

use Pods;
use Pods_Unit_Tests\Pods_TraversalTestCase;

/**
 * Class Test_Traversal
 *
 * @package Pods_Unit_Tests
 *
 * @group   pods-traversal
 * @group   pods-find
 * @group   pods-config-required
 */
class TraversalTest extends Pods_TraversalTestCase {

	public static $db_reset_teardown = false;

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-find
	 * @group        pods-traversal-shallow
	 * @group        pods-traversal-find-shallow
	 *
	 * @covers       Pods::valid
	 * @covers       Pods::find
	 * @covers       Pods::total
	 * @covers       Pods::total_found
	 * @covers       Pods::fetch
	 * @covers       Pods::id
	 * @covers       PodsData::select
	 * @covers       PodsData::build
	 * @covers       PodsData::query
	 *
	 * @dataProvider data_provider_base
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_find_base( $variant_id, $options ) {
		$this->_test_find_base( $variant_id, $options );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-find
	 * @group        pods-traversal-shallow
	 * @group        pods-traversal-find-shallow
	 * @group        pods-traversal-query-fields
	 *
	 * @covers       Pods::valid
	 * @covers       Pods::find
	 * @covers       Pods::total
	 * @covers       Pods::total_found
	 * @covers       Pods::fetch
	 * @covers       Pods::id
	 * @covers       PodsData::select
	 * @covers       PodsData::build
	 * @covers       PodsData::query
	 *
	 * @dataProvider data_provider_base
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_find_base_query_fields( $variant_id, $options ) {
		$this->_test_find_base( $variant_id, $options, true );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-find
	 * @group        pods-traversal-shallow
	 * @group        pods-traversal-find-shallow
	 *
	 * @covers       Pods::valid
	 * @covers       Pods::find
	 * @covers       Pods::total
	 * @covers       Pods::total_found
	 * @covers       Pods::fetch
	 * @covers       Pods::id
	 * @covers       PodsData::select
	 * @covers       PodsData::build
	 * @covers       PodsData::traverse
	 * @covers       PodsData::traverse_build
	 * @covers       PodsData::traverse_recurse
	 * @covers       PodsData::query
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_find_traversal( $variant_id, $options ) {
		$this->_test_find_traversal( $variant_id, $options );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-find
	 * @group        pods-traversal-shallow
	 * @group        pods-traversal-find-shallow
	 * @group        pods-traversal-query-fields
	 *
	 * @covers       Pods::valid
	 * @covers       Pods::find
	 * @covers       Pods::total
	 * @covers       Pods::total_found
	 * @covers       Pods::fetch
	 * @covers       Pods::id
	 * @covers       PodsData::select
	 * @covers       PodsData::build
	 * @covers       PodsData::traverse
	 * @covers       PodsData::traverse_build
	 * @covers       PodsData::traverse_recurse
	 * @covers       PodsData::query
	 * @covers       PodsData::query_fields
	 * @covers       PodsData::query_field
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_find_traversal_query_fields( $variant_id, $options ) {
		$this->_test_find_traversal( $variant_id, $options, false, true );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-find
	 * @group        pods-traversal-deep
	 * @group        pods-traversal-find-deep
	 *
	 * @covers       Pods::valid
	 * @covers       Pods::find
	 * @covers       Pods::total
	 * @covers       Pods::total_found
	 * @covers       Pods::fetch
	 * @covers       Pods::id
	 * @covers       PodsData::select
	 * @covers       PodsData::build
	 * @covers       PodsData::traverse
	 * @covers       PodsData::traverse_build
	 * @covers       PodsData::traverse_recurse
	 * @covers       PodsData::query
	 *
	 * @dataProvider data_provider_deep
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function test_find_deep_traversal( $variant_id, $options ) {
		$this->_test_find_traversal( $variant_id, $options, true );
	}

	/**
	 * @group        pods-traversal
	 * @group        pods-traversal-find
	 * @group        pods-traversal-deep
	 * @group        pods-traversal-find-deep
	 * @group        pods-traversal-query-fields
	 *
	 * @covers       Pods::valid
	 * @covers       Pods::find
	 * @covers       Pods::total
	 * @covers       Pods::total_found
	 * @covers       Pods::fetch
	 * @covers       Pods::id
	 * @covers       PodsData::select
	 * @covers       PodsData::build
	 * @covers       PodsData::traverse
	 * @covers       PodsData::traverse_build
	 * @covers       PodsData::traverse_recurse
	 * @covers       PodsData::query
	 * @covers       PodsData::query_fields
	 * @covers       PodsData::query_field
	 *
	 * @dataProvider data_provider_deep
	 *
	 * @param string $variant_id Testing variant identification
	 * @param array  $options    Data config to test
	 */
	public function _test_find_deep_traversal_query_fields( $variant_id, $options ) {
		$this->markTestSkipped( 'query_fields does not yet support traversal auto handling of prefix/suffix' );

		$this->_test_find_traversal( $variant_id, $options, true, true );
	}

	/**
	 *
	 * Handle all find() base tests based on variations
	 *
	 * @param string  $variant_id   Testing variant identification
	 * @param array   $options      Data config to test
	 * @param boolean $query_fields Whether to test query_fields WHERE syntax
	 */
	private function _test_find_base( $variant_id, $options, $query_fields = false ) {
		pods_debug( $variant_id );

		// Suppress MySQL errors
		add_filter( 'pods_error_die', '__return_false' );

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
			'query_fields' => (int) $query_fields,
		);

		$this->assertInstanceOf( Pods\Whatsit\Pod::class, $pod );

		// Base find() $params
		$params = array(
			'limit' => 1,
		);

		$p = self::get_pod( $pod['name'] );

		$this->assertInstanceOf( Pods::class, $p, sprintf( 'Pod not object of Pod [%s]', $variant_id ) );
		$this->assertTrue( $p->valid(), sprintf( 'Pod object not valid [%s]', $variant_id ) );

		$where = array();

		$this->assertArrayHasKey( $pod['name'], self::$data );

		$data = self::$data[ $pod['name'] ];

		$this->assertArrayHasKey( 'id', $data, sprintf( 'Data has no ID [%s]', $variant_id ) );

		$data['id'] = (int) $data['id'];

		$this->assertInstanceOf( Pods::class, $p, sprintf( 'Pod not object of Pod [%s]', $variant_id ) );

		$prefix = '`t`.';

		$check_value = $data['id'];
		$check_index = $data[ $data['_field_index'] ];

		if ( $query_fields ) {
			$where[] = array(
				'field' => $data['_field_id'],
				'value' => (int) $check_value,
			);

			$where[] = array(
				'field' => $data['_field_index'],
				'value' => $check_index,
			);
		} else {
			$where[] = $prefix . "`{$data['_field_id']}` = " . (int) $check_value;
			$where[] = $prefix . "`{$data['_field_index']}` = '" . pods_sanitize( $check_index ) . "'";

			$where = implode( ' AND ', $where );
		}

		$params['where'] = $where;

		$p->find( $params );

		$this->assertEquals( 1, $p->total(), sprintf( 'Total not correct [%s] | %s | %s', $variant_id, $p->sql, print_r( array(
			'where'  => $where,
			'params' => $p->params,
		), true ) ) );
		$this->assertEquals( 1, $p->total_found(), sprintf( 'Total found not correct [%s] | %s | %s', $variant_id, $p->sql, print_r( array(
			'where'  => $where,
			'params' => $p->params,
		), true ) ) );

		$this->assertNotEmpty( $p->fetch(), sprintf( 'Item not fetched [%s]', $variant_id ) );

		$this->assertEquals( (string) $data['id'], (string) $p->id(), sprintf( 'Item ID not as expected (%s) [%s]', $data['_field_id'], $variant_id ) );

		remove_filter( 'pods_error_die', '__return_false' );
	}

	/**
	 * Handle all find() tests based on variations
	 *
	 * @param string  $variant_id   Testing variant identification
	 * @param array   $options      Data config to test
	 * @param boolean $deep         Whether to test deep traversal
	 * @param boolean $query_fields Whether to test query_fields WHERE syntax
	 */
	private function _test_find_traversal( $variant_id, $options, $deep = false, $query_fields = false ) {
		pods_debug( $variant_id );

		// Suppress MySQL errors
		add_filter( 'pods_error_die', '__return_false' );

		global $wpdb;
		// $wpdb->suppress_errors( true );
		// $wpdb->hide_errors();
		// Options
		$pod_type          = $options['pod_type'];
		$storage_type      = $options['storage_type'];
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
			'storage_type' => $storage_type,
			'field_name'   => $field['name'],
			'field_type'   => $field_type,
			'deep'         => (int) $deep,
			'query_fields' => (int) $query_fields,
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

		$this->assertInstanceOf( Pods\Whatsit\Pod::class, $pod );

		// Base find() $params
		$params = array(
			'limit' => 1,
		);

		$p = self::get_pod( $pod['name'] );

		$this->assertArrayHasKey( $pod['name'], self::$data );

		$data = self::$data[ $pod['name'] ];

		$this->assertArrayHasKey( 'id', $data, sprintf( 'Data has no ID [%s]', $variant_id ) );

		$data['id'] = (int) $data['id'];

		$this->assertInstanceOf( Pods::class, $p, sprintf( 'Pod not object of Pod [%s]', $variant_id ) );

		$this->assertTrue( $p->valid(), sprintf( 'Pod object not valid [%s]', $variant_id ) );

		$where = array();

		$podsrel_pod_id   = $pod['id'];
		$podsrel_field_id = $field['id'];
		$podsrel_item_id  = $data['id'];

		$prefix = '';
		$suffix = '';

		if ( in_array( $field_type, array( 'pick', 'taxonomy', 'avatar', 'author' ), true ) ) {
			if ( ! isset( self::$related_data[ $field_name ] ) ) {
				$this->assertTrue( false, sprintf( 'No related item found [%s]', $variant_id ) );

				return;
			}

			$related_data = self::$related_data[ $field_name ];

			$podsrel_item_id = $related_data['id'];

			$prefix = '`' . $field_name . '`.';

			if ( ! $deep ) {
				$check_value = $related_data['id'];
				$check_index = $related_data[ $related_data['_field_index'] ];

				$check_multi_value = (array) $check_value;
				$check_multi_index = (array) $check_index;

				if ( ! empty( $field[ $field_type . '_format_type' ] ) && 'multi' === $field[ $field_type . '_format_type' ] ) {
					// Only go and get it if you need it
					if ( isset( self::$related_data[ $field['name'] ] ) ) {
						$check_multi_value = (array) self::$related_data[ $field['name'] ]['id'];
						$check_multi_index = (array) self::$related_data[ $field['name'] ]['_index'];
					}

					$check_value = current( $check_multi_value );
					$check_index = current( $check_multi_index );
				}

				$related_where = array();

				if ( empty( $check_value ) ) {
					if ( $query_fields ) {
						$related_where[] = array(
							'field'   => $field_name . '.' . $related_data['_field_id'],
							'compare' => 'NOT EXISTS',
						);
					} else {
						$related_where[] = $prefix . '`' . $related_data['_field_id'] . '` IS NULL';
					}
				}

				if ( $query_fields ) {
					$related_where_set = array();

					// Test IN / ALL (ALL uses IN logic in part of it)
					if ( 1 < count( $check_multi_value ) ) {
						$related_where_set[] = array(
							'field'   => $field_name . '.' . $related_data['_field_id'],
							'value'   => $check_multi_value,
							'compare' => 'ALL',
						);

						$related_where_set[] = array(
							'field'   => $field_name . '.' . $related_data['_field_index'],
							'value'   => $check_multi_index,
							'compare' => 'ALL',
						);
					} else {
						$related_where_set[] = array(
							'field' => $field_name . '.' . $related_data['_field_id'],
							'value' => (int) $check_value,
						);

						$related_where_set[] = array(
							'field' => $field_name . '.' . $related_data['_field_index'],
							'value' => $check_index,
						);
					}//end if

					$related_where_set['relation'] = 'AND';

					$related_where[] = $related_where_set;

					$related_where['relation'] = 'OR';
				} else {
					$related_where[] = "
						{$prefix}`{$related_data['_field_id']}` = " . (int) $check_value . "
						AND {$prefix}`{$related_data['_field_index']}` = '" . pods_sanitize( $check_index ) . "'
					";

					$related_where = '( ' . implode( ' OR ', $related_where ) . ' )';
				}//end if

				$where[] = $related_where;
				// pods_debug( array( 1, $related_where, $check_value, $check_index ) );
			} else {
				// Related pod traversal
				$related_pod_type         = $related_pod['type'];
				$related_pod_storage_type = $related_pod['storage'];

				if ( 'taxonomy' === $related_pod_type && 'none' === $related_pod_storage_type && function_exists( 'get_term_meta' ) ) {
					$related_pod_storage_type = 'meta';
				}

				$related_prefix = '';
				$related_suffix = '';

				$related_where = array();

				if ( in_array( $related_pod_field['type'], array( 'pick', 'taxonomy', 'avatar', 'author' ), true ) ) {
					if ( $field_name === $related_pod_field['name'] && ! isset( $related_data[ $related_pod_field['name'] ] ) ) {
						$this->assertTrue( false, sprintf( 'No deep related item found [%s] | %s', $variant_id, print_r( $related_data, true ) ) );

						return;
					}

					$related_object = $related_pod_field['name'];

					if ( ! empty( $related_pod_field['pick_val'] ) ) {
						$related_object = $related_pod_field['pick_val'];
					}

					$related_prefix = '`' . $related_pod_field['name'] . '`.';

					if ( isset( self::$related_data[ $related_pod_field['name'] ] ) ) {
						$related_pod_data = self::$related_data[ $related_pod_field['name'] ];
					} elseif ( isset( self::$related_data[ $related_object ] ) ) {
						$related_pod_data = self::$related_data[ $related_object ];
					} else {
						// pods_debug( array( 2, '$related_pod_field[ \'name\' ]' => $related_pod_field[ 'name' ], '$related_object' => $related_object ) );
						$this->assertTrue( false, sprintf( 'Invalid deep related item [%s]', $variant_id ) );

						return;
					}

					$podsrel_pod_id   = $related_pod['id'];
					$podsrel_field_id = $related_pod_field['id'];
					$podsrel_item_id  = $related_pod_data['id'];

					$check_value = $related_pod_data['id'];

					$check_index = '';

					if ( isset( $related_pod_data[ $related_pod_data['_field_index'] ] ) ) {
						$check_index = $related_pod_data[ $related_pod_data['_field_index'] ];
					}

					$check_multi_value = (array) $check_value;
					$check_multi_index = (array) $check_index;

					if ( ! empty( $related_pod_field[ $related_pod_field['type'] . '_format_type' ] ) && 'multi' === $related_pod_field[ $related_pod_field['type'] . '_format_type' ] ) {
						// Only go and get it if you need it
						if ( isset( self::$related_data[ $related_pod_field['name'] ] ) ) {
							$check_multi_value = (array) self::$related_data[ $related_pod_field['name'] ]['id'];
							$check_multi_index = (array) self::$related_data[ $related_pod_field['name'] ]['_index'];
						}

						$check_value = current( $check_multi_value );
						$check_index = current( $check_multi_index );
					}

					// Temporarily check against null too, recursive data not saved fully yet
					if ( empty( $check_value ) ) {
						if ( $query_fields ) {
							$related_where[] = array(
								'field'   => $field_name . '.' . $related_pod_field['name'] . '.' . $related_pod_data['_field_id'],
								'compare' => 'NOT EXISTS',
							);
						} else {
							$related_where[] = $prefix . $related_prefix . '`' . $related_pod_data['_field_id'] . '` IS NULL';
						}
						// pods_debug( array( 3, $related_where, 'empty $check_value' ) );
					}

					if ( $query_fields ) {
						$related_where[] = array(
							'relation' => 'AND',
							array(
								'field' => $field_name . '.' . $related_pod_field['name'] . '.' . $related_pod_data['_field_id'],
								'value' => (int) $check_value,
							),
							array(
								'field' => $field_name . '.' . $related_pod_field['name'] . '.' . $related_pod_data['_field_index'],
								'value' => $check_index,
							),
						);

						$related_where['relation'] = 'OR';
					} else {
						$related_where[] = "
							{$prefix}{$related_prefix}`{$related_pod_data['_field_id']}` = " . (int) $check_value . "
							AND {$prefix}{$related_prefix}`{$related_pod_data['_field_index']}` = '" . pods_sanitize( $check_index ) . "'
						";

						$related_where = '( ' . implode( ' OR ', $related_where ) . ' )';
					}
					// pods_debug( array( 4, $related_where, $check_value, $check_index ) );
				} elseif ( 'none' !== $related_pod_storage_type ) {
					if ( 'pod' === $related_pod_type ) {
						// $related_prefix = '`t`.';
					} elseif ( 'table' === $related_pod_storage_type ) {
						$related_prefix = '`d`.';
					} elseif ( 'meta' === $related_pod_storage_type ) {
						$related_suffix = '.`meta_value`';
					}

					$check_related_value = '';

					if ( isset( $related_data[ $related_pod_field['name'] ] ) ) {
						$check_related_value = $related_data[ $related_pod_field['name'] ];
					}

					// Temporarily check against null too, recursive data not saved fully yet
					if ( '.`meta_value`' === $related_suffix && '' === $check_related_value ) {
						if ( $query_fields ) {
							$related_where[] = array(
								'field'   => $field_name . '.' . $related_pod_field['name'],
								'compare' => 'NOT EXISTS',
							);
						} else {
							$related_where[] = $prefix . $related_prefix . '`' . $related_pod_field['name'] . '`' . $related_suffix . ' IS NULL';
						}
					}

					if ( $query_fields ) {
						$related_where[] = array(
							'field' => $field_name . '.' . $related_pod_field['name'],
							'value' => $check_related_value,
						);

						$related_where['relation'] = 'OR';
					} else {
						$related_where[] = "
							{$prefix}{$related_prefix}`{$related_pod_field['name']}`{$related_suffix} = '" . pods_sanitize( $check_related_value ) . "'
						";

						$related_where = '( ' . implode( ' OR ', $related_where ) . ' )';
					}
					// pods_debug( array( 5, $related_where, $related_suffix, $check_related_value, self::$related_data[ $field_name ] ) );
				}//end if

				if ( ! empty( $related_where ) ) {
					$where[] = $related_where;
				}
				// pods_debug( array( 6, $where ) );
			}//end if
		} elseif ( 'none' !== $storage_type && $field_name !== $data['_field_index'] ) {
			if ( 'pod' === $pod_type ) {
				$prefix = '`t`.';
			} elseif ( 'table' === $storage_type ) {
				$prefix = '`d`.';
			} elseif ( 'meta' === $storage_type ) {
				$suffix = '.`meta_value`';
			}

			$check_value = $data[ $field_name ];

			if ( $query_fields ) {
				$where[] = array(
					'field' => $field_name,
					'value' => $check_value,
				);
			} else {
				$where[] = "
					{$prefix}`{$field_name}`{$suffix} = '" . pods_sanitize( $check_value ) . "'
				";
			}
		}//end if

		$prefix = '`t`.';

		$check_value = $data['id'];

		if ( $query_fields ) {
			$where[] = array(
				'field' => $data['_field_id'],
				'value' => (int) $check_value,
			);
		} else {
			$where[] = $prefix . '`' . $data['_field_id'] . '`' . ' = ' . (int) $check_value;

			$where = implode( ' AND ', $where );
		}

		$params['where'] = $where;

		$p->find( $params );

		$debug_related = count( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}podsrel` WHERE `item_id` = %d AND `pod_id` = %d AND `field_id` = %d", $check_value, $pod['id'], $field['id'] ) ) ) . ' related items';

		if ( $deep ) {
			$prepare_data   = (array) $podsrel_item_id;
			$prepare_data[] = $podsrel_pod_id;
			$prepare_data[] = $podsrel_field_id;

			$debug_related .= ' | ' . count( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}podsrel` WHERE `item_id` IN ( " . implode( ',', array_fill( 0, count( (array) $podsrel_item_id ), '%d' ) ) . ' ) AND `pod_id` = %d AND `field_id` = %d', $prepare_data ) ) ) . ' deep related items';
		}

		$debug = sprintf( '%s | %s | %s', $p->sql, print_r( array(
			'where'  => $where,
			'params' => $p->params,
		), true ), $debug_related );

		$this->assertEquals( 1, $p->total(), sprintf( 'Total not correct [%s] | %s', $variant_id, $debug ) );
		$this->assertEquals( 1, $p->total_found(), sprintf( 'Total found not correct [%s] | %s', $variant_id, $debug ) );

		$this->assertNotEmpty( $p->fetch(), sprintf( 'Item not fetched [%s]', $variant_id ) );

		$this->assertEquals( (string) $data['id'], (string) $p->id(), sprintf( 'Item ID not as expected (%s) [%s]', $data['_field_id'], $variant_id ) );

		remove_filter( 'pods_error_die', '__return_false' );
	}

}
