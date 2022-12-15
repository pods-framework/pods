<?php
/**
 * Abstract for various date-based Marketing notices, e.g. Black Friday sales or special coupon initiatives.
 *
 * @since 4.14.2
 */

namespace Tribe\Admin\Notice;

use Tribe__Date_Utils as Dates;

abstract class Date_Based {
	/**
	 * The slug used to make filters specific to an individual notice.
	 *
	 * @since 4.14.2
	 *
	 * @var string
	 */
	public $slug = '';

	/**
	 * Placeholder for start date string.
	 *
	 * @since 4.14.2
	 *
	 * @var string
	 */
	public $start_date;

	/**
	 * Placeholder for start time int.
	 *
	 * @since 4.14.2
	 *
	 * @var int
	 */
	public $start_time;

	/**
	 * Placeholder for end date string.
	 *
	 * @since 4.14.2
	 *
	 * @var string
	 */
	public $end_date;

	/**
	 * Placeholder for end time int.
	 *
	 * @since 4.14.2
	 *
	 * @var int
	 */
	public $end_time;

	/**
	 * Placeholder for extension date string.
	 *
	 * @since 4.15.4
	 *
	 * @var string
	 */
	public $extension_date;

	/**
	 * Placeholder for extension time int.
	 *
	 * @since 4.14.2
	 *
	 * @var int
	 */
	public $extension_time;

	/**
	 * Whether or not The Events Calendar is active.
	 *
	 * @since 4.14.2
	 *
	 * @var boolean
	 */
	public $tec_is_active;

	/**
	 * Stores the instance of the template engine that we will use for rendering the page.
	 *
	 * @since 4.14.7
	 *
	 * @var \Tribe__Template
	 */
	protected $template;

	/**
	 * Whether or not Event Tickets is active.
	 *
	 * @since 4.14.2
	 *
	 * @var boolean
	 */
	public $et_is_active;

	/**
	 * The screens we show the notice on.
	 *
	 * @since 4.15.4
	 *
	 * @var array
	 */
	public $screens = [
		'tribe_events_page_tribe-app-shop', // App shop.
		'events_page_tribe-app-shop', // App shop.
		'toplevel_page_tec-events', // New Events Welcome.
		'tribe_events_page_tec-events-settings', // New Events Settings.
		'tribe_events_page_tec-events-help', // New Events Help.
		'tribe_events_page_tec-troubleshooting', // New Events Troubleshooting.
		'tickets_page_tec-tickets-settings', // New Tickets Settings.
		'toplevel_page_tec-tickets', // New Tickets Welcome.
		'tickets_page_tec-tickets-help', // New Tickets Help.
		'tickets_page_tec-tickets-troubleshooting', // New Ticket Troubleshooting.
		'tribe_events_page_tribe-common', // Old Settings & Welcome.
		'events_page_tribe-common', // Settings & Welcome.
		'toplevel_page_tribe-common', // Settings & Welcome.
		'tribe_events_page_aggregator', // Import page
		'edit-tribe_events', // Events admin list
	];

	public function __construct() {
		$tribe_dependency    = tribe( \Tribe__Dependency::class );
		$this->tec_is_active = $tribe_dependency->is_plugin_active( 'Tribe__Events__Main' );
		$this->et_is_active  = $tribe_dependency->is_plugin_active( 'Tribe__Tickets__Main' );

		$now            = Dates::build_date_object( 'now', 'UTC' );
		$notice_start   = $this->get_start_time();
		$notice_end     = $this->get_end_time();
		$extension_date = $this->get_extension_time();

		// If we have an extension date defined.
		if ( ! empty( $this->get_extension_time() ) ) {
			// If the sale has started and
			if (
				$notice_start <= $now
				&& $notice_end < $now
				&& $now < $extension_date
			) {
				add_filter( "tribe_{$this->slug}_notice_end_date", function() {
					return $this->get_extension_time();
				});
			}
		}

		$this->hook();
	}

	/**
	 * Register the various Marketing notices.
	 *
	 * @since 4.14.2
	 */
	public function hook() {
		$this->hook_notice();
	}

	/**
	 * Register the notice.
	 *
	 * @since 4.14.2
	 */
	public function hook_notice() {
		tribe_notice(
			$this->slug,
			[ $this, "display_notice" ],
			[
				'type'     => 'tribe-banner',
				'dismiss'  => 1,
				'priority' => -1,
				'wrap'     => false,
			],
			[ $this, "should_display" ]
		);
	}

	/**
	 * HTML for the notice.
	 *
	 * @since 4.14.2
	 *
	 * @return string The HTML string to be displayed.
	 */
	abstract function display_notice();

