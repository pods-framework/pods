/**
 * External dependencies
 */
import { isString } from 'lodash';
import moment, { isMoment } from 'moment';

/**
 * Internal dependencies
 */
import {
	date as dateUtil,
	time,
	string,
} from '@moderntribe/common/utils';

export const TIME_FORMAT = 'h:mm a';

/**
 * Make sure the format provided matches the spec used by moment.js
 *
 * @param {string} format The format to be converted to a moment format
 * @returns {string} return a moment.js valid format
 */
export const toFormat = ( format ) => {
	const replacements = {
		d: 'DD',
		D: 'ddd',
		j: 'D',
		l: 'dddd',
		N: 'E',
		S: 'o',
		w: 'e',
		z: 'DDD',
		W: 'W',
		F: 'MMMM',
		m: 'MM',
		M: 'MMM',
		n: 'M',
		t: '', // no equivalent
		L: '', // no equivalent
		o: 'YYYY',
		Y: 'YYYY',
		y: 'YY',
		a: 'a',
		A: 'A',
		B: '', // no equivalent
		g: 'h',
		G: 'H',
		h: 'hh',
		H: 'HH',
		i: 'mm',
		s: 'ss',
		u: 'SSS',
		e: 'zz', // deprecated since version 1.6.0 of moment.js
		I: '', // no equivalent
		O: '', // no equivalent
		P: '', // no equivalent
		T: '', // no equivalent
		Z: '', // no equivalent
		c: '', // no equivalent
		r: '', // no equivalent
		U: 'X',
	};

	return string.replaceWithObject( format, replacements );
};

/**
 * Round the time of a moment object if the minutes on the date is lower than 30 will set to 0 if
 * is greater will se 30 so is either 30 or 0.
 *
 * @param {moment} date Make sure the date is rounded between 0 or 30 minutes
 * @returns {moment} A moment object
 */
export const roundTime = ( date ) => {
	if ( ! isMoment( date ) ) {
		return date;
	}

	let minutes = date.minute();
	if ( minutes >= 30 ) {
		minutes = ( minutes % 30 );
	}

	return date
		.clone()
		.subtract( minutes, 'm' )
		.seconds( 0 );
};

/**
 * Parse multiple formats in a date to ensure the generated dates are valid
 *
 * @param {string} date The date to be converted
 * @param {array} formats The list of formats used to format
 * @returns {moment} moment Object with the date or current date if is non valid
 */
export const parseFormats = ( date, formats = [ dateUtil.FORMATS.DATABASE.datetime, dateUtil.FORMATS.WP.datetime ] ) => {
	for ( let i = 0; i < formats.length; i ++ ) {
		const format = formats[ i ];
		const result = toMoment( date, format );
		if ( result.isValid() ) {
			return result;
		}
	}

	const noFormat = moment( date );
	return noFormat.isValid() ? noFormat : moment();
};

/**
 * Convert a Date() object into a Moment.js object avoiding warnings of different formats
 * used by Date
 *
 * @param {(Date|moment|string)} date The date to be converted.
 * @param {string} format The format of the data to be used
 * @param {bool} Force the parse of the format default to true
 * @returns {moment} A moment object
 */
export const toMoment = ( date, format = dateUtil.FORMATS.DATABASE.datetime, parseFormat = true ) => {
	if ( isMoment( date ) || date instanceof Date ) {
		return moment( date );
	} else if ( isString( date ) ) {
		return moment( date, parseFormat ? toFormat( format ) : format );
	}

	return moment();
};

export const toMomentFromDate = ( date ) => {
	if ( ! ( date instanceof Date ) ) {
		throw new Error( 'Make sure your date is an instance of Date' );
	}

	const year = date.getFullYear();
	const month = date.getMonth();
	const day = date.getDate();

	return moment()
		.year( year )
		.month( month )
		.date( day )
		.startOf( 'day' );
};

/**
 * Convert a Date() object or date string and time into a moment object
 *
 * @param {(Date|moment|string)} date The date to be converted.
 * @param {string} time The time string in HH:mm format..
 * @returns {moment} A moment object
 */
export const toMomentFromDateTime = ( date, time ) => {
	const [ hours, minutes ] = time.split( ':' );
	return moment( date ).hours( hours ).minutes( minutes );
};

/**
 * Replace the date of a moment object with another date from another moment object
 *
 * @param {moment} original The moment object where the date is going to be replaced
 * @param {moment} replaced The moment object where the date to be used to replace is located
 * @returns {moment} A moment object where the date is replaced
 */
