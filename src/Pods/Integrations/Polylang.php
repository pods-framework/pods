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
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function unhook() {
		remove_action( 'pods_meta_init', [ $this, 'pods_meta_init' ] );
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
}
