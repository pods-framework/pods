<?php

namespace Pods_Unit_Tests;

use Pods;

/**
 * Class Pods_TraversalTestCase
 */
class Pods_TraversalTestCase extends Pods_UnitTestCase {

	/**
	 * @var array
	 */
	public static $config = array();

	/**
	 * @var array
	 */
	public static $related_fields = array();

	/**
	 * @var array
	 */
	public static $related_data = array();

	/**
	 * @var array
	 */
	public static $data = array();

	/**
	 * Demo image used for attachments.
	 *
	 * This is currently set to the Pods logo Gravatar on Scott's Gravatar account.
	 *
	 * @var string
	 * @static
	 */
	public static $sample_image = 'http://en.gravatar.com/userimage/3291122/ee00b7f0e64c35bc6463829c7c9e1f22.png?size=200';

	/**
	 * A collection of pre-built pod objects
	 *
	 * @var    array
	 * @static
	 */
	public static $builds = array(/*
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

	/**
	 *
	 */
	public function setUp() {
		static $counter = 0;

		if ( static::$db_reset_teardown ) {
			parent::setUp();

			$load_config = filter_var( getenv( 'PODS_LOAD_DATA' ), FILTER_VALIDATE_BOOLEAN );

			if ( $load_config ) {
				$counter ++;

				if ( 100 <= $counter ) {
					$counter = 0;

					usleep( 500000 );
				}
			}
		}
	}

	/**
	 * Initialize Pods test config.
	 *
	 * @throws \Exception
	 */
	public static function _initialize() {
		self::_initialize_pods_config();
		self::_initialize_pods_related_data();
		self::_initialize_pods_data();
	}

	/**
	 * Initialize Pods test config.
	 *
	 * @throws \Exception
	 */
	public static function _initialize_pods_config() {
		// Setup non-Pod taxonomy
		$args = array(
			'hierarchical' => true,
			'show_ui'      => true,
			'query_var'    => true,
			'labels'       => array(
				'name'          => 'Non-Pod Taxonomy',
				'singular_name' => 'Non-Pod Taxonomy',
			),
		);

		register_taxonomy( 'test_non_pod_ct', array( 'post', 'page', 'nav_menu_item' ), $args );

		$config = file_get_contents( dirname( __DIR__ ) . '/_data/traversal-config.json' );
		$config = json_decode( $config, true );

		$pods_api = pods_api();

		$related_fields = array(
			'test_non_pod_ct' => array(
				'object_type' => 'taxonomy',
				'object'      => 'test_non_pod_ct',
			),
		);

		foreach ( $config as $k => $pod ) {
			if ( in_array( $pod['name'], pods_reserved_keywords(), true ) ) {
				/*
				 * Extending objects when using reserved keywords.
				 *
				 * This will then accept `post`, `page` etc. as Pods object names.
				 */
				$pod['create_extend'] = 'extend';
			}

			$pods_api->save_pod( $pod );

			$pod = $pods_api->load_pod( $pod['name'] );

			if ( ! empty( $pod['fields'] ) ) {
				foreach ( $pod['fields'] as $field ) {
					if ( isset( $related_fields[ $field['name'] ] ) ) {
						continue;
					}

					/** @var Pods\Whatsit\Field $field */
					if ( 'pick' === $field['type'] ) {
						$related_info = array(
							'id'          => 0,
							'_index'      => '',
							'_items'      => array(),
							'object_type' => $field->get_related_object_type(),
							'object'      => $field->get_related_object_name(),
						);

						$related_fields[ $field['name'] ] = $related_info;
					} elseif ( in_array( $field['type'], array( 'file', 'avatar' ), true ) ) {
						$related_info = array(
							'id'          => 0,
							'_index'      => '',
							'_items'      => array(),
							'object_type' => 'media',
							'object'      => 'media',
						);

						$related_fields[ $field['name'] ] = $related_info;
					}
				}
			}

			self::index_build( $pod );

			self::$config[ $pod['name'] ] = $pod;
		}

		// Setup the content types if needed.
		if ( did_action( 'init' ) ) {
			$pods_init = pods_init();
			$pods_init->setup_content_types( true );
		}

		self::$related_fields = $related_fields;
	}

	/**
	 * Initialize Pods test related data.
	 *
	 * @param int $depth
	 *
	 * @throws \Exception
	 */
	public static function _initialize_pods_related_data( $depth = 0 ) {
		$data = self::$related_data;

		$import_data = file_get_contents( dirname( __DIR__ ) . '/_data/traversal-related-data.json' );
		$import_data = json_decode( $import_data, true );

		if ( empty( $data ) ) {
			$data = $import_data;
		}

		$save_data = array();

		foreach ( $data as $field_name => $item ) {
			$is_multi = 0;

			// Save multiple values for test_rel_pages
			if ( 'test_rel_pages2' === $field_name ) {
				$is_multi = 1;

				$field_name = 'test_rel_pages';
			}

			if ( ! isset( self::$related_fields[ $field_name ] ) ) {
				codecept_debug( 'Field invalid: ' . $field_name );

				continue;
			}

			$pod_name = self::$related_fields[ $field_name ]['object'];

			// This is not a pod, use wp_insert_term instead.
			if ( 'test_non_pod_ct' === $pod_name ) {
				if ( 0 < $depth ) {
					// Save data for later.
					$save_data[ $field_name ] = $item;

					continue;
				}

				$term = wp_insert_term( $item['name'], 'test_non_pod_ct', array( 'description' => $item['description'] ) );

				if ( is_wp_error( $term ) ) {
					throw new \Exception( sprintf( 'The term could not be inserted: %s', $term->get_error_message() ) );
				}

				$item['id']           = $term['term_id'];
				$item['term_id']      = $item['id'];
				$item['_index']       = $item['name'];
				$item['_items']       = array( $item );
				$item['_field_id']    = 'term_id';
				$item['_field_index'] = 'name';

				// Save data for later.
				$save_data[ $field_name ] = $item;

				// Save related field info for later.
				self::$related_fields[ $field_name ]['id']                         = $term['term_id'];
				self::$related_fields[ $field_name ]['_index']                     = $item['name'];
				self::$related_fields[ $field_name ]['_items'][ $term['term_id'] ] = $item;

				continue;
			}

			if ( ( 'test_rel_media' === $pod_name || 'media' === $pod_name ) && empty( self::$related_fields[ $field_name ]['id'] ) ) {
				// Get and store sample image for use later
				$sample_image_id = pods_attachment_import( self::$sample_image );

				if ( empty( $sample_image_id ) ) {
					throw new \Exception( sprintf( 'The sample image may have been deleted! Sample image: %s', self::$sample_image ) );
				}

				self::$related_fields[ $field_name ]['id'] = $sample_image_id;
			}

			$pod = pods( $pod_name, null, false );

			if ( ! $pod || ! $pod->valid() ) {
				codecept_debug( 'Pod invalid: ' . $pod_name . ' ' . __LINE__ );

				continue;
			}

			$fields = $pod->fields();

			// Handle related IDs.
			foreach ( $fields as $field ) {
				if ( ! isset( self::$related_fields[ $field['name'] ] ) ) {
					continue;
				}

				if ( ! isset( self::$related_fields[ $field['name'] ]['id'] ) ) {
					if ( isset( $item[ $field['name'] ] ) ) {
						// Don't save for now.
						unset( $item[ $field['name'] ] );
					}

					continue;
				}

				$item[ $field['name'] ] = self::$related_fields[ $field['name'] ]['id'];

				foreach ( self::$related_fields[ $field_name ]['_items'] as $related_key => $related_item ) {
					$related_item[ $field['name'] ] = $item[ $field['name'] ];

					self::$related_fields[ $field_name ]['_items'][ $related_key ] = $related_item;
				}
			}

			if ( 0 === $depth ) {
				if ( 0 < $is_multi ) {
					$id = $pod->add( $item );

					self::$related_fields[ $field_name ]['id']     = self::$related_fields[ $field_name ]['id'];
					self::$related_fields[ $field_name ]['_index'] = self::$related_fields[ $field_name ]['_index'];

					if ( ! is_array( self::$related_fields[ $field_name ]['id'] ) ) {
						if ( ! is_array( self::$related_fields[ $field_name ]['_index'] ) ) {
							self::$related_fields[ $field_name ]['_index'] = array(
								self::$related_fields[ $field_name ]['id'] => self::$related_fields[ $field_name ]['_index'],
							);
						}

						self::$related_fields[ $field_name ]['id'] = array(
							self::$related_fields[ $field_name ]['id'] => self::$related_fields[ $field_name ]['id'],
						);
					}

					self::$related_fields[ $field_name ]['id'][ $id ]     = $id;
					self::$related_fields[ $field_name ]['_index'][ $id ] = $item[ $pod->pod_data['field_index'] ];

					self::$related_fields[ $field_name ]['_items'][ $id ] = $item;
				} else {
					if ( ! empty( self::$related_fields[ $field_name ]['id'] ) ) {
						$id = $pod->save( $item, null, self::$related_fields[ $field_name ]['id'] );
					} else {
						$id = $pod->add( $item );
					}

					self::$related_fields[ $field_name ]['id'] = $id;

					self::$related_fields[ $field_name ]['_index'] = $item[ $pod->pod_data['field_index'] ];

					self::$related_fields[ $field_name ]['_items'][ $id ] = $item;
				}

				$item['id']     = self::$related_fields[ $field_name ]['id'];
				$item['_index'] = self::$related_fields[ $field_name ]['_index'];

				$item['_items'] = self::$related_fields[ $field_name ]['_items'];
			} else {
				$id    = self::$related_fields[ $field_name ]['id'];
				$index = self::$related_fields[ $field_name ]['_index'];

				// Get the last item in the array.
				if ( is_array( $id ) ) {
					// Handle ID.
					$id = array_pop( $id );

					$item['id'] = $id;

					// Handle index.
					$index = array_pop( $index );

					$item[ $pod->pod_data['field_index'] ] = $index;
				}

				$pod->save( $item, null, $id );

				if ( $is_multi ) {
					$item['id']     = self::$related_fields[ $field_name ]['id'];
					$item['_index'] = self::$related_fields[ $field_name ]['_index'];

					if ( ! is_array( $item['id'] ) ) {
						if ( ! is_array( $item['_index'] ) ) {
							$item['_index'] = array(
								$item['id'] => $item['_index'],
							);
						}

						$item['id'] = array(
							$item['id'] => $item['id'],
						);
					}

					$item['id'][ $id ]     = $id;
					$item['_index'][ $id ] = $index;
				} else {
					$item['id']     = self::$related_fields[ $field_name ]['id'];
					$item['_index'] = self::$related_fields[ $field_name ]['_index'];
				}

				$item['_items'] = self::$related_fields[ $field_name ]['_items'];

				$item[ $pod->pod_data['field_id'] ] = $id;
			}

			$item['_field_id']    = $pod->pod_data['field_id'];
			$item['_field_index'] = $pod->pod_data['field_index'];

			$save_data[ $field_name ] = $item;
		}

		self::$related_data = $save_data;

		if ( 0 === $depth ) {
			self::_initialize_pods_related_data( ++ $depth );
		}
	}

	/**
	 * Initialize Pods test data.
	 *
	 * @param int $depth
	 *
	 * @throws \Exception
	 */
	public static function _initialize_pods_data( $depth = 0 ) {
		$data = self::$data;

		$import_data = file_get_contents( dirname( __DIR__ ) . '/_data/traversal-data.json' );
		$import_data = json_decode( $import_data, true );

		if ( empty( $data ) ) {
			$data = $import_data;
		}

		$save_data = array();

		foreach ( $data as $pod_name => $item ) {
			$pod = pods( $pod_name, null, false );

			if ( ! $pod || ! $pod->valid() ) {
				codecept_debug( 'Pod invalid: ' . $pod_name . ' ' . __LINE__ );

				continue;
			}

			$fields = $pod->fields();

			// Handle related IDs.
			foreach ( $fields as $field ) {
				if ( ! isset( self::$related_data[ $field['name'] ] ) ) {
					continue;
				}

				if ( ! isset( self::$related_data[ $field['name'] ]['id'] ) ) {
					if ( isset( $item[ $field['name'] ] ) ) {
						// Don't save for now.
						unset( $item[ $field['name'] ] );
					}

					continue;
				}

				$item[ $field['name'] ] = self::$related_data[ $field['name'] ]['id'];
			}

			if ( 0 === $depth ) {
				$item['id'] = $pod->add( $item );

				$item[ $pod->pod_data['field_id'] ] = $item['id'];

				$item['_field_id']    = $pod->pod_data['field_id'];
				$item['_field_index'] = $pod->pod_data['field_index'];
			} else {
				$id = $item['id'];

				$pod->save( $item, null, $id );
			}

			$save_data[ $pod_name ] = $item;
		}

		self::$data = $save_data;

		if ( 0 === $depth ) {
			self::_initialize_pods_data( ++ $depth );
		}
	}

	/**
	 * Index build for testing.
	 *
	 * @param Pods\Whatsit $pod Whatsit object.
	 */
	public static function index_build( $pod ) {
		$pod_type     = $pod['type'];
		$object       = $pod['name'];
		$storage_type = $pod['storage_type'];

		if ( ! isset( self::$builds[ $pod_type ] ) ) {
			self::$builds[ $pod_type ] = array();
		}

		if ( ! isset( self::$builds[ $pod_type ][ $object ] ) ) {
			self::$builds[ $pod_type ][ $object ] = array();
		}

		self::$builds[ $pod_type ][ $object ][ $storage_type ] = $pod;
	}

	/**
	 * Data provider for all data to pass into Traversal test methods
	 * for all variations and combinations to be covered.
	 */
	public function data_provider_base() {
		$load_config = filter_var( getenv( 'PODS_LOAD_DATA' ), FILTER_VALIDATE_BOOLEAN );

		// Bail but don't throw skip notices.
		if ( ! $load_config ) {
			return array( array( 1, 2, 3 ) );
		}

		$data_base = array();

		foreach ( self::$builds as $pod_type => $objects ) {
			foreach ( $objects as $object => $storage_types ) {
				foreach ( $storage_types as $storage_type => $pod ) {
					$pod_name = $pod['name'];

					$data_base[] = array(
						build_query( compact( array( 'pod_type', 'storage_type', 'pod_name' ) ) ),
						array(
							'pod_type'     => $pod_type,
							'storage_type' => $storage_type,
							'pod'          => $pod,
						),
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
					foreach ( $pod['fields'] as $field_name => $field ) {
						if ( ( empty( $field['pick_val'] ) && empty( $field['pick_object'] ) ) || ! in_array( $field['type'], array(
								'pick',
								'taxonomy',
								'avatar',
								'author',
							), true ) ) {
							continue;
						}

						if ( empty( $field['pick_val'] ) ) {
							$field['pick_val'] = $field['pick_object'];
						}

						$pod_name   = $pod['name'];
						$field_name = $field['name'];

						$data[] = array(
							build_query( compact( array( 'pod_type', 'storage_type', 'pod_name', 'field_name' ) ) ),
							array(
								'pod_type'     => $pod_type,
								'storage_type' => $storage_type,
								'pod'          => $pod,
								'field'        => $field,
							),
						);
					}//end foreach

					// Non-Pod Taxonomy field
					if ( 'post_type' === $pod_type && isset( $pod['object_fields']['test_non_pod_ct'] ) ) {
						$field = $pod['object_fields']['test_non_pod_ct'];

						$pod_name   = $pod['name'];
						$field_name = $field['name'];

						$data[] = array(
							build_query( compact( array( 'pod_type', 'storage_type', 'pod_name', 'field_name' ) ) ),
							array(
								'pod_type'     => $pod_type,
								'storage_type' => $storage_type,
								'pod'          => $pod,
								'field'        => $field,
							),
						);
					}
				}//end foreach
			}//end foreach
		}//end foreach

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
					foreach ( $pod['fields'] as $field_name => $field ) {
						if ( ( empty( $field['pick_val'] ) && empty( $field['pick_object'] ) ) || ! in_array( $field['type'], array(
								'pick',
								'taxonomy',
								'avatar',
								'author',
							), true ) ) {
							continue;
						}

						if ( empty( $field['pick_val'] ) ) {
							$field['pick_val'] = $field['pick_object'];
						}

						// Related pod traversal
						if ( ! isset( self::$builds[ $field['pick_object'] ][ $field['pick_val'] ], self::$related_data[ $field['name'] ] ) ) {
							continue;
						}

						$related_pod = current( self::$builds[ $field['pick_object'] ][ $field['pick_val'] ] );

						foreach ( $related_pod['fields'] as $related_pod_field ) {
							if ( empty( $related_pod_field['pick_val'] ) && ! empty( $related_pod_field['pick_object'] ) ) {
								$related_pod_field['pick_val'] = $related_pod_field['pick_object'];
							}

							$pod_name               = $pod['name'];
							$field_name             = $field['name'];
							$related_pod_name       = $related_pod['name'];
							$related_pod_field_name = $related_pod_field['name'];

							$data_deep[] = array(
								build_query( compact( array(
									'pod_type',
									'storage_type',
									'pod_name',
									'field_name',
									'related_pod_name',
									'related_pod_field_name',
								) ) ),
								array(
									'pod_type'          => $pod_type,
									'storage_type'      => $storage_type,
									'pod'               => $pod,
									'field'             => $field,
									'related_pod'       => $related_pod,
									'related_pod_field' => $related_pod_field,
								),
							);

							continue;
							// To be continued..
							// @todo Handle one more level deeper
							if ( ! in_array( $related_pod_field['type'], array(
								'pick',
								'taxonomy',
								'avatar',
								'author',
							), true ) ) {
								continue;
							}

							if ( empty( $related_pod_field['pick_val'] ) ) {
								if ( empty( $related_pod_field['pick_object'] ) ) {
									continue;
								}

								$related_pod_field['pick_val'] = $related_pod_field['pick_object'];
							}

							if ( ! isset( self::$builds[ $related_pod_field['pick_object'] ][ $related_pod_field['pick_val'] ], self::$related_data[ $related_pod_field['name'] ] ) ) {
								continue;
							}

							// Related pod traversal
							$sub_related_pod = current( self::$builds[ $related_pod_field['pick_object'] ][ $related_pod_field['pick_val'] ] );

							foreach ( $sub_related_pod['fields'] as $sub_related_pod_field ) {
								if ( empty( $sub_related_pod_field['pick_val'] ) ) {
									if ( empty( $sub_related_pod_field['pick_object'] ) ) {
										continue;
									}

									$sub_related_pod_field['pick_val'] = $sub_related_pod_field['pick_object'];
								}

								$sub_related_pod_name       = $sub_related_pod['name'];
								$sub_related_pod_field_name = $sub_related_pod_field['name'];

								$data_deep[] = array(
									build_query( compact( array(
										'pod_type',
										'storage_type',
										'pod_name',
										'field_name',
										'related_pod_name',
										'related_pod_field_name',
										'sub_related_pod_name',
										'sub_related_pod_field_name',
									) ) ),
									array(
										'pod_type'              => $pod_type,
										'storage_type'          => $storage_type,
										'pod'                   => $pod,
										'field'                 => $field,
										'related_pod'           => $related_pod,
										'related_pod_field'     => $related_pod_field,
										'sub_related_pod'       => $sub_related_pod,
										'sub_related_pod_field' => $sub_related_pod_field,
									),
								);
							}//end foreach
						}//end foreach
					}//end foreach
				}//end foreach
			}//end foreach
		}//end foreach

		return $data_deep;
	}
}
