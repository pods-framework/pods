/* global global */
import {
	formatNumberWithPodsFormat,
} from '../formatNumberWithPodsFormat.js';

describe( 'formatNumberWithPodsFormat.js', () => {
	beforeAll( () => {
		global.window = {
			...global.window || {},
			podsDFVConfig: {
				wp_locale: {
					number_format: {
						thousands_sep: ',',
						decimal_point: '.',
					},
				},
			},
		};
	} );

	it( 'formats numeric string values correctly', () => {
		// 9,999.99
		expect( formatNumberWithPodsFormat( '132323232320.321', '9,999.99' ) ).toEqual( '132,323,232,320.321' );

		expect( formatNumberWithPodsFormat( '132323232320.32', '9,999.99' ) ).toEqual( '132,323,232,320.32' );

		expect( formatNumberWithPodsFormat( '1000', '9,999.99' ) ).toEqual( '1,000' );

		expect( formatNumberWithPodsFormat( '1000', '9,999.99' ) ).toEqual( '1,000' );

		expect( formatNumberWithPodsFormat( '100', '9,999.99' ) ).toEqual( '100' );

		expect( formatNumberWithPodsFormat( '100', '9,999.99' ) ).toEqual( '100' );

		expect( formatNumberWithPodsFormat( '0', '9,999.99' ) ).toEqual( '0' );

		// 9999.99
		expect( formatNumberWithPodsFormat( '132323232320.321', '9999.99' ) ).toEqual( '132323232320.321' );

		expect( formatNumberWithPodsFormat( '1000', '9999.99' ) ).toEqual( '1000' );

		expect( formatNumberWithPodsFormat( '1000.10', '9999.99' ) ).toEqual( '1000.1' );

		expect( formatNumberWithPodsFormat( '100', '9999.99' ) ).toEqual( '100' );

		expect( formatNumberWithPodsFormat( '100', '9999.99' ) ).toEqual( '100' );

		expect( formatNumberWithPodsFormat( '0', '9999.99' ) ).toEqual( '0' );

		// 9.999,99
		expect( formatNumberWithPodsFormat( '132323232320.321', '9.999,99' ) ).toEqual( '132.323.232.320.321' );

		expect( formatNumberWithPodsFormat( '1000', '9.999,99' ) ).toEqual( '1.000' );

		expect( formatNumberWithPodsFormat( '100', '9.999,99' ) ).toEqual( '100' );

		expect( formatNumberWithPodsFormat( '0', '9.999,99' ) ).toEqual( '0' );

		// 9999,99
		expect( formatNumberWithPodsFormat( '132323232320.321', '9999,99' ) ).toEqual( '132323232320,321' );

		expect( formatNumberWithPodsFormat( '1000', '9999,99' ) ).toEqual( '1000' );

		expect( formatNumberWithPodsFormat( '100', '9999,99' ) ).toEqual( '100' );

		expect( formatNumberWithPodsFormat( '0', '9999,99' ) ).toEqual( '0' );

		// 9'999.99
		expect( formatNumberWithPodsFormat( '132323232320.321', '9\'999.99' ) ).toEqual( '132\'323\'232\'320.321' );

		expect( formatNumberWithPodsFormat( '1000', '9\'999.99' ) ).toEqual( '1\'000' );

		expect( formatNumberWithPodsFormat( '100', '9\'999.99' ) ).toEqual( '100' );

		expect( formatNumberWithPodsFormat( '0', '9\'999.99' ) ).toEqual( '0' );

		// 9 999,99
		expect( formatNumberWithPodsFormat( '132323232320.321', '9 999,99' ) ).toEqual( '132 323 232 320,321' );

		expect( formatNumberWithPodsFormat( '1000', '9 999,99' ) ).toEqual( '1 000' );

		expect( formatNumberWithPodsFormat( '100', '9 999,99' ) ).toEqual( '100' );

		expect( formatNumberWithPodsFormat( '0', '9 999,99' ) ).toEqual( '0' );

		// i18n
		expect( formatNumberWithPodsFormat( '132323232320.321', 'i18n' ) ).toEqual( '132,323,232,320.321' );

		expect( formatNumberWithPodsFormat( '1000', 'i18n' ) ).toEqual( '1,000' );

		expect( formatNumberWithPodsFormat( '100', 'i18n' ) ).toEqual( '100' );

		expect( formatNumberWithPodsFormat( '0', 'i18n' ) ).toEqual( '0' );
	} );

	it( 'handles non-string and non-numeric values', () => {
		expect( formatNumberWithPodsFormat( undefined, 'i18n' ) ).toEqual( '0' );

		expect( formatNumberWithPodsFormat( null, 'i18n' ) ).toEqual( '0' );

		expect( formatNumberWithPodsFormat( 123, 'i18n' ) ).toEqual( '123' );

		expect( formatNumberWithPodsFormat( '', 'i18n' ) ).toEqual( '0' );

		expect( formatNumberWithPodsFormat( 'abc', 'i18n' ) ).toEqual( undefined );
	} );

	it( 'strips unneeded zero decimal values', () => {
		expect( formatNumberWithPodsFormat( '1000', '9,999.99', false ) ).toEqual( '1,000' );
		expect( formatNumberWithPodsFormat( '1000', '9,999.99', true ) ).toEqual( '1,000' );

		expect( formatNumberWithPodsFormat( '1000', '9 999,99', false ) ).toEqual( '1 000' );
		expect( formatNumberWithPodsFormat( '1000', '9 999,99', true ) ).toEqual( '1 000' );
	} );
} );
