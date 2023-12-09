<?php

/**
 * @package Pods\Global\Functions\Compatibility
 */

// Add backwards compatibility for tribe() for old add-ons that may still call it.
if (
	! function_exists( 'tribe_is_not_min_php_version' )
	&& ! function_exists( 'tribe' )
	&& ! doing_action( 'activate_plugin' )
	&& ! did_action( 'activate_plugin' )
	&& (
		! defined( 'WP_SANDBOX_SCRAPING' )
		|| ! WP_SANDBOX_SCRAPING
	)
	&& 'activate' !== pods_v( 'action' )
) {
	/**
	 * Compatibility function for Pods 2.x add-ons that may still call tribe().
	 *
	 * @since 3.0.5
	 *
	 * @param string|null $slug_or_class  Either the slug of a binding previously registered using singleton or
	 *                                    register or the full class name that should be automagically created or
	 *                                    `null` to get the container instance itself.
	 *
	 * @return mixed|null The pods_container() object or null if the function does not exist yet.
	 */
	function tribe( $slug_or_class = null ) {
		_doing_it_wrong( 'tribe', 'tribe() is no longer included in Pods Framework directly. Please use pods_container() instead.', '3.0' );

		return pods_container( $slug_or_class );
	}
}
