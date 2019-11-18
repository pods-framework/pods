/**
 * External dependencies
 */
import zeroFill from 'zero-fill';

export const MINUTE_IN_SECONDS = 60;
export const HALF_HOUR_IN_SECONDS = MINUTE_IN_SECONDS * 30;
export const HOUR_IN_SECONDS = 60 * MINUTE_IN_SECONDS;
export const DAY_IN_SECONDS = 24 * HOUR_IN_SECONDS;

export const START_OF_DAY = '00:00';
export const END_OF_DAY = '23:59';

/**
 * Round the time of a time string
 * If the minutes is lower than 30, it will round the minutes to 0
 * If the minutes is greater than or equal to 30, it will round the minutes to 30
 *
 * @param {string} time
 * @returns {moment} A moment object
 */
export const roundTime = ( time, format = TIME_FORMAT_MM_SS ) => {
	const seconds = toSeconds( time, format );
	const overage = seconds % ( MINUTE_IN_SECONDS * 30 );
	const roundedSeconds = seconds - overage;
	return fromSeconds( roundedSeconds, format );
}

/**
 * The code below is copied from the library hh-mm-ss
 * Link: https://www.npmjs.com/package/hh-mm-ss
 * The code has been copied so that Modern Tribe can maintain this library
 * internally and adjust as needed.
 */
export const TIME_FORMAT_HH_MM_SS_SSS = 'hh:mm:ss.sss';
export const TIME_FORMAT_HH_MM_SS = 'hh:mm:ss';
export const TIME_FORMAT_HH_MM = 'hh:mm';
export const TIME_FORMAT_MM_SS_SSS = 'mm:ss.sss';
export const TIME_FORMAT_MM_SS = 'mm:ss';

export const SECOND_IN_MS = 1000;
export const MINUTE_IN_MS = MINUTE_IN_SECONDS * SECOND_IN_MS;
export const HOUR_IN_MS = HOUR_IN_SECONDS * SECOND_IN_MS;

/**
 * Converts milliseconds to time in the format provided
 *
 * @param {int} ms Milliseconds to convert from
 * @param {string} format Format of time to convert to
 * @returns {string} Time string equivalent of milliseconds in format provided
 */
export const fromMilliseconds = ( ms, format = TIME_FORMAT_MM_SS ) => {
	if ( typeof ms !== 'number' || Number.isNaN( ms ) ) {
		/* eslint-disable-next-line max-len */
		throw new Error( 'Argument `ms` provided to `fromMilliseconds` is not a number or is NaN.' );
	}

	const absMs = Math.abs( ms );

	const negative = ( ms < 0 );
	const hours = Math.floor( absMs / HOUR_IN_MS );
	const minutes = Math.floor( absMs % HOUR_IN_MS / MINUTE_IN_MS );
	const seconds = Math.floor( absMs % MINUTE_IN_MS / SECOND_IN_MS );
	const miliseconds = Math.floor( absMs % SECOND_IN_MS );

	return formatTime( {
		negative,
		hours,
		minutes,
		seconds,
		miliseconds,
	}, format );
};

/**
 * Converts seconds to time in the format provided
 *
 * @param {int} s Seconds to convert from
 * @param {string} format Format of time to convert to
 * @returns {string} Time string equivalent of seconds in format provided
 */
export const fromSeconds = ( s, format = TIME_FORMAT_MM_SS ) => {
	if ( typeof s !== 'number' || Number.isNaN( s ) ) {
 		/* eslint-disable-next-line max-len */
		throw new Error( 'Argument `s` provided to `fromSeconds` is not a number or is NaN.' );
	}

	const ms = s * SECOND_IN_MS;

	return fromMilliseconds( ms, format );
};

/**
 * Converts time in the format provided to milliseconds
 *
 * @param {string} time Time string to convert from
 * @param {string} format Format of time to convert from
 * @returns {int} Milliseconds equivalent of time string in format provided
 */
