<?php

namespace Pods_Unit_Tests\Functions;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * Class Metadata
 *
 * @group pods
 * @group pods-functions
 * @group pods-functions-metadata
 */
class MetadataTest extends Pods_UnitTestCase {

	/**
	 * Pod names. Compatible names with meta getters: get_{$name}
	 *
	 * @var string[]
	 */
	protected static $pod_names = [
		'post_type' => 'test_metadata_post',
		'taxonomy'  => 'test_metadata_term',
		// Name should be equal as WP object name.
		'media'     => 'media',
		'comment'   => 'comment',
		'user'      => 'user',
	];

	protected static $pod_ids = [];

	protected static $field_ids = [];

	protected static $obj_ids = [];

	protected static $image_ids = [];

	public function setUp() : void {
		self::create_pods();

		// Reload PodsMeta.
		$meta = pods_meta();
		$meta->core();

		// Setup the post types/taxonomies again.
		$init = pods_init();
		$init->setup_content_types( true );

		self::load_pods();

		parent::setUp();
	}

	public function tearDown() : void {
		self::$obj_ids = [];
		self::$image_ids = [];

		parent::tearDown();
	}

	public static function create_pods() {
		$api = pods_api();

		foreach ( self::$pod_names as $type => $name ) {
			$pod = $api->load_pod( $name );

			if ( $pod ) {
				self::$pod_ids[ $type ] = $pod['id'];
			} else {
				self::$pod_ids[ $type ] = $api->save_pod( [
					'storage' => 'meta',
					'type'    => $type,
					'name'    => $name,
				] );
			}

			$pod_id = self::$pod_ids[ $type ];

			if ( ! empty( self::$field_ids[ $type ] ) ) {
				continue;
			}

			self::$field_ids[ $type ] = [];

			$params = [
				'pod'    => $name,
				'pod_id' => $pod_id,
				'name'   => 'metadata_test_text',
				'type'   => 'text',
			];

			self::$field_ids[ $type ][] = $api->save_field( $params );

			$params = [
				'pod'              => $name,
				'pod_id'           => $pod_id,
				'name'             => 'metadata_test_images',
				'type'             => 'file',
				'file_format_type' => 'multi',
			];

			self::$field_ids[ $type ][] = $api->save_field( $params );

			$params = [
				'pod'              => $name,
				'pod_id'           => $pod_id,
				'name'             => 'metadata_test_related_single',
				'type'             => 'pick',
				'pick_object'      => $type,
				'pick_val'         => $name,
				'pick_format_type' => 'single',
			];

			self::$field_ids[ $type ][] = $api->save_field( $params );

			$params = [
				'pod'              => $name,
				'pod_id'           => $pod_id,
				'name'             => 'metadata_test_related_multi',
				'type'             => 'pick',
				'pick_object'      => $type,
				'pick_val'         => $name,
				'pick_format_type' => 'multi',
			];

			self::$field_ids[ $type ][] = $api->save_field( $params );

			// PR #5665
			$params = [
				'pod'    => $name,
				'pod_id' => $pod_id,
				'name'   => 'metadata_test_slash',
				'type'   => 'text',
			];

			self::$field_ids[ $type ][] = $api->save_field( $params );

			$params = [
				'pod'    => $name,
				'pod_id' => $pod_id,
				'name'   => 'metadata_test_quotes',
				'type'   => 'text',
			];

			self::$field_ids[ $type ][] = $api->save_field( $params );
		}
	}

	public static function load_pods() {
		pods_no_conflict_on( 'all' );

		self::$image_ids = [
			self::factory()->attachment->create(),
			self::factory()->attachment->create(),
		];

		foreach ( self::$pod_names as $type => $name ) {
			$objects = [
				'test 1',
				'test 2',
				'test 3',
			];

			foreach ( $objects as $key => $object ) {
				$title  = $object;
				$object = [];
				$id_key = 'ID';
				$id     = 0;
				switch ( $type ) {
					case 'post_type':
						$object['post_title']   = $title;
						$object['post_content'] = 'Test content';
						$object['post_status']  = 'publish';
						$object['post_type']    = $name;

						$id = self::factory()->post->create( $object );
						break;
					case 'taxonomy':
						$object['taxonomy'] = $name;
						$object['name']     = $title;

						$id = self::factory()->term->create( $object );
						break;
					case 'user':
						$login                = str_replace( '-', '', sanitize_title_with_dashes( $title ) );
						$object['user_login'] = $login;
						$object['user_pass']  = $login;
						$object['user_email'] = $login . '@test.local';

						$id = self::factory()->user->create( $object );
						break;
					case 'comment':
						$object['comment_content']  = $title;
						$object['comment_approved'] = 1;

						$id = self::factory()->comment->create( $object );
						break;
				}

				if ( ! is_numeric( $id ) ) {
					$id = pods_v( self::get_id_key( $type ), $id, $id );
				}
				$objects[ $key ] = $id;
			}

			self::$obj_ids[ $type ] = $objects;

			$update_meta = self::get_meta_function( 'update', $type );

			foreach ( $objects as $key => $id ) {
				$data = [];

				switch ( $key ) {
					case 0:
						$data = [
							'metadata_test_text'   => 'text',
							'metadata_test_images' => self::$image_ids,
							// No relationship fields.
							// No relationship fields.

							// PR #5665
							'metadata_test_slash'  => 'Test \backslash',
							'metadata_test_quotes' => 'Test \'quotes\' "doublequotes"',
						];
						break;
					case 1:
						$data = [
							'metadata_test_related_single' => $objects[2],
							// Single multi relationship.
							'metadata_test_related_multi'  => $objects[2],
						];
						break;
					case 2:
						$data = [
							'metadata_test_related_single' => $objects[1],
							'metadata_test_related_multi'  => [ $objects[0], $objects[1] ],
						];
						break;
				}

				// Handle update of the values.
				foreach ( $data as $meta_key => $meta_value ) {
					call_user_func( $update_meta, $id, $meta_key, $meta_value );
				}
			}
		}

		pods_no_conflict_off( 'all' );
	}

