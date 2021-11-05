import { range } from 'lodash';

// For PHP symbols, see https://www.php.net/manual/en/datetime.format.php
// For Moment.js symbols, see https://momentjs.com/docs/#/displaying/format/
const PHP_DATE_FORMAT_REPLACEMENTS = new Map( [
	[ 'A', 'A' ], // for the sake of escaping below
	[ 'a', 'a' ], // for the sake of escaping below
	[ 'B', '' ], // Swatch internet time (.beats), no equivalent
	[ 'c', 'YYYY-MM-DD[T]HH:mm:ssZ' ], // ISO 8601
	[ 'D', 'ddd' ],
	[ 'd', 'DD' ],
	[ 'e', 'zz' ], // deprecated since version 1.6.0 of moment.js
	[ 'F', 'MMMM' ],
	[ 'G', 'H' ],
	[ 'g', 'h' ],
	[ 'H', 'HH' ],
	[ 'h', 'hh' ],
	[ 'I', '' ], // Daylight Saving Time? => moment().isDST();
	[ 'i', 'mm' ],
	[ 'j', 'D' ],
	[ 'L', '' ], // Leap year? => moment().isLeapYear();
	[ 'l', 'dddd' ],
	[ 'M', 'MMM' ],
	[ 'm', 'MM' ],
	[ 'N', 'E' ],
	[ 'n', 'M' ],
	[ 'O', 'ZZ' ],
	[ 'o', 'YYYY' ],
	[ 'P', 'Z' ],
	[ 'r', 'ddd, DD MMM YYYY HH:mm:ss ZZ' ], // RFC 2822
	[ 'S', 'o' ],
	[ 's', 'ss' ],
	[ 'T', 'z' ], // deprecated since version 1.6.0 of moment.js
	[ 't', '' ], // days in the month => moment().daysInMonth();
	[ 'U', 'X' ],
	[ 'u', 'SSSSSS' ], // microseconds
	[ 'v', 'SSS' ], // milliseconds (from PHP 7.0.0)
	[ 'W', 'W' ], // for the sake of escaping below
	[ 'w', 'e' ],
	[ 'Y', 'YYYY' ],
	[ 'y', 'YY' ],
	[ 'Z', '' ], // time zone offset in minutes => moment().zone();
	[ 'z', 'DDD' ],
] );

// For jQuery symbols, see https://api.jqueryui.com/datepicker/#utility-formatDate
// For Moment.js symbols, see https://momentjs.com/docs/#/displaying/format/
//
// Note that the order is important here: double letters need to come before
// single letters, so that the regex matches correctly.
const JQUERY_DATE_FORMAT_REPLACEMENTS = new Map( [
	[ 'dd', 'DD' ], // day of month (two digit)
	[ 'd', 'D' ], // day of month (no leading zero)
	[ 'oo', 'DDDD' ], // day of the year (three digit)
	[ 'o', 'DDD' ], // day of the year (no leading zeros)
	[ 'DD', 'dddd' ], // day name long
	[ 'D', 'dd' ], // day name short
	[ 'mm', 'MM' ], // month of year (two digit)
	[ 'm', 'M' ], // month of year (no leading zero)
	[ 'MM', 'MMMM' ], // month name long
	[ 'M', 'MMM' ], // month name short
	[ 'yy', 'YYYY' ], // year (four digit)
	[ 'y', 'YY' ], // year (two digit)
	[ '@', 'X' ], // Unix timestamp (ms since 01/01/1970)
	[ '!', '' ], // Windows ticks (100ns since 01/01/0001), no equivalent
] );

// For jQuery symbols, see http://trentrichardson.com/examples/timepicker/#tp-formatting
// For Moment.js symbols, see https://momentjs.com/docs/#/displaying/format/
//
// Note that the order is important here: double letters need to come before
// single letters, so that the regex matches correctly.
const JQUERY_TIME_FORMAT_REPLACEMENTS = new Map( [
	// Times
	[ 'H', 'H' ], // Hour with no leading 0 (24 hour)
	[ 'HH', 'HH' ], // Hour with leading 0 (24 hour)
	[ 'h', 'h' ], // Hour with no leading 0 (12 hour)
	[ 'hh', 'hh' ], // Hour with leading 0 (12 hour)
	[ 'm', 'm' ], // Minute with no leading 0
	[ 'mm', 'mm' ], // Minute with leading 0
	[ 'i', 'mm' ], // In case they got confused with PHP time format
	[ 's', 's' ], // Second with no leading 0
	[ 'ss', 'ss' ], // Second with leading 0
	[ 'l', 'SSS' ], // Milliseconds always with leading 0
	[ 'c', 'SSSSSS' ], // Microseconds always with leading 0
	[ 't', 'a' ], // a or p for AM/PM, no equivalent, switches to am/pm
	[ 'T', 'A' ], // A or P for AM/PM, no equivalent, switches to AM/PM
	[ 'tt', 'a' ], // am or pm for AM/PM
	[ 'TT', 'A' ], // AM or PM for AM/PM
	[ 'z', '' ], // Timezone as defined by timezoneList => moment().zone();
	[ 'Z', '' ], // Timezone in Iso 8601 format (+04:45) => moment().zone();
] );

