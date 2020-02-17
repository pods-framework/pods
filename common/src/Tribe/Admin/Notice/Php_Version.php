<?php
/**
 * Shows an admin notice for Php_Version
 */
class Tribe__Admin__Notice__Php_Version {

	public function hook() {

		// display the PHP version notice
		tribe_notice(
			'php-deprecated',
			array( $this, 'display_notice' ),
			array(
				'type'    => 'warning',
				'dismiss' => 1,
				'wrap'    => 'p',
			),
			array( $this, 'should_display' )
		);

	}

	/**
	 * Return the list of the Tribe active plugins
	 *
	 * @since 4.7.16
	 *
	 * @return string String of items
	 */
	public function get_active_plugins() {

		$active_plugins = Tribe__Dependency::instance()->get_active_plugins();

		foreach ( $active_plugins as $active_plugin ) {

			if ( ! $active_plugin['path'] ) {
				continue;
			}

			$plugin_data = get_plugin_data( $active_plugin['path'] );
			$plugins[]   = $plugin_data['Name'];

		}

		return $this->implode_with_grammar( $plugins );

	}

	/**
	 * Implodes a list items using 'and' as the final separator and a comma everywhere else
	 *
	 * @param array $items List of items to implode
	 * @since 4.7.16
	 *
	 * @return string String of items
	 */
	public function implode_with_grammar( $items ) {

		$separator   = _x( ', ', 'separator used in a list of items', 'tribe-common' );
		$conjunction = _x( ' and ', 'the final separator in a list of two or more items', 'tribe-common' );
		$output      = $last_item = array_pop( $items );

		if ( $items ) {
			$output = implode( $separator, $items ) . $conjunction . $last_item;
		}

		return $output;
	}

	/**
	 * We only want to display notices for users
	 * who are in PHP < 5.6
	 *
	 * @since  4.7.16
	 *
	 * @return boolean
	 */
	public function should_display() {
		// Bail if the user is not admin or can manage plugins
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		return ! ( version_compare( phpversion(), '5.6.0' ) > 0 );
	}

	/**
	 * HTML for the PHP notice
	 *
	 * @since  4.7.16
	 *
	 * @return string
	 */
	public function display_notice() {

		// for PHP version above 5.4 and up to 5.6
		if ( version_compare( phpversion(), '5.4.0' ) >= 0 ) {
			$text = __( 'Starting March 2019, %1$s will no longer support versions prior to PHP 5.6. Your site is currently using PHP version %2$s which will no longer be supported by %1$s. For best results, we recommend using PHP 5.6 or above.', 'tribe-common' );
		} else {
			// for PHPversions below 5.4
			$text = __( 'Starting March 2019, %1$s will no longer work with versions prior to PHP 5.4. Currently your site is using PHP version %2$s. For best results, we recommend using PHP 5.6 or above.', 'tribe-common' );
		}

		$plugins = $this->get_active_plugins();

		return sprintf( $text, $plugins, phpversion() );

	}
}
