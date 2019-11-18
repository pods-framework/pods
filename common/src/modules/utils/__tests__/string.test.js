/**
 * Internal dependencies
 */
import {
	isTruthy,
	isFalsy,
	replaceWithObject,
	getWords,
	wordsAsList,
	normalize,
	toBlockName,
} from '@moderntribe/common/utils/string';

describe( 'Tests for string.js', () => {
	test( 'isTruthy', () => {
		expect( isTruthy( 'Sample string' ) ).toEqual( false );
		expect( isTruthy( '0' ) ).toEqual( false );
		expect( isTruthy( 'false' ) ).toEqual( false );
		expect( isTruthy( '1' ) ).toEqual( true );
		expect( isTruthy( 'yes' ) ).toEqual( true );
		expect( isTruthy( 'true' ) ).toEqual( true );
	} );

	test( 'isFalsy', () => {
		expect( isFalsy( 'Sample string' ) ).toEqual( false );
		expect( isFalsy( '1' ) ).toEqual( false );
		expect( isFalsy( 'true' ) ).toEqual( false );
		expect( isFalsy( '' ) ).toEqual( true );
		expect( isFalsy( '0' ) ).toEqual( true );
		expect( isFalsy( 'no' ) ).toEqual( true );
		expect( isFalsy( 'false' ) ).toEqual( true );
	} );

	test( 'replaceWithObject', () => {
		expect( replaceWithObject() ).toEqual( '' );
		expect( replaceWithObject( '', {} ) ).toEqual( '' );
		expect( replaceWithObject( 'abcd' ) ).toEqual( 'abcd' );
		expect( replaceWithObject( 'abcd', { z: 'a' } ) ).toEqual( 'abcd' );
		expect( replaceWithObject( 'abcd', { a: 'b', c: 'd' } ) ).toEqual( 'bbdd' );
		expect( replaceWithObject( 'abcd', { a: '', c: '' } ) ).toEqual( 'bd' );
	} );

	describe( 'getWords', () => {
		test( 'when strings are formed correctly', () => {
			expect( getWords( 'Modern Tribe' ) ).toEqual( [ 'Modern', 'Tribe' ] );
			expect( getWords( 'The Next Generation of Digital Agency' ) )
				.toEqual( [ 'The', 'Next', 'Generation', 'of', 'Digital', 'Agency' ] );
			expect( getWords( 'A list with numbers: 1, 2, 3' ) )
				.toEqual( [ 'A', 'list', 'with', 'numbers:', '1,', '2,', '3' ] );
		} );

		test( 'Words with multiple spaces on it', () => {
			expect( getWords( '       Modern       Tribe       ' ) ).toEqual( [ 'Modern', 'Tribe' ] );
		} );
	} );

	describe( 'applySeparatorsAsCheckboxList', () => {
		test( 'Single word no separator is applied', () => {
			expect( wordsAsList( [ 'Modern' ] ) ).toEqual( 'Modern' );
		} );

		test( 'Two words last separator is applied', () => {
			expect( wordsAsList( getWords( 'Modern Tribe' ) ) ).toEqual( 'Modern & Tribe' );
			expect( wordsAsList( getWords( 'Events Calendar' ), ',', ' - ' ) )
				.toEqual( 'Events - Calendar' );
		} );

		test( 'A large number of words', () => {
			expect(
				wordsAsList( [ 'Dog', 'Cat', 'Hamster', 'Parrot', 'Spider', 'Goldfish' ] ),
			).toEqual( 'Dog, Cat, Hamster, Parrot, Spider & Goldfish' );
		} );

		test( 'Custom separators', () => {
			expect(
				wordsAsList( [ 'Dog', 'Cat', 'Hamster', 'Parrot', 'Spider', 'Goldfish' ], ' - ', ' => ' ),
			).toEqual( 'Dog - Cat - Hamster - Parrot - Spider => Goldfish' );
		} );
	} );

	describe( 'normalize', () => {
		test( 'single words', () => {
			expect( normalize( 'modern' ) ).toEqual( 'modern' );
			expect( normalize( 'TRIBE' ) ).toEqual( 'tribe' );
		} );

		test( 'multiple words', () => {
			expect( normalize( 'Modern Tribe' ) ).toEqual( 'modern-tribe' );
			expect( normalize( 'https://theeventscalendar.com/' ) )
				.toEqual( 'httpstheeventscalendarcom' );
		} );

		test( 'Multiple spaces', () => {
			expect( normalize( '      modern      TriBe' ) ).toEqual( 'modern-tribe' );
		} );

		test( 'non words', () => {
			expect( normalize( '       12312321-,-.(()=^^ ¨¨:;:_¨¨Ç  *¿?=)(/&%$·"!.+' ) ).toEqual( '' );
		} );

		test( 'non strings types', () => {
			expect( normalize( undefined ) ).toBe( '' );
			expect( normalize( [] ) ).toBe( '' );
			expect( normalize( [] ) ).toBe( '' );
			expect( normalize( null ) ).toBe( '' );
			expect( normalize( 1 ) ).toBe( '' );
		} );
	} );

	describe( 'toBlockName', () => {
		test( 'words', () => {
			expect( toBlockName( 'modern tribe' ) )
				.toBe( 'moderntribe' );
			expect( toBlockName( 'https://theeventscalendar.com/' ) )
				.toBe( 'httpstheeventscalendarcom' );
		} );

		test( 'non valid characters of a block', () => {
			expect( toBlockName( '_ecp_custom_2' ) ).toBe( 'ecpcustom2' );
			expect( toBlockName( 'ecp-custom-2' ) ).toBe( 'ecp-custom-2' );
			expect( toBlockName( '„…–~~}][‚|#¢∞ecp-custom-2;:_¨¨Ç¨^^=)(/&%$·"!' ) )
				.toBe( 'ecp-custom-2' );
			expect( toBlockName( '„…–    ~~}]      [‚|#¢∞ecp-custom-2;:_¨    ¨Ç¨^^=)(/    &%$·"!' ) )
				.toBe( 'ecp-custom-2' );
		} );

		test( 'non strings types', () => {
			expect( toBlockName( undefined ) ).toBe( '' );
			expect( toBlockName( [] ) ).toBe( '' );
			expect( toBlockName( [] ) ).toBe( '' );
			expect( toBlockName( null ) ).toBe( '' );
			expect( toBlockName( 1 ) ).toBe( '' );
		} );
	} );
} );
