<?php
namespace Pods_Unit_Tests;
	use Mockery;
	use Pods;

class Test_Traversal extends Pods_UnitTestCase {

	/**
	 * @group traversal
	 * @group traversal-post-type
	 * @covers Pods::valid
	 * @covers Pods::find
	 * @covers Pods::total
	 * @covers Pods::total_found
	 * @covers Pods::fetch
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_find_traversal_post_type() {

		$this->_find_traversal_type( 'post_type' );

	}

	/**
	 * @group traversal
	 * @group traversal-taxonomy
	 * @covers Pods::valid
	 * @covers Pods::find
	 * @covers Pods::total
	 * @covers Pods::total_found
	 * @covers Pods::fetch
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_find_traversal_taxonomy() {

		$this->_find_traversal_type( 'taxonomy' );

	}

	/**
	 * @group traversal
	 * @group traversal-user
	 * @covers Pods::valid
	 * @covers Pods::find
	 * @covers Pods::total
	 * @covers Pods::total_found
	 * @covers Pods::fetch
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_find_traversal_user() {

		$this->_find_traversal_type( 'user' );

	}

	/**
	 * @group traversal
	 * @group traversal-media
	 * @covers Pods::valid
	 * @covers Pods::find
	 * @covers Pods::total
	 * @covers Pods::total_found
	 * @covers Pods::fetch
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_find_traversal_media() {

		$this->_find_traversal_type( 'media' );

	}

	/**
	 * @group traversal
	 * @group traversal-comment
	 * @covers Pods::valid
	 * @covers Pods::find
	 * @covers Pods::total
	 * @covers Pods::total_found
	 * @covers Pods::fetch
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_find_traversal_comment() {

		$this->_find_traversal_type( 'comment' );

	}

	/**
	 * @group traversal
	 * @group traversal-pod
	 * @covers Pods::valid
	 * @covers Pods::find
	 * @covers Pods::total
	 * @covers Pods::total_found
	 * @covers Pods::fetch
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_find_traversal_pod() {

		$this->_find_traversal_type( 'pod' );

	}

	/**
	 *
	 */
	private function _find_traversal_type( $pod_type = null ) {
		if ( empty( $pod_type ) || ! isset( self::$builds[ $pod_type ] ) ) {
			return;
		}

		global $wpdb;

		// Suppress MySQL errors
		add_filter( 'pods_error_die', '__return_false' );

		//$wpdb->suppress_errors( true );
		//$wpdb->hide_errors();

		$params = array(
			'limit' => 1
		);

		$objects = self::$builds[ $pod_type ];

		foreach ( $objects as $object => $storage_types ) {
			foreach ( $storage_types as $storage_type => $pod ) {
				$p = pods( $pod[ 'name' ] );

				$this->assertTrue( is_object( $p ), 'Pod not object' );
				$this->assertTrue( $p->valid(), 'Pod object not valid' );
				$this->assertInstanceOf( 'Pods', $p, 'Pod object not a Pod' );

				$params[ 'where' ] = array();

				$data = self::$related_items[ $pod[ 'name' ] ];
				$data[ 'field_id' ] = $p->pod_data[ 'field_id' ];
				$data[ 'field_index' ] = $p->pod_data[ 'field_index' ];

				foreach ( $pod[ 'fields' ] as $field ) {
					$prefix = $suffix = '';

					if ( in_array( $field[ 'type' ], array( 'pick', 'taxonomy', 'avatar' ) ) ) {
						if ( ! isset( self::$related_items[ $field[ 'name' ] ] ) ) {
							continue;
						}

						$related_data = self::$related_items[ $field[ 'name' ] ];

						$prefix = '`' . $field[ 'name' ] . '`.';

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

						$related_where[] = $prefix . '`' . $related_data[ 'field_id' ] . '` = ' . (int) $check_value;
						                   //. ' AND ' . $prefix . $related_data[ 'field_index' ] . ' = "' . pods_sanitize( $check_index ) . '"';

						$params[ 'where' ][] = '( ' . implode( ' OR ', $related_where ) . ' )';

						if ( empty( $field[ 'pick_val' ] ) ) {
							$field[ 'pick_val' ] = $field[ 'pick_object' ];
						}

						// Related pod traversal
						if ( isset( self::$builds[ $field[ 'pick_object' ] ] ) && isset( self::$builds[ $field[ 'pick_object' ] ][ $field[ 'pick_val' ] ] ) && isset( self::$related_items[ $field[ 'pick_val' ] ] ) ) {
							$related_pod = current( self::$builds[ $field[ 'pick_object' ] ][ $field[ 'pick_val' ] ] );
							$related_pod_type = $related_pod[ 'type' ];
							$related_pod_storage_type = $related_pod[ 'storage' ];

							foreach ( $related_pod[ 'fields' ] as $related_pod_field ) {
								$related_prefix = $related_suffix = '';

								if ( in_array( $related_pod_field[ 'type' ], array( 'pick', 'taxonomy', 'avatar' ) ) ) {
									if ( $field[ 'name' ] == $related_pod_field[ 'name' ] && ! isset( $related_data[ 'data' ][ $related_pod_field[ 'name' ] ] ) ) {
										continue;
									}

									$related_prefix = $related_pod_field[ 'name' ] . '.';

									if ( isset( self::$related_items[ $related_pod_field[ 'name' ] ] ) ) {
										$related_pod_data = self::$related_items[ $related_pod_field[ 'name' ] ];
									}
									elseif ( isset( self::$related_items[ $related_pod[ 'name' ] ] ) ) {
										$related_pod_data = self::$related_items[ $related_pod[ 'name' ] ];
									}
									else {
										continue;
									}

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
									//if ( empty( $check_value ) ) {
										$related_where[] = $prefix . $related_prefix . $related_pod_data[ 'field_id' ] . ' IS NULL';
										$related_where[] = $prefix . $related_prefix . $related_pod_data[ 'field_id' ] . ' IS NULL';
									//}

									$related_where[] = $prefix . $related_prefix . '`' . $related_pod_data[ 'field_id' ] . '` = ' . (int) $check_value;
									                   //. ' AND ' . $prefix . $related_prefix . $related_pod_data[ 'field_index' ] . ' = "' . pods_sanitize( $check_index ) . '"';

									$params[ 'where' ][] = '( ' . implode( ' OR ', $related_where ) . ' )';


								}
								elseif ( 'none' != $related_pod_storage_type ) {
									if ( 'pod' == $related_pod_type ) {
										$related_prefix = 't.';
									} elseif ( 'table' == $related_pod_storage_type ) {
										$related_prefix = '`d`.';
									} elseif ( 'meta' == $related_pod_storage_type ) {
										$related_suffix = '.meta_value';
									}

									$check_related_value = '';

									if ( isset( $related_data[ 'data' ][ $related_pod_field[ 'name' ] ] ) ) {
										$check_related_value = $related_data[ 'data' ][ $related_pod_field[ 'name' ] ];
									}

									$related_where = array();

									// Temporarily check against null too, recursive data not saved fully yet
									//if ( '.meta_value' == $related_suffix && '' == $check_related_value ) {
										$related_where[] = $prefix . $related_prefix . $related_pod_field[ 'name' ] . $related_suffix . ' IS NULL';
									//}

									$related_where[] = $prefix . $related_prefix . '`' . $related_pod_field[ 'name' ] . '`' . $related_suffix . ' = "' . pods_sanitize( $check_related_value ) . '"';

									$params[ 'where' ][] = '( ' . implode( ' OR ', $related_where ) . ' )';
								}
							}
						}
					}
					elseif ( 'none' != $storage_type && $field[ 'name' ] != $data[ 'field_index' ] ) {
						if ( 'pod' == $pod_type ) {
							$prefix = 't.';
						}
						elseif ( 'table' == $storage_type ) {
							$prefix = '`d`.';
						}
						elseif ( 'meta' == $storage_type ) {
							$suffix = '.meta_value';
						}

						$check_value = $data[ 'data' ][ $field[ 'name' ] ];

						$params[ 'where' ][] = $prefix . '`' . $field[ 'name' ] . '`' . $suffix . ' = "' . pods_sanitize( $check_value ) . '"';
					}
				}

				$prefix = '`t`.';

				$check_value = $data[ 'id' ];
				$check_index = $data[ 'data' ][ $data[ 'field_index' ] ];

				$params[ 'where' ][] = $prefix . '`' . $data[ 'field_id' ] . '`' . ' = ' . (int) $check_value;
				$params[ 'where' ][] = $prefix . $data[ 'field_index' ] . ' = "' . pods_sanitize( $check_index ) . '"';

				$p->find( $params );

				$this->assertEquals( 1, $p->total(), 'Total not correct for ' . $pod[ 'name' ] . ': ' . $p->sql . ' | ' . print_r( $params[ 'where' ], true ) );
				$this->assertEquals( 1, $p->total_found(), 'Total found not correct for ' . $pod[ 'name' ] . ': ' . $p->sql . ' | ' . print_r( $params[ 'where' ], true ) );

				$this->assertNotEmpty( $p->fetch(), 'Item not fetched for ' . $pod[ 'name' ] );

				$this->assertEquals( (string) $data[ 'id' ], (string) $p->id(), 'Item ID not as expected for ' . $data[ 'field_id' ] );
				$this->assertEquals( (string) $data[ 'id' ], (string) $p->field( $data[ 'field_id' ] ), 'Item ID not as expected for ' . $data[ 'field_id' ] );
				$this->assertEquals( (string) $data[ 'id' ], (string) $p->display( $data[ 'field_id' ] ), 'Item ID not as expected for ' . $data[ 'field_id' ] );

				$this->assertEquals( $data[ 'data' ][ $data[ 'field_index' ] ], $p->index(), 'Item index not as expected for ' . $data[ 'field_index' ] );
				$this->assertEquals( $data[ 'data' ][ $data[ 'field_index' ] ], $p->field( $data[ 'field_index' ] ), 'Item index not as expected for ' . $data[ 'field_index' ] );
				$this->assertEquals( $data[ 'data' ][ $data[ 'field_index' ] ], $p->display( $data[ 'field_index' ] ), 'Item index not as expected for ' . $data[ 'field_index' ] );
			}
		}
	}

