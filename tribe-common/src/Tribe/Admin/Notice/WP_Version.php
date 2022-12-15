<?php
namespace Tribe\Admin\Notice;

use Tribe__Main as Common;


/**
 * Various WordPress version notices.
 *
 * @since 4.12.17
 */
class WP_Version {
	/**
	 * Register the various WordPress version notices.
	 *
	 * @since 4.12.17
	 */
	public function hook() {
		tribe_notice(
			'wp_version_57',
			[ $this, 'wp_version_57_display_notice' ],
			[
				'type'     => 'warning',
				'dismiss'  => 1,
				'priority' => -1,
				'wrap'     => 'p',
			],
			[ $this, 'wp_version_57_should_display' ]
		);
	}

	/**
	 * Whether the WordPress 5.7 notice should display.
	 *
	 * @since 4.12.17
	 *
	 * @return boolean
	 */
	public function wp_version_57_should_display() {
		global $wp_version, $current_screen;

		$screens = [
			'tribe_events_page_tribe-app-shop', // App shop.
			'events_page_tribe-app-shop', // App shop.
			'tribe_events_page_tribe-common', // Settings & Welcome.
			'tribe_events_page_tec-events-settings', // New Settings & Welcome.
			'events_page_tribe-common', // Settings & Welcome.
			'toplevel_page_tribe-common', // Settings & Welcome.
		];

		// If not a valid screen, don't display.
		if ( empty( $current_screen->id ) || ! in_array( $current_screen->id, $screens, true ) ) {
			return false;
		}

		$wp_version_min_version_required = '5.8';
		$common_version_required = '4.12.18-dev';

		return
			version_compare( Common::VERSION, $common_version_required, '<' )
			&& version_compare( $wp_version, $wp_version_min_version_required, '<' );
	}

	/**
	 * HTML for the WordPress 5.7 notice.
	 *
	 * @since 4.12.17
	 *
	 * @see https://evnt.is/wp5-7
	 *
	 * @return string
	 */
	public function wp_version_57_display_notice() {
		global $wp_version;
		$is_wp_57 = version_compare( $wp_version, '5.7-beta', '>=' );
		$html = '';

		if ( $is_wp_57 ) {
			$html .= esc_html__( 'You are using WordPress 5.7 which included a major jQuery update that may cause compatibility issues with past versions of The Events Calendar, Event Tickets and other plugins.', 'tribe-common' );
		} else {
			$html .= esc_html__( 'WordPress 5.7 includes a major jQuery update that may cause compatibility issues with past versions of The Events Calendar, Event Tickets and other plugins.', 'tribe-common' );
		}
		$html .= ' <a target="_blank" href="https://evnt.is/wp5-7">' . esc_html__( 'Read more.', 'tribe-common' ) . '</a>';

		return $html;
	}
}