const replaceAllMapEntries = ( map, value ) => {
	if ( 'string' !== typeof value ) {
		return '';
	}

	const regexString = '(?<!\\\\)(' + Array.from( map.keys() ).join( '|' ) + ')';
	const findRegex = new RegExp( regexString, 'g' );

	return value.replace(
		findRegex,
		( matched ) => map.get( matched ),
	);
};

export const convertPHPDateFormatToMomentFormat = ( phpFormat ) => replaceAllMapEntries(
	PHP_DATE_FORMAT_REPLACEMENTS,
	phpFormat
);

export const convertjQueryUIDateFormatToMomentFormat = ( jqueryFormat ) => replaceAllMapEntries(
	JQUERY_DATE_FORMAT_REPLACEMENTS,
	jqueryFormat
);

export const convertjQueryUITimeFormatToMomentFormat = ( jqueryFormat ) => replaceAllMapEntries(
	JQUERY_TIME_FORMAT_REPLACEMENTS,
	jqueryFormat
);

export const convertPodsDateFormatToMomentFormat = ( podsFormat, is24Hour = false ) => {
	switch ( podsFormat ) {
		// Date formats
		case 'mdy':
			return convertPHPDateFormatToMomentFormat( 'm/d/Y' );
		case 'mdy_dash':
			return convertPHPDateFormatToMomentFormat( 'm-d-Y' );
		case 'mdy_dot':
			return convertPHPDateFormatToMomentFormat( 'm.d.Y' );
		case 'ymd_slash':
			return convertPHPDateFormatToMomentFormat( 'Y/m/d' );
		case 'ymd_dash':
			return convertPHPDateFormatToMomentFormat( 'Y-m-d' );
		case 'ymd_dot':
			return convertPHPDateFormatToMomentFormat( 'Y.m.d' );
		case 'dmy':
			return convertPHPDateFormatToMomentFormat( 'd/m/Y' );
		case 'dmy_dash':
			return convertPHPDateFormatToMomentFormat( 'd-m-Y' );
		case 'dmy_dot':
			return convertPHPDateFormatToMomentFormat( 'd.m.Y' );
		case 'dMy':
			return convertPHPDateFormatToMomentFormat( 'd/M/Y' );
		case 'dMy_dash':
			return convertPHPDateFormatToMomentFormat( 'd-M-Y' );
		case 'fjy':
			return convertPHPDateFormatToMomentFormat( 'F j, Y' );
		case 'fjsy':
			return convertPHPDateFormatToMomentFormat( 'F jS, Y' );
		case 'c':
			return convertPHPDateFormatToMomentFormat( 'c' );
		// Time formats
		case 'h_mm_A':
			return convertPHPDateFormatToMomentFormat( 'g:i A' );
		case 'h_mm_ss_A':
			return convertPHPDateFormatToMomentFormat( 'g:i:s A' );
		case 'hh_mm_A':
			return convertPHPDateFormatToMomentFormat( 'h:i A' );
		case 'hh_mm_ss_A':
			return convertPHPDateFormatToMomentFormat( 'h:i:s A' );
		case 'h_mma':
			return convertPHPDateFormatToMomentFormat( 'g:ia' );
		case 'hh_mma':
			return convertPHPDateFormatToMomentFormat( 'h:ia' );
		case 'h_mm':
			return convertPHPDateFormatToMomentFormat( 'g:i' );
		case 'h_mm_ss':
			return convertPHPDateFormatToMomentFormat( 'g:i:s' );
		case 'hh_mm':
			return convertPHPDateFormatToMomentFormat( is24Hour ? 'H:i' : 'h:i' );
		case 'hh_mm_ss':
			return convertPHPDateFormatToMomentFormat( is24Hour ? 'H:i:s' : 'h:i:s' );
		default:
			return '';
	}
};

// Parse range of years, either relative to today's year (-nn:+nn),
// relative to currently displayed year (c-nn:c+nn), absolute (nnnn:nnnn),
// or a combination of the above (nnnn:-n).
export const getArrayOfYearsFromJqueryUIYearRange = ( yearRange, thisYear, displayedYear ) => {
	if ( ! yearRange ) {
		return undefined;
	}

	const years = yearRange.split( ':' );

	const determineYear = ( value ) => {
		let result = thisYear;

		if ( value.match( /c[+\-].*/ ) ) {
			// Relative to the displayed year.
			result = displayedYear + parseInt( value.substring( 1 ), 10 );
		} else if ( value.match( /[+\-].*/ ) ) {
			// Relative to the current year.
			result = thisYear + parseInt( value, 10 );
		} else {
			// Absolute year.
			result = parseInt( value );
		}

		return isNaN( result ) ? thisYear : result;
	};

	const startYear = determineYear( years[ 0 ] );
	const endYear = Math.max( startYear, determineYear( years[ 1 ] || '' ) );

	return range( startYear, endYear + 1 );
};
