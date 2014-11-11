<?php
namespace Pods_Unit_Tests;
	use Mockery;
	use Pods;

	// Components
	require PODS_PLUGIN_DIR . '/components/Migrate-Packages/Migrate-Packages.php';
	require PODS_PLUGIN_DIR . '/components/Advanced-Content-Types.php';
	require PODS_PLUGIN_DIR . '/components/Table-Storage.php';

	require PODS_PLUGIN_DIR . '/classes/fields/pick.php';

class Test_Traversal extends Pods_UnitTestCase {

	public static $supported_types = array(
		'post_type' => array(
			'object' => array(
				'%d',
			    'post',
			    'page',
			    'nav_menu_item'
			),
		    // @todo Figure out how to split test meta/table for existing objects
			'storage' => array(
				'meta',
			    'table'
			)
		),
	    'taxonomy' => array(
		    'object' => array(
			    '%d',
		        'category',
		        'post_tag',
		        'nav_menu'
		    ),
		    // @todo Figure out how to split test meta/table for existing objects
			'storage' => array(
			    'table',
			    'none'
			)
	    ),
	    'user' => array(
		    // @todo Figure out how to split test meta/table for existing objects
			'storage' => array(
			    'meta',
		        'table'
			),
	        'fields' => array(
		        array(
			        'name' => 'avatar',
			        'type' => 'avatar'
		        )
	        ),
	        'data' => array(
				'display_name' => 'User %s',
				'user_login' => 'User-%s',
				'user_email' => '%s@user.com',
			    'user_pass' => '%s'
	        )
	    ),
	    /*'media' => array(
		    // @todo Figure out how to split test meta/table for existing objects
			'storage' => array(
			    'meta',
		        'table'
			),
	        'data' => array(
		        'guid' => 'http://f.cl.ly/items/1f1e0d0c0D310X1z0m3C/Screen%%20Shot%%202014-11-07%%20at%%201.06.32%%20AM.png'
	        )
	    ),*/
	    'comment' => array(
		    // @todo Figure out how to split test meta/table for existing objects
			'storage' => array(
			    'meta',
		        'table'
			),
	        'data' => array(
				'comment_author' => 'Comment %s',
				'comment_author_email' => '%s@comment.com',
				'comment_author_url' => 'http://comment.com',
				'comment_content' => '%s',
				'comment_post_ID' => 1,
				'comment_type' => 'comment',
			    'post_status' => 'publish',
			    'comment_date' => '2014-11-11 00:00:00'
	        )
	    ),
	    'pod' => array(
		    'object' => array(
			    '%d'
		    ),
			'storage' => array(
		        'table'
			),
	        'fields' => array(
		        array(
			        'name' => 'name',
			        'type' => 'text'
		        ),
		        array(
			        'name' => 'author',
			        'type' => 'pick',
				    'pick_object' => 'user',
				    'pick_val' => 'user',
				    'pick_format_type' => 'single'
		        )
	        )
	    )
	);

	public static $supported_fields = array(
	    array(
			'name' => 'test_rel_user',
			'type' => 'pick',
		    'pick_object' => 'user',
		    'pick_val' => 'user',
		    'pick_format_type' => 'single'
		),
		array(
			'name' => 'test_rel_post',
			'type' => 'pick',
		    'pick_object' => 'post_type',
		    'pick_val' => 'post',
		    'pick_format_type' => 'single'
		),
	    array(
			'name' => 'test_rel_pages',
			'type' => 'pick',
		    'pick_object' => 'post_type',
		    'pick_val' => 'page',
		    'pick_format_type' => 'multi'
		),
	    array(
			'name' => 'test_rel_tag',
			'type' => 'pick',
		    'pick_object' => 'taxonomy',
		    'pick_val' => 'post_tag',
		    'pick_format_type' => 'single'
		),
	    /*array(
			'name' => 'test_rel_media',
			'type' => 'pick',
		    'pick_object' => 'media',
		    'pick_val' => '',
		    'pick_format_type' => 'single'
		),*/
	    array(
			'name' => 'test_rel_comment',
			'type' => 'pick',
		    'pick_object' => 'comment',
		    'pick_val' => 'comment',
		    'pick_format_type' => 'single'
		),
	    array(
			'name' => 'test_text_field',
			'type' => 'text'
		)
	);

