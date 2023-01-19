<?php

/**
 * Handles output of The Events Calendar credits
 */
class Tribe__Credits {

	public static function init() {
		self::instance()->hook();
	}

	/**
	 * Hook the functionality of this class into the world
	 */
	public function hook() {
		add_filter( 'tribe_events_after_html', [ $this, 'html_comment_credit' ] );
		add_filter( 'admin_footer_text', [ $this, 'rating_nudge' ], 1, 2 );
	}

	/**
	 * Add credit in HTML page source
	 *
	 * @return void
	 **/
	public function html_comment_credit( $after_html ) {

		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			return $after_html;
		}

		$html_credit = "\n<!--\n" . esc_html__( 'This calendar is powered by The Events Calendar.', 'tribe-common' ) . "\nhttp://evnt.is/18wn\n-->\n";
		$after_html .= apply_filters( 'tribe_html_credit', $html_credit );
		return $after_html;
	}

	/**
	 * Add ratings nudge in admin footer
	 *
	 * @param $footer_text
	 *
	 * @return string
	 */
	public function rating_nudge( $footer_text ) {
		$admin_helpers = Tribe__Admin__Helpers::instance();

		add_filter( 'tribe_tickets_post_types', [ $this, 'tmp_return_tribe_events' ], 99 );

		$review_text_tec = esc_html__( 'Rate %1$sThe Events Calendar%2$s %3$s', 'tribe-common' );
		$review_url_tec  = 'https://wordpress.org/support/plugin/the-events-calendar/reviews/?filter=5';

		$review_text_et = esc_html__( 'If you like %1$sEvent Tickets%2$s please leave us a %3$s. It takes a minute and it helps a lot.', 'tribe-common' );
		$review_url_et  = 'https://wordpress.org/support/plugin/event-tickets/reviews/?filter=5';

		// Only display custom text on Tribe Admin Pages.
		if ( $admin_helpers->is_screen() || $admin_helpers->is_post_type_screen() ) {

			if ( class_exists( 'Tribe__Events__Main' ) ) {
				// If we have TEC and ET, split the impressions 50/50.
				if ( class_exists( 'Tribe__Tickets__Main' ) && wp_rand( 0,1 ) ) {
					$review_text = $review_text_et;
					$review_url  = $review_url_et;
				} else {
					$review_text = $review_text_tec;
					$review_url  = $review_url_tec;
				}
			} else {
				$review_text = $review_text_et;
				$review_url  = $review_url_et;
			}

			$footer_text = sprintf(
				$review_text,
				'<strong>',
				'</strong>',
				'<a href="' . $review_url . '" target="_blank" rel="noopener noreferrer" class="tribe-rating">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		remove_filter( 'tribe_tickets_post_types', [ $this, 'tmp_return_tribe_events' ], 99 );

		/**
		 * Filters the admin footer text.
		 *
		 * @since 4.15.0
		 *
		 * @param $footer_text The admin footer text.
		 */
		return apply_filters( 'tec_admin_footer_text', $footer_text );
	}

	/**
	 * temporary function to filter event types down to only tribe-specific types
	 *
	 * This will limit the request for ratings to only those post type pages
	 */
	public function tmp_return_tribe_events( $unused_post_types ) {
		return [ 'tribe_events' ];
	}

	/**
	 * @var $instance
	 */
	private static $instance = null;

	/**
	 * @return self
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
