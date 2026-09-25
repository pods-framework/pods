<?php

namespace Pods_Unit_Tests\Pods;

use Pods\Admin\Config\Pod as Pod_Config;
use Pods\Whatsit\Pod;
use Pods_Unit_Tests\Pods_WhatsitTestCase;
use PodsAdmin;
use PodsAPI;
use PodsInit;

/**
 * @group  pods
 * @covers PodsAPI
 */
class AdminTest extends Pods_WhatsitTestCase {

	/**
	 * @var PodsAPI
	 */
	protected $api;

	/**
	 * @var PodsAdmin
	 */
	protected $admin;

	public function setUp(): void {
		parent::setUp();

		$this->api   = pods_api();
		$this->admin = new PodsAdmin();

		$post_types = [
			'ext-post-type-meta',
			'ext-post-type-table',
		];

		$taxonomies = [
			'ext-taxonomy-meta',
			'ext-taxonomy-table',
		];

		$existing_post_type_cached = (array) pods_static_cache_get( 'post_type', 'PodsInit/existing_content_types' );
		$existing_taxonomy_cached  = (array) pods_static_cache_get( 'taxonomy', 'PodsInit/existing_content_types' );

		foreach ( $post_types as $post_type ) {
			register_post_type( $post_type );

			$existing_post_type_cached[] = $post_type;
		}

		foreach ( $taxonomies as $taxonomy ) {
			register_taxonomy( $taxonomy, 'post' );

			$existing_taxonomy_cached[] = $taxonomy;
		}

		pods_static_cache_set( 'post_type', $existing_post_type_cached, 'PodsInit/existing_content_types' );
		pods_static_cache_set( 'taxonomy', $existing_taxonomy_cached, 'PodsInit/existing_content_types' );
	}

	/**
	 *
	 */
	public function tearDown(): void {
		$this->api   = null;
		$this->admin = null;

		$post_types = [
			'ext-post-type-meta',
			'ext-post-type-table',
		];

		$taxonomies = [
			'ext-taxonomy-meta',
			'ext-taxonomy-table',
		];

		$existing_post_type_cached = (array) pods_static_cache_get( 'post_type', 'PodsInit/existing_content_types' );
		$existing_taxonomy_cached  = (array) pods_static_cache_get( 'taxonomy', 'PodsInit/existing_content_types' );

		foreach ( $post_types as $post_type ) {
			unregister_post_type( $post_type );

			$found = array_search( $post_type, $existing_post_type_cached, true );

			if ( false !== $found ) {
				unset( $existing_post_type_cached[ $found ] );
			}
		}

		foreach ( $taxonomies as $taxonomy ) {
			unregister_taxonomy( $taxonomy );

			$found = array_search( $taxonomy, $existing_taxonomy_cached, true );

			if ( false !== $found ) {
				unset( $existing_taxonomy_cached[ $found ] );
			}
		}

		pods_static_cache_set( 'post_type', $existing_post_type_cached, 'PodsInit/existing_content_types' );
		pods_static_cache_set( 'taxonomy', $existing_taxonomy_cached, 'PodsInit/existing_content_types' );

		parent::tearDown();
	}

