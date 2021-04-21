<?php
namespace Tribe\Service_Providers;

use Tribe\Widget\Manager;

/**
 * Class Widget
 *
 * @since   4.12.12
 *
 * @package Tribe\Service_Providers
 */
class Widgets extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.12.12
	 */
	public function register() {
		if ( ! static::is_active() ) {
			return;
		}

		$this->container->singleton( Manager::class, Manager::class );
		$this->container->singleton(
			'widget.manager',
			function() {
				return $this->container->make( Manager::class );
			}
		);

		$this->register_hooks();

		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'widgets', $this );
	}

	/**
	 * Static method wrapper around a filter to allow full deactivation of this provider.
	 *
	 * @since 4.12.12
	 *
	 * @return boolean If this service provider is active.
	 */
	public static function is_active() {
		/**
		 * Allows filtering to prevent all Tribe widgets from loading.
		 *
		 * @since 4.12.12
		 *
		 * @param boolean $is_active If widgets should be loaded or not.
		 */
		return apply_filters( 'tribe_widgets_is_active', true );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for this service provider.
	 *
	 * @since 4.12.12
	 */
	protected function register_hooks() {
		add_action( 'widgets_init', [ $this, 'register_widgets_with_wp' ], 20 );
	}

	/**
	 * Adds the new widgets.
	 *
	 * This triggers on `init@P20` due to how v1 is added on `init@P10` and removed on `init@P15`,
	 * as it's important to leave gaps on priority for future flexibility.
	 *
	 * @since 4.12.12
	 */
	public function register_widgets_with_wp() {
		$this->container->make( Manager::class )->register_widgets_with_wp();
	}
}