	public static $builds = array(
		/*
		 * 'pod_type' => array(
		 *      'object_name' => array(
		 *          'meta' => array(
		 *              // pod array of info
		 *          ),
		 *          'table' => array(
		 *              // pod array of info
		 *          ),
		 *          'none' => array(
		 *              // pod array of info
		 *          )
		 *      )
		 * )
		 */
	);

	public static $related_items = array(
	    'test_rel_user' => array(
			'pod' => 'user',
		    'id' => 0,
		    'field_index' => 'display_name',
		    'field_id' => 'ID',
			'data' => array(
				'display_name' => 'Related user',
				'user_login' => 'related-user',
				'user_email' => 'related@user.com',
			    'user_pass' => 'changeme'
			)
		),
		'test_rel_post' => array(
			'pod' => 'post',
		    'id' => 0,
		    'field_index' => 'post_title',
		    'field_id' => 'ID',
		    'field_author' => 'post_author',
			'data' => array(
				'post_title' => 'Related post',
				'post_content' => '%s',
			    'post_status' => 'publish'
			)
		),
	    'test_rel_pages' => array(
			'pod' => 'page',
		    'id' => 0,
		    'ids' => array(),
		    'field_index' => 'post_title',
		    'field_id' => 'ID',
		    'field_author' => 'post_author',
		    'limit' => 2,
			'data' => array(
				'post_title' => 'Related page',
				'post_content' => '%s',
			    'post_status' => 'publish'
			)
		),
	    'test_rel_tag' => array(
			'pod' => 'post_tag',
		    'id' => 0,
		    'field_index' => 'name',
		    'field_id' => 'term_id',
		    'field_author' => false,
			'data' => array(
				'name' => 'Related post tag',
				'description' => '%s'
			)
		),
	    /*'test_rel_media' => array(
			'pod' => 'media',
		    'id' => 0,
		    'field_index' => 'post_title',
		    'field_id' => 'ID',
		    'field_author' => 'post_author',
			'data' => array(
				'post_title' => 'Related media',
				'post_content' => '%s',
			    'post_status' => 'publish'
			)
		),*/
	    'test_rel_comment' => array(
			'pod' => 'comment',
		    'id' => 0,
		    'field_index' => 'comment_date',
		    'field_id' => 'comment_ID',
		    'field_author' => 'user_id',
			'data' => array(
				'comment_author' => 'Related comment',
				'comment_author_email' => 'related@comment.com',
				'comment_author_url' => 'http://comment.com',
				'comment_content' => '%s',
				'comment_post_ID' => 1,
				'comment_type' => 'comment',
			    'post_status' => 'publish',
			    'comment_date' => '2014-11-11 00:00:00'
			)
		),
	    '%s' => array(
			'pod' => '%s',
		    'id' => 0,
		    'field_index' => '',
		    'field_id' => '',
		    'field_author' => false,
			'data' => array(
				'index' => 'Testing %s',
				'test_text_field' => 'Testing %s'
			)
		)
	);

