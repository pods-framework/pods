<?php
/**
 * Date utility functions used throughout TEC + Addons
 */

use Tribe\Utils\Date_I18n;
use Tribe\Utils\Date_I18n_Immutable;

// Don't load directly

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Date_Utils' ) ) {
	class Tribe__Date_Utils {
		// Default formats, they are overridden by WP options or by arguments to date methods
		const DATEONLYFORMAT        = 'F j, Y';
		const TIMEFORMAT            = 'g:i A';
		const HOURFORMAT            = 'g';
		const MINUTEFORMAT          = 'i';
		const MERIDIANFORMAT        = 'A';
		const DBDATEFORMAT          = 'Y-m-d';
		const DBDATETIMEFORMAT      = 'Y-m-d H:i:s';
		const DBTZDATETIMEFORMAT    = 'Y-m-d H:i:s O';
		const DBTIMEFORMAT          = 'H:i:s';
		const DBYEARMONTHTIMEFORMAT = 'Y-m';

		/**
		 * Default datepicker format index.
		 *
		 * @since 4.11.0.1
		 *
		 * @var int
		 */
		private static $default_datepicker_format_index = 1;

		private static $localized_months_full = [];
		private static $localized_months_short = [];
		private static $localized_weekdays = [];
		private static $localized_months = [];

		/**
		 * Get the datepickerFormat index.
		 *
		 * @since 4.11.0.1
		 *
		 * @return int
		 */
		public static function get_datepicker_format_index() {
			/**
			 * Filter the datepickerFormat index.
			 *
			 * @since 4.11.0.1
			 *
			 * @param int $format_index Index of datepickerFormat.
			 */
			return apply_filters( 'tribe_datepicker_format_index', tribe_get_option( 'datepickerFormat', static::$default_datepicker_format_index ) );
		}

		/**
		 * Try to format a Date to the Default Datepicker format
		 *
		 * @since  4.5.12
		 *
		 * @param  string      $date       Original Date that came from a datepicker
		 * @param  string|int  $datepicker Datepicker format
		 * @return string
		 */
		public static function maybe_format_from_datepicker( $date, $datepicker = null ) {
			if ( ! is_numeric( $datepicker ) ) {
				$datepicker = self::get_datepicker_format_index();
			}

			if ( is_numeric( $datepicker ) ) {
				$datepicker = self::datepicker_formats( $datepicker );
			}

			$default_datepicker = self::datepicker_formats( 1 );

			// If the current datepicker is the default we don't care
			if ( $datepicker === $default_datepicker ) {
				return $date;
			}

			return self::datetime_from_format( $datepicker, $date );
		}

		/**
		 * Get the datepicker format, that is used to translate the option from the DB to a string
		 *
		 * @param  int $translate The db Option from datepickerFormat
		 * @return string|array            If $translate is not set returns the full array, if not returns the `Y-m-d`
		 */
		public static function datepicker_formats( $translate = null ) {

			// The datepicker has issues when a period separator and no leading zero is used. Those formats are purposefully omitted.
			$formats = [
				0     => 'Y-m-d',
				1     => 'n/j/Y',
				2     => 'm/d/Y',
				3     => 'j/n/Y',
				4     => 'd/m/Y',
				5     => 'n-j-Y',
				6     => 'm-d-Y',
				7     => 'j-n-Y',
				8     => 'd-m-Y',
				9     => 'Y.m.d',
				10    => 'm.d.Y',
				11    => 'd.m.Y',
				'm0'  => 'Y-m',
				'm1'  => 'n/Y',
				'm2'  => 'm/Y',
				'm3'  => 'n/Y',
				'm4'  => 'm/Y',
				'm5'  => 'n-Y',
				'm6'  => 'm-Y',
				'm7'  => 'n-Y',
				'm8'  => 'm-Y',
				'm9'  => 'Y.m',
				'm10' => 'm.Y',
				'm11' => 'm.Y',
			];

			if ( is_null( $translate ) ) {
				return $formats;
			}

			return isset( $formats[ $translate ] ) ? $formats[ $translate ] : $formats[ static::get_datepicker_format_index() ];
		}

		/**
		 * As PHP 5.2 doesn't have a good version of `date_parse_from_format`, this is how we deal with
		 * possible weird datepicker formats not working
		 *
		 * @param  string $format The weird format you are using
		 * @param  string $date   The date string to parse
		 *
		 * @return string         A DB formated Date, includes time if possible
		 */
		public static function datetime_from_format( $format, $date ) {
			// Reverse engineer the relevant date formats
			$keys = [
				// Year with 4 Digits
				'Y' => [ 'year', '\d{4}' ],

				// Year with 2 Digits
				'y' => [ 'year', '\d{2}' ],

				// Month with leading 0
				'm' => [ 'month', '\d{2}' ],

				// Month without the leading 0
				'n' => [ 'month', '\d{1,2}' ],

				// Month ABBR 3 letters
				'M' => [ 'month', '[A-Z][a-z]{2}' ],

				// Month Name
				'F' => [ 'month', '[A-Z][a-z]{2,8}' ],

				// Day with leading 0
				'd' => [ 'day', '\d{2}' ],

				// Day without leading 0
				'j' => [ 'day', '\d{1,2}' ],

				// Day ABBR 3 Letters
				'D' => [ 'day', '[A-Z][a-z]{2}' ],

				// Day Name
				'l' => [ 'day', '[A-Z][a-z]{5,8}' ],

				// Hour 12h formatted, with leading 0
				'h' => [ 'hour', '\d{2}' ],

				// Hour 24h formatted, with leading 0
				'H' => [ 'hour', '\d{2}' ],

				// Hour 12h formatted, without leading 0
				'g' => [ 'hour', '\d{1,2}' ],

				// Hour 24h formatted, without leading 0
				'G' => [ 'hour', '\d{1,2}' ],

				// Minutes with leading 0
				'i' => [ 'minute', '\d{2}' ],

				// Seconds with leading 0
				's' => [ 'second', '\d{2}' ],
			];

			$date_regex = "/{$keys['Y'][1]}-{$keys['m'][1]}-{$keys['d'][1]}( {$keys['H'][1]}:{$keys['i'][1]}:{$keys['s'][1]})?$/";

			// if the date is already in Y-m-d or Y-m-d H:i:s, just return it
			if ( preg_match( $date_regex, $date ) ) {
				return $date;
			}


			// Convert format string to regex
			$regex = '';
			$chars = str_split( $format );
			foreach ( $chars as $n => $char ) {
				$last_char = isset( $chars[ $n - 1 ] ) ? $chars[ $n - 1 ] : '';
				$skip_current = '\\' == $last_char;
				if ( ! $skip_current && isset( $keys[ $char ] ) ) {
					$regex .= '(?P<' . $keys[ $char ][0] . '>' . $keys[ $char ][1] . ')';
				} elseif ( '\\' == $char ) {
					$regex .= $char;
				} else {
					$regex .= preg_quote( $char );
				}
			}

			$dt = [];

			// Now try to match it
			if ( preg_match( '#^' . $regex . '$#', $date, $dt ) ) {
				// Remove unwanted Indexes
				foreach ( $dt as $k => $v ) {
					if ( is_int( $k ) ) {
						unset( $dt[ $k ] );
					}
				}

				// We need at least Month + Day + Year to work with
				if ( ! checkdate( $dt['month'], $dt['day'], $dt['year'] ) ) {
					return false;
				}
			} else {
				return false;
			}

			$dt['month'] = str_pad( $dt['month'], 2, '0', STR_PAD_LEFT );
			$dt['day'] = str_pad( $dt['day'], 2, '0', STR_PAD_LEFT );

			$formatted = '{year}-{month}-{day}' . ( isset( $dt['hour'], $dt['minute'], $dt['second'] ) ? ' {hour}:{minute}:{second}' : '' );
			foreach ( $dt as $key => $value ) {
				$formatted = str_replace( '{' . $key . '}', $value, $formatted );
			}

			return $formatted;
		}

		/**
		 * Returns the date only.
		 *
		 * @param int|string $date        The date (timestamp or string).
		 * @param bool       $isTimestamp Is $date in timestamp format?
		 * @param string|null $format The format used
		 *
		 * @return string The date only in DB format.
		 */
		public static function date_only( $date, $isTimestamp = false, $format = null ) {
			$date = $isTimestamp ? $date : strtotime( $date );

			if ( is_null( $format ) ) {
				$format = self::DBDATEFORMAT;
			}

			return date( $format, $date );
		}

		/**
		 * Returns as string the nearest half a hour for a given valid string datetime.
		 *
		 * @since  4.10.2
		 *
		 * @param string $date Valid DateTime string.
		 *
		 * @return string Rounded datetime string
		 */
		public static function round_nearest_half_hour( $date ) {
			$date_object = static::build_date_object( $date );
			$rounded_minutes = floor( $date_object->format( 'i' ) / 30 ) * 30;

			return $date_object->format( 'Y-m-d H:' ) . $rounded_minutes . ':00';
		}

		/**
		 * Returns the time only.
		 *
		 * @param string $date The date.
		 *
		 * @return string The time only in DB format.
		 */
		public static function time_only( $date ) {
			$date = is_numeric( $date ) ? $date : strtotime( $date );
			return date( self::DBTIMEFORMAT, $date );
		}

		/**
		 * Returns the hour only.
		 *
		 * @param string $date The date.
		 *
		 * @return string The hour only.
		 */
		public static function hour_only( $date ) {
			$date = is_numeric( $date ) ? $date : strtotime( $date );
			return date( self::HOURFORMAT, $date );
		}

		/**
		 * Returns the minute only.
		 *
		 * @param string $date The date.
		 *
		 * @return string The minute only.
		 */
		public static function minutes_only( $date ) {
			$date = is_numeric( $date ) ? $date : strtotime( $date );
			return date( self::MINUTEFORMAT, $date );
		}

		/**
		 * Returns the meridian (am or pm) only.
		 *
		 * @param string $date The date.
		 *
		 * @return string The meridian only in DB format.
		 */
		public static function meridian_only( $date ) {
			$date = is_numeric( $date ) ? $date : strtotime( $date );
			return date( self::MERIDIANFORMAT, $date );
		}

		/**
		 * Returns the number of seconds (absolute value) between two dates/times.
		 *
		 * @param string $date1 The first date.
		 * @param string $date2 The second date.
		 *
		 * @return int The number of seconds between the dates.
		 */
		public static function time_between( $date1, $date2 ) {
			return abs( strtotime( $date1 ) - strtotime( $date2 ) );
		}

		/**
		 * The number of days between two arbitrary dates.
		 *
		 * @param string $date1 The first date.
		 * @param string $date2 The second date.
		 *
		 * @return int The number of days between two dates.
		 */
		public static function date_diff( $date1, $date2 ) {
			// Get number of days between by finding seconds between and dividing by # of seconds in a day
			$days = self::time_between( $date1, $date2 ) / ( 60 * 60 * 24 );

			return $days;
		}

		/**
		 * Returns the last day of the month given a php date.
		 *
		 * @param int $timestamp THe timestamp.
		 *
		 * @return string The last day of the month.
		 */
		public static function get_last_day_of_month( $timestamp ) {
			$curmonth  = date( 'n', $timestamp );
			$curYear   = date( 'Y', $timestamp );
			$nextmonth = mktime( 0, 0, 0, $curmonth + 1, 1, $curYear );
			$lastDay   = strtotime( date( self::DBDATETIMEFORMAT, $nextmonth ) . ' - 1 day' );

			return date( 'j', $lastDay );
		}

		/**
		 * Returns true if the timestamp is a weekday.
		 *
		 * @param int $curDate A timestamp.
		 *
		 * @return bool If the timestamp is a weekday.
		 */
		public static function is_weekday( $curdate ) {
			return in_array( date( 'N', $curdate ), [ 1, 2, 3, 4, 5 ] );
		}

		/**
		 * Returns true if the timestamp is a weekend.
		 *
		 * @param int $curDate A timestamp.
		 *
		 * @return bool If the timestamp is a weekend.
		 */
		public static function is_weekend( $curdate ) {
			return in_array( date( 'N', $curdate ), [ 6, 7 ] );
		}

		/**
		 * Gets the last day of the week in a month (ie the last Tuesday).  Passing in -1 gives you the last day in the month.
		 *
		 * @param int $curdate     A timestamp.
		 * @param int $day_of_week The index of the day of the week.
		 *
		 * @return int The timestamp of the date that fits the qualifications.
		 */
		public static function get_last_day_of_week_in_month( $curdate, $day_of_week ) {
			$nextdate = mktime( date( 'H', $curdate ), date( 'i', $curdate ), date( 's', $curdate ), date( 'n', $curdate ), self::get_last_day_of_month( $curdate ), date( 'Y', $curdate ) );;

			while ( date( 'N', $nextdate ) != $day_of_week && $day_of_week != - 1 ) {
				$nextdate = strtotime( date( self::DBDATETIMEFORMAT, $nextdate ) . ' - 1 day' );
			}

			return $nextdate;
		}

		/**
		 * Gets the first day of the week in a month (ie the first Tuesday).
		 *
		 * @param int $curdate     A timestamp.
		 * @param int $day_of_week The index of the day of the week.
		 *
		 * @return int The timestamp of the date that fits the qualifications.
		 */
		public static function get_first_day_of_week_in_month( $curdate, $day_of_week ) {
			$nextdate = mktime( 0, 0, 0, date( 'n', $curdate ), 1, date( 'Y', $curdate ) );

			while ( ! ( $day_of_week > 0 && date( 'N', $nextdate ) == $day_of_week ) &&
					! ( $day_of_week == - 1 && self::is_weekday( $nextdate ) ) &&
					! ( $day_of_week == - 2 && self::is_weekend( $nextdate ) ) ) {
				$nextdate = strtotime( date( self::DBDATETIMEFORMAT, $nextdate ) . ' + 1 day' );
			}

			return $nextdate;
		}

		/**
		 * From http://php.net/manual/en/function.date.php
		 *
		 * @param int $number A number.
		 *
		 * @return string The ordinal for that number.
		 */
		public static function number_to_ordinal( $number ) {
			$output = $number . ( ( ( strlen( $number ) > 1 ) && ( substr( $number, - 2, 1 ) == '1' ) ) ?
					'th' : date( 'S', mktime( 0, 0, 0, 0, substr( $number, - 1 ), 0 ) ) );

			return apply_filters( 'tribe_events_number_to_ordinal', $output, $number );
		}

		/**
		 * check if a given string is a timestamp
		 *
		 * @param $timestamp
		 *
		 * @return bool
		 */
		public static function is_timestamp( $timestamp ) {
			if ( is_numeric( $timestamp ) && (int) $timestamp == $timestamp && date( 'U', $timestamp ) == $timestamp ) {
				return true;
			}

			return false;
		}

		/**
		 * Accepts a string representing a date/time and attempts to convert it to
		 * the specified format, returning an empty string if this is not possible.
		 *
		 * @param $dt_string
		 * @param $new_format
		 *
		 * @return string
		 */
		public static function reformat( $dt_string, $new_format ) {
			$timestamp = self::is_timestamp( $dt_string ) ? $dt_string : strtotime( $dt_string );
			$revised   = date( $new_format, $timestamp );

			return $revised ? $revised : '';
		}

		/**
		 * Accepts a numeric offset (such as "4" or "-6" as stored in the gmt_offset
		 * option) and converts it to a strtotime() style modifier that can be used
		 * to adjust a DateTime object, etc.
		 *
		 * @param $offset
		 *
		 * @return string
		 */
		public static function get_modifier_from_offset( $offset ) {
			$modifier = '';
			$offset   = (float) $offset;

			// Separate out hours, minutes, polarity
			$hours    = (int) $offset;
			$minutes  = (int) ( ( $offset - $hours ) * 60 );
			$polarity = ( $offset >= 0 ) ? '+' : '-';

			// Correct hours and minutes to positive values
			if ( $hours < 0 )   $hours *= -1;
			if ( $minutes < 0 ) $minutes *= -1;

			// Form the modifier string
			if ( $hours >= 0 )  $modifier  = "$polarity $hours hours ";
			if ( $minutes > 0 ) $modifier .= "$minutes minutes";

			return $modifier;
		}

		/**
		 * Returns the weekday of the 1st day of the month in
		 * "w" format (ie, Sunday is 0 and Saturday is 6) or
		 * false if this cannot be established.
		 *
		 * @param  mixed $month
		 * @return int|bool
		 */
		public static function first_day_in_month( $month ) {
			try {
				$date  = new DateTime( $month );
				$day_1 = new DateTime( $date->format( 'Y-m-01 ' ) );
				return $day_1->format( 'w' );
			}
			catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Returns the weekday of the last day of the month in
		 * "w" format (ie, Sunday is 0 and Saturday is 6) or
		 * false if this cannot be established.
		 *
		 * @param  mixed $month
		 * @return int|bool
		 */
		public static function last_day_in_month( $month ) {
			try {
				$date  = new DateTime( $month );
				$day_1 = new DateTime( $date->format( 'Y-m-t' ) );
				return $day_1->format( 'w' );
			}
			catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Returns the day of the week the week ends on, expressed as a "w" value
		 * (ie, Sunday is 0 and Saturday is 6).
		 *
		 * @param  int $week_starts_on
		 *
		 * @return int
		 */
		public static function week_ends_on( $week_starts_on ) {
			if ( --$week_starts_on < 0 ) $week_starts_on = 6;
			return $week_starts_on;
		}

		/**
		 * Helper method to convert EventAllDay values to a boolean
		 *
		 * @param mixed $all_day_value Value to check for "all day" status. All day values: (true, 'true', 'TRUE', 'yes')
		 *
		 * @return boolean Is value considered "All Day"?
		 */
		public static function is_all_day( $all_day_value ) {
			$all_day_value = trim( $all_day_value );

			return (
				'true' === strtolower( $all_day_value )
				|| 'yes' === strtolower( $all_day_value )
				|| true === $all_day_value
				|| 1 == $all_day_value
			);
		}

		/**
		 * Given 2 datetime ranges, return whether the 2nd one occurs during the 1st one
		 * Note: all params should be unix timestamps
		 *
		 * @param integer $range_1_start timestamp for start of the first range
		 * @param integer $range_1_end timestamp for end of the first range
		 * @param integer $range_2_start timestamp for start of the second range
		 * @param integer $range_2_end timestamp for end of the second range
		 *
		 * @return bool
		 */
		public static function range_coincides( $range_1_start, $range_1_end, $range_2_start, $range_2_end ) {

			// Initialize the return value
			$range_coincides = false;

			/**
			 * conditions:
			 * range 2 starts during range 1 (range 2 start time is between start and end of range 1 )
			 * range 2 ends during range 1 (range 2 end time is between start and end of range 1 )
			 * range 2 encloses range 1 (range 2 starts before range 1 and ends after range 1)
			 */

			$range_2_starts_during_range_1 = $range_2_start >= $range_1_start && $range_2_start < $range_1_end;
			$range_2_ends_during_range_1   = $range_2_end > $range_1_start && $range_2_end <= $range_1_end;
			$range_2_encloses_range_1      = $range_2_start < $range_1_start && $range_2_end > $range_1_end;

			if ( $range_2_starts_during_range_1 || $range_2_ends_during_range_1 || $range_2_encloses_range_1 ) {
				$range_coincides = true;
			}

			return $range_coincides;

		}

		/**
		 * Converts a locally-formatted date to a unix timestamp. This is a drop-in
		 * replacement for `strtotime()`, except that where strtotime assumes GMT, this
		 * assumes local time (as described below). If a timezone is specified, this
		 * function defers to strtotime().
		 *
		 * If there is a timezone_string available, the date is assumed to be in that
		 * timezone, otherwise it simply subtracts the value of the 'gmt_offset'
		 * option.
		 *
		 * @see  strtotime()
		 * @uses get_option() to retrieve the value of 'gmt_offset'
		 *
		 * @param string $string A date/time string. See `strtotime` for valid formats
		 *
		 * @return int UNIX timestamp.
		 */
		public static function wp_strtotime( $string ) {
			// If there's a timezone specified, we shouldn't convert it
			try {
				$test_date = new DateTime( $string );
				if ( 'UTC' != $test_date->getTimezone()->getName() ) {
					return strtotime( $string );
				}
			} catch ( Exception $e ) {
				return strtotime( $string );
			}

			$tz = get_option( 'timezone_string' );
			if ( ! empty( $tz ) ) {
				$date = date_create( $string, new DateTimeZone( $tz ) );
				if ( ! $date ) {
					return strtotime( $string );
				}
				$date->setTimezone( new DateTimeZone( 'UTC' ) );
				return $date->format( 'U' );
			} else {
				$offset = (float) get_option( 'gmt_offset' );
				$seconds = intval( $offset * HOUR_IN_SECONDS );
				$timestamp = strtotime( $string ) - $seconds;
				return $timestamp;
			}
		}

		/**
		 * Returns an array of localized full month names.
		 *
		 * @return array
		 */
		public static function get_localized_months_full() {
			global $wp_locale;

			if ( empty( self::$localized_months ) ) {
				self::build_localized_months();
			}

			if ( empty( self::$localized_months_full ) ) {
				self::$localized_months_full = [
					'January'   => self::$localized_months['full']['01'],
					'February'  => self::$localized_months['full']['02'],
					'March'     => self::$localized_months['full']['03'],
					'April'     => self::$localized_months['full']['04'],
					'May'       => self::$localized_months['full']['05'],
					'June'      => self::$localized_months['full']['06'],
					'July'      => self::$localized_months['full']['07'],
					'August'    => self::$localized_months['full']['08'],
					'September' => self::$localized_months['full']['09'],
					'October'   => self::$localized_months['full']['10'],
					'November'  => self::$localized_months['full']['11'],
					'December'  => self::$localized_months['full']['12'],
				];
			}

			return self::$localized_months_full;
		}

		/**
		 * Returns an array of localized short month names.
		 *
		 * @return array
		 */
		public static function get_localized_months_short() {
			global $wp_locale;

			if ( empty( self::$localized_months ) ) {
				self::build_localized_months();
			}

			if ( empty( self::$localized_months_short ) ) {
				self::$localized_months_short = [
					'Jan' => self::$localized_months['short']['01'],
					'Feb' => self::$localized_months['short']['02'],
					'Mar' => self::$localized_months['short']['03'],
					'Apr' => self::$localized_months['short']['04'],
					'May' => self::$localized_months['short']['05'],
					'Jun' => self::$localized_months['short']['06'],
					'Jul' => self::$localized_months['short']['07'],
					'Aug' => self::$localized_months['short']['08'],
					'Sep' => self::$localized_months['short']['09'],
					'Oct' => self::$localized_months['short']['10'],
					'Nov' => self::$localized_months['short']['11'],
					'Dec' => self::$localized_months['short']['12'],
				];
			}

			return self::$localized_months_short;
		}

		/**
		 * Returns an array of localized full week day names.
		 *
		 * @return array
		 */
		public static function get_localized_weekdays_full() {
			if ( empty( self::$localized_weekdays ) ) {
				self::build_localized_weekdays();
			}

			return self::$localized_weekdays['full'];
		}

		/**
		 * Returns an array of localized short week day names.
		 *
		 * @return array
		 */
		public static function get_localized_weekdays_short() {
			if ( empty( self::$localized_weekdays ) ) {
				self::build_localized_weekdays();
			}

			return self::$localized_weekdays['short'];
		}

		/**
		 * Returns an array of localized week day initials.
		 *
		 * @return array
		 */
		public static function get_localized_weekdays_initial() {
			if ( empty( self::$localized_weekdays ) ) {
				self::build_localized_weekdays();
			}

			return self::$localized_weekdays['initial'];
		}

		/**
		 * Builds arrays of localized full, short and initialized weekdays.
		 */
		private static function build_localized_weekdays() {
			global $wp_locale;

			for ( $i = 0; $i <= 6; $i++ ) {
				$day = $wp_locale->get_weekday( $i );
				self::$localized_weekdays['full'][ $i ]    = $day;
				self::$localized_weekdays['short'][ $i ]   = $wp_locale->get_weekday_abbrev( $day );
				self::$localized_weekdays['initial'][ $i ] = $wp_locale->get_weekday_initial( $day );
			}
		}

		/**
		 * Builds arrays of localized full and short months.
		 *
		 * @since 4.4.3
		 */
		private static function build_localized_months() {
			global $wp_locale;

			for ( $i = 1; $i <= 12; $i++ ) {
				$month_number = str_pad( $i, 2, '0', STR_PAD_LEFT );
				$month        = $wp_locale->get_month( $month_number );
				self::$localized_months['full'][ $month_number ]  = $month;
				self::$localized_months['short'][ $month_number ] = $wp_locale->get_month_abbrev( $month );
			}
		}

		/**
		 * Return a WP Locale weekday in the specified format
		 *
		 * @since 4.4.3
		 *
		 * @param int|string $weekday Day of week
		 * @param string $format Weekday format: full, weekday, initial, abbreviation, abbrev, abbr, short
		 *
		 * @return string
		 */
		public static function wp_locale_weekday( $weekday, $format = 'weekday' ) {
			$weekday = trim( $weekday );

			$valid_formats = [
				'full',
				'weekday',
				'initial',
				'abbreviation',
				'abbrev',
				'abbr',
				'short',
			];

			// if there isn't a valid format, bail without providing a localized string
			if ( ! in_array( $format, $valid_formats ) ) {
				return $weekday;
			}

			if ( empty( self::$localized_weekdays ) ) {
				self::build_localized_weekdays();
			}

			// if the weekday isn't numeric, we need to convert to numeric in order to
			// leverage self::localized_weekdays
			if ( ! is_numeric( $weekday ) ) {
				$days_of_week = [
					'Sun',
					'Mon',
					'Tue',
					'Wed',
					'Thu',
					'Fri',
					'Sat',
				];

				$day_index = array_search( ucwords( substr( $weekday, 0, 3 ) ), $days_of_week );

				if ( false === $day_index ) {
					return $weekday;
				}

				$weekday = $day_index;
			}

			switch ( $format ) {
				case 'initial':
					$type = 'initial';
					break;
				case 'abbreviation':
				case 'abbrev':
				case 'abbr':
				case 'short':
					$type = 'short';
					break;
				case 'weekday':
				case 'full':
				default:
					$type = 'full';
					break;
			}

			return self::$localized_weekdays[ $type ][ $weekday ];
		}

		/**
		 * Return a WP Locale month in the specified format
		 *
		 * @since 4.4.3
		 *
		 * @param int|string $month Month of year
		 * @param string $format Month format: full, month, abbreviation, abbrev, abbr, short
		 *
		 * @return string
		 */
		public static function wp_locale_month( $month, $format = 'month' ) {
			$month = trim( $month );

			$valid_formats = [
				'full',
				'month',
				'abbreviation',
				'abbrev',
				'abbr',
				'short',
			];

			// if there isn't a valid format, bail without providing a localized string
			if ( ! in_array( $format, $valid_formats ) ) {
				return $month;
			}

			if ( empty( self::$localized_months ) ) {
				self::build_localized_months();
			}

			// make sure numeric months are valid
			if ( is_numeric( $month ) ) {
				$month_num = (int) $month;

				// if the month num falls out of range, bail without localizing
				if ( 0 > $month_num || 12 < $month_num ) {
					return $month;
				}
			} else {
				$months = [
					'Jan',
					'Feb',
					'Mar',
					'Apr',
					'May',
					'Jun',
					'Jul',
					'Aug',
					'Sep',
					'Oct',
					'Nov',
					'Dec',
				];

				// convert the provided month to a 3-character month and find it in the months array so we
				// can build an appropriate month number
				$month_num = array_search( ucwords( substr( $month, 0, 3 ) ), $months );

				// if we can't find the provided month in our month list, bail without localizing
				if ( false === $month_num ) {
					return $month;
				}

				// let's increment the num because months start at 01 rather than 00
				$month_num++;
			}

			$month_num = str_pad( $month_num, 2, '0', STR_PAD_LEFT );

			$type = ( 'full' === $format || 'month' === $format ) ? 'full' : 'short';

			return self::$localized_months[ $type ][ $month_num ];
		}

		// DEPRECATED METHODS
		// @codingStandardsIgnoreStart
		/**
		 * Deprecated camelCase version of self::date_only
		 *
		 * @param int|string $date        The date (timestamp or string).
		 * @param bool       $isTimestamp Is $date in timestamp format?
		 *
		 * @return string The date only in DB format.
		 */
		public static function dateOnly( $date, $isTimestamp = false ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::date_only' );
			return self::date_only( $date, $isTimestamp );
		}

		/**
		 * Deprecated camelCase version of self::time_only
		 *
		 * @param string $date The date.
		 *
		 * @return string The time only in DB format.
		 */
		public static function timeOnly( $date ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::time_only' );
			return self::time_only( $date );
		}

		/**
		 * Deprecated camelCase version of self::hour_only
		 *
		 * @param string $date The date.
		 *
		 * @return string The hour only.
		 */
		public static function hourOnly( $date ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::hour_only' );
			return self::hour_only( $date );
		}

		/**
		 * Deprecated camelCase version of self::minutes_only
		 *
		 * @param string $date The date.
		 *
		 * @return string The minute only.
		 */
		public static function minutesOnly( $date ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::minutes_only' );
			return self::minutes_only( $date );
		}

		/**
		 * Deprecated camelCase version of self::meridian_only
		 *
		 * @param string $date The date.
		 *
		 * @return string The meridian only in DB format.
		 */
		public static function meridianOnly( $date ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::meridian_only' );
			return self::meridian_only( $date );
		}

		/**
		 * Returns the end of a given day.
		 *
		 * @deprecated since 3.10 - use tribe_event_end_of_day()
		 * @todo       remove in 4.1
		 *
		 * @param int|string $date        The date (timestamp or string).
		 * @param bool       $isTimestamp Is $date in timestamp format?
		 *
		 * @return string The date and time of the end of a given day
		 */
		public static function endOfDay( $date, $isTimestamp = false ) {
			_deprecated_function( __METHOD__, '3.10', 'tribe_event_end_of_day' );

			if ( $isTimestamp ) {
				$date = date( self::DBDATEFORMAT, $date );
			}

			return tribe_event_end_of_day( $date, self::DBDATETIMEFORMAT );
		}

		/**
		 * Returns the beginning of a given day.
		 *
		 * @deprecated since 3.10
		 * @todo       remove in 4.1
		 *
		 * @param int|string $date        The date (timestamp or string).
		 * @param bool       $isTimestamp Is $date in timestamp format?
		 *
		 * @return string The date and time of the beginning of a given day.
		 */
		public static function beginningOfDay( $date, $isTimestamp = false ) {
			_deprecated_function( __METHOD__, '3.10', 'tribe_event_beginning_of_day' );

			if ( $isTimestamp ) {
				$date = date( self::DBDATEFORMAT, $date );
			}

			return tribe_event_beginning_of_day( $date, self::DBDATETIMEFORMAT );
		}

		/**
		 * Deprecated camelCase version of self::time_between
		 *
		 * @param string $date1 The first date.
		 * @param string $date2 The second date.
		 *
		 * @return int The number of seconds between the dates.
		 */
		public static function timeBetween( $date1, $date2 ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::time_between' );
			return self::time_between( $date1, $date2 );
		}

		/**
		 * Deprecated camelCase version of self::date_diff
		 *
		 * @param string $date1 The first date.
		 * @param string $date2 The second date.
		 *
		 * @return int The number of days between two dates.
		 */
		public static function dateDiff( $date1, $date2 ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::date_diff' );
			return self::date_diff( $date1, $date2 );
		}

		/**
		 * Deprecated camelCase version of self::get_last_day_of_month
		 *
		 * @param int $timestamp THe timestamp.
		 *
		 * @return string The last day of the month.
		 */
		public static function getLastDayOfMonth( $timestamp ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::get_last_day_of_month' );
			return self::get_last_day_of_month( $timestamp );
		}

		/**
		 * Deprecated camelCase version of self::is_weekday
		 *
		 * @param int $curDate A timestamp.
		 *
		 * @return bool If the timestamp is a weekday.
		 */
		public static function isWeekday( $curdate ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::is_weekday' );
			return self::is_weekday( $curdate );
		}

		/**
		 * Deprecated camelCase version of self::is_weekend
		 *
		 * @param int $curDate A timestamp.
		 *
		 * @return bool If the timestamp is a weekend.
		 */
		public static function isWeekend( $curdate ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::is_weekend' );
			return self::is_weekend( $curdate );
		}

		/**
		 * Deprecated camelCase version of self::get_last_day_of_week_in_month
		 *
		 * @param int $curdate     A timestamp.
		 * @param int $day_of_week The index of the day of the week.
		 *
		 * @return int The timestamp of the date that fits the qualifications.
		 */
		public static function getLastDayOfWeekInMonth( $curdate, $day_of_week ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::get_last_day_of_week_in_month' );
			return self::get_last_day_of_week_in_month( $curdate, $day_of_week );
		}

		/**
		 * Deprecated camelCase version of self::get_first_day_of_week_in_month
		 *
		 * @param int $curdate     A timestamp.
		 * @param int $day_of_week The index of the day of the week.
		 *
		 * @return int The timestamp of the date that fits the qualifications.
		 */
		public static function getFirstDayOfWeekInMonth( $curdate, $day_of_week ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::get_fist_day_of_week_in_month' );
			return self::get_first_day_of_week_in_month( $curdate, $day_of_week );
		}

		/**
		 * Deprecated camelCase version of self::number_to_ordinal
		 *
		 * @param int $number A number.
		 *
		 * @return string The ordinal for that number.
		 */
		public static function numberToOrdinal( $number ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::number_to_ordinal' );
			return self::number_to_ordinal( $number );
		}

		/**
		 * Deprecated camelCase version of self::is_timestamp
		 *
		 * @param $timestamp
		 *
		 * @return bool
		 */
		public static function isTimestamp( $timestamp ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::is_timestamp' );
			return self::is_timestamp( $timestamp );
		}

		/**
		 * Gets the timestamp of a day in week, month and year context.
		 *
		 * Kudos to [icedwater StackOverflow user](http://stackoverflow.com/users/1091386/icedwater) in
		 * [his answer](http://stackoverflow.com/questions/924246/get-the-first-or-last-friday-in-a-month).
		 *
		 * Usage examples:
		 * "The second Wednesday of March 2015" - `get_day_timestamp( 3, 2, 3, 2015, 1)`
		 * "The last Friday of December 2015" - `get_day_timestamp( 5, 1, 12, 2015, -1)`
		 * "The first Monday of April 2016 - `get_day_timestamp( 1, 1, 4, 2016, 1)`
		 * "The penultimate Thursday of January 2012" - `get_day_timestamp( 4, 2, 1, 2012, -1)`
		 *
		 * @param int $day_of_week    The day representing the number in the week, Monday is `1`, Tuesday is `2`, Sunday is `7`
		 * @param int $week_in_month  The week number in the month; first week is `1`, second week is `2`; when direction is reverse
		 *                  then `1` is last week of the month, `2` is penultimate week of the month and so on.
		 * @param int $month          The month number in the year, January is `1`
		 * @param int $year           The year number, e.g. "2015"
		 * @param int $week_direction Either `1` or `-1`; the direction for the search referring to the week, defaults to `1`
		 *                       to specify weeks in natural order so:
		 *                       $week_direction `1` and $week_in_month `1` means "first week of the month"
		 *                       $week_direction `1` and $week_in_month `3` means "third week of the month"
		 *                       $week_direction `-1` and $week_in_month `1` means "last week of the month"
		 *                       $week_direction `-1` and $week_in_month `2` means "penultimmate week of the month"
		 *
		 * @return int The day timestamp
		 */
		public static function get_weekday_timestamp( $day_of_week, $week_in_month, $month, $year, $week_direction = 1 ) {
			if (
				! (
					is_numeric( $day_of_week )
					&& is_numeric( $week_in_month )
					&& is_numeric( $month )
					&& is_numeric( $year )
					&& is_numeric( $week_direction )
					&& in_array( $week_direction, [ -1, 1 ] )
				)
			) {
				return false;
			}

			if ( $week_direction > 0 ) {
				$startday = 1;
			} else {
				$startday = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
			}

			$start   = mktime( 0, 0, 0, $month, $startday, $year );
			$weekday = date( 'N', $start );

			if ( $week_direction * $day_of_week >= $week_direction * $weekday ) {
				$offset = - $week_direction * 7;
			} else {
				$offset = 0;
			}

			$offset += $week_direction * ( $week_in_month * 7 ) + ( $day_of_week - $weekday );

			return mktime( 0, 0, 0, $month, $startday + $offset, $year );
		}

		/**
		 * Unescapes date format strings to be used in functions like `date`.
		 *
		 * Double escaping happens when storing a date format in the database.
		 *
		 * @param mixed $date_format A date format string.
		 *
		 * @return mixed Either the original input or an unescaped date format string.
		 */
		public static function unescape_date_format( $date_format ) {
			if ( ! is_string( $date_format ) ) {
				return $date_format;
			}

			// Why so simple? Let's handle other cases as those come up. We have tests in place!
			return str_replace( '\\\\', '\\', $date_format );
		}

		/**
		 * Builds a date object from a given datetime and timezone.
		 *
		 * @since 4.9.5
		 *
		 * @param string|DateTime|int      $datetime      A `strtotime` parse-able string, a DateTime object or
		 *                                                a timestamp; defaults to `now`.
		 * @param string|DateTimeZone|null $timezone      A timezone string, UTC offset or DateTimeZone object;
		 *                                                defaults to the site timezone; this parameter is ignored
		 *                                                if the `$datetime` parameter is a DatTime object.
		 * @param bool                     $with_fallback Whether to return a DateTime object even when the date data is
		 *                                                invalid or not; defaults to `true`.
		 *
		 * @return DateTime|false A DateTime object built using the specified date, time and timezone; if `$with_fallback`
		 *                        is set to `false` then `false` will be returned if a DateTime object could not be built.
		 */
		public static function build_date_object( $datetime = 'now', $timezone = null, $with_fallback = true ) {
			if ( $datetime instanceof DateTime ) {
				return clone $datetime;
			}

			if ( class_exists( 'DateTimeImmutable' ) && $datetime instanceof DateTimeImmutable ) {
				// Return the mutable version of the date.
				return Date_I18n::createFromImmutable( $datetime );
			}

			$timezone_object = null;
			$datetime = empty( $datetime ) ? 'now' : $datetime;

			try {
				// PHP 5.2 will not throw an exception but will generate an error.
				$utc = new DateTimeZone( 'UTC' );
				$timezone_object = Tribe__Timezones::build_timezone_object( $timezone );

				if ( self::is_timestamp( $datetime ) ) {
					$timestamp_timezone = $timezone ? $timezone_object : $utc;

					return new Date_I18n( '@' . $datetime, $timestamp_timezone );
				}

				set_error_handler( 'tribe_catch_and_throw' );
				$date = new Date_I18n( $datetime, $timezone_object );
				restore_error_handler();
			} catch ( Exception $e ) {
				// If we encounter an error, we need to restore after catching.
				restore_error_handler();

				if ( $timezone_object === null ) {
					$timezone_object = Tribe__Timezones::build_timezone_object( $timezone );
				}

				return $with_fallback
					? new Date_I18n( 'now', $timezone_object )
					: false;
			}

			return $date;
		}

		/**
		 * Validates a date string to make sure it can be used to build DateTime objects.
		 *
		 * @since 4.9.5
		 *
		 * @param string $date The date string that should validated.
		 *
		 * @return bool Whether the date string can be used to build DateTime objects, and is thus parse-able by functions
		 *              like `strtotime`, or not.
		 */
		public static function is_valid_date( $date ) {
			static $cache_var_name = __FUNCTION__;

			$cache_date_check = tribe_get_var( $cache_var_name, [] );

			if ( isset( $cache_date_check[ $date ] ) ) {
				return $cache_date_check[ $date ];
			}

			$cache_date_check[ $date ] = self::build_date_object( $date, null, false ) instanceof DateTimeInterface;

			tribe_set_var( $cache_var_name, $cache_date_check );

			return $cache_date_check[ $date ];
		}

		/**
		 * Returns the DateTime object representing the start of the week for a date.
		 *
		 * @since 4.9.21
		 *
		 * @throws Exception
		 *
		 * @param string|int|\DateTime $date          The date string, timestamp or object.
		 * @param int|null             $start_of_week The number representing the start of week day as handled by
		 *                                            WordPress: `0` (for Sunday) through `6` (for Saturday).
		 *
		 * @return array An array of objects representing the week start and end days, or `false` if the
		 *                        supplied date is invalid. The timezone of the returned object is set to the site one.
		 *                        The week start has its time set to `00:00:00`, the week end will have its time set
		 *                        `23:59:59`.
		 */
		public static function get_week_start_end( $date, $start_of_week = null ) {
			static $cache_var_name = __FUNCTION__;

			$cache_week_start_end = tribe_get_var( $cache_var_name, [] );

			$date_obj = static::build_date_object( $date );
			$date_obj->setTime( 0, 0, 0 );

			$date_string = $date_obj->format( static::DBDATEFORMAT );

			// `0` (for Sunday) through `6` (for Saturday), the way WP handles the `start_of_week` option.
			$week_start_day = null !== $start_of_week
				? (int) $start_of_week
				: (int) get_option( 'start_of_week', 0 );

			$memory_cache_key = "{$date_string}:{$week_start_day}";

			if ( isset( $cache_week_start_end[ $memory_cache_key ] ) ) {
				return $cache_week_start_end[ $memory_cache_key ];
			}

			$cache_key = md5(
				__METHOD__ . serialize( [ $date_obj->format( static::DBDATEFORMAT ), $week_start_day ] )
			);
			$cache = tribe( 'cache' );

			if ( false !== $cached = $cache[ $cache_key ] ) {
				return $cached;
			}

			// `0` (for Sunday) through `6` (for Saturday), the way WP handles the `start_of_week` option.
			$date_day = (int) $date_obj->format( 'w' );

			$week_offset = 0;
			if ( 0 === $date_day && 0 !== $week_start_day ) {
				$week_offset = 0;
			} elseif ( $date_day < $week_start_day ) {
				// If the current date of the week is before the start of the week, move back a week.
				$week_offset = -1;
			} elseif ( 0 === $date_day ) {
				// When start of the week is on a sunday we add a week.
				$week_offset = 1;
			}

			$week_start = clone $date_obj;

			/*
			 * From the PHP docs, the `W` format stands for:
			 * - ISO-8601 week number of year, weeks starting on Monday
			 */
			$week_start->setISODate(
				(int) $week_start->format( 'o' ),
				(int) $week_start->format( 'W' ) + $week_offset,
				$week_start_day
			);

			$week_end = clone $week_start;
			// Add 6 days, then move at the end of the day.
			$week_end->add( new DateInterval( 'P6D' ) );
			$week_end->setTime( 23, 59, 59 );

			$week_start = static::immutable( $week_start );
			$week_end   = static::immutable( $week_end );

			$cache[ $cache_key ]                       = [ $week_start, $week_end ];
			$cache_week_start_end[ $memory_cache_key ] = [ $week_start, $week_end ];

			tribe_set_var( $cache_var_name, $cache_week_start_end );

			return [ $week_start, $week_end ];
		}

		/**
		 * Given a specific DateTime we determine the end of that day based on our Internal End of Day Cut-off.
		 *
		 * @since 4.11.2
		 *
		 * @param string|DateTimeInterface $date    Date that we are getting the end of day from.
		 * @param null|string              $cutoff  Which cutoff to use.
		 *
		 * @return DateTimeInterface|false Returns a DateTimeInterface when a valid date is given or false.
		 */
		public static function get_shifted_end_of_day( $date, $cutoff = null ) {
			$date_obj = static::build_date_object( $date );

			if ( ! $date_obj ) {
				return false;
			}

			$start_of_day = clone $date_obj;
			$end_of_day   = clone $date_obj;

			if ( empty( $cutoff ) || ! is_string( $cutoff ) || false === strpos( $cutoff, ':' ) ) {
				$cutoff = tribe_get_option( 'multiDayCutoff', '00:00' );
			}

			list( $hours_to_add, $minutes_to_add ) = array_map( 'absint', explode( ':', $cutoff ) );

			$seconds_to_add = ( $hours_to_add * HOUR_IN_SECONDS ) + ( $minutes_to_add * MINUTE_IN_SECONDS );
			if ( 0 !== $seconds_to_add ) {
				$interval = static::interval( "PT{$seconds_to_add}S" );
			}

			$start_of_day->setTime( '0', '0', '0' );
			$end_of_day->setTime( '23', '59', '59' );

			if ( 0 !== $seconds_to_add ) {
				$start_of_day->add( $interval );
				$end_of_day->add( $interval );
			}

			if ( $end_of_day >= $date_obj && $date_obj >= $start_of_day ) {
				return $end_of_day;
			}

			$start_of_day->sub( static::interval( 'P1D' ) );

			if ( $start_of_day < $date_obj ) {
				$end_of_day->sub( static::interval( 'P1D' ) );
			}

			return $end_of_day;
		}

		/**
		 * Given a specific DateTime we determine the start of that day based on our Internal End of Day Cut-off.
		 *
		 * @since 4.11.2
		 *
		 * @param string|DateTimeInterface $date    Date that we are getting the start of day from.
		 * @param null|string              $cutoff  Which cutoff to use.
		 *
		 * @return DateTimeInterface|false Returns a DateTimeInterface when a valid date is given or false.
		 */
		public static function get_shifted_start_of_day( $date, $cutoff = null ) {
			$date_obj = static::build_date_object( $date );

			if ( ! $date_obj ) {
				return false;
			}

			$start_of_day = clone $date_obj;
			$end_of_day   = clone $date_obj;

			if ( empty( $cutoff ) || ! is_string( $cutoff ) || false === strpos( $cutoff, ':' ) ) {
				$cutoff = tribe_get_option( 'multiDayCutoff', '00:00' );
			}

			list( $hours_to_add, $minutes_to_add ) = array_map( 'absint', explode( ':', $cutoff ) );

			$seconds_to_add = ( $hours_to_add * HOUR_IN_SECONDS ) + ( $minutes_to_add * MINUTE_IN_SECONDS );
			if ( 0 !== $seconds_to_add ) {
				$interval = static::interval( "PT{$seconds_to_add}S" );
			}

			$start_of_day->setTime( '0', '0', '0' );
			$end_of_day->setTime( '23', '59', '59' );

			if ( 0 !== $seconds_to_add ) {
				$start_of_day->add( $interval );
				$end_of_day->add( $interval );
			}

			if ( $end_of_day <= $date_obj && $date_obj >= $start_of_day ) {
				return $start_of_day;
			}

			$end_of_day->sub( static::interval( 'P1D' ) );

			if ( $end_of_day > $date_obj ) {
				$start_of_day->sub( static::interval( 'P1D' ) );
			}

			return $start_of_day;
		}

		/**
		 * Builds and returns a `DateInterval` object from the interval specification.
		 *
		 * For performance purposes the use of `DateInterval` specifications is preferred, so `P1D` is better than
		 * `1 day`.
		 *
		 * @since 4.10.2
		 *
		 * @return DateInterval The built date interval object.
		 */
		public static function interval( $interval_spec ) {
			try {
				$interval = new \DateInterval( $interval_spec );
			} catch ( \Exception $e ) {
				$interval = DateInterval::createFromDateString( $interval_spec );
			}

			return $interval;
		}

		/**
		 * Builds the immutable version of a date from a string, integer (timestamp) or \DateTime object.
		 *
		 * It's the immutable version of the `Tribe__Date_Utils::build_date_object` method.
		 *
		 * @since 4.10.2
		 *
		 * @param string|DateTime|int      $datetime      A `strtotime` parse-able string, a DateTime object or
		 *                                                a timestamp; defaults to `now`.
		 * @param string|DateTimeZone|null $timezone      A timezone string, UTC offset or DateTimeZone object;
		 *                                                defaults to the site timezone; this parameter is ignored
		 *                                                if the `$datetime` parameter is a DatTime object.
		 * @param bool                     $with_fallback Whether to return a DateTime object even when the date data is
		 *                                                invalid or not; defaults to `true`.
		 *
		 * @return DateTimeImmutable|false A DateTime object built using the specified date, time and timezone; if
		 *                                 `$with_fallback` is set to `false` then `false` will be returned if a
		 *                                 DateTime object could not be built.
		 */
		static function immutable( $datetime = 'now', $timezone = null, $with_fallback = true ) {
			if ( $datetime instanceof DateTimeImmutable ) {
				return $datetime;
			}

			if ( $datetime instanceof DateTime ) {
				return Date_I18n_Immutable::createFromMutable( $datetime );
			}

			$mutable = static::build_date_object( $datetime, $timezone, $with_fallback );

			if ( false === $mutable ) {
				return false;
			}

			$cache_key = md5( ( __METHOD__ . $mutable->getTimezone()->getName() . $mutable->getTimestamp() ) );
			$cache     = tribe( 'cache' );

			if ( false !== $cached = $cache[ $cache_key ] ) {
				return $cached;
			}

			$immutable = Date_I18n_Immutable::createFromMutable( $mutable );

			$cache[ $cache_key ] = $immutable;

			return $immutable;
		}

		/**
		 * Builds a date object from a given datetime and timezone.
		 *
		 * An alias of the `Tribe__Date_Utils::build_date_object` function.
		 *
		 * @since 4.10.2
		 *
		 * @param string|DateTime|int      $datetime      A `strtotime` parse-able string, a DateTime object or
		 *                                                a timestamp; defaults to `now`.
		 * @param string|DateTimeZone|null $timezone      A timezone string, UTC offset or DateTimeZone object;
		 *                                                defaults to the site timezone; this parameter is ignored
		 *                                                if the `$datetime` parameter is a DatTime object.
		 * @param bool                     $with_fallback Whether to return a DateTime object even when the date data is
		 *                                                invalid or not; defaults to `true`.
		 *
		 * @return DateTime|false A DateTime object built using the specified date, time and timezone; if `$with_fallback`
		 *                        is set to `false` then `false` will be returned if a DateTime object could not be built.
		 */
		public static function mutable( $datetime = 'now', $timezone = null, $with_fallback = true ) {
			return static::build_date_object( $datetime, $timezone, $with_fallback );
		}
	}
}
