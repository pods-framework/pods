<?php

namespace Pods_Unit_Tests\Meta;

/**
 * Class Test_Each
 *
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-shortcodes
 * @group   pods-shortcodes-each
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
		'comment'   => 'comment_meta',
		'user'      => 'user_meta',
	);

	protected static $pod_ids = array();

	public static function wpSetUpBeforeClass() {
		self::create_pods();
		self::load_pods();
	}

	public static function wpTearDownAfterClass() {
		foreach ( self::$pod_names as $type => $name ) {
			// Delete all pod objects as well.
			$delete_all = true;
			pods_api()->delete_pod( $name, false, $delete_all );
		}
	}

	public static function create_pods() {

		foreach ( self::$pod_names as $type => $name ) {

			self::$pod_ids[ $type ] = pods_api()->save_pod(
				array(
					'storage' => 'meta',
					'type'    => $type,
					'name'    => $name,
				)
			);

			$pod_id = self::$pod_ids[ $type ];

			$params = array(
				'pod'              => $name,
				'pod_id'           => $pod_id,
				'name'             => 'related_single',
				'type'             => 'pick',
				'pick_object'      => $type,
				'pick_val'         => $name,
				'pick_format_type' => 'single',
			);

			pods_api()->save_field( $params );

			$params = array(
				'pod'              => $name,
				'pod_id'           => $pod_id,
				'name'             => 'related_multi',
				'type'             => 'pick',
				'pick_object'      => $type,
				'pick_val'         => $name,
				'pick_format_type' => 'multi',
			);

			pods_api()->save_field( $params );
		}
		pods_init()->setup_content_types( true );
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
						$object['post_status'] = 'published';
						$object['post_type']   = $name;

						$id = wp_insert_post( $object );
						break;
					case 'taxonomy':
						$id = wp_insert_term( $title, $name );
						$id_key = 'term_ID';
						break;
					case 'user':
						$login = str_replace( '-', '', sanitize_title_with_dashes( $title ) );
						$object['user_login'] = $login;
						$object['user_pass'] = $login;
						$object['user_email'] = $login . '@test.local';

						$id = wp_insert_user( $object );
						break;
					case 'comment':
						$object['comment_content']  = $title;
						$object['comment_approved'] = 1;

						$id = wp_insert_comment( $object );
						$id_key = 'comment_ID';
						break;
				}

				$objects[ $key ] = pods_v( $id_key, $id, $id );
			}

			self::$pod_ids[ $type ] = $objects;

			foreach ( $objects as $key => $id ) {

				$update_meta = 'update_' . $name;

				switch ( $key ) {
					case 0:
						// Only empty fields.
						break;
					case 1:
						call_user_func( $update_meta, $id, 'related_single', $objects[ 2 ] );
						// Single multi relationship.
						call_user_func( $update_meta, $id, 'related_multi', $objects[ 2 ] );
						break;
					case 2:
						call_user_func( $update_meta, $id, 'related_single', $objects[ 1 ] );
						// Multi relationship.
						call_user_func( $update_meta, $id, 'related_multi', array( $objects[ 0 ], $objects[ 1 ] ) );
						break;
				}
			}
		}
	}

	public function test_get_metadata() {

		add_filter( 'pods_pods_field_related_output_type', array( $this, 'filter_output_type_ids' ) );

		foreach ( self::$pod_ids as $type => $ids ) {

			$get_meta = 'get_' . self::$pod_names[ $type ];
			foreach ( $ids as $key => $id ) {

				$single        = call_user_func( $get_meta, $id, 'related_single', false );
				$single_single = call_user_func( $get_meta, $id, 'related_single', true );
				$multi         = call_user_func( $get_meta, $id, 'related_multi', false );
				$multi_single  = call_user_func( $get_meta, $id, 'related_multi', true );

				// Add a bit of context when a assertion has failed.
				$message = "Method: `{$get_meta}()`:";

				switch ( $key ) {
					case 0:

						// Single param false
						$this->assertEquals( array(), $single, $message );
						$this->assertEquals( array(), $multi, $message );

						// Single param true
						$this->assertEquals( '', $single_single, $message );
						$this->assertEquals( '', $multi_single, $message );

						break;
					case 1:
						// Single related to: 2
						// Multi related to: 2

						$single_rel = self::$pod_ids[ $type ][ 2 ];
						$multi_rel  = self::$pod_ids[ $type ][ 2 ];

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

						$single_rel = self::$pod_ids[ $type ][ 1 ];
						$multi_rel  = array(
							self::$pod_ids[ $type ][ 0 ],
							self::$pod_ids[ $type ][ 1 ]
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

	public function filter_output_type_ids() {
		return 'ids';
	}
}
