/**
 * Internal dependencies
 */
import { date, moment as momentUtil } from '@moderntribe/common/utils';
import moment from 'moment';

const {
	FORMATS,
	TODAY,
	timezones,
	timezonesAsSelectData,
	toNaturalLanguage,
	rangeToNaturalLanguage,
	labelToDate,
} = date;

jest.mock( '@moderntribe/common/utils/timezone', () => ( {
	getItems: () => [
		{
			options: [
				{
					key: 'America/Argentina/Buenos_Aires',
					text: 'Argentina - Buenos Aires',
				},
			],
		},
		{
			options: [
				{
					key: 'America/Argentina/Catamarca',
					text: 'Argentina - Catamarca',
				},
			],
		},
	],
} ) );

afterAll( () => {
	jest.unmock( '@moderntribe/common/utils/timezone' );
} );

describe( 'Tests for date.js', () => {
	test( 'formats', () => {
		const draft = {
			TIME: 'HH:mm:ss',
			DATE_TIME: 'YYYY-MM-DD HH:mm:ss',
			WP: {
				time: 'g:i a',
				time24Hr: 'H:i',
				date: 'F j, Y',
				datetime: 'F j, Y g:i a',
				dateNoYear: 'F j',
			},
			DATABASE: {
				date: 'Y-m-d',
				datetime: 'Y-m-d H:i:s',
				time: 'H:i:s',
			},
			TIMEZONE: {
				string: 'UTC',
			},
		};
		expect( FORMATS ).toEqual( draft );
	} );

	test( 'today', () => {
		const now = new Date();
		expect( TODAY ).toBeInstanceOf( Date );
		expect( TODAY ).hasOwnProperty( 'getDay' );
		expect( TODAY.getDay() ).toEqual( now.getDay() );
	} );

	test( 'timezones', () => {
		const expected = [
			{
				key: 'America/Argentina/Buenos_Aires',
				text: 'Argentina - Buenos Aires',
			},
			{
				key: 'America/Argentina/Catamarca',
				text: 'Argentina - Catamarca',
			},
		];
		expect( timezones() ).toEqual( expected );
	} );

	test( 'timezonesAsSelectData', () => {
		const expected = [
			{
				value: 'America/Argentina/Buenos_Aires',
				label: 'Argentina - Buenos Aires',
			},
			{
				value: 'America/Argentina/Catamarca',
				label: 'Argentina - Catamarca',
			},
		];
		expect( timezonesAsSelectData() ).toEqual( expected );
	} );

	describe( 'toNaturalLanguage', () => {
		it( 'Should return empty string when non parsed', () => {
			const defaultDetail = { month: '', day: '', year: '', time: '' };
			expect( toNaturalLanguage( {} ) ).toEqual( { moment: null, text: '', detail: defaultDetail, isValid: false } );
			expect( toNaturalLanguage( { date: undefined } ) ).toEqual( {
				moment: undefined,
				text: '',
				detail: defaultDetail,
				isValid: false
			} );
			expect( toNaturalLanguage( { date: '' } ) ).toEqual( {
				moment: '',
				text: '',
				detail: defaultDetail,
				isValid: false
			} );
		} );

		it( 'Should return the parsed date', () => {
			expect( toNaturalLanguage( { date: '2018-05-04 17:00:00' } ) )
				.toEqual( {
					moment: momentUtil.toMoment( '2018-05-04 17:00:00' ),
					text: 'May 4 2018  5:00 pm',
					detail: {
						month: 'May',
						day: '4',
						year: '2018',
						time: '5:00 pm',
					},
					isValid: true,
				} );
			expect( toNaturalLanguage( { date: '2019-12-24 12:00:00' } ) )
				.toEqual( {
					moment: momentUtil.toMoment( '2019-12-24 12:00:00' ),
					text: 'December 24 2019  12:00 pm',
					detail: {
						month: 'December',
						day: '24',
						year: '2019',
						time: '12:00 pm',
					},
					isValid: true,
				} );
		} );
	} );

	describe( 'rangeToNaturalLanguage', () => {
		it( 'Should return empty string when range is invalid', () => {
			expect( rangeToNaturalLanguage( null, null ) ).toBe( '' );
			expect( rangeToNaturalLanguage( undefined, undefined ) ).toBe( '' );
			expect( rangeToNaturalLanguage( '', '' ) ).toBe( '' );
		} );

		it( 'Should return only the start date', () => {
			expect( rangeToNaturalLanguage( '2019-12-24 12:00:00' ) )
				.toBe( 'December 24 2019 at 12:00 pm' );
			expect( rangeToNaturalLanguage( '2019-12-24 12:00:00', '' ) )
				.toBe( 'December 24 2019 at 12:00 pm' );
		} );

		it( 'Should return the range with time on same day', () => {
			expect( rangeToNaturalLanguage( '2019-12-24 12:00:00', '2019-12-24 17:00:00' ) )
				.toBe( 'December 24 2019 at 12:00 pm - 5:00 pm' );
		} );

		it( 'Should return the range without year on same year', () => {
			expect( rangeToNaturalLanguage( '2019-12-24 12:00:00', '2019-12-29 17:00:00' ) )
				.toBe( 'December 24 2019 at 12:00 pm - December 29 at 5:00 pm' );
		} );

		it( 'Should return the range on different years', () => {
			expect( rangeToNaturalLanguage( '2019-12-24 12:00:00', '2020-12-24 17:00:00' ) )
				.toBe( 'December 24 2019 at 12:00 pm - December 24 2020 at 5:00 pm' );
		} );
	} );

	describe( 'labelToDate - be aware chrono module is being mocked to avoid parsing', () => {
		test( 'Default value when date is invalid', () => {
			expect( labelToDate() ).toEqual( { start: null, end: null } );
		} );

		test( 'Valid dates', () => {
			const momentDate = moment( '12-25-1995', 'MM-DD-YYYY' );
			expect( labelToDate( momentDate ) )
				.toEqual( { start: momentUtil.toDateTime( momentDate ), end: momentUtil.toDateTime( momentDate ) } );
		} );
	} );

} );
