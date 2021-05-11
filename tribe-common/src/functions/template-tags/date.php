<?php
/**
 * Date Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Main' ) ) {
	return;
}

if ( ! function_exists( 'tribe_format_date' ) ) {
	/**
	 * Formatted Date
	 *
	 * Returns formatted date
	 *
	 * @category Events
	 *
	 * @param string $date         String representing the datetime, assumed to be UTC (relevant if timezone conversion is used)
	 * @param bool   $display_time If true shows date and time, if false only shows date
	 * @param string $date_format  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 *
	 * @return string
	 */
	function tribe_format_date( $date, $display_time = true, $date_format = '' ) {

		if ( ! Tribe__Date_Utils::is_timestamp( $date ) ) {
			$date = strtotime( $date );
		}

		if ( $date_format ) {
			$format = $date_format;
		} else {
			$date_year = date( 'Y', $date );
			$cur_year  = date( 'Y', current_time( 'timestamp' ) );

			// only show the year in the date if it's not in the current year
			$with_year = $date_year == $cur_year ? false : true;

			if ( $display_time ) {
				$format = tribe_get_datetime_format( $with_year );
			} else {
				$format = tribe_get_date_format( $with_year );
			}
		}

		$date = date_i18n( $format, $date );

		/**
		 * Deprecated tribe_event_formatted_date in 4.0 in favor of tribe_formatted_date. Remove in 5.0
		 */
		$date = apply_filters( 'tribe_event_formatted_date', $date, $display_time, $date_format );

		return apply_filters( 'tribe_formatted_date', $date, $display_time, $date_format );
	}
}//end if

if ( ! function_exists( 'tribe_beginning_of_day' ) ) {
	/**
	 * Returns formatted date for the official beginning of the day according to the Multi-day cutoff time option
	 *
	 * @category Events
	 *
	 * @param string $date   The date to find the beginning of the day, defaults to today
	 * @param string $format Allows date and time formatting using standard php syntax (http://php.net/manual/en/function.date.php)
	 *
	 * @return string
	 */
	function tribe_beginning_of_day( $date = null, $format = 'Y-m-d H:i:s' ) {
		$multiday_cutoff = explode( ':', tribe_get_option( 'multiDayCutoff', '00:00' ) );
		$hours_to_add    = $multiday_cutoff[0];
		$minutes_to_add  = $multiday_cutoff[1];
		if ( is_null( $date ) || empty( $date ) ) {
			$date = date( $format, strtotime( date( 'Y-m-d' ) . ' +' . $hours_to_add . ' hours ' . $minutes_to_add . ' minutes' ) );
		} else {
			$date      = Tribe__Date_Utils::is_timestamp( $date ) ? $date : strtotime( $date );
			$timestamp = strtotime( date( 'Y-m-d', $date ) . ' +' . $hours_to_add . ' hours ' . $minutes_to_add . ' minutes' );
			$date      = date( $format, $timestamp );
		}

		/**
		 * Deprecated filter tribe_event_beginning_of_day in 4.0 in favor of tribe_beginning_of_day. Remove in 5.0
		 */
		$date = apply_filters( 'tribe_event_beginning_of_day', $date );

		/**
		 * Filters the beginning of day date
		 *
		 * @param string $date
		 */
		return apply_filters( 'tribe_beginning_of_day', $date );
	}
}//end if

if ( ! function_exists( 'tribe_end_of_day' ) ) {
	/**
	 * Returns formatted date for the official end of the day according to the Multi-day cutoff time option
	 *
	 * @category Events
	 *
	 * @param string $date   The date to find the end of the day, defaults to today
	 * @param string $format Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 *
	 * @return string
	 */
	function tribe_end_of_day( $date = null, $format = 'Y-m-d H:i:s' ) {
		$multiday_cutoff = explode( ':', tribe_get_option( 'multiDayCutoff', '00:00' ) );
		$hours_to_add    = $multiday_cutoff[0];
		$minutes_to_add  = $multiday_cutoff[1];
		if ( is_null( $date ) || empty( $date ) ) {
			$date = date( $format, strtotime( 'tomorrow  +' . $hours_to_add . ' hours ' . $minutes_to_add . ' minutes' ) - 1 );
		} else {
			$date      = Tribe__Date_Utils::is_timestamp( $date ) ? $date : strtotime( $date );
			$timestamp = strtotime( date( 'Y-m-d', $date ) . ' +1 day ' . $hours_to_add . ' hours ' . $minutes_to_add . ' minutes' ) - 1;
			$date      = date( $format, $timestamp );
		}

		/**
		 * Deprecated filter tribe_event_end_of_day in 4.0 in favor of tribe_end_of_day. Remove in 5.0
		 */
		$date = apply_filters( 'tribe_event_end_of_day', $date );

		/**
		 * Filters the end of day date
		 *
		 * @param string $date
		 */
		return apply_filters( 'tribe_end_of_day', $date );
	}
}//end if