	/**
	 * @group traversal
	 * @group traversal-post-type
	 * @covers Pods::valid
	 * @covers Pods::exists
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_field_traversal_post_type() {

		$this->_field_traversal_type( 'post_type' );

	}

	/**
	 * @group traversal
	 * @group traversal-taxonomy
	 * @covers Pods::valid
	 * @covers Pods::exists
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_field_traversal_taxonomy() {

		$this->_field_traversal_type( 'taxonomy' );

	}

	/**
	 * @group traversal
	 * @group traversal-user
	 * @covers Pods::valid
	 * @covers Pods::exists
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_field_traversal_user() {

		$this->_field_traversal_type( 'user' );

	}

	/**
	 * @group traversal
	 * @group traversal-media
	 * @covers Pods::valid
	 * @covers Pods::exists
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_field_traversal_media() {

		$this->_field_traversal_type( 'media' );

	}

	/**
	 * @group traversal
	 * @group traversal-comment
	 * @covers Pods::valid
	 * @covers Pods::exists
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_field_traversal_comment() {

		$this->_field_traversal_type( 'comment' );

	}

	/**
	 * @group traversal
	 * @group traversal-pod
	 * @covers Pods::valid
	 * @covers Pods::exists
	 * @covers Pods::id
	 * @covers Pods::field
	 * @covers Pods::display
	 */
	public function test_field_traversal_pod() {

		$this->_field_traversal_type( 'pod' );

	}

