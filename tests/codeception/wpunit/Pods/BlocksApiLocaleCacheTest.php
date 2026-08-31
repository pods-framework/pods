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
	public function test_js_blocks_cache_is_keyed_per_locale() {
		$api = pods_container( 'pods.blocks' );

		$locale = determine_locale();

		// Seed the current locale's cache with a recognisable payload.
		pods_transient_set( 'pods_blocks_js_' . $locale, [ 'sentinel' => [ 'title' => 'from-' . $locale ] ], 60 );

		$this->assertArrayHasKey(
			'sentinel',
			$api->get_js_blocks(),
			'The current locale should be served from its own cache entry.'
		);

		// A different locale must not see it. Before the fix a single global key meant
		// every locale shared one payload, so this returned the seeded value.
		$switched = switch_to_locale( 'fr_FR' );

		if ( ! $switched ) {
			$this->markTestSkipped( 'Could not switch locale in this environment.' );
		}

		$other = $api->get_js_blocks();

		restore_previous_locale();

		$this->assertArrayNotHasKey(
			'sentinel',
			$other,
			'A different locale must not be served cached block configs from another locale.'
		);
	}
}
