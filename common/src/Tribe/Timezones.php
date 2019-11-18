<?php

/**
 * Helpers for handling timezone based event datetimes.
 *
 * In our timezone logic, the term "local" refers to the locality of an event
 * rather than the local WordPress timezone.
 */
class Tribe__Timezones {
	const SITE_TIMEZONE  = 'site';
	const EVENT_TIMEZONE = 'event';


	/**
	 * Container for reusable DateTimeZone objects.
	 *
	 * @var array
	 */
	protected static $timezones = array();


	public static function init() {
		self::invalidate_caches();
	}

	/**
	 * Clear any cached timezone-related values when appropriate.
	 *
	 * Currently we are concerned only with the site timezone abbreviation.
	 */
	protected static function invalidate_caches() {
		add_filter( 'pre_update_option_gmt_offset', array( __CLASS__, 'clear_site_timezone_abbr' ) );
		add_filter( 'pre_update_option_timezone_string', array( __CLASS__, 'clear_site_timezone_abbr' ) );
	}

	/**
	 * Wipe the cached site timezone abbreviation, if set.
	 *
	 * @param mixed $option_val (passed through without modification)
	 *
	 * @return mixed
	 */
	public static function clear_site_timezone_abbr( $option_val ) {
		delete_transient( 'tribe_events_wp_timezone_abbr' );
		return $option_val;
	}

	/**
	 * Returns the current site-wide timezone string abbreviation, if it can be
	 * determined or falls back on the full timezone string/offset text.
	 *
	 * @param string $date
	 *
	 * @return string
	 */
	public static function wp_timezone_abbr( $date ) {
		$timezone_string = self::wp_timezone_string();
		$abbr            = self::abbr( $date, $timezone_string );

		return empty( $abbr ) ? $timezone_string : $abbr;
	}

	/**
	 * Returns the current site-wide timezone string.
	 *
	 * Based on the core WP code found in wp-admin/options-general.php.
	 *
	 * @return string
	 */
	public static function wp_timezone_string() {
		$current_offset = get_option( 'gmt_offset' );
		$tzstring       = get_option( 'timezone_string' );

		// Return the timezone string if already set
		if ( ! empty( $tzstring ) ) {
			return $tzstring;
		}

		// Otherwise return the UTC offset
		if ( 0 == $current_offset ) {
			return 'UTC+0';
		} elseif ( $current_offset < 0 ) {
			return 'UTC' . $current_offset;
		}

		return 'UTC+' . $current_offset;
	}

	/**
	 * Attempts to provide the correct timezone abbreviation for the provided timezone string
	 * on the date given (and so should account for daylight saving time, etc).
	 *
	 * @param string|DateTime|DateTimeImmutable $date The date string representation or object.
	 * @param string|DateTimeZone $timezone_string The timezone string or object.
	 *
	 * @return string
	 */
	public static function abbr( $date, $timezone_string ) {
		try {
			$timezone_object = $timezone_string instanceof DateTimeZone
				? $timezone_string
				: new DateTimeZone( $timezone_string );
			$date_time = $date instanceof DateTime
			             || ( class_exists( 'DateTimeImmutable' ) && $date instanceof DateTimeImmutable )
				? $date
				: Tribe__Date_Utils::build_date_object( $date, $timezone_object );

			$abbr = $date_time->format( 'T' );

			// If PHP date "T" format is a -03 or +03, it's a bugged abbreviation, we can find it manually.
			if ( 0 === strpos( $abbr, '-' ) || 0 === strpos( $abbr, '+' ) ) {
				$abbreviations = timezone_abbreviations_list();

				foreach ( $abbreviations as $abbreviation => $timezones ) {
					foreach ( $timezones as $timezone ) {
						if ( $timezone['timezone_id'] === $timezone_string ) {
							return strtoupper( $abbreviation );
						}
					}
				}
			}
		} catch ( Exception $e ) {
			$abbr = '';
		}

		return $abbr;
	}