	/**
	 * @beforeClass
	 */
	public static function _initialize_config() {

		$api = pods_api();

		$test_pod = 1;

		// Loop through supported types and fields and setup test builds
		foreach ( self::$supported_types as $pod_type => $options ) {
			$main_pod = array(
				'name'    => '',
				'type'    => $pod_type,
				'storage' => '',
				'fields'  => array(),
				// Hack for 2.x
				// @todo Remove for 3.x
				'options' => array()
			);

			if ( 'pod' == $pod_type ) {
				$main_pod[ 'options' ][ 'pod_index' ] = 'name';
			}

			$objects = array();

			if ( ! isset( $options[ 'object' ] ) ) {
				$objects[ ] = $pod_type;
			} else {
				$objects = (array) $options[ 'object' ];
			}

			foreach ( $objects as $object ) {
				$object_pod = $main_pod;

				$pod_object = $object;

				if ( '%d' == $pod_object ) {
					$pod_object = 'test_' . substr( $pod_type, 0, 4 );

					$object_pod[ 'object' ] = '';
				} else {
					$object_pod[ 'object' ] = $pod_object;
				}

				$object_pod[ 'name' ] = $pod_object;

				foreach ( $options[ 'storage' ] as $storage_type ) {
					$pod = $object_pod;

					if ( empty( $pod[ 'object' ] ) ) {
						$pod[ 'name' ] = $pod_object . '_' . substr( $storage_type, 0, 3 ) . '_' . $test_pod;
					}

					$pod[ 'storage' ] = $storage_type;

					if ( 'none' != $storage_type ) {
						$pod[ 'fields' ] = self::$supported_fields;

						if ( isset( $options[ 'fields' ] ) ) {
							foreach ( $options[ 'fields' ] as $field ) {
								if ( isset( $field[ 'id' ] ) ) {
									unset( $field[ 'id' ] );
								}

								// Hack for 2.x
								// @todo Remove for 3.x
								$field[ 'options' ] = $field;

								$pod[ 'fields' ][ ] = $field;
							}
						}
					}

					if ( ! isset( self::$builds[ $pod_type ] ) ) {
						self::$builds[ $pod_type ] = array();
					}

					if ( ! isset( self::$builds[ $pod_type ][ $object ] ) ) {
						self::$builds[ $pod_type ][ $object ] = array();
					}

					if ( isset( self::$builds[ $pod_type ][ $object ][ $storage_type ] ) ) {
						continue;
					}

					self::$builds[ $pod_type ][ $object ][ $storage_type ]             = $pod;
					self::$builds[ $pod_type ][ $object ][ $storage_type ][ 'fields' ] = array();

					foreach ( $pod[ 'fields' ] as $field ) {
						self::$builds[ $pod_type ][ $object ][ $storage_type ][ 'fields' ][ $field[ 'name' ] ] = $field;
					}

					$id = $api->save_pod( $pod );

					//$this->assertGreaterThan( 0, $id, 'Pod not added' );

					self::$builds[ $pod_type ][ $object ][ $storage_type ][ 'id' ] = $id;

					$test_pod++;

					// @todo Figure out how to split test meta/table for existing objects
					// If object set, we can't create multiple Pods to test, use first storage provided
					if ( ! empty( $pod[ 'object' ] ) ) {
						break;
					}
				}
			}
		}

		global $pods_init;

        $pods_init->setup_content_types( true );

	}