	/**
	 * Provide get_* methods to be tested.
	 *
	 * @return array
	 */
	public function provider_global_config_checks() {
		$groups_for_group = [
			'basic',
			'advanced',
		];

		$groups_for_field = [
			'basic',
			'additional-field-text',
			'additional-field-website',
			'additional-field-phone',
			'additional-field-email',
			'additional-field-password',
			'additional-field-paragraph',
			'additional-field-wysiwyg',
			'additional-field-code',
			'additional-field-datetime',
			'additional-field-date',
			'additional-field-time',
			'additional-field-number',
			'additional-field-currency',
			'additional-field-file',
			'additional-field-oembed',
			'additional-field-pick',
			'additional-field-boolean',
			'additional-field-color',
			'additional-field-heading',
			'additional-field-html',
			'repeatable',
			'advanced',
			'conditional-logic',
		];

		$groups_for_field_with_rest = array_merge( $groups_for_field, [ 'rest' ] );

		$groups_for_field_for_user = [
			'basic',
			'additional-field-text',
			'additional-field-website',
			'additional-field-phone',
			'additional-field-email',
			'additional-field-password',
			'additional-field-paragraph',
			'additional-field-wysiwyg',
			'additional-field-code',
			'additional-field-datetime',
			'additional-field-date',
			'additional-field-time',
			'additional-field-number',
			'additional-field-currency',
			'additional-field-file',
			'additional-field-avatar',
			'additional-field-oembed',
			'additional-field-pick',
			'additional-field-boolean',
			'additional-field-color',
			'additional-field-heading',
			'additional-field-html',
			'repeatable',
			'advanced',
			'conditional-logic',
			'rest',
		];

		$groups_for_field_on_act = [
			'basic',
			'additional-field-text',
			'additional-field-website',
			'additional-field-phone',
			'additional-field-email',
			'additional-field-password',
			'additional-field-paragraph',
			'additional-field-wysiwyg',
			'additional-field-code',
			'additional-field-datetime',
			'additional-field-date',
			'additional-field-time',
			'additional-field-number',
			'additional-field-currency',
			'additional-field-file',
			'additional-field-oembed',
			'additional-field-pick',
			'additional-field-boolean',
			'additional-field-color',
			'additional-field-slug',
			'additional-field-heading',
			'additional-field-html',
			'repeatable',
			'advanced',
			'conditional-logic',
		];

		yield 'new post type with meta storage' => [
			[
				'pod_args' => [
					'name'    => 'new-post-type-meta',
					'type'    => 'post_type',
					'storage' => 'meta',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'labels',
							'admin-ui',
							'connections',
							'advanced',
							'access-rights',
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_with_rest,
					],
				],
			],
		];