	/**
	 * Helper function to retrieve the timezone string for a given UTC offset
	 *
	 * This is a close copy of WooCommerce's wc_timezone_string() method
	 *
	 * @param string $offset UTC offset
	 *
	 * @return string
	 */
	public static function generate_timezone_string_from_utc_offset( $offset ) {
		if ( ! self::is_utc_offset( $offset ) ) {
			return $offset;
		}

		// ensure we have the minutes on the offset
		if ( ! strpos( $offset, ':' ) ) {
			$offset .= ':00';
		}

		$offset = str_replace( 'UTC', '', $offset );

		list( $hours, $minutes ) = explode( ':', $offset );
		$seconds = $hours * 60 * 60 + $minutes * 60;

		// attempt to guess the timezone string from the UTC offset
		$timezone = timezone_name_from_abbr( '', $seconds, 0 );

		if ( false === $timezone ) {
			$is_dst = (bool) date( 'I' );

			foreach ( timezone_abbreviations_list() as $abbr ) {
				foreach ( $abbr as $city ) {
					if (
						(bool) $city['dst'] === $is_dst
						&& intval( $city['offset'] ) === intval( $seconds )
						&& $city['timezone_id']
					) {
						return $city['timezone_id'];
					}
				}
			}

			// fallback to UTC
			return 'UTC';
		}

		return $timezone;
	}

	/**
	 * Tests to see if the timezone string is a UTC offset, ie "UTC+2".
	 *
	 * @param string $timezone
	 *
	 * @return bool
	 */
	public static function is_utc_offset( $timezone ) {
		$timezone = trim( $timezone );
		return ( 0 === strpos( $timezone, 'UTC' ) && strlen( $timezone ) > 3 );
	}

	/**
	 * Returns a DateTimeZone object matching the representation in $tzstring where
	 * possible, or else representing UTC (or, in the worst case, false).
	 *
	 * If optional parameter $with_fallback is true, which is the default, then in
	 * the event it cannot find/create the desired timezone it will try to return the
	 * UTC DateTimeZone before bailing.
	 *
	 * @param  string $tzstring
	 * @param  bool   $with_fallback = true
	 *
	 * @return DateTimeZone|false
	 */
	public static function get_timezone( $tzstring, $with_fallback = true ) {
		if ( isset( self::$timezones[ $tzstring ] ) ) {
			return self::$timezones[ $tzstring ];
		}

		try {
			self::$timezones[ $tzstring ] = new DateTimeZone( $tzstring );
			return self::$timezones[ $tzstring ];
		}
		catch ( Exception $e ) {
			if ( $with_fallback ) {
				return self::get_timezone( 'UTC', true );
			}
		}

		return false;
	}

	/**
	 * Confirms if the current timezone mode matches the $possible_mode.
	 *
	 * @param string $possible_mode
	 *
	 * @return bool
	 */
	public static function is_mode( $possible_mode ) {
		return $possible_mode === self::mode();
	}

	/**
	 * Returns a string representing the timezone/offset currently desired for
	 * the display of dates and times.
	 *
	 * @return string
	 */
	public static function mode() {
		$mode = self::EVENT_TIMEZONE;

		if ( 'site' === tribe_get_option( 'tribe_events_timezone_mode' ) ) {
			$mode = self::SITE_TIMEZONE;
		}

		return apply_filters( 'tribe_events_current_display_timezone', $mode );
	}