if ( ! function_exists( 'tribe_get_datetime_separator' ) ) {
	/**
	 * Get the datetime saparator from the database option with escaped characters or not ;)
	 *
	 * @param string $default Default Separator if it's blank on the Database
	 * @param bool   $esc     If it's going to be used on a `date` function or method it needs to be escaped
	 *
	 * @filter tribe_datetime_separator
	 *
	 * @return string
	 */
	function tribe_get_datetime_separator( $default = ' @ ', $esc = false ) {
		$separator = (string) tribe_get_option( 'dateTimeSeparator', $default );
		if ( $esc ) {
			$separator = (array) str_split( $separator );
			$separator = ( ! empty( $separator ) ? '\\' : '' ) . implode( '\\', $separator );
		}

		return apply_filters( 'tribe_datetime_separator', $separator );
	}
}//end if

if ( ! function_exists( 'tribe_get_start_time' ) ) {
	/**
	 * Start Time
	 *
	 * Returns the event start time
	 *
	 * @category Events
	 *
	 * @param int    $event       (optional)
	 * @param string $date_format Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @param string $timezone    Timezone in which to present the date/time (or default behaviour if not set)
	 *
	 * @return string|null Time
	 */
	function tribe_get_start_time( $event = null, $date_format = '', $timezone = null ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}

		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( ! is_object( $event ) ) {
			return;
		}

		if ( Tribe__Date_Utils::is_all_day( get_post_meta( $event->ID, '_EventAllDay', true ) ) ) {
			return;
		}

		// @todo [BTRIA-584]: Move timezones to Common.
		if ( class_exists( 'Tribe__Events__Timezones' ) ) {
			$start_date = Tribe__Events__Timezones::event_start_timestamp( $event->ID, $timezone );
		}

		if ( '' == $date_format ) {
			$date_format = tribe_get_time_format();
		}

		/**
		 * Filters the returned event start time
		 *
		 * @param string  $start_date
		 * @param WP_Post $event
		 */
		return apply_filters( 'tribe_get_start_time', tribe_format_date( $start_date, false, $date_format ), $event );
	}
}

if ( ! function_exists( 'tribe_get_end_time' ) ) {
	/**
	 * End Time
	 *
	 * Returns the event end time
	 *
	 * @category Events
	 *
	 * @param int    $event       (optional)
	 * @param string $date_format Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @param string $timezone    Timezone in which to present the date/time (or default behaviour if not set)
	 *
	 * @return string|null Time
	 */
	function tribe_get_end_time( $event = null, $date_format = '', $timezone = null ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}

		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( ! is_object( $event ) ) {
			return;
		}

		if ( Tribe__Date_Utils::is_all_day( get_post_meta( $event->ID, '_EventAllDay', true ) ) ) {
			return;
		}

		// @todo [BTRIA-584]: Move timezones to Common.
		if ( class_exists( 'Tribe__Events__Timezones' ) ) {
			$end_date = Tribe__Events__Timezones::event_end_timestamp( $event->ID, $timezone );
		}

		if ( '' == $date_format ) {
			$date_format = tribe_get_time_format();
		}

		/**
		 * Filters the returned event end time
		 *
		 * @param string  $end_date
		 * @param WP_Post $event
		 */
		return apply_filters( 'tribe_get_end_time', tribe_format_date( $end_date, false, $date_format ), $event );
	}
}

if ( ! function_exists( 'tribe_get_start_date' ) ) {
	/**
	 * Start Date
	 *
	 * Returns the event start date and time
	 *
	 * @category Events
	 *
	 * @since 4.7.6 Deprecated the $timezone parameter.
	 *
	 * @param int    $event        (optional)
	 * @param bool   $display_time If true shows date and time, if false only shows date
	 * @param string $date_format  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @param string $timezone     Deprecated. Timezone in which to present the date/time (or default behaviour if not set)
	 *
	 * @return string|null Date
	 */
	function tribe_get_start_date( $event = null, $display_time = true, $date_format = '', $timezone = null ) {
		static $cache_var_name = __FUNCTION__;

		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}

		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( ! is_object( $event ) ) {
			return '';
		}

		$start_dates = tribe_get_var( $cache_var_name, [] );
		$cache_key = "{$event->ID}:{$display_time}:{$date_format}:{$timezone}";

		if ( ! isset( $start_dates[ $cache_key ] ) ) {
			if ( Tribe__Date_Utils::is_all_day( get_post_meta( $event->ID, '_EventAllDay', true ) ) ) {
				$display_time = false;
			}

			// @todo [BTRIA-584]: Move timezones to Common.
			if ( class_exists( 'Tribe__Events__Timezones' ) ) {
				$start_date = Tribe__Events__Timezones::event_start_timestamp( $event->ID, $timezone );
			} else {
				return null;
			}

			$start_dates[ $cache_key ] = tribe_format_date( $start_date, $display_time, $date_format );
			tribe_set_var( $cache_var_name, $start_dates );
		}

		/**
		 * Filters the returned event start date and time
		 *
		 * @param string  $start_date
		 * @param WP_Post $event
		 */
		return apply_filters( 'tribe_get_start_date', $start_dates[ $cache_key ], $event );
	}
}