	/**
	 * Function to get and filter the screens the notice is displayed on.
	 *
	 * @since 4.15.4
	 *
	 * @return array<string> List of allowed screens.
	 */
	public function get_screens() {
		$screens = $this->screens;

		/**
		 * Allows filtering of the screens for all date-based notices.
		 *
		 * @since 4.15.4
		 *
		 * @param array<string> $screens The current list of allowed screens.
		 * @param string        $slug    The slug for the current notice.
		 *
		 * @return array<string> $screens The modified list of allowed screens.
		 */
		$screens = apply_filters(
			'tec_date_based_notice_get_screens',
			$screens,
			$this->slug
		);

		/**
		 * Allows filtering of the screens for a specific date-based notice.
		 *
		 * @since 4.15.4
		 *
		 * @param array<string> $screens The current list of allowed screens.
		 *
		 * @return array<string> $screens The modified list of allowed screens.
		 */
		$screens = apply_filters(
			"tec_date_based_notice_get_screens_{$this->slug}",
			$screens
		);

		return $screens;
	}

	/**
	 * Whether the notice should display.
	 *
	 * @since 4.14.2
	 *
	 * @return boolean $should_display Whether the notice should display or not.
	 */
	public function should_display() {
		// If upsells have been manually hidden, respect that.
		if ( tec_should_hide_upsell() ) {
			return false;
		}

		$current_screen = get_current_screen();

		$screens = $this->get_screens();

		// If not a valid screen, don't display.
		if ( empty( $current_screen->id ) || ! in_array( $current_screen->id, $screens, true ) ) {
			return false;
		}

		$now            = Dates::build_date_object( 'now', 'UTC' );
		$notice_start   = $this->get_start_time();
		$notice_end     = $this->get_end_time();

		$should_display = $notice_start <= $now && $now < $notice_end;


		/**
		 * Allow filtering of whether the notice should display.
		 *
		 * @since 4.14.2
		 *
		 * @param boolean                          $should_display Whether the notice should display.
		 * @param Tribe__Admin__Notice_Date_Based $notice  The notice object.
		 */
		return apply_filters( "tribe_{$this->slug}_notice_should_display", $should_display, $this );
	}

	/**
	 * Unix time for notice start.
	 *
	 * @since 4.14.2
	 *
	 * @return int $start_time The date & time the notice should start displaying, as a Unix timestamp.
	 */
	public function get_start_time() {
		$date = Dates::build_date_object( $this->start_date, 'UTC' );
		$date = $date->setTime( $this->start_time, 0 );

		/**
		 * Allow filtering of the start date DateTime object,
		 * to allow for things like "the day before" ( $date->modify( '-1 day' ) ) and such.
		 *
		 * @since 4.14.2
		 *
		 * @param \DateTime $date Date object for the notice start.
		 */
		$date = apply_filters( "tribe_{$this->slug}_notice_start_date", $date, $this );

		return $date;
	}

	/**
	 * Unix time for notice end.
	 *
	 * @since 4.14.2
	 *
	 * @return int $end_time The date & time the notice should stop displaying, or shift to the extension datetime as a Unix timestamp.
	 */
	public function get_end_time() {
		$date = Dates::build_date_object( $this->end_date, 'UTC' );
		$date = $date->setTime( $this->end_time, 0 );

		/**
		* Allow filtering of the end date DateTime object,
		* to allow for things like "the day after" ( $date->modify( '+1 day' ) ) and such.
		*
		* @since 4.14.2
		*
		* @param \DateTime $date Date object for the notice end.
		*/
		$date = apply_filters( "tribe_{$this->slug}_notice_end_date", $date, $this );

		return $date;
	}



	/**
	 * Unix time for notice extension end.
	 *
	 * @since 4.15.4
	 *
	 * @return int $end_time The date & time the notice should stop displaying, as a Unix timestamp.
	 */
	public function get_extension_time() {
		$date = Dates::build_date_object( $this->extension_date, 'UTC' );
		$date = $date->setTime( $this->extension_time, 0 );

		/**
		* Allow filtering of the extension date DateTime object,
		* to allow for things like "the day after" ( $date->modify( '+1 day' ) ) and such.
		*
		* @since 4.14.2
		*
		* @param \DateTime $date Date object for the notice end.
		*/
		$date = apply_filters( "tribe_{$this->slug}_notice_extension_date", $date, $this );

		return $date;
	}

	/**
	 * Gets the template instance used to setup the rendering of the page.
	 *
	 * @since 4.14.7
	 *
	 * @return \Tribe__Template
	 */
	public function get_template() {
		if ( empty( $this->template ) ) {
			$this->template = new \Tribe__Template();
			$this->template->set_template_origin( \Tribe__Main::instance() );
			$this->template->set_template_folder( 'src/admin-views' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}
}
