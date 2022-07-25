<?php

abstract class Tribe__Abstract_Deactivation {
	protected $network = false;

	public function __construct( $network ) {
		$this->network = (bool) $network;
	}

	/**
	 * Tell WordPress to flush rewrite rules.
	 * Since our post types are already registered,
	 * we delete the option and let WP regenerate it
	 * on the next page load.
	 */
	protected function flush_rewrite_rules() {
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Deactivate the plugin. This should not remove data.
	 * It's job is to remove run-time traces of the plugin.
	 *
	 * @return void
	 */
	public function deactivate() {
		if ( is_multisite() && $this->network ) {
			$this->multisite_deactivate();
		} else {
			$this->blog_deactivate();
		}
	}

	/**
	 * Run the deactivation script on every blog for a multisite install
	 *
	 * @return void
	 */
	protected function multisite_deactivate() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$site = get_current_site();
		$blog_ids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id=%d", $site->id ) );
		$large = wp_is_large_network();
		foreach ( $blog_ids as $blog ) {
			tribe_set_time_limit( 30 );
			switch_to_blog( $blog );
			$large ? $this->short_blog_deactivate() : $this->blog_deactivate();
			restore_current_blog();
		}
	}

	/**
	 * The deactivation routine for a single blog
	 *
	 * @return void
	 */
	abstract protected function blog_deactivate();


	/**
	 * An abridged version that is less DB intensive for use on large networks.
	 *
	 * @see wp_is_large_network() and the 'wp_is_large_network' filter
	 *
	 * @return void
	 */
	protected function short_blog_deactivate() {
		$this->blog_deactivate();
	}
}
