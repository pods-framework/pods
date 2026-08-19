import omit from '../omit';

describe( 'omit', () => {
	it( 'removes a single key', () => {
		const obj = { a: 1, b: 2, c: 3 };

		expect( omit( obj, [ 'b' ] ) ).toEqual( { a: 1, c: 3 } );
	} );

	it( 'removes multiple keys', () => {
		const obj = { a: 1, b: 2, c: 3, d: 4 };

		expect( omit( obj, [ 'a', 'c' ] ) ).toEqual( { b: 2, d: 4 } );
	} );

	it( 'returns a new equivalent object when no keys match', () => {
		const obj = { a: 1, b: 2 };
		const result = omit( obj, [ 'z' ] );

		expect( result ).not.toBe( obj );
		expect( result ).toEqual( { a: 1, b: 2 } );
	} );

	it( 'returns an empty object when all keys are omitted', () => {
		const obj = { a: 1 };

		expect( omit( obj, [ 'a' ] ) ).toEqual( {} );
	} );

	it( 'does not mutate the original object', () => {
		const obj = { a: 1, b: 2 };
		const result = omit( obj, [ 'a' ] );

		expect( obj ).toEqual( { a: 1, b: 2 } );
		expect( result ).toEqual( { b: 2 } );
	} );
} );
