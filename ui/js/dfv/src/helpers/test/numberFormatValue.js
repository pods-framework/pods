import numberFormatValue from '../numberFormatValue';

describe( 'numberFormatValue', () => {
	it( 'formats numbers correctly', () => {
		// 9,999.99
		expect( numberFormatValue( 132323232320.321, 2, '9,999.99' ) ).toEqual( '132,323,232,320.32' );

		expect( numberFormatValue( 132323232320.321, 0, '9,999.99' ) ).toEqual( '132,323,232,320' );

		expect( numberFormatValue( 1000, 2, '9,999.99' ) ).toEqual( '1,000.00' );

		expect( numberFormatValue( 1000, 0, '9,999.99' ) ).toEqual( '1,000' );

		expect( numberFormatValue( 100, 2, '9,999.99' ) ).toEqual( '100.00' );

		expect( numberFormatValue( 100, 3, '9,999.99' ) ).toEqual( '100.000' );

		expect( numberFormatValue( 0, 0, '9,999.99' ) ).toEqual( '0' );

		// 9999.99
		expect( numberFormatValue( 132323232320.321, 2, '9999.99' ) ).toEqual( '132323232320.32' );

		expect( numberFormatValue( 132323232320.321, 0, '9999.99' ) ).toEqual( '132323232320' );

		expect( numberFormatValue( 1000, 2, '9999.99' ) ).toEqual( '1000.00' );

		expect( numberFormatValue( 1000, 0, '9999.99' ) ).toEqual( '1000' );

		expect( numberFormatValue( 100, 2, '9999.99' ) ).toEqual( '100.00' );

		expect( numberFormatValue( 100, 3, '9999.99' ) ).toEqual( '100.000' );

		expect( numberFormatValue( 0, 0, '9999.99' ) ).toEqual( '0' );

		// 9.999,99
		expect( numberFormatValue( 132323232320.321, 2, '9.999,99' ) ).toEqual( '132.323.232.320,32' );

		expect( numberFormatValue( 132323232320.321, 0, '9.999,99' ) ).toEqual( '132.323.232.320' );

		expect( numberFormatValue( 1000, 2, '9.999,99' ) ).toEqual( '1.000,00' );

		expect( numberFormatValue( 1000, 0, '9.999,99' ) ).toEqual( '1.000' );

		expect( numberFormatValue( 100, 2, '9.999,99' ) ).toEqual( '100,00' );

		expect( numberFormatValue( 100, 3, '9.999,99' ) ).toEqual( '100,000' );

		expect( numberFormatValue( 0, 0, '9.999,99' ) ).toEqual( '0' );

		// 9999,99
		expect( numberFormatValue( 132323232320.321, 2, '9999,99' ) ).toEqual( '132323232320,32' );

		expect( numberFormatValue( 132323232320.321, 0, '9999,99' ) ).toEqual( '132323232320' );

		expect( numberFormatValue( 1000, 2, '9999,99' ) ).toEqual( '1000,00' );

		expect( numberFormatValue( 1000, 0, '9999,99' ) ).toEqual( '1000' );

		expect( numberFormatValue( 100, 2, '9999,99' ) ).toEqual( '100,00' );

		expect( numberFormatValue( 100, 3, '9999,99' ) ).toEqual( '100,000' );

		expect( numberFormatValue( 0, 0, '9999,99' ) ).toEqual( '0' );

		// 9'999.99
		expect( numberFormatValue( 132323232320.321, 2, '9\'999.99' ) ).toEqual( '132\'323\'232\'320.32' );

		expect( numberFormatValue( 132323232320.321, 0, '9\'999.99' ) ).toEqual( '132\'323\'232\'320' );

		expect( numberFormatValue( 1000, 2, '9\'999.99' ) ).toEqual( '1\'000.00' );

		expect( numberFormatValue( 1000, 0, '9\'999.99' ) ).toEqual( '1\'000' );

		expect( numberFormatValue( 100, 2, '9\'999.99' ) ).toEqual( '100.00' );

		expect( numberFormatValue( 100, 3, '9\'999.99' ) ).toEqual( '100.000' );

		expect( numberFormatValue( 0, 0, '9\'999.99' ) ).toEqual( '0' );

		// 9 999,99
		expect( numberFormatValue( 132323232320.321, 2, '9 999,99' ) ).toEqual( '132 323 232 320,32' );

		expect( numberFormatValue( 132323232320.321, 0, '9 999,99' ) ).toEqual( '132 323 232 320' );

		expect( numberFormatValue( 1000, 2, '9 999,99' ) ).toEqual( '1 000,00' );

		expect( numberFormatValue( 1000, 0, '9 999,99' ) ).toEqual( '1 000' );

		expect( numberFormatValue( 100, 2, '9 999,99' ) ).toEqual( '100,00' );

		expect( numberFormatValue( 100, 3, '9 999,99' ) ).toEqual( '100,000' );

		expect( numberFormatValue( 0, 0, '9 999,99' ) ).toEqual( '0' );

		// i18n
		expect( numberFormatValue( 132323232320.321, 2, 'i18n' ) ).toEqual( '132,323,232,320.32' );

		expect( numberFormatValue( 132323232320.321, 0, 'i18n' ) ).toEqual( '132,323,232,320' );

		expect( numberFormatValue( 1000, 2, 'i18n' ) ).toEqual( '1,000.00' );

		expect( numberFormatValue( 1000, 0, 'i18n' ) ).toEqual( '1,000' );

		expect( numberFormatValue( 100, 2, 'i18n' ) ).toEqual( '100.00' );

		expect( numberFormatValue( 100, 3, 'i18n' ) ).toEqual( '100.000' );

		expect( numberFormatValue( 0, 0, 'i18n' ) ).toEqual( '0' );
	} );
} );