	public function test_created_pods() {

		foreach( self::$pod_names as $type => $name ) {
			$pod = pods( $name );
			$this->assertInstanceOf( \Pods::class, $pod );
			$this->assertNotFalse( $pod->valid() );
			$this->assertNotEmpty( self::$obj_ids[ $type ] );
		}
	}

	public function test_get_metadata() {
		$this->markTestSkipped( 'This test class needs to be revamped, the pod get/create/update is messed up between tests' );

		foreach ( self::$obj_ids as $type => $ids ) {
			$id_key   = self::get_id_key( $type );
			$name     = self::$pod_names[ $type ];
			$get_meta = self::get_meta_function( 'get', $type );

			foreach ( $ids as $key => $id ) {
				$single        = call_user_func( $get_meta, $id, 'metadata_test_related_single', false );
				$single_single = call_user_func( $get_meta, $id, 'metadata_test_related_single', true );
				$multi         = call_user_func( $get_meta, $id, 'metadata_test_related_multi', false );
				$multi_single  = call_user_func( $get_meta, $id, 'metadata_test_related_multi', true );

				// Pods returns object arrays by default. Fetch the ID's of these objects.
				$single        = self::convert_output_type_ids( $single, $id_key );
				$multi         = self::convert_output_type_ids( $multi, $id_key );
				$single_single = isset( $single_single[ $id_key ] ) ? $single_single[ $id_key ] : $single_single;
				$multi_single  = isset( $multi_single[ $id_key ] ) ? $multi_single[ $id_key ] : $multi_single;

				// Add a bit of context when an assertion has failed.
				$message = "Method: `{$get_meta}()`:";

				switch ( $key ) {
					case 0:

						$images = self::$image_ids;

						codecept_debug( var_export( compact( 'type', 'name', 'id' ), true ) );
						codecept_debug( var_export( call_user_func( $get_meta, $id ), true ) );

						// Single param false
						$value = call_user_func( $get_meta, $id, 'metadata_test_text', false );
						$this->assertEquals( [ 'text' ], $value, $message );

						$value = call_user_func( $get_meta, $id, 'metadata_test_images', false );
						codecept_debug( var_export( compact( 'value', 'id', 'images' ), true ) );
						$value = self::convert_output_type_ids( $value, 'ID' );
						codecept_debug( var_export( compact( 'value' ), true ) );
						$this->assertEquals( $images, $value, $message );

						$this->assertEquals( [], $single, $message );
						$this->assertEquals( [], $multi, $message );

						// Single param true
						$value = call_user_func( $get_meta, $id, 'metadata_test_text', true );
						$this->assertEquals( 'text', $value, $message );

						$value = call_user_func( $get_meta, $id, 'metadata_test_images', true );
						$value = isset( $value['ID'] ) ? $value['ID'] : $value;
						$this->assertEquals( $images[0], $value, $message );

						$this->assertEquals( '', $single_single, $message );
						$this->assertEquals( '', $multi_single, $message );

						// PR #5665
						$value = call_user_func( $get_meta, $id, 'metadata_test_slash', true );
						$this->assertEquals( 'Test backslash', $value, $message );
						$value = call_user_func( $get_meta, $id, 'metadata_test_quotes', true );
						$this->assertEquals( 'Test \'quotes\' "doublequotes"', $value, $message );

						break;
					case 1:
						// Single related to: 2
						// Multi related to: 2

						$single_rel = $ids[2];
						$multi_rel  = $ids[2];

						// Single param false
						$this->assertEquals( [ $single_rel ], $single, $message );
						$this->assertEquals( [ $multi_rel ], $multi, $message );

						// Single param true
						$this->assertEquals( $single_rel, $single_single, $message );
						$this->assertEquals( $multi_rel, $multi_single, $message );

						break;
					case 2:
						// Single related to: 1
						// Multi related to: 0 and 1

						$single_rel = $ids[1];
						$multi_rel  = [
							$ids[0],
							$ids[1],
						];

						codecept_debug( var_export( compact( 'single_rel', 'multi_rel', 'ids', 'single', 'multi' ), true ) );

						// Single param false
						$this->assertEquals( [ $single_rel ], $single, $message );
						$this->assertEquals( $multi_rel, $multi, $message );

						// Single param true
						$this->assertEquals( $single_rel, $single_single, $message );
						// Even when having multiple values, if single is true then it should return the first value.
						$this->assertEquals( $multi_rel[0], $multi_single, $message );

						break;
				}
			}
		}
	}

