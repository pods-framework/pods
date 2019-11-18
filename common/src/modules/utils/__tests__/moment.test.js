/**
 * External dependencies
 */
import moment from 'moment/moment';

/**
 * Internal dependencies
 */
import {
	date,
	moment as momentUtil,
	time,
} from '@moderntribe/common/utils';

const FORMAT = 'MM-DD-YYYY HH:mm:ss';

describe( 'Tests for moment.js', () => {
	let console;
	beforeAll( () => {
		console = window.console;
		window.console = {
			...console,
			warn: jest.fn(),
		};
	} );

	afterAll( () => {
		window.console = console;
	} );

	test( 'TIME_FORMAT', () => {
		expect( momentUtil.TIME_FORMAT ).toEqual( 'h:mm a' );
	} );

	test( 'roundTime', () => {
		const test1 = momentUtil.roundTime(
			moment( '05-09-2018 12:26:02', FORMAT ),
		);
		expect( test1 ).toBeInstanceOf( moment );
		expect( test1.hour() ).toEqual( 12 );
		expect( test1.minutes() ).toEqual( 0 );
		expect( test1.seconds() ).toEqual( 0 );

		const test2 = momentUtil.roundTime(
			moment( '05-09-2018 15:30:02', FORMAT ),
		);
		expect( test2 ).toBeInstanceOf( moment );
		expect( test2.hour() ).toEqual( 15 );
		expect( test2.minutes() ).toEqual( 30 );
		expect( test2.seconds() ).toEqual( 0 );

		const test3 = momentUtil.roundTime(
			moment( '05-09-2018 23:59:59', FORMAT ),
		);
		expect( test3 ).toBeInstanceOf( moment );
		expect( test3.hour() ).toEqual( 23 );
		expect( test3.minutes() ).toEqual( 30 );
		expect( test3.seconds() ).toEqual( 0 );

		const test4 = momentUtil.roundTime(
			moment( '05-09-2018 08:01:59', FORMAT ),
		);
		expect( test4 ).toBeInstanceOf( moment );
		expect( test4.hour() ).toEqual( 8 );
		expect( test4.minutes() ).toEqual( 0 );
		expect( test4.seconds() ).toEqual( 0 );
	} );

	test( 'toMoment', () => {
		const input = momentUtil.toMoment( new Date( 'January 2, 2015 08:01:59 UTC' ).toISOString() );

		expect( input ).toBeInstanceOf( moment );
		expect( input.date() ).toEqual( 2 );
		expect( input.month() ).toEqual( 0 );
		expect( input.year() ).toEqual( 2015 );
		expect( input.hour() ).toEqual( 8 );
		expect( input.minutes() ).toEqual( 1 );
		expect( input.seconds() ).toEqual( 59 );
		expect( input.milliseconds() ).toEqual( 0 );
		expect( input.format( FORMAT ) ).toEqual( '01-02-2015 08:01:59' );
	} );

	test( 'replaceDate', () => {
		expect( () => momentUtil.replaceDate( 'Sample string', 123123 ) ).toThrowError();

		const a = moment( '02-28-2010 14:24:40', FORMAT );
		const b = moment( '05-10-2012 20:14:20', FORMAT );

		const replaced = momentUtil.replaceDate( a, b );
		expect( replaced ).toBeInstanceOf( moment );
		expect( replaced.date() ).toEqual( 10 );
		expect( replaced.month() ).toEqual( 4 );
		expect( replaced.year() ).toEqual( 2012 );
		expect( replaced.hour() ).toEqual( 14 );
		expect( replaced.minute() ).toEqual( 24 );
		expect( replaced.second() ).toEqual( 40 );
		expect( replaced.format( FORMAT ) ).toEqual( '05-10-2012 14:24:40' );
	} );

	test( 'setTimeInSeconds', () => {
		expect( () => momentUtil.setTimeInSeconds( 'Sample String', 123123 ) ).toThrowError();

		const a = moment( '02-28-2010 14:24:40', FORMAT );
		const SECONDS = ( 12.5 ) * 60 * 60;
		const replaced = momentUtil.setTimeInSeconds( a, SECONDS );
		expect( replaced ).toBeInstanceOf( moment );
		expect( replaced.date() ).toEqual( 28 );
		expect( replaced.month() ).toEqual( 1 );
		expect( replaced.year() ).toEqual( 2010 );
		expect( replaced.hour() ).toEqual( 12 );
		expect( replaced.minute() ).toEqual( 30 );
		expect( replaced.seconds() ).toEqual( 0 );
		expect( replaced.milliseconds() ).toEqual( 0 );

		const test2 = momentUtil.setTimeInSeconds( a, 0 );
		expect( test2.date() ).toEqual( 28 );
		expect( test2.month() ).toEqual( 1 );
		expect( test2.year() ).toEqual( 2010 );
		expect( test2.hour() ).toEqual( 0 );
		expect( test2.minute() ).toEqual( 0 );
		expect( test2.seconds() ).toEqual( 0 );
		expect( test2.milliseconds() ).toEqual( 0 );
	} );

	test( 'totalSeconds', () => {
		expect( momentUtil.totalSeconds( null ) ).toEqual( 0 );
		expect( momentUtil.totalSeconds( new Date() ) ).toEqual( 0 );
		expect( momentUtil.totalSeconds( moment().startOf( 'day' ) ) ).toEqual( 0 );
		expect( momentUtil.totalSeconds( moment( 'May 23, 2018 12:30 am', 'MMM D, YYYY k:m a' ) ) )
			.toEqual( time.HALF_HOUR_IN_SECONDS );
	} );

	test( 'toDateTime', () => {
		const converted = momentUtil.toDateTime( moment() );
		expect( typeof converted ).toBe( 'string' );
		const format = momentUtil.toFormat( date.FORMATS.DATABASE.datetime );
		expect( converted ).toBe( moment().format( format ) );
	} );

	test( 'toDate', () => {
		const converted = momentUtil.toDate( moment() );
		expect( typeof converted ).toBe( 'string' );
		expect( typeof converted ).toBe( 'string' );
		const format = momentUtil.toFormat( date.FORMATS.WP.date );
		expect( converted ).toBe( moment().format( format ) );
	} );

	test( 'toDateNoYear', () => {
		const converted = momentUtil.toDateNoYear( moment() );
		expect( typeof converted ).toBe( 'string' );
		expect( converted ).toBe( moment().format( 'MMMM D' ) );
	} );

	test( 'toTime', () => {
		const converted = momentUtil.toTime( moment() );
		expect( typeof converted ).toBe( 'string' );
		expect( converted ).toBe( moment().format( 'h:mm a' ) );
	} );

	test( 'toTime24Hr', () => {
		const converted = momentUtil.toTime24Hr( moment() );
		expect( typeof converted ).toBe( 'string' );
		expect( converted ).toBe( moment().format( 'HH:mm' ) );
	} );

	test( 'toDatabaseDate', () => {
		const converted = momentUtil.toDatabaseDate( moment() );
		expect( typeof converted ).toBe( 'string' );
		expect( converted ).toBe( moment().format( 'YYYY-MM-DD' ) );
	} );

	test( 'toDatabaseTime', () => {
		const converted = momentUtil.toDatabaseTime( moment() );
		expect( typeof converted ).toBe( 'string' );
		expect( converted ).toBe( moment().format( 'HH:mm:ss' ) );
	} );

	test( 'toDatePicker', () => {
		const converted = momentUtil.toDatePicker( moment() );
		expect( typeof converted ).toBe( 'string' );
		expect( converted ).toBe( moment().format( 'YYYY-MM-DDTHH:mm:ss' ) );
	} );

	test( 'isSameDay', () => {
		expect( momentUtil.isSameDay() ).toBe( false );
		expect( momentUtil.isSameDay( false, '' ) ).toBe( false );
		expect( momentUtil.isSameDay( 0, null ) ).toBe( false );
		expect( momentUtil.isSameDay( moment(), moment().endOf( 'day' ) ) ).toBeTruthy();
		expect( momentUtil.isSameDay( moment().endOf( 'day' ), moment().endOf( 'day' ) ) ).toBeTruthy();
		expect( momentUtil.isSameDay( moment(), moment().add( 10, 'days' ) ) ).toBeFalsy();
		expect( momentUtil.isSameDay( new Date(), new Date() ) ).toBeTruthy();
	} );

	test( 'isSameMonth', () => {
		const date = moment( 'October 8, 2018 5:30 pm', 'MMMM D, Y h:mm a' );

		expect( momentUtil.isSameMonth() ).toBe( false );
		expect( momentUtil.isSameMonth( false, '' ) ).toBe( false );
		expect( momentUtil.isSameMonth( 0, null ) ).toBe( false );
		expect( momentUtil.isSameMonth( date, date.clone().add( 24, 'days' ) ) ).toBe( false );
		expect( momentUtil.isSameMonth( date, date.clone().add( 23, 'days' ) ) ).toBe( true );
		expect( momentUtil.isSameMonth( date, date.clone().endOf( 'month' ) ) ).toBe( true );
		expect( momentUtil.isSameMonth( date.clone().endOf( 'month' ), date.clone().endOf( 'month' ) ) ).toBe( true );
		expect( momentUtil.isSameMonth( date, date.clone().add( 10, 'days' ) ) ).toBe( true );
		expect( momentUtil.isSameMonth( date, date ) ).toBe( true );
	} );

	test( 'isSameYear', () => {
		expect( momentUtil.isSameYear(
			moment( 'May 23, 2018 12:30 am', 'MMM D, YYYY k:m a' ),
			moment( 'September 15, 2018 5:30 am', 'MMM D, YYYY k:m a' )
		) ).toBeTruthy();
		expect( momentUtil.isSameYear(
			moment( 'May 23, 2022 12:30 am', 'MMM D, YYYY k:m a' ),
			moment( 'September 15, 2022 5:30 am', 'MMM D, YYYY k:m a' )
		) ).toBeTruthy();
		expect( momentUtil.isSameYear(
			moment( 'May 23, 2018 12:30 am', 'MMM D, YYYY k:m a' ),
			moment( 'September 15, 2022 5:30 am', 'MMM D, YYYY k:m a' )
		) ).toBeFalsy();
	} );

	test( 'toMomentFromDate', () => {
		expect( () => momentUtil.toMomentFromDate( '' ) ).toThrowError();
		expect( () => momentUtil.toMomentFromDate( moment() ) ).toThrowError();
		Date.now = jest.fn( () => '2018-05-04T05:23:19.000Z' );
		const format = 'YYYY-MM-DD HH:mm:ss';
		const now = new Date( 'December 17, 2015 03:24:00' );
		expect( momentUtil.toMomentFromDate( now ) ).toBeInstanceOf( moment );
		const expected = momentUtil.toMomentFromDate( now ).format( format );
		expect( expected ).toBe( '2015-12-17 00:00:00' );
	} );

	test( 'toFormat', () => {
		expect( momentUtil.toFormat( '' ) ).toEqual( '' );
		expect( momentUtil.toFormat( 'Y-m-d H:i:s' ) ).toEqual( 'YYYY-MM-DD HH:mm:ss' );
		expect( momentUtil.toFormat( 'F j, Y g:i a' ) ).toEqual( 'MMMM D, YYYY h:mm a' );
		expect( momentUtil.toFormat( 'tLBIOPTZcr' ) ).toEqual( '' );
		expect( momentUtil.toFormat( 'd' ) ).toEqual( 'DD' );
		expect( momentUtil.toFormat( 'D' ) ).toEqual( 'ddd' );
		expect( momentUtil.toFormat( 'j' ) ).toEqual( 'D' );
		expect( momentUtil.toFormat( 'l' ) ).toEqual( 'dddd' );
		expect( momentUtil.toFormat( 'N' ) ).toEqual( 'E' );
		expect( momentUtil.toFormat( 'S' ) ).toEqual( 'o' );
		expect( momentUtil.toFormat( 'w' ) ).toEqual( 'e' );
		expect( momentUtil.toFormat( 'z' ) ).toEqual( 'DDD' );
		expect( momentUtil.toFormat( 'W' ) ).toEqual( 'W' );
		expect( momentUtil.toFormat( 'F' ) ).toEqual( 'MMMM' );
		expect( momentUtil.toFormat( 'm' ) ).toEqual( 'MM' );
		expect( momentUtil.toFormat( 'M' ) ).toEqual( 'MMM' );
		expect( momentUtil.toFormat( 'n' ) ).toEqual( 'M' );
		expect( momentUtil.toFormat( 'o' ) ).toEqual( 'YYYY' );
		expect( momentUtil.toFormat( 'Y' ) ).toEqual( 'YYYY' );
		expect( momentUtil.toFormat( 'y' ) ).toEqual( 'YY' );
		expect( momentUtil.toFormat( 'a' ) ).toEqual( 'a' );
		expect( momentUtil.toFormat( 'A' ) ).toEqual( 'A' );
		expect( momentUtil.toFormat( 'g' ) ).toEqual( 'h' );
		expect( momentUtil.toFormat( 'G' ) ).toEqual( 'H' );
		expect( momentUtil.toFormat( 'h' ) ).toEqual( 'hh' );
		expect( momentUtil.toFormat( 'H' ) ).toEqual( 'HH' );
		expect( momentUtil.toFormat( 'i' ) ).toEqual( 'mm' );
		expect( momentUtil.toFormat( 's' ) ).toEqual( 'ss' );
		expect( momentUtil.toFormat( 'u' ) ).toEqual( 'SSS' );
		expect( momentUtil.toFormat( 'e' ) ).toEqual( 'zz' );
		expect( momentUtil.toFormat( 'U' ) ).toEqual( 'X' );
	} );

	describe( 'parseFormats', () => {
		test( 'Use DB format', () => {
			const format = 'YYYY-MM-DD HH:mm:ss';
			const expected = momentUtil.parseFormats( '2019-11-19 22:32:00' );
			expect( expected.format( format ) ).toBe( '2019-11-19 22:32:00' );
		} );

		test( 'Use WP datetime format', () => {
			const format = 'MMMM D, YYYY h:mm a';
			const expected = momentUtil.parseFormats( 'November 19, 2019 10:32 pm' );
			expect( expected.format( format ) ).toBe( 'November 19, 2019 10:32 pm' );
		} );

		test( 'Invalid date', () => {
			Date.now = jest.fn( () => new Date( 'July 1, 2018 00:07:31 UTC' ).toISOString() );
			const format = 'YYYY-MM-DD HH:mm:ss';
			const expected = momentUtil.parseFormats( 'No date!' );
			expect( expected.format( format ) ).toBe( '2018-07-01 00:07:31' );
			expect( window.console.warn ).toHaveBeenCalled();
		} );
	} );

	describe( 'resetTimes', () => {
		const format = 'YYYY-MM-DD HH:mm:ss';
		it( 'Should add an hour in seconds', () => {
			const startMoment = moment( new Date( 'July 19, 2018 19:30:00 UTC' ).toISOString() );
			const { start, end } = momentUtil.resetTimes( startMoment );
			expect( start.format( format ) ).toBe( '2018-07-19 19:30:00' );
			expect( end.format( format ) ).toBe( '2018-07-19 20:30:00' );
		} );

		it( 'Should add hour in seconds on start of the day', () => {
			const startMoment = moment( new Date( 'July 19, 2018 00:00:00 UTC' ).toISOString() );
			const { start, end } = momentUtil.resetTimes( startMoment );
			expect( start.format( format ) ).toBe( '2018-07-19 00:00:00' );
			expect( end.format( format ) ).toBe( '2018-07-19 01:00:00' );
		} );

		it( 'Should prevent overflow to the next day', () => {
			const startMoment = moment( new Date( 'July 19, 2018 23:59:59 UTC' ).toISOString() );
			const { start, end } = momentUtil.resetTimes( startMoment );
			expect( start.format( format ) ).toBe( '2018-07-19 22:59:59' );
			expect( end.format( format ) ).toBe( '2018-07-19 23:59:59' );
		} );
	} );

	describe( 'adjustStart', () => {
		const format = 'YYYY-MM-DD HH:mm:ss';
		it( 'Should keep the same order when start is before', () => {
			const start = moment( new Date( 'July 10, 2018 14:30:00 UTC' ).toISOString() );
			const end = moment( new Date( 'July 10, 2018 20:35:00 UTC' ).toISOString() );
			const output = momentUtil.adjustStart( start, end );
			expect( output.start.format( format ) ).toBe( '2018-07-10 14:30:00' );
			expect( output.end.format( format ) ).toBe( '2018-07-10 20:35:00' );
		} );

		it( 'Should adjust the start and end time', () => {
			const start = moment( new Date( 'July 10, 2018 20:35:00 UTC' ).toISOString() );
			const end = moment( new Date( 'July 10, 2018 10:30:00 UTC' ).toISOString() );
			const output = momentUtil.adjustStart( start, end );
			expect( output.start.format( format ) ).toBe( '2018-07-10 20:35:00' );
			expect( output.end.format( format ) ).toBe( '2018-07-10 21:35:00' );
		} );
	} );
} );