	/**
	 * Tries to convert the provided $datetime to UTC from the timezone represented by $tzstring.
	 *
	 * Though the usual range of formats are allowed, $datetime ordinarily ought to be something
	 * like the "Y-m-d H:i:s" format (ie, no timezone information). If it itself contains timezone
	 * data, the results may be unexpected.
	 *
	 * In those cases where the conversion fails to take place, the $datetime string will be
	 * returned untouched.
	 *
	 * @param string $datetime
	 * @param string $tzstring
	 * @param string $format The optional format of the resulting date, defaults to
	 *                      `Tribe__Date_Utils::DBDATETIMEFORMAT`.
	 *
	 * @return string
	 */
	public static function to_utc( $datetime, $tzstring, $format = null ) {
		if ( self::is_utc_offset( $tzstring ) ) {
			return self::apply_offset( $datetime, $tzstring, true );
		}

		$local = self::get_timezone( $tzstring );
		$utc   = self::get_timezone( 'UTC' );

		$new_datetime = date_create( $datetime, $local );

		if ( $new_datetime ) {
			$new_datetime->setTimezone( $utc );
			$format = ! empty( $format ) ? $format : Tribe__Date_Utils::DBDATETIMEFORMAT;

			return $new_datetime->format( $format );
		}

		// Fallback to the unmodified datetime if there was a failure during conversion
		return $datetime;
	}

	/**
	 * Tries to convert the provided $datetime to the timezone represented by $tzstring.
	 *
	 * This is the sister function of self::to_utc() - please review the docs for that method
	 * for more information.
	 *
	 * @param string $datetime
	 * @param string $tzstring
	 *
	 * @return string
	 */
	public static function to_tz( $datetime, $tzstring ) {

		if ( self::is_utc_offset( $tzstring ) ) {

			return self::apply_offset( $datetime, $tzstring );
		}

		$local = self::get_timezone( $tzstring );
		$utc   = self::get_timezone( 'UTC' );

		$new_datetime = date_create( $datetime, $utc );

		if ( $new_datetime && $new_datetime->setTimezone( $local ) ) {
			return $new_datetime->format( Tribe__Date_Utils::DBDATETIMEFORMAT );
		}

		// Fallback to the unmodified datetime if there was a failure during conversion
		return $datetime;
	}

	/**
	 * Localizes a date or timestamp using WordPress timezone and returns it in the specified format.
	 *
	 * @param string     $format   The format the date shouuld be formatted to.
	 * @param string|int $date     The date UNIX timestamp or `strtotime` parseable string.
	 * @param string     $timezone An optional timezone string identifying the timezone the date shoudl be localized
	 *                             to; defaults to the WordPress installation timezone (if available) or to the system
	 *                             timezone.
	 *
	 * @return string|bool The parsed date in the specified format and localized to the system or specified
	 *                     timezone, or `false` if the specified date is not a valid date string or timestamp
	 *                     or the specified timezone is not a valid timezone string.
	 */
	public static function localize_date( $format = null, $date = null, $timezone = null ) {
		if ( empty( $timezone ) ) {
			$timezone = self::wp_timezone_string();
		}

		$timezone = self::generate_timezone_string_from_utc_offset( $timezone );

		try {
			$timezone_object = new DateTimeZone( $timezone );

			if ( Tribe__Date_Utils::is_timestamp( $date ) ) {
				$date = new DateTime( "@{$date}" );
			} else {
				$date = new DateTime( $date );
			}
		} catch ( Exception $e ) {
			return false;
		}

		$date->setTimezone( $timezone_object );

		return $date->format( $format );
	}

	/**
	 * Converts a date string or timestamp to a destination timezone.
	 *
	 * @param string|int $date          Either a string parseable by the `strtotime` function or a UNIX timestamp.
	 * @param string     $from_timezone The timezone of the source date.
	 * @param string     $to_timezone   The timezone the destination date should use.
	 * @param string     $format        The format that should be used for the destination date.
	 *
	 * @return string The formatted and converted date.
	 */
	public static function convert_date_from_timezone( $date, $from_timezone, $to_timezone, $format ) {
		if ( ! Tribe__Date_Utils::is_timestamp( $date ) ) {
			$from_date = new DateTime( $date, new DateTimeZone( $from_timezone ) );
			$timestamp = $from_date->format( 'U' );
		} else {
			$timestamp = $date;
		}

		$to_date = new DateTime( "@{$timestamp}", new DateTimeZone( $to_timezone ) );

		return $to_date->format( $format );
	}