	/**
	 * @beforeClass
	 */
	public static function _initialize_data() {

		// Insert initial data
		$related_author = 0;

		$new_related_items = array();

		foreach ( self::$related_items as $item => $item_data ) {
			if ( ! empty( $item_data[ 'is_build' ] ) ) {
				continue;
			}
			elseif ( '%s' != $item ) {
				foreach ( $item_data[ 'data' ] as $k => $v ) {
					$item_data[ 'data' ][ $k ] = sprintf( $v, wp_generate_password( 4, false ) );
				}

				$p = pods( $item_data[ 'pod' ] );

				$item_data[ 'field_id' ] = $p->pod_data[ 'field_id' ];
				$item_data[ 'field_index' ] = $p->pod_data[ 'field_index' ];

				/*$this->assertTrue( is_object( $p ), 'Pod not object' );
				$this->assertTrue( $p->valid(), 'Pod object not valid' );
				$this->assertInstanceOf( 'Pods', $p, 'Pod object not a Pod' );

				$this->assertEquals( $item_data[ 'field_index' ], $item_data[ 'field_index' ], 'Pod field index does not match configuration' );
				$this->assertEquals( $item_data[ 'field_id' ], $item_data[ 'field_id' ], 'Pod field id does not match configuration' );*/

				$id = $p->add( $item_data[ 'data' ] );

				//$this->assertGreaterThan( 0, $id, 'Item not added' );

				if ( ! empty( $item_data[ 'limit' ] ) ) {
					$ids = array();

					$ids[] = $id;

					for ( $x = 1; $x < $item_data[ 'limit' ]; $x++ ) {
						$sub_item_data = $item_data[ 'data' ];
						$sub_item_data[ $item_data[ 'field_index' ] ] .= ' (' . $x . ')';

						$id = $p->add( $sub_item_data );

						//$this->assertGreaterThan( 0, $id, 'Item not added' );

						$ids[] = $id;
					}

					$id = $ids;
				}
				elseif ( 'test_rel_user' == $item ) {
					$related_author = $id;

					// Init user data to other items for saving
					foreach ( self::$related_items as $r_item => $r_item_data ) {
						if ( empty( $r_item_data[ 'is_build' ] ) ) {
							self::$related_items[ $r_item ][ 'data' ][ $item ] = $id;
						}
					}
				}

				$item_data[ 'id' ] = $id;

				self::$related_items[ $item ] = $related_items[ $item ] = $item_data;
			}
			else {
				foreach ( self::$builds as $pod_type => $objects ) {
					foreach ( $objects as $object => $storage_types ) {
						foreach ( $storage_types as $storage_type => $pod ) {
							$pod_item_data = $item_data;

							if ( ! empty( self::$supported_types[ $pod_type ][ 'data' ] ) ) {
								foreach ( self::$supported_types[ $pod_type ][ 'data' ] as $k => $v ) {
									$pod_item_data[ 'data' ][ $k ] = $v;
								}
							}

							foreach ( $pod_item_data[ 'data' ] as $k => $v ) {
								$pod_item_data[ 'data' ][ $k ] = sprintf( $v, wp_generate_password( 4, false ) );
							}

							foreach ( self::$supported_fields as $field ) {
								if ( 'pick' == $field[ 'type' ] && isset( self::$related_items[ $field[ 'name' ] ] ) ) {
									$pod_item_data[ 'data' ][ $field[ 'name' ] ] = self::$related_items[ $field[ 'name' ] ][ 'id' ];
								}
							}

							$pod_item_data[ 'pod' ] = $pod[ 'name' ];

							$p = pods( $pod_item_data[ 'pod' ] );

							$pod_item_data[ 'field_index' ] = $p->pod_data[ 'field_index' ];
						    $pod_item_data[ 'field_id' ] = $p->pod_data[ 'field_id' ];

							/*$this->assertTrue( is_object( $p ), 'Pod not object' );
							$this->assertTrue( $p->valid(), 'Pod object not valid' );
							$this->assertInstanceOf( 'Pods', $p, 'Pod object not a Pod' );*/

							$index = $pod_item_data[ 'data' ][ 'index' ];

							unset( $pod_item_data[ 'data' ][ 'index' ] );

							if ( empty( $pod_item_data[ 'data' ][ $pod_item_data[ 'field_index' ] ] ) ) {
								$pod_item_data[ 'data' ][ $pod_item_data[ 'field_index' ] ] = $index;
							}

							if ( in_array( $pod_type, array( 'post_type', 'media' ) ) ) {
								$pod_item_data[ 'data' ][ 'post_author' ] = $related_author;
							}
							elseif ( 'comment' == $pod_type ) {
								$pod_item_data[ 'data' ][ 'user_id' ] = $related_author;
							}
							elseif ( 'pod' == $pod_type ) {
								$pod_item_data[ 'data' ][ 'author' ] = $related_author;
							}

							$id = $p->add( $pod_item_data[ 'data' ] );

							//$this->assertGreaterThan( 0, $id, 'Item not added' );

							$new_related_items[ $pod_item_data[ 'pod' ] ] = $pod_item_data;
							$new_related_items[ $pod_item_data[ 'pod' ] ][ 'id' ] = $id;
							$new_related_items[ $pod_item_data[ 'pod' ] ][ 'is_build' ] = true;
						}
					}
				}
			}
		}

		foreach ( $new_related_items as $item => $item_data ) {
			self::$related_items[ $item ] = $item_data;
		}

	}

