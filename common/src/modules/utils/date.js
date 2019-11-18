/**
 * External dependencies
 */
import { identity } from 'lodash';
import chrono from 'chrono-node';

/**
 * Wordpress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	moment as momentUtil,
	timezone as timezoneUtil,
} from '@moderntribe/common/utils';
import { dateSettings } from '@moderntribe/common/utils/globals';

const formats = dateSettings() && dateSettings().formats ? dateSettings().formats : {};
const timezone = dateSettings() && dateSettings().formats ? dateSettings().formats : {};

export const FORMATS = {
	TIME: 'HH:mm:ss',
	DATE_TIME: 'YYYY-MM-DD HH:mm:ss',
	WP: {
		time: 'g:i a',
		time24Hr: 'H:i',
		date: 'F j, Y',
		datetime: 'F j, Y g:i a',
		dateNoYear: 'F j',
		...formats,
	},
	TIMEZONE: {
		string: 'UTC',
		...timezone,
	},
	DATABASE: {
		date: 'Y-m-d',
		datetime: 'Y-m-d H:i:s',
		time: 'H:i:s',
	},
};

export const TODAY = new Date();

export const timezonesAsSelectData = () => {
	return timezones().map( ( tzone ) => ( {
		value: tzone.key,
		label: tzone.text,
	} ) );
};

export const timezones = () => {
	return timezoneUtil.getItems()
		.map( ( group ) => group.options || [] )
		.reduce( ( prev, current ) => [ ...prev, ...current ], [] );
};

export const toNaturalLanguage = ( params = {} ) => {
	const options = {
		date: null,
		format: {
			month: 'MMMM',
			day: 'D',
			year: 'YYYY',
			time: momentUtil.toFormat( FORMATS.WP.time ),
		},
		separator: '',
		...params,
	};

	const parsed = {
		text: '',
		moment: options.date && momentUtil.toMoment( options.date ),
		detail: {
			day: '',
			month: '',
			year: '',
			time: '',
		},
		isValid: false,
	};

	parsed.isValid = Boolean( parsed.moment && parsed.moment.isValid() );

	if ( parsed.isValid ) {
		parsed.detail = {
			month: `${ parsed.moment.format( options.format.month ) }`,
			day: `${ parsed.moment.format( options.format.day ) }`,
			year: `${ parsed.moment.format( options.format.year ) }`,
			time: `${ parsed.moment.format( options.format.time ) }`,
		};
		const { detail } = parsed;
		parsed.text = `${ detail.month } ${ detail.day } ${ detail.year } ${ options.separator } ${ detail.time }`;
	}
	return parsed;
};

export const rangeToNaturalLanguage = ( start = '', end = '', separators = {} ) => {
	const separatorOptions = {
		time: __( 'at', 'tribe-common' ),
		date: ' - ',
		...separators,
	};
	const from = toNaturalLanguage( { date: start, separator: separatorOptions.time } );
	const to = toNaturalLanguage( { date: end, separator: separatorOptions.time } );
	const parts = [ from.text ];

	if ( from.isValid && to.isValid ) {
		if ( momentUtil.isSameDay( from.moment, to.moment ) ) {
			/**
			 * If both dates are happening on the same day the only relevant thing is the time on the second
			 * part of the string (to keep string cleaner).
			 *
			 * - Current behavior 'Oct 8 2018 at 12:00 pm - Oct 8 2018 at 12:30 pm'
			 * - New behavior: 'Oct 8 2018 at 12:00 pm - 12:30 pm'
			 */
			parts.push( to.detail.time );
		} else if ( momentUtil.isSameMonth( from.moment, to.moment ) ) {
			/**
			 * If both dates are happening on the same month and not on the same day but during the same year
			 * we don't need to show the same year twice.
			 *
			 * - Current Behavior: 'Oct 8 2018 at 12:00 pm - Oct 24 2018 12:30 pm'
			 * - New Behavior: 'Oct 8 2018 at 12:00 pm - Oct 24 12:30 pm'
			 */
			parts.push( `${ to.detail.month } ${ to.detail.day } ${ separatorOptions.time } ${ to.detail.time }` );
		} else {
			// Otherwise just use the full text
			parts.push( to.text );
		}
	}

	return parts.filter( identity ).join( separatorOptions.date );
};

export const labelToDate = ( label ) => {
	const [ parsed ] = chrono.parse( label );
	const dates = {
		start: null,
		end: null,
	};
	if ( parsed ) {
		const { start, end } = parsed;
		dates.start = start ? momentUtil.toDateTime( momentUtil.toMoment( start.date() ) ) : null;
		dates.end = end ? momentUtil.toDateTime( momentUtil.toMoment( end.date() ) ) : null;
	}
	return dates;
};
