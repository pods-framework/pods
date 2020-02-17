<?php
/**
 * Hooks and manages the plugins Debug Bar integrations.
 *
 * @since 4.9.5
 */

class Tribe__Service_Providers__Debug_Bar extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		add_filter( 'debug_bar_panels', array( $this, 'add_panels' ) );
	}

	/**
	 * Adds Modern Tribe panels to the Debug Bar panels.
	 *
	 * @since 4.9.5
	 *
	 * @param Debug_Bar_Panel[] $panels The current list of Debug Bar panels.
	 *
	 * @return array A modified list of Debug Bar panels.
	 */
	public function add_panels( array $panels ) {
		/**
		 * Filters the list of Modern Tribe debug bar panels that will be added to the
		 * Debug Bar.
		 *
		 * @since 4.9.5
		 *
		 * @param Debug_Bar_Panel[] The default list of Modern Tribe panels added to the Debug Bar.
		 */
		$tribe_panels = apply_filters( 'tribe_debug_bar_panels', array(
			new Tribe__Debug_Bar__Panels__Context(),
		) );

		if ( count( $tribe_panels ) > 0 ) {
			$panels = array_merge( $panels, $tribe_panels );
		}

		return $panels;
	}
}
