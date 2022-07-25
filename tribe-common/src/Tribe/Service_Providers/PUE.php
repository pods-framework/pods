<?php
namespace Tribe\Service_Providers;

use Tribe\PUE\Update_Prevention;

/**
 * Hooks and manages the implementation and loading of PUE.
 *
 * We are still moving pieces into this Service Provider, so look around
 * the `src/Tribe/PUE/` folder for other items that are not managed here
 * just yet.
 *
 * @since 4.9.12
 */
class PUE extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since  4.9.12
	 */
	public function register() {
		$this->container->singleton( Update_Prevention::class, Update_Prevention::class );

		// Setup all of WP hooks associated with PUE.
		$this->register_hooks();
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for PUE.
	 *
	 * @since 4.9.2
	 */
	protected function register_hooks() {
		add_filter( 'upgrader_source_selection', [ $this, 'filter_upgrader_source_selection' ], 15, 4 );
	}

	/**
	 * Filters the source file location for the upgrade package for the PUE Update_Prevention engine.
	 *
	 * @since  4.9.12
	 *
	 * @param string      $source        File source location.
	 * @param string      $remote_source Remote file source location.
	 * @param WP_Upgrader $upgrader      WP_Upgrader instance.
	 * @param array       $extra         Extra arguments passed to hooked filters.
	 */
	public function filter_upgrader_source_selection( $source, $remote_source, $upgrader, $extras ) {
		return $this->container->make( Update_Prevention::class )->filter_upgrader_source_selection( $source, $remote_source, $upgrader, $extras );
	}
}
