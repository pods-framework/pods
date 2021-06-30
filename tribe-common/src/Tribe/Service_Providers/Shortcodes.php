<?php
namespace Tribe\Service_Providers;

use Tribe\Shortcode\Manager;

/**
 * Class Shortcode
 *
 * @since   4.12.0
 *
 * @package Tribe\Service_Providers
 */
class Shortcodes extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.12.0
	 */
	public function register() {
		if ( ! static::is_active() ) {
			return;
		}

		$this->container->singleton( Manager::class, Manager::class );
		$this->container->singleton(
			'shortcode.manager',
			function() {
				return $this->container->make( Manager::class );
			}
		);

		$this->register_hooks();
		$this->register_assets();

		$this->container->singleton( static::class, $this );

	}

	/**
	 * Static method wrapper around a filter to allow full deactivation of this provider
	 *
	 * @since 4.12.0
	 *
	 * @return boolean If this service provider is active.
	 */
	public static function is_active() {
		/**
		 * Allows filtering to deactivate all shortcodes loading.
		 *
		 * @since 4.12.0
		 *
		 * @param boolean $is_active If shortcodes should be loaded or not.
		 */
		return apply_filters( 'tribe_shortcodes_is_active', true );
	}

	/**
	 * Register all the assets associated with this service provider.
	 *
	 * @since 4.12.0
	 */
	protected function register_assets() {

	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for this service provider.
	 *
	 * @since 4.12.0
	 */
	protected function register_hooks() {
		add_action( 'init', [ $this, 'action_add_shortcodes' ], 20 );
		add_filter( 'pre_do_shortcode_tag', [ $this, 'filter_pre_do_shortcode_tag' ], 10, 4 );
		add_filter( 'do_shortcode_tag', [ $this, 'filter_do_shortcode_tag' ], 10, 4 );
	}

	/**
	 * Adds the new shortcodes, this normally will trigger on `init@P20` due to how we the
	 * v1 is added on `init@P10` and we remove them on `init@P15`.
	 *
	 * It's important to leave gaps on priority for better injection.
	 *
	 * @since 4.12.0
	 */
	public function action_add_shortcodes() {
		$this->container->make( Manager::class )->add_shortcodes();
	}

	/**
	 * Filters `pre_do_shortcode_tag` to mark that a tribe shortcode is currently being done.
	 *
	 * @since 4.12.9
	 *
	 * @param bool|string $return      Short-circuit return value. Either false or the value to replace the shortcode with.
	 * @param string      $tag         Shortcode name.
	 * @param array       $attr        Shortcode attributes array,
	 * @param array       $m           Regular expression match array.
	 *
	 * @return bool|string Short-circuit return value.
	 */
	public function filter_pre_do_shortcode_tag( $false, $tag, $attr, $m ) {
		return $this->container->make( Manager::class )->filter_pre_do_shortcode_tag( $false, $tag, $attr, $m );
	}

	/**
	 * * Filters `do_shortcode_tag` to mark that a tribe shortcode is complete, and remove it from the current list.
	 *
	 * @since 4.12.9
	 *
	 * @param string       $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string Shortcode output.
	 */
	public function filter_do_shortcode_tag( $output, $tag, $attr, $m ) {
		return $this->container->make( Manager::class )->filter_do_shortcode_tag( $output, $tag, $attr, $m );
	}
}