export const replaceDate = ( original, replaced ) => {
	if ( ! isMoment( original ) || ! isMoment( replaced ) ) {
		throw new Error( 'Make sure your values are instances of moment' );
	}

	return original
		.year( replaced.year() )
		.month( replaced.month() )
		.date( replaced.date() );
};

/**
 * Set time in seconds to a moment object
 *
 * @param {moment} original The original moment where the date is going to be set
 * @param {number} seconds Amount of seconds to be set to the moment object.
 * @returns {moment} A moment object with the new date
 */
export const setTimeInSeconds = ( original, seconds = 0 ) => {
	if ( ! isMoment( original ) ) {
		throw new Error( 'Make sure your values are instances of moment' );
	}

	if ( seconds < 0 ) {
		return original;
	}

	return original
		.startOf( 'day' )
		.seconds( seconds || original.seconds() );
};

/**
 * Total seconds of a current date from moment
 *
 * @param {moment} date The date to compare on the current day
 * @returns {int} Total of seconds from start of the day to the current moment,
 */
export const totalSeconds = ( date ) => {
	if ( ! date || ! isMoment( date ) ) {
		return 0;
	}
	return date.diff( moment( date ).startOf( 'day' ), 'seconds' );
};

/**
 * Convert a moment object into a WP date time format
 *
 * @param {moment} date A moment date object
 * @param {string} format Format used to output the date
 * @returns {string} A date time format
 */
export const toDateTime = ( date, format = dateUtil.FORMATS.DATABASE.datetime ) => (
	date.format( toFormat( format ) )
);

export const toDate = ( date, format = dateUtil.FORMATS.WP.date ) => (
	date.format( toFormat( format ) )
);

export const toDateNoYear = ( date, format = dateUtil.FORMATS.WP.dateNoYear ) => (
	date.format( toFormat( format ) )
);

export const toTime = ( date, format = dateUtil.FORMATS.WP.time ) => (
	date.format( toFormat( format ) )
);

export const toTime24Hr = ( date, format = dateUtil.FORMATS.WP.time24Hr ) => (
	date.format( toFormat( format ) )
);

export const toDatabaseDate = ( date, format = dateUtil.FORMATS.DATABASE.date ) => (
	date.format( toFormat( format ) )
);

export const toDatabaseTime = ( date, format = dateUtil.FORMATS.DATABASE.time ) => (
	date.format( toFormat( format ) )
);

export const toDatePicker = ( date = moment(), format = 'YYYY-MM-DDTHH:mm:ss' ) => (
	date.format( format )
);

/**
 * Test if the start and end dates are the same day.
 *
 * @param {moment} start The start date
 * @param {(moment|String)} end The end date
 * @returns {boolean} if the start and end dates are the same day
 */
export const isSameDay = ( start, end ) => {

	if ( ! start || ! end ) {
		return false;
	}

	return moment( start ).isSame( end, 'day' );
};

/**
 * Test if two moment objects are in the same month
 *
 * @param {moment} start The start moment
 * @param {moment} end The end moment
 * @returns {boolean} true if start and end are on the same month
 */
export const isSameMonth = ( start, end ) => {

	if ( ! start || ! end ) {
		return false;
	}

	return moment( start ).isSame( end, 'month' );
};

/**
 * Test if the start and end dates have the same year.
 *
 * @param {moment} start The start date
 * @param {(moment|String)} end The end date
 * @returns {boolean} if the start and end dates have the same year
 */
export const isSameYear = ( start, end ) => (
	toMoment( start ).isSame( toMoment( end ), 'year' )
);

/**
 * Reset the time of an event by creating an object with start and end ensuring the end event is
 * after the start date and both are on the same day if the start is one hour before the end of the
 * day it will remove an hour of the start to ensure both start / end happen on the same day
 *
 * @param {moment} start The start date
 * @returns {{start: {moment}, end: {moment}}} Object with two keys: start, end
 */
export const resetTimes = ( start ) => {
	const testMoment = start.clone().add( time.HOUR_IN_SECONDS, 'seconds' );

	// Rollback an hour before adding half an hour as we are on the edge of the day
	if ( ! isSameDay( start, testMoment ) ) {
		start.subtract( time.HOUR_IN_SECONDS, 'seconds' );
	}

	const end = start.clone().add( time.HOUR_IN_SECONDS, 'seconds' );

	return {
		start,
		end,
	};
};

/**
 * Make sure the start time is always before the end time
 *
 * @param {moment} start The start date
 * @param {moment} end The end date
 * @returns {{start: {moment}, end: {moment}}} Object with two keys: start, end
 */
export const adjustStart = ( start, end ) => {
	if ( end.isSameOrBefore( start ) ) {
		return resetTimes( start );
	}

	return {
		start,
		end,
	};
};
