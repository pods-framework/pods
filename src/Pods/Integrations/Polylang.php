<?php

namespace Pods\Integrations;

/**
 * Class Polylang
 *
 * @since 2.8.0
 */
class Polylang {

	/**
	 * Add the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function hook() {
		add_action( 'pods_meta_init', [ $this, 'pods_meta_init' ] );
		add_action( 'pll_get_post_types', [ $this, 'pll_get_post_types' ], 10, 2 );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function unhook() {
		remove_action( 'pods_meta_init', [ $this, 'pods_meta_init' ] );
		remove_action( 'pll_get_post_types', [ $this, 'pll_get_post_types' ], 10 );
	}

	/**
	 * @param \PodsMeta $pods_meta
	 *
	 * @since 2.8.0
	 */
	public function pods_meta_init( $pods_meta ) {

		if ( function_exists( 'pll_current_language' ) ) {
			add_action( 'init', array( $pods_meta, 'cache_pods' ), 101, 0 );
		}
	}

	/**
	 * Add Pods templates to possible i18n enabled post-types (polylang settings).
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Moved from PodsI18n class.
	 *
	 * @param  array $post_types
	 * @param  bool  $is_settings
	 *
	 * @return array  mixed
	 */
	public function pll_get_post_types( $post_types, $is_settings = false ) {

		if ( $is_settings ) {
			$post_types['_pods_template'] = '_pods_template';
		}

		return $post_types;
}
