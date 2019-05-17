import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';
import {
	getState,
	getPodName,
	getPodMetaValue,
	getFields,
} from '../selectors';

describe( 'selectors', () => {

	describe( 'getState()', () => {
		it( 'Should return the full state', () => {
			const state = deepFreeze( {
				foo: {
					'xyzzy': 42,
					'plugh': false
				},
				bar: {
					name: 'bob',
					relationship: 'your uncle'
				},
				baz: [ 0, 1, 2 ]
			} );
			const result = getState( state );

			expect( result ).toBeDefined();
			expect( result ).toEqual( state );
		} );
	} );

	describe( 'podMeta', () => {
		describe( 'getPodName()', () => {
			it( 'Should return the Pod name', () => {
				const state = deepFreeze(
					paths.POD_META.createTree( { name: 'plugh' } )
				);
				const result = getPodName( state );
				const expected = paths.POD_NAME.getFrom( state );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getPodMetaValue()', () => {
			it( 'Should return the meta value', () => {
				const key = 'foo';
				const expected = 'bar';
				const state = deepFreeze(
					paths.POD_META.createTree( { [ key ]: expected } )
				);
				const result = getPodMetaValue( state, key );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );

	describe( 'fields', () => {
		describe( 'getFields()', () => {
			it( 'Should return the fields array', () => {
				const fields = [
					{ name: 'field1', label: 'label1' },
					{ name: 'field2', label: 'label2' },
					{ name: 'field3', label: 'label3' },
				];
				const state = deepFreeze(
					paths.FIELDS.createTree( fields )
				);
				const result = getFields( state );
				const expected = paths.FIELDS.getFrom( state );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );
} );
