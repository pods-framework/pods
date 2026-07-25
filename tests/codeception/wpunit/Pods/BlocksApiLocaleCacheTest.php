<?php

namespace Pods_Unit_Tests\Pods;

use Pods\Blocks\API;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * Verifies that the JS-side block config cache is invalidated when the
 * active locale changes. Stale cached strings otherwise leave block
 * inserter and sidebar labels stuck in the previous language.
 *
 * See Pods Framework issue #7290.
 *
 * @group pods
 * @group pods-blocks
 * @covers API::invalidate_js_block_cache
 */
class BlocksApiLocaleCacheTest extends Pods_UnitTestCase {

	/**
	 * @var API
	 */
	protected $api;

	/**
	 * Locale-change actions that should trigger cache invalidation.
	 *
	 * @return array<string, array{string, array}>
	 */
	public function provideLocaleChangeActions(): array {
		return [
			'switch_to_locale'        => [ 'switch_to_locale',        [ 'en_US' ] ],
			'restore_previous_locale' => [ 'restore_previous_locale', [ 'de_DE' ] ],
			'change_user_locale'      => [ 'change_user_locale',      [ 1, 'en_US', 'de_DE' ] ],
			'update_option_WPLANG'    => [ 'update_option_WPLANG',    [ 'en_US', 'de_DE' ] ],
			'update_option_site_lang' => [ 'update_option_site_lang', [ 'en_US', 'de_DE' ] ],
		];
	}

	public function setUp(): void {
		parent::setUp();

		$this->api = pods_container( 'pods.blocks' );
	}

	public function tearDown(): void {
		pods_transient_clear( 'pods_blocks_js' );
		pods_static_cache_clear( true, API::class );

		parent::tearDown();
	}

	public function test_invalidate_js_block_cache_clears_pods_blocks_js_transient() {
		pods_transient_set( 'pods_blocks_js', [ 'fixture' => 'stale' ], DAY_IN_SECONDS );

		$this->api->invalidate_js_block_cache();

		$this->assertFalse( pods_transient_get( 'pods_blocks_js' ) );
	}

	public function test_invalidate_js_block_cache_clears_api_static_cache() {
		// Seed the per-class static cache with a sentinel value.
		pods_static_cache_set( 'sentinel', [ 'fixture' => 'stale' ], API::class );

		$this->api->invalidate_js_block_cache();

		$this->assertNull(
			pods_static_cache_get( 'sentinel', API::class ),
			'Static cache for Pods\Blocks\API class should be cleared.'
		);
	}

	/**
	 * @dataProvider provideLocaleChangeActions
	 */
	public function test_locale_change_action_clears_js_blocks_cache( string $action, array $args ) {
		// First ensure hooks are wired (Service_Provider::hooks() may not have
		// run in this test bootstrap). Idempotent — has_action is true on
		// each repeat.
		if ( ! has_action( $action, [ $this->api, 'invalidate_js_block_cache' ] ) ) {
			add_action( $action, [ $this->api, 'invalidate_js_block_cache' ] );
		}

		$this->assertNotFalse(
			has_action( $action, [ $this->api, 'invalidate_js_block_cache' ] ),
			"Expected invalidate_js_block_cache to be hooked to {$action}."
		);

		pods_transient_set( 'pods_blocks_js', [ 'fixture' => 'stale' ] );

		do_action_ref_array( $action, $args );

		$this->assertFalse(
			pods_transient_get( 'pods_blocks_js' ),
			"After {$action} fires, the cached JS block config must be gone."
		);
	}
}
