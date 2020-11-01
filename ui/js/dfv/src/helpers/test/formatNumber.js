import formatNumber from '../formatNumber';

describe( 'formatNumber', () => {
	it( 'formats numbers correctly', () => {
		expect( formatNumber( 132323232320.321, 2, ',', '.' ) ).toEqual( '132.323.232.320,32' );

		expect( formatNumber( 132323232320.321, 0, ',', '.' ) ).toEqual( '132.323.232.320' );

		expect( formatNumber( 10.22, 2 ) ).toEqual( '10.22' );

		expect( formatNumber( 100 ) ).toEqual( '100' );

		expect( formatNumber( 1000, 2, ',', '.' ) ).toEqual( '1.000,00' );

		expect( formatNumber( 95 ** 12, 2 ) ).toEqual( '540,360,087,662,636,990,201,856' );

		expect( formatNumber( 123e5, 2 ) ).toEqual( '12,300,000.00' );

		expect( formatNumber( 123e5 ) ).toEqual( '12,300,000' );

		expect( formatNumber( 123e-5 ) ).toEqual( '0' );

		expect( formatNumber( 123e-15 ) ).toEqual( '0' );

		expect( formatNumber( 123e-15, 15 ) ).toEqual( '0.000000000000123' );
	} );
} );
