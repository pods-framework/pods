<?php
/**
 * Various Marketing notices, e.g. Black Friday sales or special coupon initiatives.
 *
 * @since 4.7.23
 */
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
		$this->bf_2018_hook_notice();
		$this->gutenberg_release_notice();
	}

	/**
	 * Register the Black Friday 2018 notice.
	 *
	 * @since 4.7.23
	 */
	public function bf_2018_hook_notice() {

		tribe_notice(
			'black-friday-2018',
			array( $this, 'bf_2018_display_notice' ),
			array(
				'type'    => 'warning',
				'dismiss' => 1,
				'wrap'    => false,
			),
			array( $this, 'bf_2018_should_display' )
		);
	}

	/**
	 * Unix time for Nov 20 2018 @ 6am UTC. (6am UTC is midnight for TheEventsCalendar.com, which uses the America/Los_Angeles time zone).
	 *
	 * @since 4.7.23
	 *
	 * @return int
	 */
	public function get_bf_2018_start_time() {
		/**
		 * Allow filtering of the Black Friday sale start date, mainly for testing purposes.
		 *
		 * @since 4.7.23
		 *
		 * @param int $bf_start_date Unix time for Nov 20 2018 @ 6am UTC.
		 */
		return apply_filters( 'tribe_bf_2018_start_time', 1542693600 );
	}

	/**
	 * Unix time for Nov 26 2018 @ 6am UTC. (6am UTC is midnight for TheEventsCalendar.com, which uses the America/Los_Angeles time zone).
	 *
	 * @since 4.7.23
	 *
	 * @return int
	 */
	public function get_bf_2018_end_time() {
		/**
		 * Allow filtering of the Black Friday sale end date, mainly for testing purposes.
		 *
		 * @since 4.7.23
		 *
		 * @param int $bf_end_date Unix time for Nov 20 2018 @ 6am UTC.
		 */
		return apply_filters( 'tribe_bf_2018_end_time', 1543212000 );
	}
	/**
	 * Whether the Black Friday 2018 notice should display.
	 *
	 * Unix times for Nov 20 2018 @ 6am UTC and Nov 26 2018 @ 6am UTC.
	 * 6am UTC is midnight for TheEventsCalendar.com, which uses the America/Los_Angeles time zone.
	 *
	 * @since 4.7.23
	 *
	 * @return boolean
	 */
	public function bf_2018_should_display() {
		$bf_sale_start = $this->get_bf_2018_start_time();
		$bf_sale_end   = $this->get_bf_2018_end_time();

		return $bf_sale_start <= time() && time() < $bf_sale_end;
	}

	/**
	 * HTML for the Black Friday 2018 notice.
	 *
	 * @since 4.7.23
	 *
	 * @return string
	 */
	public function bf_2018_display_notice() {

		Tribe__Assets::instance()->enqueue( array( 'tribe-common-admin' ) );

		$mascot_url = Tribe__Main::instance()->plugin_url . 'src/resources/images/mascot.png';
		$end_time   = $this->get_bf_2018_end_time();

		ob_start();

		if ( $this->tec_is_active && ! $this->et_is_active ) {
			include Tribe__Main::instance()->plugin_path . 'src/admin-views/notices/tribe-bf-2018-tec.php';
		} elseif ( $this->et_is_active && ! $this->tec_is_active ) {
			include Tribe__Main::instance()->plugin_path . 'src/admin-views/notices/tribe-bf-2018-et.php';
		} else {
			include Tribe__Main::instance()->plugin_path . 'src/admin-views/notices/tribe-bf-2018-general.php';
		}

		return ob_get_clean();
	}

	/**
	 * Register the Gutenberg Release notice (November 2018).
	 *
	 * @since 4.7.23
	 */
	public function gutenberg_release_notice() {

		tribe_notice(
			'gutenberg-release-2018',
			array( $this, 'gutenberg_release_display_notice' ),
			array(
				'type'    => 'warning',
				'dismiss' => 1,
				'wrap'    => false,
			),
			array( $this, 'gutenberg_release_should_display' )
		);
	}

	/**
	 * Gets the end time for the Gutenberg release notice.
	 *
	 * @since 4.7.23
	 *
	 * @return int
	 */
	public function get_gutenberg_release_end_time() {

		/**
		 * Allows filtering of the default Gutenberg Release Notice's end time, mainly for testing purposes.
		 *
		 * @since 4.7.23
		 *
		 * @param int $gutenberg_release_end_time Defaults to Nov 17 2018 @ midnight, California time.
		 */
		return apply_filters( 'tribe_gutenberg_release_notice_end_time', 1542434400 );
	}

	/**
	 * Whether the Gutenberg Release notice should display.
	 *
	 * @since 4.7.23
	 *
	 * @return boolean
	 */
	public function gutenberg_release_should_display() {
		return time() < $this->get_gutenberg_release_end_time();
	}

	/**
	 * HTML for the Gutenberg Release notice (November 2018).
	 *
	 * @since 4.7.23
	 *
	 * @return string
	 */
	public function gutenberg_release_display_notice() {

		Tribe__Assets::instance()->enqueue( array( 'tribe-common-admin' ) );

		$end_time = $this->get_gutenberg_release_end_time();

		if ( $this->et_is_active && ! $this->tec_is_active ) {
			$icon_url = Tribe__Main::instance()->plugin_url . 'src/resources/images/gutenberg-admin-notice-tickets.png';
		} else {
			$icon_url = Tribe__Main::instance()->plugin_url . 'src/resources/images/gutenberg-admin-notice-TEC.png';
		}

		ob_start();

		include Tribe__Main::instance()->plugin_path . 'src/admin-views/notices/tribe-gutenberg-release.php';

		return ob_get_clean();
	}
}
