import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';
import { getPodName, getPodMetaValue } from '../selectors';

describe( 'podMeta selectors', () => {
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
