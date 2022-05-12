<?php

namespace Pods_Unit_Tests\Pods;

use Pods\Static_Cache;
use Pods\Whatsit\Pod;
use Pods_Unit_Tests\Pods_WhatsitTestCase;
use PodsAdmin;
use PodsAPI;

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

		$static_cache = pods_container( Static_Cache::class );

		$existing_post_type_cached = (array) $static_cache->get( 'post_type', 'PodsInit/existing_content_types' );
		$existing_taxonomy_cached  = (array) $static_cache->get( 'taxonomy', 'PodsInit/existing_content_types' );

		foreach ( $post_types as $post_type ) {
			register_post_type( $post_type );

			$existing_post_type_cached[] = $post_type;
		}

		foreach ( $taxonomies as $taxonomy ) {
			register_taxonomy( $taxonomy, 'post' );

			$existing_taxonomy_cached[] = $taxonomy;
		}

		$static_cache->set( 'post_type', $existing_post_type_cached, 'PodsInit/existing_content_types' );
		$static_cache->set( 'taxonomy', $existing_taxonomy_cached, 'PodsInit/existing_content_types' );
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

		$static_cache = pods_container( Static_Cache::class );

		$existing_post_type_cached = (array) $static_cache->get( 'post_type', 'PodsInit/existing_content_types' );
		$existing_taxonomy_cached  = (array) $static_cache->get( 'taxonomy', 'PodsInit/existing_content_types' );

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

		$static_cache->set( 'post_type', $existing_post_type_cached, 'PodsInit/existing_content_types' );
		$static_cache->set( 'taxonomy', $existing_taxonomy_cached, 'PodsInit/existing_content_types' );

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
			'advanced',
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
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => array_merge( $groups_for_field, [ 'rest' ] ),
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
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => array_merge( $groups_for_field, [ 'rest' ] ),
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
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => array_merge( $groups_for_field, [ 'rest' ] ),
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
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => array_merge( $groups_for_field, [ 'rest' ] ),
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
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => array_merge( $groups_for_field, [ 'rest' ] ),
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
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => array_merge( $groups_for_field, [ 'rest' ] ),
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
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => array_merge( $groups_for_field, [ 'rest' ] ),
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
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => array_merge( $groups_for_field, [ 'rest' ] ),
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
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => array_merge( $groups_for_field, [ 'rest' ] ),
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
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => array_merge( $groups_for_field, [ 'rest' ] ),
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
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => [
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
							'advanced',
							'rest',
						],
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
							'pods-pfat',
							'rest-api',
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => [
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
							'advanced',
							'rest',
						],
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
						],
					],
					'group' => [
						'groups' => $groups_for_group,
					],
					'field' => [
						'groups' => [
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
							'advanced',
						],
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
}
