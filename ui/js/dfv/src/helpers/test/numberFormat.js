import numberFormat from '../numberFormat';

describe( 'numberFormat', () => {
	it( 'formats numbers correctly', () => {
		expect( numberFormat( 132323232320.321, 2, ',', '.' ) ).toEqual( '132.323.232.320,32' );

		expect( numberFormat( 132323232320.321, 0, ',', '.' ) ).toEqual( '132.323.232.320' );

		expect( numberFormat( 10.22, 2 ) ).toEqual( '10.22' );

		expect( numberFormat( 100 ) ).toEqual( '100' );

		expect( numberFormat( 1000, 2, ',', '.' ) ).toEqual( '1.000,00' );

		expect( numberFormat( 95 ** 12, 2 ) ).toEqual( '540,360,087,662,636,990,201,856' );

		expect( numberFormat( 123e5, 2 ) ).toEqual( '12,300,000.00' );

		expect( numberFormat( 123e5 ) ).toEqual( '12,300,000' );

		expect( numberFormat( 123e-5 ) ).toEqual( '0' );

		expect( numberFormat( 123e-15 ) ).toEqual( '0' );

		expect( numberFormat( 123e-15, 15 ) ).toEqual( '0.000000000000123' );
	} );
} );
