/**
 * Internal dependencies
 */
import {
	parseChars,
	extractParts,
	parser,
	isFree,
} from '@moderntribe/common/utils/range';

describe( 'Tests for range.js', () => {
	test( 'parseChars', () => {
		expect( parseChars( '' ) ).toEqual( '' );
		expect( parseChars( '12312312321321 123123123123' ) ).toEqual( '12312312321321 123123123123' );
		expect( parseChars( '1"$!%$&/)=(=?^^Ç¨¨_:;' ) ).toEqual( '1' );
		expect( parseChars( ',.-¨¨*^^?=)(/&%$·"!ª12' ) ).toEqual( ',.-12' );
		expect( parseChars( '1-2-3-!"·$%&4-5-6$,.-' ) ).toEqual( '1-2-3-4-5-6,.-' );
	} );

	test( 'extractParts', () => {
		expect( extractParts( '' ) ).toEqual( [] );
		expect( extractParts( '12' ) ).toEqual( [ '12' ] );
		expect( extractParts( '12 - 23' ) ).toEqual( [ '12', '23' ] );
		expect( extractParts( '12.23 - ' ) ).toEqual( [ '12.23' ] );
		expect( extractParts( '12.23 - 5,10' ) ).toEqual( [ '12.23', '5.10' ] );
		expect( extractParts( '12.23 - - - - - 5,10' ) ).toEqual( [ '12.23', '5.10' ] );
		expect( extractParts( '- - - - - 12.23 - 5,10' ) ).toEqual( [ '12.23', '5.10' ] );
		expect( extractParts( '......,,,,12.23 - 5,10' ) ).toEqual( [ '12.23', '5.10' ] );
		expect( extractParts( '.12.23 - 5,10.....,,,,' ) ).toEqual( [ '12.23', '5.10' ] );
		expect( extractParts( '12.2.....3 ,-. 5,10' ) ).toEqual( [ '12.20', '5.10' ] );
		expect( extractParts( '1-2-3-!"·$%&4-5-6$,.-' ) ).toEqual( [ '1', '2' ] );
		expect( extractParts( '12.23 ,-. ----5,,,,,,10' ) ).toEqual( [ '12.23', '5' ] );
	} );

	test( 'parser', () => {
		expect( parser( '' ) ).toEqual( '' );
		expect( parser( 'cupidatat occaecat' ) ).toEqual( '' );
		expect( parser( 'cupidatat 12 occaecat - 1,2' ) ).toEqual( '1.20 - 12' );
		expect( parser( '1,2 cupidatat 12 occaecat' ) ).toEqual( '1.20' );
		expect( parser( '1-2-1-1-1-1' ) ).toEqual( '1 - 2' );
		expect( parser( '......,,,,12.23 - 5,10' ) ).toEqual( '5.10 - 12.23' );
		expect( parser( '2.2.....3 ,-. 2,10' ) ).toEqual( '2.10 - 2.20' );
		expect( parser( '12.23 ,-. ----5,,,,,,10' ) ).toEqual( '5 - 12.23' );
		expect( parser( '1-2-3-!"·$%&4-5-6$,.-' ) ).toEqual( '1 - 2' );
		expect( parser( ',.-¨¨*^^?=)(/&%$·"!ª12' ) ).toEqual( '12' );
		expect( parser( '10 - 10' ) ).toEqual( '10' );
		expect( parser( '0' ) ).toEqual( '' );
		expect( parser( '0.0' ) ).toEqual( '' );
		expect( parser( '0 -' ) ).toEqual( '' );
		expect( parser( '0 - 0' ) ).toEqual( '' );
		expect( parser( '0.0 - 0' ) ).toEqual( '' );
		expect( parser( '0.0 - 0.5' ) ).toEqual( '0.00 - 0.50' );
	} );

	test( 'isFree', () => {
		expect( isFree( '' ) ).toEqual( false );
		expect( isFree( '0.12' ) ).toEqual( false );
		expect( isFree( '0 - 0.12' ) ).toEqual( false );
		expect( isFree( '0.12 - 0' ) ).toEqual( false );
		expect( isFree( '0' ) ).toEqual( true );
		expect( isFree( '0.0' ) ).toEqual( true );
		expect( isFree( '0 - 0' ) ).toEqual( true );
		expect( isFree( '0.0 - 0.0' ) ).toEqual( true );
	} );
} );