	/**
	 *
	 */
	public function test_find_traversal_post_type() {

		$this->_find_traversal_type( 'post_type' );

	}

	/**
	 *
	 */
	public function test_find_traversal_taxonomy() {

		$this->_find_traversal_type( 'taxonomy' );

	}

	/**
	 *
	 */
	public function test_find_traversal_user() {

		$this->_find_traversal_type( 'user' );

	}

	/**
	 *
	 */
	public function test_find_traversal_media() {

		$this->_find_traversal_type( 'media' );

	}

	/**
	 *
	 */
	public function test_find_traversal_comment() {

		$this->_find_traversal_type( 'comment' );

	}

	/**
	 *
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
		/*add_filter( 'pods_error_die', '__return_false' );

		$wpdb->suppress_errors( true );
		$wpdb->hide_errors();*/

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

						$prefix = $field[ 'name' ] . '.';

						$check_value = $related_data[ 'id' ];
						$check_index = $related_data[ 'data' ][ $related_data[ 'field_index' ] ];

						if ( isset( $field[ 'pick_format_type' ] ) && 'multi' == $field[ 'pick_format_type' ] ) {
							$check_value = (array) $check_value;
							$check_value = current( $check_value );
						}

						$params[ 'where' ][] = $prefix . $related_data[ 'field_id' ] . ' = ' . (int) $check_value;
						$params[ 'where' ][] = $prefix . $related_data[ 'field_index' ] . ' = "' . pods_sanitize( $check_index ) . '"';

						if ( empty( $field[ 'pick_val' ] ) ) {
							$field[ 'pick_val' ] = $field[ 'pick_object' ];
						}

