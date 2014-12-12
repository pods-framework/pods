<?php
namespace Pods_Unit_Tests;
	use Mockery;
	use Pods;

	class Test_Traversal extends Pods_UnitTestCase {

		/**
		 * @group traversal
		 * @group traversal-find
		 * @group traversal-shallow
		 * @group traversal-find-shallow
		 *
		 * @covers Pods::valid
		 * @covers Pods::find
		 * @covers Pods::total
		 * @covers Pods::total_found
		 * @covers Pods::fetch
		 * @covers Pods::id
		 * @covers PodsData::select
		 * @covers PodsData::build
		 * @covers PodsData::query
		 *
		 * @dataProvider data_provider_base
		 *
		 * @param string $variant_id Testing variant identification
		 * @param array $options Data config to test
		 */
		public function test_find_base( $variant_id, $options ) {

			// Suppress MySQL errors
			add_filter( 'pods_error_die', '__return_false' );

			//global $wpdb;
			//$wpdb->suppress_errors( true );
			//$wpdb->hide_errors();

			// Options
			$pod_type = $options[ 'pod_type' ];
			$storage_type = $options[ 'storage_type' ];
			$pod = $options[ 'pod' ];

			// Do setup for Pod (tearDown / setUp) per storage type
			if ( in_array( $pod_type, array( 'user', 'media', 'comment' ) ) && 'meta' != $storage_type ) {
				return;

				// @todo do magic
				$this->assertTrue( false, sprintf( 'Pod / Storage type requires new setUp() not yet built to continue [%s]', $variant_id ) );
			}

			// Base find() $params
			$params = array(
				'limit' => 1
			);

			$p = pods( $pod[ 'name' ] );

			$this->assertTrue( is_object( $p ), sprintf( 'Pod not object [%s]', $variant_id ) );
			$this->assertTrue( $p->valid(), sprintf( 'Pod object not valid [%s]', $variant_id ) );
			$this->assertInstanceOf( 'Pods', $p, sprintf( 'Pod object not a Pod [%s]', $variant_id ) );

			$where = array();

			$data = self::$related_items[ $pod[ 'name' ] ];
			$data[ 'field_id' ] = $p->pod_data[ 'field_id' ];
			$data[ 'field_index' ] = $p->pod_data[ 'field_index' ];

			$prefix = '`t`.';

			$check_value = $data[ 'id' ];
			$check_index = $data[ 'data' ][ $data[ 'field_index' ] ];

			$where[] = $prefix . '`' . $data[ 'field_id' ] . '`' . ' = ' . (int) $check_value;
			$where[] = $prefix . $data[ 'field_index' ] . ' = "' . pods_sanitize( $check_index ) . '"';

			$params[ 'where' ] = implode( ' AND ', $where );

			$p->find( $params );

			$this->assertEquals( 1, $p->total(), sprintf( 'Total not correct [%s] | %s | %s', $variant_id, $p->sql, print_r( $where, true ) ) );
			$this->assertEquals( 1, $p->total_found(), sprintf( 'Total found not correct [%s] | %s | %s', $variant_id, $p->sql, print_r( $where, true ) ) );

			$this->assertNotEmpty( $p->fetch(), sprintf( 'Item not fetched [%s]', $variant_id ) );

			$this->assertEquals( (string) $data[ 'id' ], (string) $p->id(), sprintf( 'Item ID not as expected (%s) [%s]', $data[ 'field_id' ], $variant_id ) );

		}

		/**
		 * @group traversal
		 * @group traversal-find
		 * @group traversal-shallow
		 * @group traversal-find-shallow
		 *
		 * @covers Pods::valid
		 * @covers Pods::find
		 * @covers Pods::total
		 * @covers Pods::total_found
		 * @covers Pods::fetch
		 * @covers Pods::id
		 * @covers PodsData::select
		 * @covers PodsData::build
		 * @covers PodsData::traverse
		 * @covers PodsData::traverse_build
		 * @covers PodsData::traverse_recurse
		 * @covers PodsData::query
		 *
		 * @dataProvider data_provider
		 *
		 * @param string $variant_id Testing variant identification
		 * @param array $options Data config to test
		 */
		public function test_find_traversal( $variant_id, $options ) {

			$this->_test_find_traversal( $variant_id, $options, false );

		}

		/**
		 * @group traversal
		 * @group traversal-find
		 * @group traversal-deep
		 * @group traversal-find-deep
		 *
		 * @covers Pods::valid
		 * @covers Pods::find
		 * @covers Pods::total
		 * @covers Pods::total_found
		 * @covers Pods::fetch
		 * @covers Pods::id
		 * @covers PodsData::select
		 * @covers PodsData::build
		 * @covers PodsData::traverse
		 * @covers PodsData::traverse_build
		 * @covers PodsData::traverse_recurse
		 * @covers PodsData::query
		 *
		 * @dataProvider data_provider_deep
		 *
		 * @param string $variant_id Testing variant identification
		 * @param array $options Data config to test
		 */
		public function test_find_deep_traversal( $variant_id, $options ) {

			$this->_test_find_traversal( $variant_id, $options, true );

		}

		/**
		 * @group traversal
		 * @group traversal-field
		 * @group traversal-shallow
		 * @group traversal-field-shallow
		 *
		 * @covers Pods::valid
		 * @covers Pods::exists
		 * @covers Pods::field
		 * @covers Pods::display
		 * @covers Pods::id
		 * @covers PodsData::query
		 *
		 * @dataProvider data_provider_base
		 *
		 * @param string $variant_id Testing variant identification
		 * @param array $options Data config to test
		 */
		public function test_field_base( $variant_id, $options ) {

			// Suppress MySQL errors
			add_filter( 'pods_error_die', '__return_false' );

			//global $wpdb;
			//$wpdb->suppress_errors( true );
			//$wpdb->hide_errors();

			// Options
			$pod_type = $options[ 'pod_type' ];
			$storage_type = $options[ 'storage_type' ];
			$pod = $options[ 'pod' ];

			// Do setup for Pod (tearDown / setUp) per storage type
			if ( in_array( $pod_type, array( 'user', 'media', 'comment' ) ) && 'meta' != $storage_type ) {
				return;

				// @todo do magic
				$this->assertTrue( false, sprintf( 'Pod / Storage type requires new setUp() not yet built to continue [%s]', $variant_id ) );
			}

			$data = self::$related_items[ $pod[ 'name' ] ];

			$data[ 'id' ] = (int) $data[ 'id' ];

			$p = pods( $pod[ 'name' ], $data[ 'id' ] );

			$data[ 'field_id' ] = $p->pod_data[ 'field_id' ];
			$data[ 'field_index' ] = $p->pod_data[ 'field_index' ];

			$this->assertTrue( is_object( $p ), sprintf( 'Pod not object [%s]', $variant_id ) );
			$this->assertTrue( $p->valid(), sprintf( 'Pod object not valid [%s]', $variant_id ) );
			$this->assertInstanceOf( 'Pods', $p, sprintf( 'Pod object not a Pod [%s]', $variant_id ) );

			$this->assertTrue( $p->exists(), sprintf( 'Pod item not found [%s]', $variant_id ) );

			$this->assertEquals( (string) $data[ 'id' ], (string) $p->id(), sprintf( 'Item ID not as expected (%s) [%s]', $data[ 'field_id' ], $variant_id ) );
			$this->assertEquals( (string) $data[ 'id' ], (string) $p->field( $data[ 'field_id' ] ), sprintf( 'Item ID not as expected (%s) [%s]', $data[ 'field_id' ], $variant_id ) );
			$this->assertEquals( (string) $data[ 'id' ], (string) $p->display( $data[ 'field_id' ] ), sprintf( 'Item ID not as expected (%s) [%s]', $data[ 'field_id' ], $variant_id ) );

			$this->assertEquals( $data[ 'data' ][ $data[ 'field_index' ] ], $p->index(), sprintf( 'Item index not as expected (%s) [%s]', $data[ 'field_index' ], $variant_id ) );
			$this->assertEquals( $data[ 'data' ][ $data[ 'field_index' ] ], $p->field( $data[ 'field_index' ] ), sprintf( 'Item index not as expected (%s) [%s]', $data[ 'field_index' ], $variant_id ) );
			$this->assertEquals( $data[ 'data' ][ $data[ 'field_index' ] ], $p->display( $data[ 'field_index' ] ), sprintf( 'Item index not as expected (%s) [%s]', $data[ 'field_index' ], $variant_id ) );

		}

		/**
		 * @group traversal
		 * @group traversal-field
		 * @group traversal-shallow
		 * @group traversal-field-shallow
		 *
		 * @covers Pods::valid
		 * @covers Pods::exists
		 * @covers Pods::field
		 * @covers Pods::id
		 * @covers PodsData::query
		 *
		 * @dataProvider data_provider
		 *
		 * @param string $variant_id Testing variant identification
		 * @param array $options Data config to test
		 */
		public function test_field_traversal( $variant_id, $options ) {

			$this->_test_field_traversal( $variant_id, $options, 'field', false );

		}

		/**
		 * @group traversal
		 * @group traversal-field
		 * @group traversal-deep
		 * @group traversal-field-deep
		 *
		 * @covers Pods::valid
		 * @covers Pods::exists
		 * @covers Pods::field
		 * @covers Pods::id
		 * @covers PodsData::query
		 *
		 * @dataProvider data_provider_deep
		 *
		 * @param string $variant_id Testing variant identification
		 * @param array $options Data config to test
		 */
		public function test_field_deep_traversal( $variant_id, $options ) {

			$this->_test_field_traversal( $variant_id, $options, 'field', true );

		}

		/**
		 * @group traversal
		 * @group traversal-display
		 * @group traversal-shallow
		 * @group traversal-display-shallow
		 *
		 * @covers Pods::valid
		 * @covers Pods::exists
		 * @covers Pods::field
		 * @covers Pods::display
		 * @covers Pods::id
		 * @covers PodsData::query
		 *
		 * @dataProvider data_provider
		 *
		 * @param string $variant_id Testing variant identification
		 * @param array $options Data config to test
		 */
		public function test_display_traversal( $variant_id, $options ) {

			$this->_test_field_traversal( $variant_id, $options, 'display', false );

		}

		/**
		 * @group traversal
		 * @group traversal-display
		 * @group traversal-deep
		 * @group traversal-display-deep
		 *
		 * @covers Pods::valid
		 * @covers Pods::exists
		 * @covers Pods::field
		 * @covers Pods::display
		 * @covers Pods::id
		 * @covers PodsData::query
		 *
		 * @dataProvider data_provider_deep
		 *
		 * @param string $variant_id Testing variant identification
		 * @param array $options Data config to test
		 */
		public function test_display_deep_traversal( $variant_id, $options ) {

			$this->_test_field_traversal( $variant_id, $options, 'display', true );

		}

		/**
		 * Data provider for all data to pass into Traversal test methods
		 * for all variations and combinations to be covered.
		 */
		public function data_provider_base() {

			$data_base = array();

			foreach ( self::$builds as $pod_type => $objects ) {
				foreach ( $objects as $object => $storage_types ) {
					foreach ( $storage_types as $storage_type => $pod ) {
						$pod_name = $pod[ 'name' ];

						$data_base[] = array(
							build_query( compact( array( 'pod_type', 'storage_type', 'pod_name' ) ) ),
							array(
								'pod_type'     => $pod_type,
								'storage_type' => $storage_type,
								'pod'          => $pod
							)
						);
					}
				}
			}

			return $data_base;

		}

		/**
		 * Data provider for all data to pass into Traversal test methods
		 * for all variations and combinations to be covered.
		 */
		public function data_provider() {

			$data = array();

			foreach ( self::$builds as $pod_type => $objects ) {
				foreach ( $objects as $object => $storage_types ) {
					foreach ( $storage_types as $storage_type => $pod ) {
						foreach ( $pod[ 'fields' ] as $field_name => $field ) {
							if ( in_array( $field[ 'type' ], array( 'pick', 'taxonomy', 'avatar', 'author' ) ) && empty( $field[ 'pick_val' ] ) ) {
								if ( empty( $field[ 'pick_object' ] ) ) {
									continue;
								}

								$field[ 'pick_val' ] = $field[ 'pick_object' ];
							}

							$pod_name = $pod[ 'name' ];
							$field_name = $field[ 'name' ];

							$data[] = array(
								build_query( compact( array( 'pod_type', 'storage_type', 'pod_name', 'field_name' ) ) ),
								array(
									'pod_type'     => $pod_type,
									'storage_type' => $storage_type,
									'pod'          => $pod,
									'field'        => $field
								)
							);
						}
					}
				}
			}

			return $data;

		}

		/**
		 * Data provider for all data to pass into Traversal test methods
		 * for all variations and combinations to be covered.
		 */
		public function data_provider_deep() {

			$data_deep = array();

			foreach ( self::$builds as $pod_type => $objects ) {
				foreach ( $objects as $object => $storage_types ) {
					foreach ( $storage_types as $storage_type => $pod ) {
						foreach ( $pod[ 'fields' ] as $field_name => $field ) {
							if ( ! in_array( $field[ 'type' ], array( 'pick', 'taxonomy', 'avatar', 'author' ) ) ) {
								continue;
							}

							if ( empty( $field[ 'pick_val' ] ) ) {
								if ( empty( $field[ 'pick_object' ] ) ) {
									continue;
								}

								$field[ 'pick_val' ] = $field[ 'pick_object' ];
							}

							// Related pod traversal
							if ( isset( self::$builds[ $field[ 'pick_object' ] ] ) && isset( self::$builds[ $field[ 'pick_object' ] ][ $field[ 'pick_val' ] ] ) && isset( self::$related_items[ $field[ 'pick_val' ] ] ) ) {
								$related_pod = current( self::$builds[ $field[ 'pick_object' ] ][ $field[ 'pick_val' ] ] );

								foreach ( $related_pod[ 'fields' ] as $related_pod_field ) {
									if ( empty( $related_pod_field[ 'pick_val' ] ) ) {
										if ( empty( $related_pod_field[ 'pick_object' ] ) ) {
											continue;
										}

										$related_pod_field[ 'pick_val' ] = $related_pod_field[ 'pick_object' ];
									}

									$pod_name = $pod[ 'name' ];
									$field_name = $field[ 'name' ];
									$related_pod_name = $related_pod[ 'name' ];
									$related_pod_field_name = $related_pod_field[ 'name' ];

									$data_deep[] = array(
										build_query( compact( array( 'pod_type', 'storage_type', 'pod_name', 'field_name', 'related_pod_name', 'related_field_name' ) ) ),
										array(
											'pod_type'          => $pod_type,
											'storage_type'      => $storage_type,
											'pod'               => $pod,
											'field'             => $field,
											'related_pod'       => $related_pod,
											'related_pod_field' => $related_pod_field
										)
									);
								}
							}
						}
					}
				}
			}

			return $data_deep;

		}

		/**
		 * Handle all find() tests based on variations
		 *
		 * @param string $variant_id Testing variant identification
		 * @param array $options Data config to test
		 * @param boolean $deep Whether to test deep traversal
		 */
		private function _test_find_traversal( $variant_id, $options, $deep ) {

			// Suppress MySQL errors
			add_filter( 'pods_error_die', '__return_false' );

			global $wpdb;
			//$wpdb->suppress_errors( true );
			//$wpdb->hide_errors();

			// Options
			$pod_type = $options[ 'pod_type' ];
			$storage_type = $options[ 'storage_type' ];
			$pod = $options[ 'pod' ];
			$field = $options[ 'field' ];
			$field_name = $field[ 'name' ];
			$field_type = $field[ 'type' ];
			$related_pod = array();
			$related_pod_field = array();

			if ( $deep ) {
				$related_pod = $options[ 'related_pod' ];
				$related_pod_field = $options[ 'related_pod_field' ];
			}

			// Do setup for Pod (tearDown / setUp) per storage type
			if ( in_array( $pod_type, array( 'user', 'media', 'comment' ) ) && 'meta' != $storage_type ) {
				return;

				// @todo do magic
				$this->assertTrue( false, sprintf( 'Pod / Storage type requires new setUp() not yet built to continue [%s]', $variant_id ) );
			}

			// Base find() $params
			$params = array(
				'limit' => 1
			);

			$p = pods( $pod[ 'name' ] );

			$this->assertTrue( is_object( $p ), sprintf( 'Pod not object [%s]', $variant_id ) );
			$this->assertTrue( $p->valid(), sprintf( 'Pod object not valid [%s]', $variant_id ) );
			$this->assertInstanceOf( 'Pods', $p, sprintf( 'Pod object not a Pod [%s]', $variant_id ) );

			$where = array();

			$data = self::$related_items[ $pod[ 'name' ] ];
			$data[ 'field_id' ] = $p->pod_data[ 'field_id' ];
			$data[ 'field_index' ] = $p->pod_data[ 'field_index' ];

			$podsrel_pod_id = $pod[ 'id' ];
			$podsrel_field_id = $field[ 'id' ];
			$podsrel_item_id = $data[ 'id' ];

			$prefix = $suffix = '';

			if ( in_array( $field_type, array( 'pick', 'taxonomy', 'avatar', 'author' ) ) ) {
				if ( !isset( self::$related_items[ $field_name ] ) ) {
					$this->assertTrue( false, sprintf( 'No related item found [%s]', $variant_id ) );

					return;
				}

				$related_data = self::$related_items[ $field_name ];

				$podsrel_item_id = $related_data[ 'id' ];

				$prefix = '`' . $field_name . '`.';

				if ( ! $deep ) {
					$check_value = $related_data[ 'id' ];
					$check_index = $related_data[ 'data' ][ $related_data[ 'field_index' ] ];

					if ( isset( $field[ 'pick_format_type' ] ) && 'multi' == $field[ 'pick_format_type' ] ) {
						$check_value = (array) $check_value;
						$check_value = current( $check_value );
					}

					$related_where = array();

					if ( empty( $check_value ) ) {
						$related_where[] = $prefix . $related_data[ 'field_id' ] . ' IS NULL';
					}

					$related_where[] = $prefix . '`' . $related_data[ 'field_id' ] . '` = ' . (int) $check_value
					                   . ' AND ' . $prefix . $related_data[ 'field_index' ] . ' = "' . pods_sanitize( $check_index ) . '"';

					$where[] = '( ' . implode( ' OR ', $related_where ) . ' )';
				}
				else {
					// Related pod traversal
					$related_pod_type = $related_pod[ 'type' ];
					$related_pod_storage_type = $related_pod[ 'storage' ];

					$related_prefix = $related_suffix = '';

					if ( in_array( $related_pod_field[ 'type' ], array( 'pick', 'taxonomy', 'avatar', 'author' ) ) ) {
						if ( $field_name == $related_pod_field[ 'name' ] && !isset( $related_data[ 'data' ][ $related_pod_field[ 'name' ] ] ) ) {
							$this->assertTrue( false, sprintf( 'No deep related item found [%s] | %s', $variant_id, print_r( $related_data[ 'data' ], true ) ) );

							return;
						}

						$related_object = $related_pod_field[ 'name' ];

						if ( ! empty( $related_pod_field[ 'pick_val' ] ) ) {
							$related_object = $related_pod_field[ 'pick_val' ];
						}

						$related_prefix = $related_pod_field[ 'name' ] . '.';

						if ( isset( self::$related_items[ $related_pod_field[ 'name' ] ] ) ) {
							$related_pod_data = self::$related_items[ $related_pod_field[ 'name' ] ];
						}
						elseif ( isset( self::$related_items[ $related_object ] ) ) {
							$related_pod_data = self::$related_items[ $related_object ];
						}
						else {
							var_dump( array( '$related_pod_field[ \'name\' ]' => $related_pod_field[ 'name' ], '$related_object' => $related_object ) );

							$this->assertTrue( false, sprintf( 'Invalid deep related item [%s]', $variant_id ) );

							return;
						}

						$podsrel_pod_id = $related_pod[ 'id' ];
						$podsrel_field_id = $related_pod_field[ 'id' ];
						$podsrel_item_id = $related_pod_data[ 'id' ];

						$check_value = $related_pod_data[ 'id' ];

						$check_index = '';

						if ( isset( $related_pod_data[ 'data' ][ $related_pod_data[ 'field_index' ] ] ) ) {
							$check_index = $related_pod_data[ 'data' ][ $related_pod_data[ 'field_index' ] ];
						}

						if ( isset( $related_pod_field[ 'pick_format_type' ] ) && 'multi' == $related_pod_field[ 'pick_format_type' ] ) {
							$check_value = (array) $check_value;
							$check_value = current( $check_value );
						}

						$related_where = array();

						// Temporarily check against null too, recursive data not saved fully yet
						if ( empty( $check_value ) ) {
							$related_where[] = $prefix . $related_prefix . $related_pod_data[ 'field_id' ] . ' IS NULL';
						}

						$related_where[] = $prefix . $related_prefix . '`' . $related_pod_data[ 'field_id' ] . '` = ' . (int) $check_value
						                   . ' AND ' . $prefix . $related_prefix . $related_pod_data[ 'field_index' ] . ' = "' . pods_sanitize( $check_index ) . '"';

						$where[] = '( ' . implode( ' OR ', $related_where ) . ' )';
					}
					elseif ( 'none' != $related_pod_storage_type ) {
						if ( 'pod' == $related_pod_type ) {
							$related_prefix = 't.';
						}
						elseif ( 'table' == $related_pod_storage_type ) {
							$related_prefix = '`d`.';
						}
						elseif ( 'meta' == $related_pod_storage_type ) {
							$related_suffix = '.meta_value';
						}

						$check_related_value = '';

						if ( isset( $related_data[ 'data' ][ $related_pod_field[ 'name' ] ] ) ) {
							$check_related_value = $related_data[ 'data' ][ $related_pod_field[ 'name' ] ];
						}

						$related_where = array();

						// Temporarily check against null too, recursive data not saved fully yet
						if ( '.meta_value' == $related_suffix && '' == $check_related_value ) {
							$related_where[] = $prefix . $related_prefix . $related_pod_field[ 'name' ] . $related_suffix . ' IS NULL';
						}

						$related_where[] = $prefix . $related_prefix . '`' . $related_pod_field[ 'name' ] . '`' . $related_suffix . ' = "' . pods_sanitize( $check_related_value ) . '"';

						$where[] = '( ' . implode( ' OR ', $related_where ) . ' )';
					}
				}
			}
			elseif ( 'none' != $storage_type && $field_name != $data[ 'field_index' ] ) {
				if ( 'pod' == $pod_type ) {
					$prefix = 't.';
				}
				elseif ( 'table' == $storage_type ) {
					$prefix = '`d`.';
				}
				elseif ( 'meta' == $storage_type ) {
					$suffix = '.meta_value';
				}

				$check_value = $data[ 'data' ][ $field_name ];

				$where[] = $prefix . '`' . $field_name . '`' . $suffix . ' = "' . pods_sanitize( $check_value ) . '"';
			}

			$prefix = '`t`.';

			$check_value = $data[ 'id' ];

			$where[] = $prefix . '`' . $data[ 'field_id' ] . '`' . ' = ' . (int) $check_value;

			$params[ 'where' ] = implode( ' AND ', $where );

			$p->find( $params );

			$debug_related = count( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}podsrel` WHERE `item_id` = %d AND `pod_id` = %d AND `field_id` = %d", $check_value, $pod[ 'id' ], $field[ 'id' ] ) ) ) . ' related items';

			if ( $deep ) {
				$debug_related .= ' | ' . count( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}podsrel` WHERE `item_id` = %d AND `pod_id` = %d AND `field_id` = %d", $podsrel_item_id, $podsrel_pod_id, $podsrel_field_id ) ) ) . ' deep related items';
			}

			$debug = sprintf( '%s | %s | %s', $p->sql, print_r( $where, true ), $debug_related );

			$this->assertEquals( 1, $p->total(), sprintf( 'Total not correct [%s] | %s', $variant_id, $debug ) );
			$this->assertEquals( 1, $p->total_found(), sprintf( 'Total found not correct [%s] | %s', $variant_id, $debug ) );

			$this->assertNotEmpty( $p->fetch(), sprintf( 'Item not fetched [%s]', $variant_id ) );

			$this->assertEquals( (string) $data[ 'id' ], (string) $p->id(), sprintf( 'Item ID not as expected (%s) [%s]', $data[ 'field_id' ], $variant_id ) );

		}

		/**
		 * Handle all field() and display() tests based on variations
		 *
		 * @param string $variant_id Testing variant identification
		 * @param array $options Data config to test
		 * @param string $method Method to test
		 * @param boolean $deep Whether to test deep traversal
		 */
		private function _test_field_traversal( $variant_id, $options, $method, $deep ) {

			// Suppress MySQL errors
			add_filter( 'pods_error_die', '__return_false' );

			//global $wpdb;
			//$wpdb->suppress_errors( true );
			//$wpdb->hide_errors();

			// Options
			$pod_type = $options[ 'pod_type' ];
			$storage_type = $options[ 'storage_type' ];
			$pod = $options[ 'pod' ];
			$field = $options[ 'field' ];
			$field_type = $field[ 'type' ];
			$related_pod = array();
			$related_pod_field = array();

			if ( $deep ) {
				$related_pod = $options[ 'related_pod' ];
				$related_pod_field = $options[ 'related_pod_field' ];
			}

			// Do setup for Pod (tearDown / setUp) per storage type
			if ( in_array( $pod_type, array( 'user', 'media', 'comment' ) ) && 'meta' != $storage_type ) {
				return;

				// @todo do magic
				$this->assertTrue( false, sprintf( 'Pod / Storage type requires new setUp() not yet built to continue [%s]', $variant_id ) );
			}

			$data = self::$related_items[ $pod[ 'name' ] ];

			$data[ 'id' ] = (int) $data[ 'id' ];

			$p = pods( $pod[ 'name' ], $data[ 'id' ] );

			$data[ 'field_id' ] = $p->pod_data[ 'field_id' ];
			$data[ 'field_index' ] = $p->pod_data[ 'field_index' ];

			$this->assertTrue( is_object( $p ), sprintf( 'Pod not object [%s]', $variant_id ) );
			$this->assertTrue( $p->valid(), sprintf( 'Pod object not valid [%s]', $variant_id ) );
			$this->assertInstanceOf( 'Pods', $p, sprintf( 'Pod object not a Pod [%s]', $variant_id ) );

			$this->assertTrue( $p->exists(), sprintf( 'Pod item not found [%s]', $variant_id ) );

			$this->assertEquals( (string) $data[ 'id' ], (string) $p->id(), sprintf( 'Item ID not as expected (%s) [%s]', $data[ 'field_id' ], $variant_id ) );

			$metadata_type = 'post';

			if ( !in_array( $pod_type, array( 'post_type', 'media' ) ) ) {
				$metadata_type = $pod_type;
			}

			// @todo other field type coverage for relational
			if ( 'pick' == $field_type ) {
				if ( !isset( self::$related_items[ $field[ 'name' ] ] ) ) {
					$this->assertTrue( false, sprintf( 'No related item found [%s]', $variant_id ) );

					return;
				}

				$related_data = self::$related_items[ $field[ 'name' ] ];

				$check_value = $related_data[ 'id' ];
				$check_index = $related_data[ 'data' ][ $related_data[ 'field_index' ] ];

				$check_display_value = $check_value;
				$check_display_index = $check_index;

				if ( 'multi' == $pod[ 'fields' ][ $field[ 'name' ] ][ 'pick_format_type' ] ) {
					$check_value = (array) $check_value;

					if ( 'multi' == $pod[ 'fields' ][ $field[ 'name' ] ][ 'pick_format_type' ] && !empty( $related_data[ 'limit' ] ) ) {
						$check_indexes = array();

						$check_indexes[] = $check_index;

						for ( $x = 1; $x < $related_data[ 'limit' ]; $x++ ) {
							$check_indexes[] = $check_index . ' (' . $x . ')';
						}

						$check_index = $check_indexes;
					}

					$check_display_value = pods_serial_comma( $check_value );
					$check_display_index = pods_serial_comma( $check_index );
				}

				$prefix = $field[ 'name' ] . '.';
				$traverse_id = $prefix . $related_data[ 'field_id' ];
				$traverse_index = $prefix . $related_data[ 'field_index' ];

				/*if ( false === $p->field( $traverse_id ) ) {
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
					if ( 'field' == $method ) {
						$this->assertEquals( $check_value, $p->field( $traverse_id ), sprintf( 'Related Item field value not as expected (%s) [%s]', $traverse_id, $variant_id ) );
						$this->assertEquals( $check_index, $p->field( $traverse_index ), sprintf( 'Related Item index field value not as expected (%s) [%s]', $traverse_index, $variant_id ) );

						if ( 'meta' == $storage_type ) {
							$check_value = array_map( 'absint', (array) $check_value );
							$check_index = (array) $check_index;

							/*var_dump( array(
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

							$this->assertEquals( $check_value, array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $traverse_id ) ), sprintf( 'Related Item field meta value not as expected (%s) [%s]', $traverse_id, $variant_id ) );
							$this->assertEquals( current( $check_value ), (int) get_metadata( $metadata_type, $data[ 'id' ], $traverse_id, true ), sprintf( 'Related Item field single meta value not as expected (%s) [%s]', $traverse_id, $variant_id ) );

							$this->assertEquals( $check_index, get_metadata( $metadata_type, $data[ 'id' ], $traverse_index ), sprintf( 'Related Item index field meta value not as expected (%s) [%s]', $traverse_index, $variant_id ) );
							$this->assertEquals( current( $check_index ), get_metadata( $metadata_type, $data[ 'id' ], $traverse_index, true ), sprintf( 'Related Item index field single meta value not as expected (%s) [%s]', $traverse_index, $variant_id ) );
						}
					}
					elseif ( 'display' == $method ) {
						$this->assertEquals( $check_display_value, $p->display( $traverse_id ), sprintf( 'Related Item field display value not as expected (%s) [%s]', $traverse_id, $variant_id ) );
						$this->assertEquals( $check_display_index, $p->display( $traverse_index ), sprintf( 'Related Item index field display value not as expected (%s) [%s]', $traverse_index, $variant_id ) );
					}
				}
				else {
					// Related pod traversal
					$related_pod_storage_type = $related_pod[ 'storage' ];

					if ( in_array( $related_pod_field[ 'type' ], array( 'pick', 'taxonomy', 'avatar', 'author' ) ) ) {
						if ( $field[ 'name' ] == $related_pod_field[ 'name' ] && !isset( $related_data[ 'data' ][ $related_pod_field[ 'name' ] ] ) ) {
							$this->assertTrue( false, sprintf( 'No deep related item found [%s] | %s', $variant_id, print_r( $related_data[ 'data' ], true ) ) );

							return;
						}

						$related_object = $related_pod_field[ 'name' ];

						if ( ! empty( $related_pod_field[ 'pick_val' ] ) ) {
							$related_object = $related_pod_field[ 'pick_val' ];
						}

						if ( isset( self::$related_items[ $related_pod_field[ 'name' ] ] ) ) {
							$related_pod_data = self::$related_items[ $related_pod_field[ 'name' ] ];
						}
						elseif ( isset( self::$related_items[ $related_object ] ) ) {
							$related_pod_data = self::$related_items[ $related_object ];
						}
						/*elseif ( isset( self::$related_items[ $related_pod[ 'name' ] ] ) ) {
							$related_pod_data = self::$related_items[ $related_pod[ 'name' ] ];
						}*/
						else {
							var_dump( array( '$related_pod_field[ \'name\' ]' => $related_pod_field[ 'name' ], '$related_object' => $related_object ) );

							$this->assertTrue( false, sprintf( 'Invalid related item [%s]', $variant_id ) );

							return;
						}

						$related_prefix = $related_pod_field[ 'name' ] . '.';
						$related_traverse_id = $prefix . $related_prefix . $related_pod_data[ 'field_id' ];
						$related_traverse_index = $prefix . $related_prefix . $related_pod_data[ 'field_index' ];

						$check_value = $related_pod_data[ 'id' ];

						$check_index = '';

						if ( isset( $related_pod_data[ 'data' ][ $related_pod_data[ 'field_index' ] ] ) ) {
							$check_index = $related_pod_data[ 'data' ][ $related_pod_data[ 'field_index' ] ];
						}

						$check_display_value = $check_value;
						$check_display_index = $check_index;

						if ( 'multi' == $related_pod[ 'fields' ][ $related_pod_field[ 'name' ] ][ 'pick_format_type' ] ) {
							$check_value = (array) $check_value;

							if ( 'multi' == $related_pod[ 'fields' ][ $related_pod_field[ 'name' ] ][ 'pick_format_type' ] && !empty( $related_pod_data[ 'limit' ] ) ) {
								$check_indexes = array();

								$check_indexes[] = $check_index;

								for ( $x = 1; $x < $related_pod_data[ 'limit' ]; $x++ ) {
									$check_indexes[] = $check_index . ' (' . $x . ')';
								}

								$check_index = $check_indexes;
							}

							$check_display_value = pods_serial_comma( $check_value );
							$check_display_index = pods_serial_comma( $check_index );
						}

						if ( 'field' == $method ) {
							$this->assertEquals( $check_value, $p->field( $related_traverse_id, true ), sprintf( 'Deep Related Item field value not as expected (%s) [%s]', $related_traverse_id, $variant_id ) );
							$this->assertEquals( $check_index, $p->field( $related_traverse_index, true ), sprintf( 'Deep Related Item index field value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );

							if ( 'meta' == $storage_type ) {
								$check_value = array_map( 'absint', (array) $check_value );
								$check_index = (array) $check_index;

								/*var_dump( array(
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

								$this->assertEquals( $check_value, array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_id ) ), sprintf( 'Deep Related Item field meta value not as expected (%s) [%s]', $related_traverse_id, $variant_id ) );
								$this->assertEquals( current( $check_value ), (int) get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_id, true ), sprintf( 'Deep Related Item field single meta value not as expected (%s) [%s]', $related_traverse_id, $variant_id ) );

								$this->assertEquals( $check_index, get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_index ), sprintf( 'Deep Related Item index field meta value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
								$this->assertEquals( current( $check_index ), get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_index, true ), sprintf( 'Deep Related Item index field single meta value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
							}
						}
						elseif ( 'display' == $method ) {
							$this->assertEquals( $check_display_value, $p->display( $related_traverse_id ), sprintf( 'Deep Related Item field display value not as expected (%s) [%s]', $related_traverse_id, $variant_id ) );
							$this->assertEquals( $check_display_index, $p->display( $related_traverse_index ), sprintf( 'Deep Related Item index field display value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
						}
					}
					elseif ( 'none' != $related_pod_storage_type ) {
						$check_related_value = '';

						if ( isset( $related_data[ 'data' ][ $related_pod_field[ 'name' ] ] ) ) {
							$check_related_value = $related_data[ 'data' ][ $related_pod_field[ 'name' ] ];
						}

						$related_traverse_index = $prefix . $related_pod_field[ 'name' ];

						if ( 'field' == $method ) {
							$this->assertEquals( $check_related_value, $p->field( $related_traverse_index ), sprintf( 'Deep Related Item field value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );

							if ( 'meta' == $storage_type ) {
								$check_related_value = (array) $check_related_value;

								$this->assertEquals( $check_related_value, get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_index ), sprintf( 'Deep Related Item field meta value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
								$this->assertEquals( current( $check_related_value ), get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_index, true ), sprintf( 'Deep Related Item field single meta value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
							}
						}
						elseif ( 'display' == $method ) {
							$this->assertEquals( $check_related_value, $p->display( $related_traverse_index ), sprintf( 'Deep Related Item field display value not as expected (%s) [%s]', $related_traverse_index, $variant_id ) );
						}
					}
				}
			}
			elseif ( isset( $data[ 'data' ][ $field[ 'name' ] ] ) ) {
				$check_value = $data[ 'data' ][ $field[ 'name' ] ];

				if ( 'field' == $method ) {
					$this->assertEquals( $check_value, $p->field( $field[ 'name' ] ), sprintf( 'Item field value not as expected [%s]', $variant_id ) );

					if ( 'meta' == $storage_type ) {
						$check_value = (array) $check_value;

						$this->assertEquals( $check_value, get_metadata( $metadata_type, $data[ 'id' ], $field[ 'name' ] ), sprintf( 'Item field meta value not as expected [%s]', $variant_id ) );
						$this->assertEquals( current( $check_value ), get_metadata( $metadata_type, $data[ 'id' ], $field[ 'name' ], true ), sprintf( 'Item field single meta value not as expected [%s]', $variant_id ) );
					}
				}
				elseif ( 'display' == $method ) {
					$this->assertEquals( $check_value, $p->display( $field[ 'name' ] ), sprintf( 'Item field display value not as expected [%s]', $variant_id ) );
				}
			}

		}

	}