	/**
	 *
	 */
	private function _field_traversal_type( $pod_type = null ) {
		if ( empty( $pod_type ) || ! isset( self::$builds[ $pod_type ] ) ) {
			return;
		}

		$objects = self::$builds[ $pod_type ];

		foreach ( $objects as $object => $storage_types ) {
			foreach ( $storage_types as $storage_type => $pod ) {
				$data = self::$related_items[ $pod[ 'name' ] ];

				$data[ 'id' ] = (int) $data[ 'id' ];

				$p = pods( $pod[ 'name' ], $data[ 'id' ] );

				$data[ 'field_id' ]    = $p->pod_data[ 'field_id' ];
				$data[ 'field_index' ] = $p->pod_data[ 'field_index' ];

				$this->assertTrue( is_object( $p ), 'Pod not object' );
				$this->assertTrue( $p->valid(), 'Pod object not valid' );
				$this->assertInstanceOf( 'Pods', $p, 'Pod object not a Pod' );

				$this->assertTrue( $p->exists(), 'Pod item not found' );

				$this->assertEquals( (string) $data[ 'id' ], (string) $p->id(), 'Item ID not as expected for ' . $data[ 'field_id' ] );
				$this->assertEquals( (string) $data[ 'id' ], (string) $p->field( $data[ 'field_id' ] ), 'Item ID not as expected for ' . $data[ 'field_id' ] );
				$this->assertEquals( (string) $data[ 'id' ], (string) $p->display( $data[ 'field_id' ] ), 'Item ID not as expected for ' . $data[ 'field_id' ] );

				$this->assertEquals( $data[ 'data' ][ $data[ 'field_index' ] ], $p->index(), 'Item index not as expected for ' . $data[ 'field_index' ] );
				$this->assertEquals( $data[ 'data' ][ $data[ 'field_index' ] ], $p->field( $data[ 'field_index' ] ), 'Item index not as expected for ' . $data[ 'field_index' ] );
				$this->assertEquals( $data[ 'data' ][ $data[ 'field_index' ] ], $p->display( $data[ 'field_index' ] ), 'Item index not as expected for ' . $data[ 'field_index' ] );

				$metadata_type = 'post';

				if ( ! in_array( $pod_type, array( 'post_type', 'media' ) ) ) {
					$metadata_type = $pod_type;
				}

				// Loop through field types
				foreach ( $pod[ 'fields' ] as $field ) {
					if ( 'pick' == $field[ 'type' ] ) {
						if ( ! isset( self::$related_items[ $field[ 'name' ] ] ) ) {
							continue;
						}

						$related_data = self::$related_items[ $field[ 'name' ] ];

						$check_value = $related_data[ 'id' ];
						$check_index = $related_data[ 'data' ][ $related_data[ 'field_index' ] ];

						$check_display_value = $check_value;
						$check_display_index = $check_index;

						if ( 'multi' == $pod[ 'fields' ][ $field[ 'name' ] ][ 'pick_format_type' ] ) {
							$check_value = (array) $check_value;

							if ( 'multi' == $pod[ 'fields' ][ $field[ 'name' ] ][ 'pick_format_type' ] && ! empty( $related_data[ 'limit' ] ) ) {
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

						$this->assertEquals( $check_value, $p->field( $traverse_id ), 'Related Item field value not as expected for ' . $traverse_id );
						$this->assertEquals( $check_display_value, $p->display( $traverse_id ), 'Related Item field display value not as expected for ' . $traverse_id );

						$this->assertEquals( $check_index, $p->field( $traverse_index ), 'Related Item index field value not as expected for ' . $traverse_index );
						$this->assertEquals( $check_display_index, $p->display( $traverse_index ), 'Related Item index field display value not as expected for ' . $traverse_index );

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

							$this->assertEquals( $check_value, array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $traverse_id ) ), 'Related Item field meta value not as expected for ' . $traverse_id );
							$this->assertEquals( current( $check_value ), (int) get_metadata( $metadata_type, $data[ 'id' ], $traverse_id, true ), 'Related Item field single meta value not as expected for ' . $traverse_id );

							$this->assertEquals( $check_index, get_metadata( $metadata_type, $data[ 'id' ], $traverse_index ), 'Related Item index field meta value not as expected for ' . $traverse_index );
							$this->assertEquals( current( $check_index ), get_metadata( $metadata_type, $data[ 'id' ], $traverse_index, true ), 'Related Item index field single meta value not as expected for ' . $traverse_index );
						}

						if ( empty( $field[ 'pick_val' ] ) ) {
							$field[ 'pick_val' ] = $field[ 'pick_object' ];
						}

						// Related pod traversal
						if ( isset( self::$builds[ $field[ 'pick_object' ] ] ) && isset( self::$builds[ $field[ 'pick_object' ] ][ $field[ 'pick_val' ] ] ) && isset( self::$related_items[ $field[ 'pick_val' ] ] ) ) {
							$related_pod              = current( self::$builds[ $field[ 'pick_object' ] ][ $field[ 'pick_val' ] ] );
							$related_pod_type         = $related_pod[ 'type' ];
							$related_pod_storage_type = $related_pod[ 'storage' ];

							foreach ( $related_pod[ 'fields' ] as $related_pod_field ) {
								if ( in_array( $related_pod_field[ 'type' ], array( 'pick', 'taxonomy', 'avatar' ) ) ) {
									if ( $field[ 'name' ] == $related_pod_field[ 'name' ] && ! isset( $related_data[ 'data' ][ $related_pod_field[ 'name' ] ] ) ) {
										continue;
									}

									if ( isset( self::$related_items[ $related_pod_field[ 'name' ] ] ) ) {
										$related_pod_data = self::$related_items[ $related_pod_field[ 'name' ] ];
									}
									elseif ( isset( self::$related_items[ $related_pod[ 'name' ] ] ) ) {
										$related_pod_data = self::$related_items[ $related_pod[ 'name' ] ];
									}
									else {
										continue;
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

										if ( 'multi' == $related_pod[ 'fields' ][ $related_pod_field[ 'name' ] ][ 'pick_format_type' ] && ! empty( $related_pod_data[ 'limit' ] ) ) {
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

									$this->assertEquals( $check_value, $p->field( $related_traverse_id ), 'Deep Related Item field value not as expected for ' . $related_traverse_id );
									$this->assertEquals( $check_display_value, $p->display( $related_traverse_id ), 'Deep Related Item field display value not as expected for ' . $related_traverse_id );

									$this->assertEquals( $check_index, $p->field( $related_traverse_index ), 'Deep Related Item index field value not as expected for ' . $related_traverse_index );
									$this->assertEquals( $check_display_index, $p->display( $related_traverse_index ), 'Deep Related Item index field display value not as expected for ' . $related_traverse_index );

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

										$this->assertEquals( $check_value, array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_id ) ), 'Deep Related Item field meta value not as expected for ' . $related_traverse_id );
										$this->assertEquals( current( $check_value ), (int) get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_id, true ), 'Deep Related Item field single meta value not as expected for ' . $related_traverse_id );

										$this->assertEquals( $check_index, get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_index ), 'Deep Related Item index field meta value not as expected for ' . $related_traverse_index );
										$this->assertEquals( current( $check_index ), get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_index, true ), 'Deep Related Item index field single meta value not as expected for ' . $related_traverse_index );
									}
								}
								elseif ( 'none' != $related_pod_storage_type ) {
									$check_related_value = '';

									if ( isset( $related_data[ 'data' ][ $related_pod_field[ 'name' ] ] ) ) {
										$check_related_value = $related_data[ 'data' ][ $related_pod_field[ 'name' ] ];
									}

									$related_traverse_index = $prefix . $related_pod_field[ 'name' ];

									$this->assertEquals( $check_related_value, $p->field( $related_traverse_index ), 'Deep Related Item field value not as expected for ' . $related_traverse_index );
									$this->assertEquals( $check_related_value, $p->display( $related_traverse_index ), 'Deep Related Item field display value not as expected for ' . $related_traverse_index );

									if ( 'meta' == $storage_type ) {
										$check_related_value = (array) $check_related_value;

										$this->assertEquals( $check_related_value, get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_index ), 'Deep Related Item field meta value not as expected for ' . $related_traverse_index );
										$this->assertEquals( current( $check_related_value ), get_metadata( $metadata_type, $data[ 'id' ], $related_traverse_index, true ), 'Deep Related Item field single meta value not as expected for ' . $related_traverse_index );
									}
								}
							}
						}
					} elseif ( isset( $data[ 'data' ][ $field[ 'name' ] ] ) ) {
						$check_value = $data[ 'data' ][ $field[ 'name' ] ];

						$this->assertEquals( $check_value, $p->field( $field[ 'name' ] ), 'Item field value not as expected for ' . $field[ 'name' ] );
						$this->assertEquals( $check_value, $p->display( $field[ 'name' ] ), 'Item field display value not as expected for ' . $field[ 'name' ] );

						if ( 'meta' == $storage_type ) {
							$check_value = (array) $check_value;

							$this->assertEquals( $check_value, get_metadata( $metadata_type, $data[ 'id' ], $field[ 'name' ] ), 'Item field meta value not as expected for ' . $field[ 'name' ] );
							$this->assertEquals( current( $check_value ), get_metadata( $metadata_type, $data[ 'id' ], $field[ 'name' ], true ), 'Item field single meta value not as expected for ' . $field[ 'name' ] );
						}
					}
				}
			}
		}
	}
