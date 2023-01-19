<?php
_deprecated_file(__FILE__, '4.14.2', 'Deprecated to a more granular approach.' );
/**
 * Various Marketing notices, e.g. Black Friday sales or special coupon initiatives.
 *
 * @since 4.7.23
 */

use Tribe__Date_Utils as Dates;

class Tribe__Admin__Notice__Marketing {

	/**
	 * Whether or not The Events Calendar is active.
	 *
	 * @since 4.7.23
	 *
	 * @var boolean
	 */
	public $tec_is_active;

	/**
	 * Whether or not Event Tickets is active.
	 *
	 * @since 4.7.23
	 *
	 * @var boolean
	 */
	public $et_is_active;

	public function __construct() {
		$tribe_dependency    = Tribe__Dependency::instance();
		$this->tec_is_active = $tribe_dependency->is_plugin_active( 'Tribe__Events__Main' );
		$this->et_is_active  = $tribe_dependency->is_plugin_active( 'Tribe__Tickets__Main' );
	}

	/**
	 * Register the various Marketing notices.
	 *
	 * @since 4.7.23
	 */
	public function hook() {
		$this->black_friday_hook_notice();
	}

	/**
	 * Register the Black Friday notice.
	 *
	 * @since 4.12.14
	 */
	public function black_friday_hook_notice() {

		tribe_notice(
			'black-friday',
			[ $this, 'black_friday_display_notice' ],
			[
				'type'     => 'tribe-banner',
				'dismiss'  => 1,
				'priority' => -1,
				'wrap'     => false,
			],
			[ $this, 'black_friday_should_display' ]
		);
	}

	/**
	 * Unix time for Monday of Thanksgiving week @ 11am UTC. (11am UTC is 6am EST).
	 *
	 * @since 4.12.14
	 *
	 * @return int
	 */
	public function get_black_friday_start_time() {
		$date = Dates::build_date_object( 'fourth Thursday of November ' . date( 'Y' ), 'UTC' );
		$date = $date->modify( '-3 days' );
		$date = $date->setTime( 11, 0 );

		$start_time = $date->format( 'U' );

		/**
		 * Allow filtering of the Black Friday sale start date, mainly for testing purposes.
		 *
		 * @since 4.12.14
		 *
		 * @param int $bf_start_date Unix time for the Monday of Thanksgiving week @ 6am UTC.
		 */
		return apply_filters( 'tribe_black_friday_start_time', $start_time );
	}

	/**
	 * Unix time for Dec 1 @ 5am UTC. (5am UTC is 12am EST).
	 *
	 * @since 4.12.14
	 *
	 * @return int
	 */
	public function get_black_friday_end_time() {
		$date = Dates::build_date_object( 'December 1st', 'UTC' );
		$date = $date->setTime( 5, 0 );

		$end_time = $date->format( 'U' );

		/**
		 * Allow filtering of the Black Friday sale end date, mainly for testing purposes.
		 *
		 * @since 4.12.14
		 *
		 * @param int $bf_end_date Unix time for Dec 1 @ 6am UTC.
		 */
		return apply_filters( 'tribe_black_friday_end_time', $end_time );
	}
	/**
	 * Whether the Black Friday notice should display.
	 *
	 * Unix times for Monday of Thanksgiving week @ 6am UTC and Dec 1 2020 @ 6am UTC.
	 * 6am UTC is midnight for TheEventsCalendar.com, which uses the America/Los_Angeles time zone.
	 *
	 * @since 4.12.14
	 *
	 * @return boolean
	 */
	public function black_friday_should_display() {
		// If upsells have been manually hidden, respect that.
		if ( tec_should_hide_upsell() ) {
			return false;
		}

		$now           = Dates::build_date_object( 'now', 'UTC' )->format( 'U' );
		$bf_sale_start = $this->get_black_friday_start_time();
		$bf_sale_end   = $this->get_black_friday_end_time();

		$current_screen = get_current_screen();

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

		return $bf_sale_start <= $now && $now < $bf_sale_end;
	}

	/**
	 * HTML for the Black Friday notice.
	 *
	 * @since 4.12.14
	 *
	 * @return string
	 */
	public function black_friday_display_notice() {
		Tribe__Assets::instance()->enqueue( [ 'tribe-common-admin' ] );

		$current_screen = get_current_screen();

		$icon_url = Tribe__Main::instance()->plugin_url . 'src/resources/images/icons/sale-burst.svg';
		$cta_url  = 'https://evnt.is/bf' . date( 'Y' );
		$screens = [
			'tribe_events_page_tribe-common',
			'tribe_events_page_tec-events-settings',
			'events_page_tribe-common',
			'toplevel_page_tribe-common',
		];

		// If we are on the settings page or a welcome page, change the Black Friday URL.
		if (
			! empty( $current_screen->id )
			&& in_array( $current_screen->id, $screens )
		) {
			if ( isset( $_GET['welcome-message-the-events-calendar'] ) || isset( $_GET['welcome-message-event-tickets' ] ) ) {
				$cta_url .= 'welcome';
			} else {
				$cta_url .= 'settings';
			}
		}

		ob_start();

		include Tribe__Main::instance()->plugin_path . 'src/admin-views/notices/tribe-bf-general.php';

		return ob_get_clean();
	}
}
