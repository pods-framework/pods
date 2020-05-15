<?php


/**
 * Run schema updates on plugin activation or updates
 *
 * @since 4.9.4
 *
 */
class Tribe__Updater {

	protected $version_option  = 'schema-version';
	protected $reset_version   = '3.9'; // when a reset() is called, go to this version
	protected $current_version = 0;

	public $capabilities;

	/**
	 * Tribe__Updater constructor.
	 *
	 * @since 4.9.4
	 *
	 * @param int $current_version the current version number of a plugin
	 */
	public function __construct( $current_version ) {
		$this->current_version = $current_version;
	}

	/**
	 * We've had problems with the notoptions and
	 * alloptions caches getting out of sync with the DB,
	 * forcing an eternal update cycle
	 *
	 * @since 4.9.4
	 *
	 */
	protected function clear_option_caches() {
		wp_cache_delete( 'notoptions', 'options' );
		wp_cache_delete( 'alloptions', 'options' );
	}

	/**
	 * Run Updates for a Plugin
	 *
	 * @since 4.9.4
	 *
	 */
	public function do_updates() {
		$this->clear_option_caches();
		$updates = $this->get_update_callbacks();
		uksort( $updates, 'version_compare' );

		try {
			foreach ( $updates as $version => $callback ) {

				if ( ! $this->is_new_install() && version_compare( $version, $this->current_version, '<=' ) && $this->is_version_in_db_less_than( $version ) ) {
					call_user_func( $callback );
				}
			}

			foreach ( $this->get_constant_update_callbacks() as $callback ) {
				call_user_func( $callback );
			}

			$this->update_version_option( $this->current_version );
		} catch ( Exception $e ) {
			// fail silently, but it should try again next time
		}
	}

	/**
	 * Update Version Number for a Plugin
	 *
	 * @since 4.9.4
	 *
	 * @param int $new_version the current version number of a plugin
	 */
	public function update_version_option( $new_version ) {
		Tribe__Settings_Manager::set_option( $this->version_option, $new_version );
	}

	/**
	 * Returns an array of callbacks with version strings as keys.
	 * Any key higher than the version recorded in the DB
	 * and lower than $this->current_version will have its
	 * callback called.
	 *
	 * @since 4.9.4
	 *
	 * @return array
	 */
	public function get_update_callbacks() {
		return array();
	}

	/**
	 * Returns an array of callbacks that should be called
	 * every time the version is updated
	 *
	 * @since 4.9.4
	 *
	 * @return array
	 */
	public function get_constant_update_callbacks() {
		return array(
			array( $this, 'flush_rewrites' ),
		);
	}

	/**
	 * Get version from Tribe Settings for the Plugin
	 *
	 * @since 4.9.4
	 *
	 * @return mixed the version number of the plugin saved in the options
	 */
	public function get_version_from_db() {
		return Tribe__Settings_Manager::get_option( $this->version_option );
	}

	/**
	 * Returns true if the version in the DB is less than the provided version
	 *
	 * @since 4.9.4
	 *
	 * @return boolean
	 */
	public function is_version_in_db_less_than( $version ) {
		$version_in_db = $this->get_version_from_db();

		return ( version_compare( $version, $version_in_db ) > 0 );
	}

	/**
	 * Returns true if this is a new install
	 *
	 * @since 4.9.4
	 *
	 * @return boolean
	 */
	public function is_new_install() {
		$version_in_db = $this->get_version_from_db();

		return empty( $version_in_db );
	}

	/**
	 * Returns true if an update is required
	 *
	 * @since 4.9.4
	 *
	 * @return boolean
	 */
	public function update_required() {
		return $this->is_version_in_db_less_than( $this->current_version );
	}

	/**
	 * Flush Rewrite rules
	 *
	 * @since 4.9.4
	 *
	 */
	public function flush_rewrites() {
		// run after 'init' to ensure that all CPTs are registered
		add_action( 'wp_loaded', 'flush_rewrite_rules' );
	}

	/**
	 * Reset update flags. All updates past $this->reset_version will
	 * run again on the next page load
	 *
	 * @since 4.9.4
	 *
	 */
	public function reset() {
		$this->update_version_option( $this->reset_version );
	}

}
