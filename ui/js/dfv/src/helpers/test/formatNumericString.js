import formatNumericString from '../formatNumericString';

describe( 'formatNumericString', () => {
	it( 'formats numeric string values correctly', () => {
		// 9,999.99
		expect( formatNumericString( '132323232320.321', 2, '9,999.99' ) ).toEqual( '132,323,232,320.32' );

		expect( formatNumericString( '132323232320.321', 0, '9,999.99' ) ).toEqual( '132,323,232,320' );

		expect( formatNumericString( '1000', 2, '9,999.99' ) ).toEqual( '1,000.00' );

		expect( formatNumericString( '1000', 0, '9,999.99' ) ).toEqual( '1,000' );

		expect( formatNumericString( '100', 2, '9,999.99' ) ).toEqual( '100.00' );

		expect( formatNumericString( '100', 3, '9,999.99' ) ).toEqual( '100.000' );

		expect( formatNumericString( '0', 0, '9,999.99' ) ).toEqual( '0' );

		// 9999.99
		expect( formatNumericString( '132323232320.321', 2, '9999.99' ) ).toEqual( '132323232320.32' );

		expect( formatNumericString( '132323232320.321', 0, '9999.99' ) ).toEqual( '132323232320' );

		expect( formatNumericString( '1000', 2, '9999.99' ) ).toEqual( '1000.00' );

		expect( formatNumericString( '1000', 0, '9999.99' ) ).toEqual( '1000' );

		expect( formatNumericString( '100', 2, '9999.99' ) ).toEqual( '100.00' );

		expect( formatNumericString( '100', 3, '9999.99' ) ).toEqual( '100.000' );

		expect( formatNumericString( '0', 0, '9999.99' ) ).toEqual( '0' );

		// 9.999,99
		expect( formatNumericString( '132323232320.321', 2, '9.999,99' ) ).toEqual( '132.323.232.320.321,00' );

		expect( formatNumericString( '132323232320.321', 0, '9.999,99' ) ).toEqual( '132.323.232.320.321' );

		expect( formatNumericString( '1000', 2, '9.999,99' ) ).toEqual( '1.000,00' );

		expect( formatNumericString( '1000', 0, '9.999,99' ) ).toEqual( '1.000' );

		expect( formatNumericString( '100', 2, '9.999,99' ) ).toEqual( '100,00' );

		expect( formatNumericString( '100', 3, '9.999,99' ) ).toEqual( '100,000' );

		expect( formatNumericString( '0', 0, '9.999,99' ) ).toEqual( '0' );

		// 9999,99
		expect( formatNumericString( '132323232320.321', 2, '9999,99' ) ).toEqual( '132323232320,32' );

		expect( formatNumericString( '132323232320.321', 0, '9999,99' ) ).toEqual( '132323232320' );

		expect( formatNumericString( '1000', 2, '9999,99' ) ).toEqual( '1000,00' );

		expect( formatNumericString( '1000', 0, '9999,99' ) ).toEqual( '1000' );

		expect( formatNumericString( '100', 2, '9999,99' ) ).toEqual( '100,00' );

		expect( formatNumericString( '100', 3, '9999,99' ) ).toEqual( '100,000' );

		expect( formatNumericString( '0', 0, '9999,99' ) ).toEqual( '0' );

		// 9'999.99
		expect( formatNumericString( '132323232320.321', 2, '9\'999.99' ) ).toEqual( '132\'323\'232\'320.32' );

		expect( formatNumericString( '132323232320.321', 0, '9\'999.99' ) ).toEqual( '132\'323\'232\'320' );

		expect( formatNumericString( '1000', 2, '9\'999.99' ) ).toEqual( '1\'000.00' );

		expect( formatNumericString( '1000', 0, '9\'999.99' ) ).toEqual( '1\'000' );

		expect( formatNumericString( '100', 2, '9\'999.99' ) ).toEqual( '100.00' );

		expect( formatNumericString( '100', 3, '9\'999.99' ) ).toEqual( '100.000' );

		expect( formatNumericString( '0', 0, '9\'999.99' ) ).toEqual( '0' );

		// 9 999,99
		expect( formatNumericString( '132323232320.321', 2, '9 999,99' ) ).toEqual( '132 323 232 320,32' );

		expect( formatNumericString( '132323232320.321', 0, '9 999,99' ) ).toEqual( '132 323 232 320' );

		expect( formatNumericString( '1000', 2, '9 999,99' ) ).toEqual( '1 000,00' );

		expect( formatNumericString( '1000', 0, '9 999,99' ) ).toEqual( '1 000' );

		expect( formatNumericString( '100', 2, '9 999,99' ) ).toEqual( '100,00' );

		expect( formatNumericString( '100', 3, '9 999,99' ) ).toEqual( '100,000' );

		expect( formatNumericString( '0', 0, '9 999,99' ) ).toEqual( '0' );

		// i18n
		expect( formatNumericString( '132323232320.321', 2, 'i18n' ) ).toEqual( '132,323,232,320.32' );

		expect( formatNumericString( '132323232320.321', 0, 'i18n' ) ).toEqual( '132,323,232,320' );

		expect( formatNumericString( '1000', 2, 'i18n' ) ).toEqual( '1,000.00' );

		expect( formatNumericString( '1000', 0, 'i18n' ) ).toEqual( '1,000' );

		expect( formatNumericString( '100', 2, 'i18n' ) ).toEqual( '100.00' );

		expect( formatNumericString( '100', 3, 'i18n' ) ).toEqual( '100.000' );

		expect( formatNumericString( '0', 0, 'i18n' ) ).toEqual( '0' );
	} );

	it( 'handles non-string and non-numeric values', () => {
		expect( formatNumericString( undefined, 0, 'i18n' ) ).toEqual( undefined );

		expect( formatNumericString( null, 2, 'i18n' ) ).toEqual( undefined );

		expect( formatNumericString( 123, 2, 'i18n' ) ).toEqual( undefined );

		expect( formatNumericString( '', 0, 'i18n' ) ).toEqual( undefined );

		expect( formatNumericString( 'abc', 0, 'i18n' ) ).toEqual( undefined );
	} );

	it( 'strips unneeded zero decimal values', () => {
		expect( formatNumericString( '1000', 2, '9,999.99', false ) ).toEqual( '1,000.00' );
		expect( formatNumericString( '1000', 2, '9,999.99', true ) ).toEqual( '1,000' );

		expect( formatNumericString( '1000', 2, '9 999,99', false ) ).toEqual( '1 000,00' );
		expect( formatNumericString( '1000', 2, '9 999,99', true ) ).toEqual( '1 000' );
	} );
} );
