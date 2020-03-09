<?php

/**
 * Class Tribe__Editor__Configuration
 *
 * setup the configuration variables used on the editor
 *
 * @since 4.8
 */
class Tribe__Editor__Configuration implements Tribe__Editor__Configuration_Interface {
	/**
	 * Localize variables that are part of common
	 *
	 * @since 4.8
	 *
	 * @return array
	 */
	public function localize() {
		$editor_config = array(
			'common' => array(
				'adminUrl'     => admin_url(),
				'timeZone'     => array(
					'showTimeZone' => false,
					'label'        => $this->get_timezone_label(),
				),
				'rest'         => array(
					'url'        => get_rest_url(),
					'nonce'      => array(
						'wp_rest' => wp_create_nonce( 'wp_rest' ),
					),
					'namespaces' => array(
						'core' => 'wp/v2',
					),
				),
				'dateSettings' => $this->get_date_settings(),
				'constants'    => array(
					'hideUpsell' => ( defined( 'TRIBE_HIDE_UPSELL' ) && TRIBE_HIDE_UPSELL ),
				),
				'countries'    => tribe( 'languages.locations' )->get_countries(),
				'usStates'     => Tribe__View_Helpers::loadStates(),
			),
		);

		/**
		 * Filter the default configuration used to localize variables
		 *
		 * @since 4.8
		 *
		 * array $editor_config An associative array with the configuration to be send into the client
		 */
		return apply_filters( 'tribe_editor_config', $editor_config );
	}


	/**
	 * Returns the site timezone as a string
	 *
	 * @since 4.8
	 *
	 * @return string
	 */
	public function get_timezone_label() {
		return class_exists( 'Tribe__Timezones' )
			? Tribe__Timezones::wp_timezone_string()
			: get_option( 'timezone_string', 'UTC' );
	}

	/**
	 * Get Localization data for Date settings
	 *
	 * @since 4.8
	 *
	 * @return array
	 */
	public function get_date_settings() {
		global $wp_locale;

		return array(
			'l10n'     => array(
				'locale'        => get_user_locale(),
				'months'        => array_values( $wp_locale->month ),
				'monthsShort'   => array_values( $wp_locale->month_abbrev ),
				'weekdays'      => array_values( $wp_locale->weekday ),
				'weekdaysShort' => array_values( $wp_locale->weekday_abbrev ),
				'meridiem'      => (object) $wp_locale->meridiem,
				'relative'      => array(
					/* translators: %s: duration */
					'future' => __( '%s from now', 'default' ),
					/* translators: %s: duration */
					'past'   => __( '%s ago', 'default' ),
				),
			),
			'formats'  => array(
				'time'       => get_option( 'time_format', __( 'g:i a', 'default' ) ),
				'date'       => get_option( 'date_format', __( 'F j, Y', 'default' ) ),
				'dateNoYear' => __( 'F j', 'default' ),
				'datetime'   => get_option( 'date_format', __( 'F j, Y', 'default' ) ) . ' ' . get_option( 'time_format', __( 'g:i a', 'default' ) ),
			),
			'timezone' => array(
				'offset' => get_option( 'gmt_offset', 0 ),
				'string' => $this->get_timezone_label(),
			),
		);
	}
}