						// Related pod traversal
						if ( isset( self::$builds[ $field[ 'pick_object' ] ] ) && isset( self::$builds[ $field[ 'pick_object' ] ][ $field[ 'pick_val' ] ] ) && isset( self::$related_items[ $field[ 'pick_val' ] ] ) ) {
							$related_pod_data = self::$related_items[ $field[ 'pick_val' ] ];

							$related_pod = current( self::$builds[ $field[ 'pick_object' ] ][ $field[ 'pick_val' ] ] );
							$related_pod_type = $related_pod[ 'type' ];
							$related_pod_storage_type = $related_pod[ 'storage' ];

							// @todo Get recursive traversal tested
							continue;

							foreach ( $related_pod[ 'fields' ] as $related_pod_field ) {
								$related_prefix = $related_suffix = '';

								if ( in_array( $related_pod_field[ 'type' ], array( 'pick', 'taxonomy', 'avatar' ) ) ) {
									if ( $field[ 'name' ] == $related_pod_field[ 'name' ] && ! isset( $related_data[ 'data' ][ $related_pod_field[ 'name' ] ] ) ) {
										continue;
									}

									$related_prefix = $related_pod_field[ 'name' ] . '.';

									$check_value = $related_pod_data[ 'id' ];
									$check_index = $related_pod_data[ 'data' ][ $related_pod_data[ 'field_index' ] ];

									if ( isset( $related_pod_field[ 'pick_format_type' ] ) && 'multi' == $related_pod_field[ 'pick_format_type' ] ) {
										$check_value = (array) $check_value;
										$check_value = current( $check_value );
									}

									$params[ 'where' ][] = $prefix . $related_prefix . $related_pod_data[ 'field_id' ] . ' = ' . (int) $check_value;
									$params[ 'where' ][] = $prefix . $related_prefix . $related_pod_data[ 'field_index' ] . ' = "' . pods_sanitize( $check_index ) . '"';
								}
								elseif ( 'none' != $related_pod_storage_type ) {
									if ( 'pod' == $related_pod_type ) {
										$related_prefix = 't.';
									} elseif ( 'table' == $related_pod_storage_type ) {
										$related_prefix = 'd.';
									} elseif ( 'meta' == $related_pod_storage_type ) {
										$related_suffix = '.meta_value';
									}

									$check_related_value = $related_pod_data[ 'data' ][ $related_pod_field[ 'name' ] ];

									$params[ 'where' ][] = $prefix . $related_prefix . $related_pod_field[ 'name' ] . $related_suffix . ' = "' . pods_sanitize( $check_related_value ) . '"';
								}
							}
						}
					}
					elseif ( 'none' != $storage_type ) {
						if ( 'pod' == $pod_type ) {
							$prefix = 't.';
						}
						elseif ( 'table' == $storage_type ) {
							$prefix = 'd.';
						}
						elseif ( 'meta' == $storage_type ) {
							$suffix = '.meta_value';
						}

						$check_value = $data[ 'data' ][ $field[ 'name' ] ];

						$params[ 'where' ][] = $prefix . $field[ 'name' ] . $suffix . ' = "' . pods_sanitize( $check_value ) . '"';
					}
				}

				$prefix = 't.';
				$suffix = '';

				$check_value = $data[ 'id' ];
				$check_index = $data[ 'data' ][ $data[ 'field_index' ] ];

				$params[ 'where' ][] = $prefix . $data[ 'field_id' ] . $suffix . ' = ' . (int) $check_value;
				$params[ 'where' ][] = $prefix . $data[ 'field_index' ] . $suffix . ' = "' . pods_sanitize( $check_index ) . '"';

				$p->find( $params );

				$this->assertEquals( 1, $p->total(), 'Total not correct for ' . $pod[ 'name' ] . ': ' . $p->sql );
				$this->assertEquals( 1, $p->total_found(), 'Total found not correct for ' . $pod[ 'name' ] );

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
	 *
	 */
	public function test_field_traversal_post_type() {

		$this->_field_traversal_type( 'post_type' );

	}

	/**
	 *
	 */
	public function test_field_traversal_taxonomy() {

		$this->_field_traversal_type( 'taxonomy' );

	}

	/**
	 *
	 */
	public function test_field_traversal_user() {

		$this->_field_traversal_type( 'user' );

	}

	/**
	 *
	 */
	public function test_field_traversal_media() {

		$this->_field_traversal_type( 'media' );

	}

	/**
	 *
	 */
	public function test_field_traversal_comment() {

		$this->_field_traversal_type( 'comment' );

	}

	/**
	 *
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

						$traverse_id = $field[ 'name' ] . '.' . $related_data[ 'field_id' ];
						$traverse_index = $field[ 'name' ] . '.' . $related_data[ 'field_index' ];

						$this->assertEquals( $check_value, $p->field( $traverse_id ), 'Related Item field value not as expected for ' . $traverse_id );
						$this->assertEquals( $check_display_value, $p->display( $traverse_id ), 'Related Item field display value not as expected for ' . $traverse_id );

						$this->assertEquals( $check_index, $p->field( $traverse_index ), 'Related Item index field value not as expected for ' . $traverse_index );
						$this->assertEquals( $check_display_index, $p->display( $traverse_index ), 'Related Item index field display value not as expected for ' . $traverse_index );

						if ( 'meta' == $storage_type ) {
							$check_value = array_map( 'absint', (array) $check_value );
							$check_index = (array) $check_index;

							//var_dump( array( 'check' => $check_value, 'metadata' => array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $traverse_id ) ), 'metadata_full' => array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $field[ 'name' ] ) ) ) );

							$this->assertEquals( $check_value, array_map( 'absint', get_metadata( $metadata_type, $data[ 'id' ], $traverse_id ) ), 'Related Item field meta value not as expected for ' . $traverse_id );
							$this->assertEquals( current( $check_value ), (int) get_metadata( $metadata_type, $data[ 'id' ], $traverse_id, true ), 'Related Item field single meta value not as expected for ' . $traverse_id );

							$this->assertEquals( $check_index, get_metadata( $metadata_type, $data[ 'id' ], $traverse_index ), 'Related Item index field meta value not as expected for ' . $traverse_index );
							$this->assertEquals( current( $check_index ), get_metadata( $metadata_type, $data[ 'id' ], $traverse_index, true ), 'Related Item index field single meta value not as expected for ' . $traverse_index );
						}

						// @todo Add recursive traversal (1 level)
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
}