	/**
	 * Whether the candidate timezone is a valid PHP timezone or a supported UTC offset.
	 *
	 * @param string $candidate
	 *
	 * @return bool
	 */
	public static function is_valid_timezone( $candidate ) {
		if ( self::is_utc_offset( $candidate ) ) {
			return true;
		}
		try {
			new DateTimeZone( $candidate );
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Given a string in the form "UTC+2.5" returns the corresponding DateTimeZone object.
	 *
	 * If this is not possible or if $utc_offset_string does not match the expected pattern,
	 * boolean false is returned.
	 *
	 * @todo revise to eliminate all of these: maybe_get_tz_name, apply_offset, timezone_from_utc_offset, and adjust_timestamp
	 *
	 * @since 4.6.3
	 *
	 * @param string $utc_offset_string
	 *
	 * @return DateTimeZone | bool
	 */
	public static function timezone_from_utc_offset( $utc_offset_string ) {
		// Test for strings looking like "UTC-2" or "UTC+5.25" etc
		if ( ! preg_match( '/^UTC[\-\+]{1}[0-9\.]{1,4}$/', $utc_offset_string ) ) {
			return false;
		}

		// Breakdown into polarity, hours and minutes
		$parts    = explode( '.', substr( $utc_offset_string, 4 ) );
		$hours    = (int) $parts[ 0 ];
		$fraction = isset( $parts[ 1 ] ) ? '0.' . (int) $parts[ 1 ] : 0;
		$minutes  = $fraction * 60;
		$polarity = substr( $utc_offset_string, 3, 1 );

		// Reassemble in the form +/-hhmm (ie "-0200" or "+0930")
		$utc_offset = sprintf( $polarity . "%'.02d%'.02d", $hours, $minutes );

		if ( '+0000' === $utc_offset || '-0000' === $utc_offset ) {
			$utc_offset = 'UTC';
		}

		// Use this to build a new DateTimeZone
		try {
			return new DateTimeZone( $utc_offset );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Applies an time offset to the specified date time.
	 *
	 * @todo revise to eliminate all of these: maybe_get_tz_name, apply_offset, timezone_from_utc_offset, and adjust_timestamp
	 *
	 * @param string $datetime The date and time string in a valid date format.
	 * @param int|string  $offset (string or numeric offset)
	 * @param bool   $invert = false Whether the offset should be added (`true`) or
	 *                       subtracted (`false`); signum operations carry over so
	 *                       `-(-23) = +23`.
	 *
	 * @return string
	 */
	public static function apply_offset( $datetime, $offset, $invert = false ) {
		// Normalize
		$offset = strtolower( trim( $offset ) );

		// Strip any leading "utc" text if set
		if ( 0 === strpos( $offset, 'utc' ) ) {
			$offset = substr( $offset, 3 );
		}

		// It's possible no adjustment will be needed
		if ( 0 === (int) $offset ) {
			return $datetime;
		}

		// if the offset contains fractions like :15, :30 or :45 convert them
		$supported_offsets = array(
			'/:15$/' => '.25',
			'/:30$/' => '.5',
			'/:45$/' => '.75',
		);
		$offset = preg_replace( array_keys( $supported_offsets ), array_values( $supported_offsets ), $offset );

		// Convert the offset to minutes for easier handling of fractional offsets
		$offset = (int) ( $offset * 60 );

		// Invert the offset? Useful for stripping an offset that has already been applied
		if ( $invert ) {
			$offset *= - 1;
		}

		if ( $offset > 0 ) {
			$offset = '+' . $offset;
		}

		$offset = $offset . ' minutes';

		$offset_datetime = date_create( $datetime );

		if ( $offset_datetime && $offset_datetime->modify( $offset ) ) {
			return $offset_datetime->format( Tribe__Date_Utils::DBDATETIMEFORMAT );
		}

		return $datetime;
	}

	/**
	 * Try to figure out the Timezone name base on offset
	 *
	 * @since  4.0.7
	 *
	 * @todo revise to eliminate all of these: maybe_get_tz_name, apply_offset, timezone_from_utc_offset, and adjust_timestamp
	 *
	 * @param  string|int|float $timezone The timezone
	 *
	 * @return string           The Guessed Timezone String
	 */
	public static function maybe_get_tz_name( $timezone ) {
		if ( ! self::is_utc_offset( $timezone ) && ! is_numeric( $timezone ) ) {
			return $timezone;
		}

		if ( ! is_numeric( $timezone ) ) {
			$offset = str_replace( 'utc', '', trim( strtolower( $timezone ) ) );
		} else {
			$offset = $timezone;
		}


		// try to get timezone from gmt_offset, respecting daylight savings
		$timezone = timezone_name_from_abbr( null, $offset * 3600, true );

		// if that didn't work, maybe they don't have daylight savings
		if ( false === $timezone ) {
			$timezone = timezone_name_from_abbr( null, $offset * 3600, false );
		}

		// and if THAT didn't work, round the gmt_offset down and then try to get the timezone respecting daylight savings
		if ( false === $timezone ) {
			$timezone = timezone_name_from_abbr( null, (int) $offset * 3600, true );
		}

		// lastly if that didn't work, round the gmt_offset down and maybe that TZ doesn't do daylight savings
		if ( false === $timezone ) {
			$timezone = timezone_name_from_abbr( null, (int) $offset * 3600, false );
		}

		return $timezone;
	}

	/**
	 * Accepts a unix timestamp and adjusts it so that when it is used to constitute
	 * a new datetime string, that string reflects the designated timezone.
	 *
	 * @todo revise to eliminate all of these: maybe_get_tz_name, apply_offset, timezone_from_utc_offset, and adjust_timestamp
	 *
	 * @deprecated 4.7.12
	 *
	 * @param string $unix_timestamp
	 * @param string $tzstring
	 *
	 * @return string
	 */
	public static function adjust_timestamp( $unix_timestamp, $tzstring ) {
		try {
			$local = self::get_timezone( $tzstring );

			$datetime = date_create_from_format( 'U', $unix_timestamp )->format( Tribe__Date_Utils::DBDATETIMEFORMAT );

			// We prefer format('U') to getTimestamp() here due to our requirement for compatibility with PHP 5.2
			return date_create_from_format( 'Y-m-d H:i:s', $datetime, $local )->format( 'U' );
		}
		catch( Exception $e ) {
			return $unix_timestamp;
		}
	}

	/**
	 * Returns a valid timezone object built from the passed timezone or from the
	 * site one if a timezone in not passed.
	 *
	 * @since 4.9.5
	 *
	 * @param string|null|DateTimeZone $timezone A DateTimeZone object, a timezone string
	 *                                           or `null` to build an object using the site one.
	 *
	 * @return DateTimeZone The built DateTimeZone object.
	 */
	public static function build_timezone_object( $timezone = null ) {
		if ( $timezone instanceof DateTimeZone ) {
			return $timezone;
		}

		$timezone = null === $timezone ? self::wp_timezone_string() : $timezone;

		try {
			$object = new DateTimeZone( self::get_valid_timezone( $timezone ) );
		} catch ( Exception $e ) {
			return new DateTimeZone( 'UTC' );
		}

		return $object;
	}

	/**
	 * Parses the timezone string to validate or convert it into a valid one.
	 *
	 * @since 4.9.5
	 *
	 * @param string|\DateTimeZone $timezone_candidate The timezone string candidate.
	 *
	 * @return string The validated timezone string or a valid timezone string alternative.
	 */
	public static function get_valid_timezone( $timezone_candidate ) {
		if ( $timezone_candidate instanceof DateTimeZone ) {
			return $timezone_candidate->getName();
		}

		$timezone_string = preg_replace( '/(\\+||\\-)0$/', '', $timezone_candidate );
		$timezone_string = self::is_utc_offset( $timezone_string )
			? self::generate_timezone_string_from_utc_offset( $timezone_string )
			: $timezone_string;

		return $timezone_string;
	}
}

