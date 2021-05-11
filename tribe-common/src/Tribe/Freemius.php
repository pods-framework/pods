<?php
/**
 * Class Tribe__Freemius
 *
 * @since 4.9.5
 */
class Tribe__Freemius {
	/**
	 * Store all instances of Freemius that we would use
	 *
	 * @since  4.9.5
	 *
	 * @var  array
	 */
	private $instances = [];

	/**
	 * Load the vendor files for Freemius vendor.
	 *
	 * Freemius class should only be loaded once since it will be registered as a Singleton.
	 *
	 * @since  4.9.5
	 */
	public function __construct() {
		require_once Tribe__Main::instance()->plugin_path . 'vendor/freemius/start.php';
	}

	/**
	 * Initialize the Fremius instance using their methods
	 *
	 * @since  4.9.5
	 *
	 * @param  string $slug  Slug of the plugin
	 * @param  string $id    ID in Freemius
	 * @param  string $key   Your public key in freemius
	 * @param  array  $args  Array of extra arguments to register on Freemius
	 *
	 * @return Freemius
	 */
	public function initialize( $slug, $id, $key, array $args = [] ) {
		$defaults = [
			'id' => null,
			'slug' => null,
			'type' => 'plugin',
			'public_key' => null,
			'is_premium' => false,
			'has_addons' => false,
			'has_paid_plans' => false,
		];
		$args = wp_parse_args( $args, $defaults );

		// These three values can't be overwritten
		$args['slug'] = $slug;
		$args['id'] = $id;
		$args['public_key'] = $key;

		$freemius = fs_dynamic_init( $args );

		$this->instances[ $slug ] = $freemius;

		return $freemius;
	}
}