		yield 'new post type with table storage' => [
			[
				'pod_args' => [
					'name'    => 'new-post-type-table',
					'type'    => 'post_type',
					'storage' => 'table',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'labels',
							'admin-ui',
							'connections',
							'advanced',
							'access-rights',
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_with_rest,
					],
				],
			],
		];

		yield 'extended post type with meta storage' => [
			[
				'pod_args' => [
					'name'    => 'ext-post-type-meta',
					'type'    => 'post_type',
					'storage' => 'meta',
					'object'  => 'ext-post-type-meta',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'access-rights',
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_with_rest,
					],
				],
			],
		];

		yield 'extended post type with table storage' => [
			[
				'pod_args' => [
					'name'    => 'ext-post-type-table',
					'type'    => 'post_type',
					'storage' => 'table',
					'object'  => 'ext-post-type-table',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'access-rights',
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_with_rest,
					],
				],
			],
		];

		yield 'new taxonomy with meta storage' => [
			[
				'pod_args' => [
					'name'    => 'new-taxonomy-meta',
					'type'    => 'taxonomy',
					'storage' => 'meta',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'labels',
							'admin-ui',
							'connections',
							'advanced',
							'access-rights',
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_with_rest,
					],
				],
			],
		];

		yield 'new taxonomy with table storage' => [
			[
				'pod_args' => [
					'name'    => 'new-taxonomy-table',
					'type'    => 'taxonomy',
					'storage' => 'table',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'labels',
							'admin-ui',
							'connections',
							'advanced',
							'access-rights',
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_with_rest,
					],
				],
			],
		];

		yield 'extended taxonomy with meta storage' => [
			[
				'pod_args' => [
					'name'    => 'ext-taxonomy-meta',
					'type'    => 'taxonomy',
					'storage' => 'meta',
					'object'  => 'ext-taxonomy-meta',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'access-rights',
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_with_rest,
					],
				],
			],
		];

		yield 'extended taxonomy with table storage' => [
			[
				'pod_args' => [
					'name'    => 'ext-taxonomy-table',
					'type'    => 'taxonomy',
					'storage' => 'table',
					'object'  => 'ext-taxonomy-table',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'access-rights',
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_with_rest,
					],
				],
			],
		];

		yield 'extended media with meta storage' => [
			[
				'pod_args' => [
					'name'    => 'media',
					'type'    => 'media',
					'storage' => 'meta',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'access-rights',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_with_rest,
					],
				],
			],
		];

		yield 'extended media with table storage' => [
			[
				'pod_args' => [
					'name'    => 'media',
					'type'    => 'media',
					'storage' => 'table',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'access-rights',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_with_rest,
					],
				],
			],
		];

		yield 'extended comment with meta storage' => [
			[
				'pod_args' => [
					'name'    => 'comment',
					'type'    => 'comment',
					'storage' => 'meta',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'access-rights',
							'pods-pfat',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field,
					],
				],
			],
		];

		yield 'extended comment with table storage' => [
			[
				'pod_args' => [
					'name'    => 'comment',
					'type'    => 'comment',
					'storage' => 'table',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'access-rights',
							'pods-pfat',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field,
					],
				],
			],
		];

		yield 'extended user with meta storage' => [
			[
				'pod_args' => [
					'name'    => 'user',
					'type'    => 'user',
					'storage' => 'meta',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'access-rights',
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_for_user,
					],
				],
			],
		];

		yield 'extended user with table storage' => [
			[
				'pod_args' => [
					'name'    => 'user',
					'type'    => 'user',
					'storage' => 'table',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'access-rights',
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_for_user,
					],
				],
			],
		];

		yield 'settings' => [
			[
				'pod_args' => [
					'name' => 'settings-pod',
					'type' => 'settings',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'labels',
							'admin-ui',
							'access-rights',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field,
					],
				],
			],
		];

		yield 'advanced content type' => [
			[
				'pod_args' => [
					'name' => 'act-pod',
					'type' => 'pod',
				],
				'config'   => [
					'pod'   => [
						'groups' => [
							'labels',
							'admin-ui',
							'advanced',
							'access-rights',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => $groups_for_field_on_act,
					],
				],
			],
		];
	}

	/**
	 * @dataProvider provider_global_config_checks
	 */
	public function test_admin_get_global_config( $test ) {
		$check_params = [
			'name' => $test['pod_args']['name'],
		];

		if ( $pod = $this->api->load_pod( $check_params ) ) {
			$this->api->delete_pod( $check_params );
		}

		$this->api->save_pod( $test['pod_args'] );

		$pod = $this->api->load_pod( $check_params );

		$this->assertInstanceOf( Pod::class, $pod );

		$config = $this->admin->get_global_config( $pod );

		$this->assertCount( 4, $config );
		$this->assertArrayHasKey( 'pod', $config );
		$this->assertArrayHasKey( 'group', $config );
		$this->assertArrayHasKey( 'field', $config );

		$pod_names   = wp_list_pluck( $config['pod']['groups'], 'name' );
		$group_names = wp_list_pluck( $config['group']['groups'], 'name' );
		$field_names = wp_list_pluck( $config['field']['groups'], 'name' );

		$this->assertEquals( '_pods_pod', $config['pod']['name'] );
		$this->assertEquals( $test['config']['pod']['groups'], $pod_names );
		$this->assertEquals( '_pods_group', $config['group']['name'] );
		$this->assertEquals( $test['config']['group']['groups'], $group_names );
		$this->assertEquals( '_pods_field', $config['field']['name'] );
		$this->assertEquals( $test['config']['field']['groups'], $field_names );
	}

	/**
	 * Verifies the new "hide_meta_box" option is exposed in the Admin UI tab
	 * for taxonomy pods, and that when enabled it results in meta_box_cb=false
	 * being passed to register_taxonomy().
	 *
	 * @covers \PodsInit::setup_content_types
	 */
	public function test_taxonomy_hide_meta_box_option() {
		$taxonomy_disabled = 'test-hide-meta-box-tax-off';
		$taxonomy_enabled  = 'test-hide-meta-box-tax-on';
		$taxonomies        = [ $taxonomy_disabled, $taxonomy_enabled ];

		// Clean up if a previous run left the pods behind.
		foreach ( $taxonomies as $existing_name ) {
			$existing = $this->api->load_pod( [ 'name' => $existing_name ] );
			if ( $existing ) {
				$this->api->delete_pod( [ 'name' => $existing_name ] );
			}
		}

		try {
			// 1. Verify the option is exposed in the admin-ui config for taxonomy pods.
			$pod_id = $this->api->save_pod( [
				'name'    => $taxonomy_disabled,
				'type'    => 'taxonomy',
				'storage' => 'meta',
			] );
			$this->assertNotEmpty( $pod_id, 'Taxonomy pod was not created.' );

			$pod = $this->api->load_pod( [ 'name' => $taxonomy_disabled ] );
			$this->assertInstanceOf( Pod::class, $pod );

			$pod_config = new Pod_Config();
			$tabs       = $pod_config->get_tabs( $pod );
			$fields     = $pod_config->get_fields( $pod, $tabs );

			$this->assertArrayHasKey( 'admin-ui', $fields, 'admin-ui tab missing from config.' );
			$this->assertArrayHasKey( 'hide_meta_box', $fields['admin-ui'], 'hide_meta_box option missing from admin-ui tab.' );
			$this->assertSame( 'boolean', $fields['admin-ui']['hide_meta_box']['type'] );
			$this->assertFalse( $fields['admin-ui']['hide_meta_box']['default'] );

			// 2. With hide_meta_box disabled (default), the registered taxonomy must NOT carry meta_box_cb.
			pods_init()->setup_content_types( true );

			$registered = get_taxonomy( $taxonomy_disabled );
			$this->assertNotNull( $registered, 'Taxonomy was not registered.' );
			// WP_Taxonomy::set_props() replaces a null meta_box_cb with the default
			// hierarchy-appropriate callback, so it is never null after registration.
			// What matters is that it is not false, i.e. the meta box is still shown.
			$this->assertNotFalse( $registered->meta_box_cb, 'meta_box_cb should not be disabled when hide_meta_box is off.' );

			// 3. A separate pod saved with hide_meta_box enabled must register with meta_box_cb=false.
			$pod_id = $this->api->save_pod( [
				'name'          => $taxonomy_enabled,
				'type'          => 'taxonomy',
				'storage'       => 'meta',
				'hide_meta_box' => true,
			] );
			$this->assertNotEmpty( $pod_id, 'Enabled taxonomy pod was not created.' );

			pods_init()->setup_content_types( true );

			$registered = get_taxonomy( $taxonomy_enabled );
			$this->assertNotNull( $registered, 'Taxonomy was not registered with hide_meta_box enabled.' );
			$this->assertFalse( $registered->meta_box_cb, 'meta_box_cb should be false when hide_meta_box is enabled.' );
		} finally {
			// Clean up: delete pods, unregister taxonomies, and clear the
			// registration guard so later tests start with clean state.
			foreach ( $taxonomies as $taxonomy_name ) {
				$this->api->delete_pod( [ 'name' => $taxonomy_name ] );

				if ( taxonomy_exists( $taxonomy_name ) ) {
					unregister_taxonomy( $taxonomy_name );
				}
			}

			$registered = isset( PodsInit::$content_types_registered['taxonomies'] )
				? PodsInit::$content_types_registered['taxonomies']
				: [];

			$registered = array_values( array_diff( $registered, $taxonomies ) );

			PodsInit::$content_types_registered['taxonomies'] = $registered;
		}
	}
}
