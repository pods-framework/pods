<?php

namespace Pods_Unit_Tests\Meta;

/**
 * Class Metadata
 *
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods_meta
 */
class Test_Metadata extends \Pods_Unit_Tests\Pods_UnitTestCase
{
	/**
	 * Pod names. Compatible names with meta getters: get_{$name}
	 *
	 * @var string[]
	 */
	protected static $pod_names = array(
		'post_type' => 'post_meta',
		'taxonomy'  => 'term_meta',
		// Name should be equal as WP object name.
		'comment'   => 'comment',
		'user'      => 'user',
	);

	protected static $obj_ids = array();

	public static function wpSetUpBeforeClass() {

		self::create_pods();
		// Reload PodsMeta.
		pods_meta()->core();

		self::load_pods();
	}

	/*public function setUp() {
		parent::setUp();

		self::create_pods();
		self::load_pods();
	}*/

	public static function wpTearDownAfterClass() {
		foreach ( self::$pod_names as $type => $name ) {
			if ( in_array( $name, array( 'comment', 'user' ) ) ){
				continue;
			}
			// Delete all pod objects as well.
			$delete_all = true;
			pods_api()->delete_pod( $name, false, $delete_all );
		}
	}

	public static function create_pods() {

		foreach ( self::$pod_names as $type => $name ) {

			pods_api()->save_pod(
				array(
					'storage' => 'meta',
					'type'    => $type,
					'name'    => $name,
				)
			);

			$params = array(
				'pod'              => $name,
				'name'             => 'text',
				'type'             => 'text',
			);
			pods_api()->save_field( $params );

			$params = array(
				'pod'              => $name,
				'name'             => 'images',
				'type'             => 'file',
				'file_format_type' => 'multi',
			);
			pods_api()->save_field( $params );

			$params = array(
				'pod'              => $name,
				'name'             => 'related_single',
				'type'             => 'pick',
				'pick_object'      => $type,
				'pick_val'         => $name,
				'pick_format_type' => 'single',
			);
			pods_api()->save_field( $params );

			$params = array(
				'pod'              => $name,
				'name'             => 'related_multi',
				'type'             => 'pick',
				'pick_object'      => $type,
				'pick_val'         => $name,
				'pick_format_type' => 'multi',
			);
			pods_api()->save_field( $params );

			// PR #5665
			$params = array(
				'pod'              => $name,
				'name'             => 'slash',
				'type'             => 'test',
			);
			pods_api()->save_field( $params );
		}
	}

