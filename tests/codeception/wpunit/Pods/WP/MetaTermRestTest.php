<?php

namespace Pods_Unit_Tests\Pods\WP;

use Pods\WP\Meta;
use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsMeta;

/**
 * Regression coverage for term meta not being exposed to the REST API.
 *
 * Integrations that read Pods data over REST (Yoast SEO among them) could never
 * see taxonomy fields, because the REST eligibility check required the Pod to be
 * a post type.
 *
 * @group  pods-meta
 * @group  pods-issue-7288
 * @covers \Pods\WP\Meta
 */
class MetaTermRestTest extends Pods_UnitTestCase {

	/**
	 * @var string
	 */
	protected $taxonomy = 'test_rest_tax';

	/**
	 * @var string
	 */
	protected $field_name = 'tax_rest_field';

	/**
	 * @var Meta|null
	 */
	protected $meta;

	public function setUp(): void {
		parent::setUp();

		// Make the dynamic-feature gates deterministic rather than depending on
		// whatever the global defaults happen to be.
		add_filter( 'pods_access_can_use_dynamic_features', '__return_true' );
		add_filter( 'pods_access_can_use_dynamic_feature', '__return_true' );

		pods_update_setting( 'register_meta_integration', 1 );

		$api = pods_api();

		$pod_id = $api->save_pod( [
			'type'                    => 'taxonomy',
			'name'                    => $this->taxonomy,
			'label'                   => 'Test REST Taxonomy',
			'rest_enable'             => 1,
			'rest_api_field_location' => 'meta',
		] );

		$api->save_field( [
			'pod_id'    => $pod_id,
			'name'      => $this->field_name,
			'label'     => 'Tax REST Field',
			'type'      => 'text',
			'rest_read' => 1,
		] );

		pods_meta()->cache_pods( true );
	}

	public function tearDown(): void {
		if ( $this->meta instanceof Meta ) {
			$this->meta->unregister_meta();
			$this->meta = null;
		}

		pods_update_setting( 'register_meta_integration', 0 );

		remove_filter( 'pods_access_can_use_dynamic_features', '__return_true' );
		remove_filter( 'pods_access_can_use_dynamic_feature', '__return_true' );

		parent::tearDown();
	}

	/**
	 * The taxonomy Pod must actually reach the registration loop.
	 */
	public function test_taxonomy_pod_is_cached_for_meta_registration() {
		$names = array_map(
			static function ( $pod ) {
				return $pod->get_name();
			},
			PodsMeta::$taxonomies
		);

		$this->assertContains( $this->taxonomy, $names );
	}

	/**
	 * Term meta must be registered with show_in_rest enabled.
	 *
	 * Before the fix the field was registered with show_in_rest => false, because
	 * the eligibility check was hard-gated on 'post_type' === $pod_type.
	 */
	public function test_taxonomy_field_is_registered_with_show_in_rest() {
		$this->meta = new Meta();
		$this->meta->register_meta();

		$registered = get_registered_meta_keys( 'term', $this->taxonomy );

		$this->assertArrayHasKey(
			$this->field_name,
			$registered,
			'Taxonomy field should be registered as term meta.'
		);

		$this->assertTrue(
			$registered[ $this->field_name ]['show_in_rest'],
			'Term meta must be exposed to REST so integrations such as Yoast SEO can read it.'
		);
	}

	/**
	 * Post type behaviour must be unchanged: without custom-fields support the
	 * field stays out of REST.
	 */
	public function test_post_type_without_custom_fields_support_is_still_excluded() {
		$api = pods_api();

		$pod_id = $api->save_pod( [
			'type'                    => 'post_type',
			'name'                    => 'test_rest_cpt',
			'label'                   => 'Test REST CPT',
			'rest_enable'             => 1,
			'rest_api_field_location' => 'meta',
			'supports_custom_fields'  => 0,
		] );

		$api->save_field( [
			'pod_id'    => $pod_id,
			'name'      => 'cpt_rest_field',
			'label'     => 'CPT REST Field',
			'type'      => 'text',
			'rest_read' => 1,
		] );

		pods_meta()->cache_pods( true );

		$this->meta = new Meta();
		$this->meta->register_meta();

		$registered = get_registered_meta_keys( 'post', 'test_rest_cpt' );

		if ( isset( $registered['cpt_rest_field'] ) ) {
			$this->assertFalse(
				$registered['cpt_rest_field']['show_in_rest'],
				'Post types without custom-fields support must not gain REST exposure.'
			);
		} else {
			$this->assertArrayNotHasKey( 'cpt_rest_field', $registered );
		}
	}
}
