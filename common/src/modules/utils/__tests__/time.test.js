/**
 * Internal dependencies
 */
import { time } from '@moderntribe/common/utils';
import { roundTime } from '../moment';

describe( 'Tests for time.js', () => {
	test( 'MINUTE_IN_SECONDS', () => {
		expect( time.MINUTE_IN_SECONDS ).toEqual( 60 );
	} );

	test( 'HALF_HOUR_IN_SECONDS', () => {
		expect( time.HALF_HOUR_IN_SECONDS ).toEqual( 1800 );
	} );

	test( 'HOUR_IN_SECONDS', () => {
		expect( time.HOUR_IN_SECONDS ).toEqual( 3600 );
	} );

	test( 'DAY_IN_SECONDS', () => {
		expect( time.DAY_IN_SECONDS ).toEqual( 86400 );
	} );

	test( 'roundTime', () => {
		expect( time.roundTime( '01:23:54.423', time.TIME_FORMAT_HH_MM_SS_SSS ) )
			.toEqual( '01:00:00.000' );
		expect( time.roundTime( '14:46:13.042', time.TIME_FORMAT_HH_MM_SS_SSS ) )
			.toEqual( '14:30:00.000' );
		expect( time.roundTime( '01:23:54', time.TIME_FORMAT_HH_MM_SS ) )
			.toEqual( '01:00:00' );
		expect( time.roundTime( '14:46:13', time.TIME_FORMAT_HH_MM_SS ) )
			.toEqual( '14:30:00' );
		expect( time.roundTime( '01:23', time.TIME_FORMAT_HH_MM ) )
			.toEqual( '01:00' );
		expect( time.roundTime( '14:46', time.TIME_FORMAT_HH_MM ) )
			.toEqual( '14:30' );
		expect( time.roundTime( '23:54.423', time.TIME_FORMAT_MM_SS_SSS ) )
			.toEqual( '00:00.000' );
		expect( time.roundTime( '46:13.042', time.TIME_FORMAT_MM_SS_SSS ) )
			.toEqual( '30:00.000' );
		expect( time.roundTime( '23:54', time.TIME_FORMAT_MM_SS ) )
			.toEqual( '00:00' );
		expect( time.roundTime( '46:13', time.TIME_FORMAT_MM_SS ) )
			.toEqual( '30:00' );
	} );

	test( 'START_OF_DAY', () => {
		expect( time.START_OF_DAY ).toEqual( '00:00' );
	} );

	test( 'END_OF_DAY', () => {
		expect( time.END_OF_DAY ).toEqual( '23:59' );
	} );

	test( 'TIME_FORMAT_HH_MM_SS_SSS', () => {
		expect( time.TIME_FORMAT_HH_MM_SS_SSS ).toEqual( 'hh:mm:ss.sss' );
	} );

	test( 'TIME_FORMAT_HH_MM_SS', () => {
		expect( time.TIME_FORMAT_HH_MM_SS ).toEqual( 'hh:mm:ss' );
	} );

	test( 'TIME_FORMAT_HH_MM', () => {
		expect( time.TIME_FORMAT_HH_MM ).toEqual( 'hh:mm' );
	} );

	test( 'TIME_FORMAT_MM_SS_SSS', () => {
		expect( time.TIME_FORMAT_MM_SS_SSS ).toEqual( 'mm:ss.sss' );
	} );

	test( 'TIME_FORMAT_MM_SS', () => {
		expect( time.TIME_FORMAT_MM_SS ).toEqual( 'mm:ss' );
	} );

	test( 'HOUR_IN_MS', () => {
		expect( time.HOUR_IN_MS ).toEqual( 3600000 );
	} );

	test( 'MINUTE_IN_MS', () => {
		expect( time.MINUTE_IN_MS ).toEqual( 60000 );
	} );

	test( 'SECOND_IN_MS', () => {
		expect( time.SECOND_IN_MS ).toEqual( 1000 );
	} );

	/**
	 * Below are tests copied from the hh-mm-ss library and adjusted to use
	 * Jest instead of Tape for testing.
	 * Link: https://github.com/Goldob/hh-mm-ss/blob/master/test/index.js
	 */
	test( 'fromMilliseconds() test', () => {
		// Basic functionality
		expect( time.fromMilliseconds( 75000 ) ).toEqual( '01:15' );
		expect( time.fromMilliseconds( 442800000 ) ).toEqual( '123:00:00' );
		expect( time.fromMilliseconds( 90576 ) ).toEqual( '01:30.576' );
		expect( time.fromMilliseconds( -157250 ) ).toEqual( '-02:37.250' );

		// Output formatting
		expect( time.fromMilliseconds( 38000, 'mm:ss.sss' ) ).toEqual( '00:38.000' );
		expect( time.fromMilliseconds( 0, 'hh:mm:ss' ) ).toEqual( '00:00:00' );
		expect( time.fromMilliseconds( 3600000, 'mm:ss' ) ).toEqual( '01:00:00' );
		expect( time.fromMilliseconds( 4500000, 'hh:mm' ) ).toEqual( '01:15' );
		expect( time.fromMilliseconds( -9900000, 'hh:mm' ) ).toEqual( '-02:45' );

		// Input validation
		expect( () => time.fromMilliseconds( null ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.fromMilliseconds('text') ).toThrowErrorMatchingSnapshot();
		expect( () => time.fromMilliseconds(0, 'mm:hh:ss') ).toThrowErrorMatchingSnapshot();
	} );

	test( 'fromSeconds() test', () => {
		// Basic functionality
		expect( time.fromSeconds( 75 ) ).toEqual( '01:15' );
		expect( time.fromSeconds( 442800 ) ).toEqual( '123:00:00' );
		expect( time.fromSeconds( -442800 ) ).toEqual( '-123:00:00' );

		// Output formatting
		expect( time.fromSeconds( 38, 'mm:ss.sss' ) ).toEqual( '00:38.000' );
		expect( time.fromSeconds( 0, 'hh:mm:ss' ) ).toEqual( '00:00:00' );
		expect( time.fromSeconds( 3600, 'mm:ss' ) ).toEqual( '01:00:00' );
		expect( time.fromSeconds( 4500, 'hh:mm' ) ).toEqual( '01:15' );
		expect( time.fromSeconds( -9900, 'hh:mm' ) ).toEqual( '-02:45' );

		// Input validation
		expect( () => time.fromSeconds( null ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.fromSeconds( 'text' ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.fromSeconds( 0, 'mm:hh:ss' ) ).toThrowErrorMatchingSnapshot();
	} );

	test( 'toMilliseconds() test', () => {
		// Basic functionality
		expect( time.toMilliseconds( '01:05:17' ) ).toEqual( 3917000 );
		expect( time.toMilliseconds( '137:00:00.0' ) ).toEqual( 493200000 );
		expect( time.toMilliseconds( '00:10.230' ) ).toEqual( 10230 );
		expect( time.toMilliseconds( '00:00:07.10845' ) ).toEqual( 7108 );
		expect( time.toMilliseconds( '-02:07:12' ) ).toEqual( -7632000 );
		expect( time.toMilliseconds( '02:00' ) ).toEqual( 120000 );
		expect( time.toMilliseconds( '02:00', 'hh:mm' ) ).toEqual( 7200000 );
		expect( time.toMilliseconds( '-04:35', 'hh:mm' ) ).toEqual( -16500000 );
		expect( time.toMilliseconds( '00:00:07.10845', 'hh:mm' ) ).toEqual( 7108 );

		// Input validation
		expect( () => time.toMilliseconds( '13:05:02:11' ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.toMilliseconds( '153' ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.toMilliseconds( null ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.toMilliseconds( '00:62' ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.toMilliseconds( '01:30', 'mm:hh:ss' ) ).toThrowErrorMatchingSnapshot();
	} );

	test( 'toSeconds() test', () => {
		// Basic functionality
		expect( time.toSeconds( '01:05:17' ) ).toEqual( 3917 );
		expect( time.toSeconds( '137:00:00.0' ) ).toEqual( 493200 );
		expect( time.toSeconds( '00:10.230' ) ).toEqual( 10 );
		expect( time.toSeconds( '00:00:07.10845' ) ).toEqual( 7 );
		expect( time.toSeconds( '-02:07:12' ) ).toEqual( -7632 );
		expect( time.toSeconds( '02:00' ) ).toEqual( 120 );
		expect( time.toSeconds( '02:00', 'hh:mm' ) ).toEqual( 7200 );
		expect( time.toSeconds( '-04:35', 'hh:mm' ) ).toEqual( -16500 );
		expect( time.toSeconds( '00:00:07.10845', 'hh:mm' ) ).toEqual( 7 );

		// Input validation
		expect( () => time.toSeconds( '13:05:02:11' ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.toSeconds( '153' ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.toSeconds( null ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.toSeconds( '00:62' ) ).toThrowErrorMatchingSnapshot();
		expect( () => time.toSeconds( '01:30', 'mm:hh:ss' ) ).toThrowErrorMatchingSnapshot();
	} );

	test( 'symmetrical conversion test', () => {
		/*
		 * fromMilliseconds() and toMilliseconds() for all formats
		 */

		// 90000ms = 1m 30s
		expect( time.toMilliseconds( time.fromMilliseconds( 90000, 'mm:ss' ), 'mm:ss' ) ).toEqual( 90000 );
		expect( time.toMilliseconds( time.fromMilliseconds( 90000, 'mm:ss.sss' ), 'mm:ss.sss' ) ).toEqual( 90000 );
		expect( time.toMilliseconds( time.fromMilliseconds( 90000, 'hh:mm' ), 'hh:mm' ) ).toEqual( 90000 );
		expect( time.toMilliseconds( time.fromMilliseconds( 90000, 'hh:mm:ss' ), 'hh:mm:ss' ) ).toEqual( 90000 );
		expect( time.toMilliseconds( time.fromMilliseconds( 90000, 'hh:mm:ss.sss' ), 'hh:mm:ss.sss' ) ).toEqual( 90000 );

		// 7517245ms = 2h 5m 17.245s
		expect( time.toMilliseconds( time.fromMilliseconds( 7517245, 'mm:ss' ), 'mm:ss' ) ).toEqual( 7517245 );
		expect( time.toMilliseconds( time.fromMilliseconds( 7517245, 'mm:ss.sss' ), 'mm:ss.sss' ) ).toEqual( 7517245 );
		expect( time.toMilliseconds( time.fromMilliseconds( 7517245, 'hh:mm' ), 'hh:mm' ) ).toEqual( 7517245 );
		expect( time.toMilliseconds( time.fromMilliseconds( 7517245, 'hh:mm:ss' ), 'hh:mm:ss' ) ).toEqual( 7517245 );
		expect( time.toMilliseconds( time.fromMilliseconds( 7517245, 'hh:mm:ss.sss' ), 'hh:mm:ss.sss' ) ).toEqual( 7517245 );

		// -10800000ms = -3h
		expect( time.toMilliseconds( time.fromMilliseconds( -10800000, 'mm:ss' ), 'mm:ss' ) ).toEqual( -10800000 );
		expect( time.toMilliseconds( time.fromMilliseconds( -10800000, 'mm:ss.sss' ), 'mm:ss.sss' ) ).toEqual( -10800000 );
		expect( time.toMilliseconds( time.fromMilliseconds( -10800000, 'hh:mm' ), 'hh:mm' ) ).toEqual( -10800000 );
		expect( time.toMilliseconds( time.fromMilliseconds( -10800000, 'hh:mm:ss' ), 'hh:mm:ss' ) ).toEqual( -10800000 );
		expect( time.toMilliseconds( time.fromMilliseconds( -10800000, 'hh:mm:ss.sss' ), 'hh:mm:ss.sss' ) ).toEqual( -10800000 );

		/*
		 * fromSeconds() and toMilliseconds() for all formats
		 */

		// 930s = 15m 30s
		expect( time.toSeconds( time.fromSeconds( 930, 'mm:ss' ), 'mm:ss') ).toEqual( 930 );
		expect( time.toSeconds( time.fromSeconds( 930, 'mm:ss.sss' ), 'mm:ss.sss') ).toEqual( 930 );
		expect( time.toSeconds( time.fromSeconds( 930, 'hh:mm' ), 'hh:mm') ).toEqual( 930 );
		expect( time.toSeconds( time.fromSeconds( 930, 'hh:mm:ss' ), 'hh:mm:ss') ).toEqual( 930 );
		expect( time.toSeconds( time.fromSeconds( 930, 'hh:mm:ss.sss' ), 'hh:mm:ss.sss') ).toEqual( 930 );

		// 4850s = 1h 20m 50s
		expect( time.toSeconds( time.fromSeconds( 4850, 'mm:ss' ), 'mm:ss') ).toEqual( 4850 );
		expect( time.toSeconds( time.fromSeconds( 4850, 'mm:ss.sss' ), 'mm:ss.sss') ).toEqual( 4850 );
		expect( time.toSeconds( time.fromSeconds( 4850, 'hh:mm' ), 'hh:mm') ).toEqual( 4850 );
		expect( time.toSeconds( time.fromSeconds( 4850, 'hh:mm:ss' ), 'hh:mm:ss') ).toEqual( 4850 );
		expect( time.toSeconds( time.fromSeconds( 4850, 'hh:mm:ss.sss' ), 'hh:mm:ss.sss') ).toEqual( 4850 );

		// -300s = -5m
		expect( time.toSeconds( time.fromSeconds( -300, 'mm:ss' ), 'mm:ss') ).toEqual( -300 );
		expect( time.toSeconds( time.fromSeconds( -300, 'mm:ss.sss' ), 'mm:ss.sss') ).toEqual( -300 );
		expect( time.toSeconds( time.fromSeconds( -300, 'hh:mm' ), 'hh:mm') ).toEqual( -300 );
		expect( time.toSeconds( time.fromSeconds( -300, 'hh:mm:ss' ), 'hh:mm:ss') ).toEqual( -300 );
		expect( time.toSeconds( time.fromSeconds( -300, 'hh:mm:ss.sss' ), 'hh:mm:ss.sss') ).toEqual( -300 );
	} );
} );