if ( ! function_exists( 'tribe_get_end_date' ) ) {
	/**
	 * End Date
	 *
	 * Returns the event end date
	 *
	 * @category Events
	 *
	 * @since 4.7.6 Deprecated the $timezone parameter.
	 *
	 * @param int    $event        (optional)
	 * @param bool   $display_time If true shows date and time, if false only shows date
	 * @param string $date_format  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @param string $timezone     Deprecated. Timezone in which to present the date/time (or default behaviour if not set)
	 *
	 * @return string|null Date
	 */
	function tribe_get_end_date( $event = null, $display_time = true, $date_format = '', $timezone = null ) {
		static $cache_var_name = __FUNCTION__;

		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}

		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( ! is_object( $event ) ) {
			return '';
		}

		$end_dates = tribe_get_var( $cache_var_name, [] );
		$cache_key = "{$event->ID}:{$display_time}:{$date_format}:{$timezone}";

		if ( ! isset( $end_dates[ $cache_key ] ) ) {
			if ( Tribe__Date_Utils::is_all_day( get_post_meta( $event->ID, '_EventAllDay', true ) ) ) {
				$display_time = false;
			}

			// @todo [BTRIA-584]: Move timezones to Common.
			if ( class_exists( 'Tribe__Events__Timezones' ) ) {
				$end_date = Tribe__Events__Timezones::event_end_timestamp( $event->ID );
			} else {
				return null;
			}

			$end_dates[ $cache_key ] = tribe_format_date( $end_date, $display_time, $date_format );
			tribe_set_var( $cache_var_name, $end_dates );
		}

		/**
		 * Filters the returned event end date and time
		 *
		 * @param string  $end_date
		 * @param WP_Post $event
		 */
		return apply_filters( 'tribe_get_end_date', $end_dates[ $cache_key ], $event );
	}
}

if ( ! function_exists( 'tribe_normalize_manual_utc_offset' ) ) {
	/**
	 * Normalizes a manual UTC offset string.
	 *
	 * @param string $utc_offset
	 *
	 * @return string The normalized manual UTC offset.
	 *                e.g. 'UTC+3', 'UTC-4.5', 'UTC+2.75'
	 */
	function tribe_normalize_manual_utc_offset( $utc_offset ) {
		$matches = [];
		if ( preg_match( '/^UTC\\s*((\\+|-)(\\d{1,2}))((:|.|,)(\\d{1,2})+)*/ui', $utc_offset, $matches ) ) {
			if ( ! empty( $matches[6] ) ) {
				$minutes = $matches[6] > 10 && $matches[6] <= 60 ? $minutes = $matches[6] / 60 : $matches[6];
				$minutes = str_replace( '0.', '', $minutes );
			}

			$utc_offset = sprintf( 'UTC%s%s', $matches[1], ! empty( $minutes ) ? '.' . $minutes : '' );

		}

		return $utc_offset;
	}
}

if ( ! function_exists( 'tribe_wp_locale_weekday' ) ) {
	/**
	 * Return a WP Locale weekday in the specified format
	 *
	 * @param int|string $weekday Day of week
	 * @param string $format Weekday format: full, weekday, initial, abbreviation, abbrev, abbr, short
	 *
	 * @return string
	 */
	function tribe_wp_locale_weekday( $weekday, $format ) {
		return Tribe__Date_Utils::wp_locale_weekday( $weekday, $format );
	}
}

if ( ! function_exists( 'tribe_wp_locale_month' ) ) {
	/**
	 * Return a WP Locale month in the specified format
	 *
	 * @param int|string $month Month of year
	 * @param string $format month format: full, month, abbreviation, abbrev, abbr, short
	 *
	 * @return string
	 */
	function tribe_wp_locale_month( $month, $format ) {
		return Tribe__Date_Utils::wp_locale_month( $month, $format );
	}
}

if ( ! function_exists( 'tribe_is_site_using_24_hour_time' ) ) {
	/**
	 * Handy function for easily detecting if this site's using the 24-hour time format.
	 *
	 * @since 4.7.1
	 *
	 * @return boolean
	 */
	function tribe_is_site_using_24_hour_time() {
		$time_format = get_option( 'time_format' );
		return strpos( $time_format, 'H' ) !== false;
	}
}