	public function test_get_metadata_ids() {
		$this->markTestSkipped( 'This test class needs to be revamped, the pod get/create/update is messed up between tests' );

		// Make sure we return ID's.
		add_filter( 'pods_pods_field_related_output_type', [ $this, 'filter_output_type_ids' ] );
		//remove_filter( 'get_post_metadata', array( pods_meta(), 'get_post_meta' ), 10 );

		foreach ( self::$obj_ids as $type => $ids ) {
			$name     = self::$pod_names[ $type ];
			$get_meta = self::get_meta_function( 'get', $type );

			foreach ( $ids as $key => $id ) {
				$single        = call_user_func( $get_meta, $id, 'metadata_test_related_single', false );
				$single_single = call_user_func( $get_meta, $id, 'metadata_test_related_single', true );
				$multi         = call_user_func( $get_meta, $id, 'metadata_test_related_multi', false );
				$multi_single  = call_user_func( $get_meta, $id, 'metadata_test_related_multi', true );

				// Add a bit of context when a assertion has failed.
				$message = "Method: `{$get_meta}()`:";

				switch ( $key ) {
					case 0:

						$images = self::$image_ids;

						// Single param false
						$value = call_user_func( $get_meta, $id, 'metadata_test_text', false );
						$this->assertEquals( [ 'text' ], $value, $message );

						$value = call_user_func( $get_meta, $id, 'metadata_test_images', false );
						$this->assertEquals( $images, $value, $message );

						$this->assertEquals( [], $single, $message );
						$this->assertEquals( [], $multi, $message );

						// Single param true
						$value = call_user_func( $get_meta, $id, 'metadata_test_text', true );
						$this->assertEquals( 'text', $value, $message );

						$value = call_user_func( $get_meta, $id, 'metadata_test_images', true );
						$this->assertEquals( $images[0], $value, $message );

						$this->assertEquals( '', $single_single, $message );
						$this->assertEquals( '', $multi_single, $message );

						break;
					case 1:
						// Single related to: 2
						// Multi related to: 2

						$single_rel = $ids[2];
						$multi_rel  = $ids[2];

						// Single param false
						$this->assertEquals( [ $single_rel ], $single, $message );
						$this->assertEquals( [ $multi_rel ], $multi, $message );

						// Single param true
						$this->assertEquals( $single_rel, $single_single, $message );
						$this->assertEquals( $multi_rel, $multi_single, $message );

						break;
					case 2:
						// Single related to: 1
						// Multi related to: 0 and 1

						$single_rel = $ids[1];
						$multi_rel  = [
							$ids[0],
							$ids[1],
						];

						// Single param false
						$this->assertEquals( [ $single_rel ], $single, $message );
						$this->assertEquals( $multi_rel, $multi, $message );

						// Single param true
						$this->assertEquals( $single_rel, $single_single, $message );
						// Even when having multiple values, if single is true then it should return the first value.
						$this->assertEquals( $multi_rel[0], $multi_single, $message );

						break;
				}
			}
		}

		remove_filter( 'pods_pods_field_related_output_type', [ $this, 'filter_output_type_ids' ] );
	}

	public static function get_meta_function( $action, $type ) {
		switch ( $type ) {
			case 'post_type':
			case 'media':
				$type = 'post';
				break;
			case 'taxonomy':
				$type = 'term';
				break;
		}
		return $action . '_' . $type . '_meta';
	}

	public static function get_id_key( $type ) {
		$id_key = 'ID';
		switch ( $type ) {
			case 'comment':
				$id_key = 'comment_ID';
				break;
			case 'term':
			case 'taxonomy':
				$id_key = 'term_id';
				break;
		}

		return $id_key;
	}

	public static function convert_output_type_ids( $list, $id_key ) {
		if ( empty( $list ) ) {
			return [];
		}

		$return = [];

		foreach ( $list as $key => $value ) {
			if ( ! is_array( $value ) ) {
				$return[] = $value;
			} elseif ( isset( $value[ $id_key ] ) ) {
				$return[] = $value[ $id_key ];
			}
		}

		return $return;
	}

	public function filter_output_type_ids() {
		return 'ids';
	}
}