export const toMilliseconds = ( time, format = TIME_FORMAT_MM_SS ) => {
	let re;

	if ( [
		TIME_FORMAT_HH_MM_SS_SSS,
		TIME_FORMAT_HH_MM_SS,
		TIME_FORMAT_MM_SS_SSS,
		TIME_FORMAT_MM_SS,
	].includes( format ) ) {
		re = /^(-)?(?:(\d\d+):)?(\d\d):(\d\d)(\.\d+)?$/;
	} else if ( format === TIME_FORMAT_HH_MM ) {
		re = /^(-)?(\d\d):(\d\d)(?::(\d\d)(?:(\.\d+))?)?$/;
	} else {
 		/* eslint-disable-next-line max-len */
		throw new Error( 'Argument `format` provided to `toMilliseconds` is not a recognized format.' );
	}

	const result = re.exec( time );
	if ( ! result ) {
		/* eslint-disable-next-line max-len */
		throw new Error( 'Argument `time` provided to `toMilliseconds` is not a recognized format.' );
	}

	const negative = result[ 1 ] === '-';
	const hours = result[ 2 ] | 0;
	const minutes = result[ 3 ] | 0;
	const seconds = result[ 4 ] | 0;
	const miliseconds = Math.floor( 1000 * result[ 5 ] | 0 );

	if ( minutes >= 60 || seconds >= 60 ) {
		/* eslint-disable-next-line max-len */
		throw new Error( 'Argument `time` provided to `toMilliseconds` contains minutes or seconds greater than 59.' );
	}

	return ( negative ? -1 : 1 ) * (
		hours * HOUR_IN_MS
		+ minutes * MINUTE_IN_MS
		+ seconds * SECOND_IN_MS
		+ miliseconds
	);
};

/**
 * Converts time in the format provided to seconds
 *
 * @param {string} time Time string to convert from
 * @param {string} format Format of time to convert from
 * @returns {int} Seconds equivalent of time string in format provided
 */
export const toSeconds = ( time, format = TIME_FORMAT_MM_SS ) => {
	const ms = toMilliseconds( time, format );
	return Math.floor( ms / SECOND_IN_MS );
};

/**
 * Formats time object to time string in the format provided
 *
 * @param {object} time Time object to format from
 * @param {string} format Format of time to format to
 * @returns {string} Time string in format provided
 */
export const formatTime = ( time, format ) => {
	let showMs;
	let showSc;
	let showHr;

	switch ( format ) {
		case TIME_FORMAT_HH_MM_SS_SSS:
			showMs = true;
			showSc = true;
			showHr = true;
			break;
		case TIME_FORMAT_HH_MM_SS:
			showMs = ! ! time.miliseconds;
			showSc = true;
			showHr = true;
			break;
		case TIME_FORMAT_HH_MM:
			showMs = ! ! time.miliseconds;
			showSc = showMs || ! ! time.seconds;
			showHr = true;
			break;
		case TIME_FORMAT_MM_SS_SSS:
			showMs = true;
			showSc = true;
			showHr = ! ! time.hours;
			break;
		case TIME_FORMAT_MM_SS:
			showMs = ! ! time.miliseconds;
			showSc = true;
			showHr = ! ! time.hours;
			break;
		default:
			/* eslint-disable-next-line max-len */
			throw new Error( 'Argument `format` provided to `formatTime` is not a recognized format.' );
	}

	const hh = zeroFill( 2, time.hours );
	const mm = zeroFill( 2, time.minutes );
	const ss = zeroFill( 2, time.seconds );
	const sss = zeroFill( 3, time.miliseconds );

	return ( time.negative ? '-' : '' ) + ( showHr ? (
		showMs ? `${hh}:${mm}:${ss}.${sss}` : showSc ? `${hh}:${mm}:${ss}` : `${hh}:${mm}`
	) : (
		showMs ? `${mm}:${ss}.${sss}` : `${mm}:${ss}`
	) );
};