	public static function load_pods() {

		foreach ( self::$pod_names as $type => $name ) {

			$objects = array(
				'test 1',
				'test 2',
				'test 3',
			);

			foreach ( $objects as $key => $object ) {
				$title  = $object;
				$object = array();
				$id_key = 'ID';
				$id     = 0;
				switch ( $type ) {
					case 'post_type':
						$object['post_title']  = $title;
						$object['post_status'] = 'publish';
						$object['post_type']   = $name;

						$id = self::factory()->post->create( $object );
						break;
					case 'taxonomy':
						$object['taxonomy'] = $name;
						$object['name']     = $title;

						$id = self::factory()->term->create( $object );
						break;
					case 'user':
						$login = str_replace( '-', '', sanitize_title_with_dashes( $title ) );
						$object['user_login'] = $login;
						$object['user_pass'] = $login;
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

			$update_meta = 'update_' . $name;
			foreach ( $objects as $key => $id ) {
				$data = array();

				switch ( $key ) {
					case 0:
						$data = array(
							'text'   => 'text',
							'images' => self::get_images(),
							// No relationship fields.

							// PR #5665
							'slash'  => 'Test \backslash',
						);
						break;
					case 1:
						$data = array(
							'related_single' => $objects[ 2 ],
							// Single multi relationship.
							'related_multi'  => $objects[ 2 ],
						);
						break;
					case 2:
						$data = array(
							'related_single' => $objects[ 1 ],
							'related_multi'  => array( $objects[ 0 ], $objects[ 1 ] ),
						);
						break;
				}

				// Pods doesn't fully handle update_metadata requests.
				/*foreach ( $data as $meta_key => $meta_value ) {
					call_user_func( $update_meta, $id, $meta_key, $meta_value );
				}*/

				$pod = pods( $name, $id );
				$pod->save( $data );
			}
		}
	}

	public static function get_images() {
		static $images = null;
		if ( ! $images ) {
			$images = array(
				self::factory()->attachment->create(),
				self::factory()->attachment->create(),
			);
		}
		return $images;
	}

	public function test_created_pods() {

		foreach( self::$pod_names as $type => $name ) {
			$pod = pods( $name );
			$this->assertNotFalse( $pod->valid() );
			$this->assertNotEmpty( self::$obj_ids[ $type ] );
		}
	}

	public function test_get_metadata() {

		foreach ( self::$obj_ids as $type => $ids ) {

			$id_key   = self::get_id_key( $type );
			$name     = self::$pod_names[ $type ];
			$get_meta = self::get_meta_function( 'get', $type );

			foreach ( $ids as $key => $id ) {

				$single        = call_user_func( $get_meta, $id, 'related_single', false );
				$single_single = call_user_func( $get_meta, $id, 'related_single', true );
				$multi         = call_user_func( $get_meta, $id, 'related_multi', false );
				$multi_single  = call_user_func( $get_meta, $id, 'related_multi', true );

				// Pods returns object arrays by default. Fetch the ID's of these objects.
				$single        = self::convert_output_type_ids( $single, $id_key );
				$multi         = self::convert_output_type_ids( $multi, $id_key );
				$single_single = isset( $single_single[ $id_key ] ) ? $single_single[ $id_key ] : $single_single;
				$multi_single  = isset( $multi_single[ $id_key ] ) ? $multi_single[ $id_key ] : $multi_single;

				// Add a bit of context when an assertion has failed.
				$message = "Method: `{$get_meta}()`:";

				switch ( $key ) {
					case 0:

						$images = self::get_images();

						// Single param false
						$value = call_user_func( $get_meta, $id, 'text', false );
						$this->assertEquals( array( 'text' ), $value, $message );

						$value = call_user_func( $get_meta, $id, 'images', false );
						$value = self::convert_output_type_ids( $value, 'ID' );
						$this->assertEquals( $images, $value, $message );

						$this->assertEquals( array(), $single, $message );
						$this->assertEquals( array(), $multi, $message );

						// Single param true
						$value = call_user_func( $get_meta, $id, 'text', true );
						$this->assertEquals( 'text', $value, $message );

						$value = call_user_func( $get_meta, $id, 'images', true );
						$value = isset( $value[ 'ID' ] ) ? $value[ 'ID' ] : $value;
						$this->assertEquals( $images[0], $value, $message );

						$this->assertEquals( '', $single_single, $message );
						$this->assertEquals( '', $multi_single, $message );

						// PR #5665
						$value = call_user_func( $get_meta, $id, 'slash', true );
						$this->assertEquals( 'Test \backslash', $value, $message );

						break;
					case 1:
						// Single related to: 2
						// Multi related to: 2

						$single_rel = $ids[ 2 ];
						$multi_rel  = $ids[ 2 ];

						// Single param false
						$this->assertEquals( array( $single_rel ), $single, $message );
						$this->assertEquals( array( $multi_rel ), $multi, $message );

						// Single param true
						$this->assertEquals( $single_rel, $single_single, $message );
						$this->assertEquals( $multi_rel, $multi_single, $message );

						break;
					case 2:
						// Single related to: 1
						// Multi related to: 0 and 1

						$single_rel = $ids[ 1 ];
						$multi_rel  = array(
							$ids[ 0 ],
							$ids[ 1 ]
						);

						// Single param false
						$this->assertEquals( array( $single_rel ), $single, $message );
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

		// Make sure we return ID's.
		add_filter( 'pods_pods_field_related_output_type', array( $this, 'filter_output_type_ids' ) );
		//remove_filter( 'get_post_metadata', array( pods_meta(), 'get_post_meta' ), 10 );

		foreach ( self::$obj_ids as $type => $ids ) {

			$name     = self::$pod_names[ $type ];
			$get_meta = self::get_meta_function( 'get', $type );

			foreach ( $ids as $key => $id ) {

				$single        = call_user_func( $get_meta, $id, 'related_single', false );
				$single_single = call_user_func( $get_meta, $id, 'related_single', true );
				$multi         = call_user_func( $get_meta, $id, 'related_multi', false );
				$multi_single  = call_user_func( $get_meta, $id, 'related_multi', true );

				// Add a bit of context when a assertion has failed.
				$message = "Method: `{$get_meta}()`:";

				switch ( $key ) {
					case 0:

						$images = self::get_images();

						// Single param false
						$value = call_user_func( $get_meta, $id, 'text', false );
						$this->assertEquals( array( 'text' ), $value, $message );

						$value = call_user_func( $get_meta, $id, 'images', false );
						$this->assertEquals( $images, $value, $message );

						$this->assertEquals( array(), $single, $message );
						$this->assertEquals( array(), $multi, $message );

						// Single param true
						$value = call_user_func( $get_meta, $id, 'text', true );
						$this->assertEquals( 'text', $value, $message );

						$value = call_user_func( $get_meta, $id, 'images', true );
						$this->assertEquals( $images[0], $value, $message );

						$this->assertEquals( '', $single_single, $message );
						$this->assertEquals( '', $multi_single, $message );

						break;
					case 1:
						// Single related to: 2
						// Multi related to: 2

						$single_rel = $ids[ 2 ];
						$multi_rel  = $ids[ 2 ];

						// Single param false
						$this->assertEquals( array( $single_rel ), $single, $message );
						$this->assertEquals( array( $multi_rel ), $multi, $message );

						// Single param true
						$this->assertEquals( $single_rel, $single_single, $message );
						$this->assertEquals( $multi_rel, $multi_single, $message );

						break;
					case 2:
						// Single related to: 1
						// Multi related to: 0 and 1

						$single_rel = $ids[ 1 ];
						$multi_rel  = array(
							$ids[ 0 ],
							$ids[ 1 ]
						);

						// Single param false
						$this->assertEquals( array( $single_rel ), $single, $message );
						$this->assertEquals( $multi_rel, $multi, $message );

						// Single param true
						$this->assertEquals( $single_rel, $single_single, $message );
						// Even when having multiple values, if single is true then it should return the first value.
						$this->assertEquals( $multi_rel[0], $multi_single, $message );

						break;
				}

			}

		}

		remove_filter( 'pods_pods_field_related_output_type', array( $this, 'filter_output_type_ids' ) );
	}

	public static function get_meta_function( $action, $type ) {
		switch ( $type ) {
			case 'post_type':
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
		foreach ( $list as $key => $value ) {
			if ( isset( $value[ $id_key ] ) ) {
				$list[ $key ] = $value[ $id_key ];
			}
		}
		return $list;
	}

	public function filter_output_type_ids() {
		return 'ids';
	}
}
